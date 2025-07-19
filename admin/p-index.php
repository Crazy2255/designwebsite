<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Handle product deletion
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    
    // Get product images before deletion
    $stmt = $conn->prepare("SELECT image FROM product_images WHERE product_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $images = $result->fetch_all(MYSQLI_ASSOC);
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Delete product relationships
        $tables = ['product_images', 'product_colors', 'product_materials', 'product_marbles', 'product_wooden'];
        foreach ($tables as $table) {
            $stmt = $conn->prepare("DELETE FROM $table WHERE product_id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
        }
        
        // Delete product
        $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        // Delete product images from storage
        foreach ($images as $image) {
            $image_path = 'uploads/products/' . $image['image'];
            if (file_exists($image_path)) {
                unlink($image_path);
            }
        }
        
        $conn->commit();
        $_SESSION['success'] = "Product deleted successfully!";
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = "Failed to delete product!";
    }
    
    header("Location: p-index.php");
    exit();
}

// Fetch all products with their relationships
$query = "SELECT p.*, 
          c.name as category_name,
          s.name as subcategory_name,
          GROUP_CONCAT(DISTINCT pi.image) as images,
          GROUP_CONCAT(DISTINCT col.name) as colors,
          GROUP_CONCAT(DISTINCT m.name) as materials,
          GROUP_CONCAT(DISTINCT mar.name) as marbles,
          GROUP_CONCAT(DISTINCT w.name) as wooden
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
          GROUP BY p.id
          ORDER BY p.created_at ASC";
$result = $conn->query($query);
$products = $result->fetch_all(MYSQLI_ASSOC);

// Set the current directory for proper includes
$currentDirectory = '';
?>

<?php include 'components/header.php'; ?>

<div id="layoutSidenav">
    <?php include 'components/sidebar.php'; ?>
    <div id="layoutSidenav_content">
        <main>
            <div class="container-fluid px-4">
                <!-- Breadcrumb -->
                <nav aria-label="breadcrumb" class="mt-4">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Products</li>
                    </ol>
                </nav>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h1 class="h3 mb-0">Products</h1>
                    <a href="p-create.php" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Add New Product
                    </a>
                </div>

                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php 
                        echo $_SESSION['success'];
                        unset($_SESSION['success']);
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php 
                        echo $_SESSION['error'];
                        unset($_SESSION['error']);
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="productsTable" class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th width="80">ID</th>
                                        <th width="100">Image</th>
                                        <th>Name</th>
                                        <th>Category</th>
                                        <th>SKU</th>
                                        <th>Price</th>
                                        <th>Quantity</th>
                                        <th width="100">Status</th>
                                        <th width="180">Created At</th>
                                        <th width="150">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($products as $product): ?>
                                        <tr>
                                            <td><?php echo $product['id']; ?></td>
                                            <td class="text-center">
                                                <?php 
                                                $images = explode(',', $product['images']);
                                                if (!empty($images[0]) && file_exists('uploads/products/' . $images[0])): 
                                                ?>
                                                    <img src="uploads/products/<?php echo $images[0]; ?>" 
                                                         alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                                         class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover;">
                                                <?php else: ?>
                                                    <img src="assets/img/no-image.png" 
                                                         alt="No Image" 
                                                         class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover;">
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                                            <td>
                                                <?php 
                                                echo htmlspecialchars($product['category_name']);
                                                if (!empty($product['subcategory_name'])) {
                                                    echo ' > ' . htmlspecialchars($product['subcategory_name']);
                                                }
                                                ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($product['sku']); ?></td>
                                            <td>
                                                <?php 
                                                if (!empty($product['price'])) {
                                                    echo number_format($product['price'], 2);
                                                } else {
                                                    echo '-';
                                                }
                                                ?>
                                            </td>
                                            <td><?php echo $product['quantity']; ?></td>
                                            <td>
                                                <span class="badge <?php echo $product['status'] == 'active' ? 'bg-success' : 'bg-danger'; ?>">
                                                    <?php echo ucfirst($product['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('Y-m-d H:i:s', strtotime($product['created_at'])); ?></td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="p-edit.php?id=<?php echo $product['id']; ?>" 
                                                       class="btn btn-sm btn-primary" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="p-view.php?id=<?php echo $product['id']; ?>" 
                                                       class="btn btn-sm btn-info" title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-danger" 
                                                            onclick="deleteProduct(<?php echo $product['id']; ?>)" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
        <?php include 'components/footer.php'; ?>
    </div>
</div>

<script>
// Initialize DataTable
$(document).ready(function() {
    $('#productsTable').DataTable({
        dom: 'Bfrtip',
        buttons: [
            'copy', 'csv', 'excel', 'pdf', 'print'
        ],
        pageLength: 10,
        order: [[0, 'desc']],
        columnDefs: [
            { orderable: false, targets: [1, 9] },
            { searchable: false, targets: [1, 9] }
        ],
        responsive: true
    });
});

// Delete confirmation
function deleteProduct(id) {
    if (confirm('Are you sure you want to delete this product? This will also delete all related images and relationships.')) {
        window.location.href = 'p-index.php?delete=' + id;
    }
}
</script> 