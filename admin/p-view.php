<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Check if product ID is provided
if (!isset($_GET['id'])) {
    $_SESSION['error'] = "Product ID is required!";
    header("Location: p-index.php");
    exit();
}

$id = intval($_GET['id']);

// Fetch product details with all relationships and images
$query = "SELECT p.*, 
          c.name as category_name,
          s.name as subcategory_name,
          GROUP_CONCAT(DISTINCT pi.image) as images,
          GROUP_CONCAT(DISTINCT CONCAT(col.name, ':', col.image)) as colors,
          GROUP_CONCAT(DISTINCT CONCAT(m.name, ':', m.image)) as materials,
          GROUP_CONCAT(DISTINCT CONCAT(mar.name, ':', mar.image)) as marbles,
          GROUP_CONCAT(DISTINCT CONCAT(w.name, ':', w.image)) as wooden
          FROM products p
          LEFT JOIN categories c ON p.category_id = c.id
          LEFT JOIN subcategories s ON p.subcategory_id = s.id
          LEFT JOIN product_images pi ON p.id = pi.product_id
          LEFT JOIN product_colors pc ON p.id = pc.product_id
          LEFT JOIN colors col ON pc.color_id = col.id
          LEFT JOIN product_materials pm ON p.id = pm.product_id
          LEFT JOIN materials m ON pm.material_id = m.id
          LEFT JOIN product_marbles pmar ON p.id = pmar.product_id
          LEFT JOIN marbles mar ON pmar.marble_id = mar.id
          LEFT JOIN product_wooden pw ON p.id = pw.product_id
          LEFT JOIN wooden w ON pw.wooden_id = w.id
          WHERE p.id = ?
          GROUP BY p.id";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    $_SESSION['error'] = "Product not found!";
    header("Location: p-index.php");
    exit();
}

// Process the concatenated fields
$images = $product['images'] ? explode(',', $product['images']) : [];

// Process colors with images
$colors = [];
if ($product['colors']) {
    foreach (explode(',', $product['colors']) as $color) {
        $parts = explode(':', $color);
        if (count($parts) == 2) {
            $colors[] = ['name' => $parts[0], 'image' => $parts[1]];
        }
    }
}

// Process materials with images
$materials = [];
if ($product['materials']) {
    foreach (explode(',', $product['materials']) as $material) {
        $parts = explode(':', $material);
        if (count($parts) == 2) {
            $materials[] = ['name' => $parts[0], 'image' => $parts[1]];
        }
    }
}

// Process marbles with images
$marbles = [];
if ($product['marbles']) {
    foreach (explode(',', $product['marbles']) as $marble) {
        $parts = explode(':', $marble);
        if (count($parts) == 2) {
            $marbles[] = ['name' => $parts[0], 'image' => $parts[1]];
        }
    }
}

// Process wooden finishes with images
$wooden = [];
if ($product['wooden']) {
    foreach (explode(',', $product['wooden']) as $wood) {
        $parts = explode(':', $wood);
        if (count($parts) == 2) {
            $wooden[] = ['name' => $parts[0], 'image' => $parts[1]];
        }
    }
}

$currentDirectory = '';
?>

<?php include 'components/header.php'; ?>
<style>
    .container, .container-lg, .container-md, .container-sm, .container-xl, .container-xxl {
        max-width: 1320px;
    }
    .container-fluid, .container-lg, .container-md, .container-sm, .container-xl, .container-xxl {
        max-width: 1440px;
    }
</style>

<div id="layoutSidenav">
    <?php include 'components/sidebar.php'; ?>
    <div id="layoutSidenav_content">
        <main>
            <div class="container-fluid">
                <!-- Breadcrumb -->
                <nav aria-label="breadcrumb" class="mt-4">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="p-index.php">Products</a></li>
                        <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($product['name']); ?></li>
                    </ol>
                </nav>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3 mb-0">Product Details</h1>
                    <div>
                        <a href="p-edit.php?id=<?php echo $product['id']; ?>" class="btn btn-primary me-2">
                            <i class="fas fa-edit me-2"></i>Edit Product
                        </a>
                        <a href="p-index.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back to List
                        </a>
                    </div>
                </div>

                <div class="row">
                    <!-- Product Images -->
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Product Images</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <?php foreach ($images as $image): ?>
                                        <div class="col-6 col-sm-4 mb-3">
                                            <a href="uploads/products/<?php echo htmlspecialchars($image); ?>" 
                                               data-fancybox="product-gallery"
                                               class="d-block">
                                                <img src="uploads/products/<?php echo htmlspecialchars($image); ?>" 
                                                     alt="<?php echo htmlspecialchars($product['name']); ?>"
                                                     class="img-fluid rounded shadow-sm"
                                                     style="width: 100%; height: 150px; object-fit: cover;">
                                            </a>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Basic Product Information -->
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="card-title mb-0">Basic Information</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped mb-0">
                                        <tr>
                                            <th class="bg-light" width="150">SKU</th>
                                            <td><strong><?php echo htmlspecialchars($product['sku']); ?></strong></td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">Category</th>
                                            <td>
                                                <span class="badge bg-primary"><?php echo htmlspecialchars($product['category_name']); ?></span>
                                                <?php if ($product['subcategory_name']): ?>
                                                    <i class="fas fa-chevron-right mx-2"></i>
                                                    <span class="badge bg-secondary"><?php echo htmlspecialchars($product['subcategory_name']); ?></span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">Price</th>
                                            <td>
                                                <span class="text-success fw-bold">
                                                    ₹<?php echo $product['price'] ? number_format($product['price'], 2) : '0.00'; ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">Quantity</th>
                                            <td>
                                                <?php if ($product['quantity'] > 0): ?>
                                                    <span class="badge bg-success"><?php echo $product['quantity']; ?> in stock</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Out of stock</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Colors -->
                    <?php if (!empty($colors)): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <div class="card-header bg-info text-white">
                                <h5 class="card-title mb-0">Available Colors</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <?php foreach ($colors as $color): ?>
                                        <div class="col-4">
                                            <div class="color-option text-center">
                                                <img src="uploads/colors/<?php echo htmlspecialchars($color['image']); ?>" 
                                                     alt="<?php echo htmlspecialchars($color['name']); ?>"
                                                     class="img-fluid rounded mb-2"
                                                     style="width: 100%; height: 100px; object-fit: cover;">
                                                <span class="d-block"><?php echo htmlspecialchars($color['name']); ?></span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>


                    <?php if (!empty($wooden)): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <div class="card-header bg-success text-white">
                                <h5 class="card-title mb-0">Wooden Finishes</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <?php foreach ($wooden as $wood): ?>
                                        <div class="col-4">
                                            <div class="wooden-option text-center">
                                                <img src="uploads/wooden/<?php echo htmlspecialchars($wood['image']); ?>" 
                                                     alt="<?php echo htmlspecialchars($wood['name']); ?>"
                                                     class="img-fluid rounded mb-2"
                                                     style="width: 100%; height: 100px; object-fit: cover;">
                                                <span class="d-block"><?php echo htmlspecialchars($wood['name']); ?></span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>


                        <!-- Marble Finishes -->
                        <?php if (!empty($marbles)): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <div class="card-header bg-secondary text-white">
                                <h5 class="card-title mb-0">Marble/Stone Finishes</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <?php foreach ($marbles as $marble): ?>
                                        <div class="col-4">
                                            <div class="marble-option text-center">
                                                <img src="uploads/marbles/<?php echo htmlspecialchars($marble['image']); ?>" 
                                                     alt="<?php echo htmlspecialchars($marble['name']); ?>"
                                                     class="img-fluid rounded mb-2"
                                                     style="width: 100%; height: 100px; object-fit: cover;">
                                                <span class="d-block"><?php echo htmlspecialchars($marble['name']); ?></span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>


                    <!-- Materials -->
                    <?php if (!empty($materials)): ?>
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header bg-warning">
                                <h5 class="card-title mb-0">Available Materials</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <?php foreach ($materials as $material): ?>
                                        <div class="col-4">
                                            <div class="material-option text-center">
                                                <img src="uploads/materials/<?php echo htmlspecialchars($material['image']); ?>" 
                                                     alt="<?php echo htmlspecialchars($material['name']); ?>"
                                                     class="img-fluid rounded mb-2"
                                                     style="object-fit: cover;">
                                                <span class="d-block"><?php echo htmlspecialchars($material['name']); ?></span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Wooden Finishes -->
                 

                
                </div>
            </div>
        </main>
        <?php include 'components/footer.php'; ?>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.css" />
<script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.umd.js"></script>
<script>
$(document).ready(function() {
    // Initialize tooltips
    $('[data-bs-toggle="tooltip"]').tooltip();
    
    // Initialize Fancybox for image gallery
    Fancybox.bind("[data-fancybox]", {
        // Your custom options
    });
});
</script>

<style>
.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}
.card-header {
    border-bottom: 0;
}
.color-option, .material-option, .wooden-option, .marble-option {
    transition: transform 0.2s;
    cursor: pointer;
}
.color-option:hover, .material-option:hover, .wooden-option:hover, .marble-option:hover {
    transform: translateY(-5px);
}
.table th {
    font-weight: 600;
}
.badge {
    font-weight: 500;
    padding: 0.5em 1em;
}
</style> 