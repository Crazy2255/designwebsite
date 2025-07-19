<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Fetch active categories
$categories = [];
$stmt = $conn->prepare("SELECT id, name FROM categories WHERE status = 'active' ORDER BY name");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $categories[] = $row;
}

// Fetch active subcategories
$subcategories = [];
$stmt = $conn->prepare("SELECT id, name, category_id FROM subcategories WHERE status = 'active' ORDER BY name");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $subcategories[] = $row;
}

// Fetch active colors
$colors = [];
$stmt = $conn->prepare("SELECT id, name FROM colors WHERE status = 'active' ORDER BY name");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $colors[] = $row;
}

// Fetch active materials
$materials = [];
$stmt = $conn->prepare("SELECT id, name FROM materials WHERE status = 'active' ORDER BY name");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $materials[] = $row;
}

// Fetch active marbles
$marbles = [];
$stmt = $conn->prepare("SELECT id, name FROM marbles WHERE status = 'active' ORDER BY name");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $marbles[] = $row;
}

// Fetch active wooden items
$wooden = [];
$stmt = $conn->prepare("SELECT id, name FROM wooden WHERE status = 'active' ORDER BY name");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $wooden[] = $row;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $category_id = intval($_POST['category_id']);
    $subcategory_id = !empty($_POST['subcategory_id']) ? intval($_POST['subcategory_id']) : null;
    $description = trim($_POST['description']);
    $sku = trim($_POST['sku']);
    $price = !empty($_POST['price']) ? floatval($_POST['price']) : null;
    $quantity = !empty($_POST['quantity']) ? intval($_POST['quantity']) : 0;
    $length = !empty($_POST['length']) ? floatval($_POST['length']) : null;
    $width = !empty($_POST['width']) ? floatval($_POST['width']) : null;
    $height = !empty($_POST['height']) ? floatval($_POST['height']) : null;
    $weight = !empty($_POST['weight']) ? floatval($_POST['weight']) : null;
    $status = isset($_POST['status']) ? 'active' : 'inactive';
    
    // Selected relationships
    $selected_colors = isset($_POST['colors']) ? $_POST['colors'] : [];
    $selected_materials = isset($_POST['materials']) ? $_POST['materials'] : [];
    $selected_marbles = isset($_POST['marbles']) ? $_POST['marbles'] : [];
    $selected_wooden = isset($_POST['wooden']) ? $_POST['wooden'] : [];
    
    // Validate input
    if (empty($name)) {
        $_SESSION['error'] = "Name is required!";
    } elseif (empty($category_id)) {
        $_SESSION['error'] = "Category is required!";
    } elseif (empty($sku)) {
        $_SESSION['error'] = "SKU is required!";
    } else {
        // Check if SKU already exists
        $stmt = $conn->prepare("SELECT id FROM products WHERE sku = ?");
        $stmt->bind_param("s", $sku);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $_SESSION['error'] = "SKU already exists!";
        } else {
            // Start transaction
            $conn->begin_transaction();
            
            try {
                // Insert product
                $stmt = $conn->prepare("INSERT INTO products (name, category_id, subcategory_id, description, sku, price, quantity, length, width, height, weight, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("siissiidddds", $name, $category_id, $subcategory_id, $description, $sku, $price, $quantity, $length, $width, $height, $weight, $status);
                $stmt->execute();
                $product_id = $conn->insert_id;
                
                // Handle image uploads
                if (isset($_FILES['images']) && is_array($_FILES['images']['name'])) {
                    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
                    $upload_path = 'uploads/products/';
                    
                    // Create directory if it doesn't exist
                    if (!file_exists($upload_path)) {
                        mkdir($upload_path, 0777, true);
                    }
                    
                    foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                        if ($_FILES['images']['error'][$key] == 0) {
                            $filename = $_FILES['images']['name'][$key];
                            $filetype = pathinfo($filename, PATHINFO_EXTENSION);
                            
                            if (in_array(strtolower($filetype), $allowed)) {
                                $new_filename = time() . '_' . $key . '.' . $filetype;
                                $filepath = $upload_path . $new_filename;
                                
                                if (move_uploaded_file($tmp_name, $filepath)) {
                                    // Insert image record
                                    $stmt = $conn->prepare("INSERT INTO product_images (product_id, image, sort_order) VALUES (?, ?, ?)");
                                    $stmt->bind_param("isi", $product_id, $new_filename, $key);
                                    $stmt->execute();
                                }
                            }
                        }
                    }
                }
                
                // Insert relationships
                $relationship_data = [
                    ['table' => 'product_colors', 'field' => 'color_id', 'values' => $selected_colors],
                    ['table' => 'product_materials', 'field' => 'material_id', 'values' => $selected_materials],
                    ['table' => 'product_marbles', 'field' => 'marble_id', 'values' => $selected_marbles],
                    ['table' => 'product_wooden', 'field' => 'wooden_id', 'values' => $selected_wooden]
                ];
                
                foreach ($relationship_data as $rel) {
                    if (!empty($rel['values'])) {
                        $stmt = $conn->prepare("INSERT INTO {$rel['table']} (product_id, {$rel['field']}) VALUES (?, ?)");
                        foreach ($rel['values'] as $value) {
                            $stmt->bind_param("ii", $product_id, $value);
                            $stmt->execute();
                        }
                    }
                }
                
                $conn->commit();
                $_SESSION['success'] = "Product added successfully!";
                header("Location: p-index.php");
                exit();
            } catch (Exception $e) {
                $conn->rollback();
                $_SESSION['error'] = "Failed to add product!";
            }
        }
    }
}

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
                        <li class="breadcrumb-item"><a href="p-index.php">Products</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Add New Product</li>
                    </ol>
                </nav>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h1 class="h3 mb-0">Add New Product</h1>
                    <a href="p-index.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to List
                    </a>
                </div>

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
                        <form action="" method="POST" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="name" name="name" 
                                               value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" 
                                               required>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="category_id" class="form-label">Category <span class="text-danger">*</span></label>
                                                <select class="form-select" id="category_id" name="category_id" required>
                                                    <option value="">Select Category</option>
                                                    <?php foreach ($categories as $category): ?>
                                                        <option value="<?php echo $category['id']; ?>" 
                                                            <?php echo (isset($_POST['category_id']) && $_POST['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                                            <?php echo htmlspecialchars($category['name']); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="subcategory_id" class="form-label">Subcategory</label>
                                                <select class="form-select" id="subcategory_id" name="subcategory_id">
                                                    <option value="">Select Subcategory</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea class="form-control" id="description" name="description" rows="4"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="sku" class="form-label">SKU <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="sku" name="sku" 
                                                       value="<?php echo isset($_POST['sku']) ? htmlspecialchars($_POST['sku']) : ''; ?>" 
                                                       required>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label for="price" class="form-label">Price</label>
                                                <input type="number" class="form-control" id="price" name="price" step="0.01" min="0" 
                                                       value="<?php echo isset($_POST['price']) ? htmlspecialchars($_POST['price']) : ''; ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label for="quantity" class="form-label">Quantity</label>
                                                <input type="number" class="form-control" id="quantity" name="quantity" min="0" 
                                                       value="<?php echo isset($_POST['quantity']) ? htmlspecialchars($_POST['quantity']) : '0'; ?>">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card mb-3">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">Dimensions</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <div class="mb-3">
                                                        <label for="length" class="form-label">Length (cm)</label>
                                                        <input type="number" class="form-control" id="length" name="length" step="0.01" min="0" 
                                                               value="<?php echo isset($_POST['length']) ? htmlspecialchars($_POST['length']) : ''; ?>">
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="mb-3">
                                                        <label for="width" class="form-label">Width (cm)</label>
                                                        <input type="number" class="form-control" id="width" name="width" step="0.01" min="0" 
                                                               value="<?php echo isset($_POST['width']) ? htmlspecialchars($_POST['width']) : ''; ?>">
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="mb-3">
                                                        <label for="height" class="form-label">Height (cm)</label>
                                                        <input type="number" class="form-control" id="height" name="height" step="0.01" min="0" 
                                                               value="<?php echo isset($_POST['height']) ? htmlspecialchars($_POST['height']) : ''; ?>">
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="mb-3">
                                                        <label for="weight" class="form-label">Weight (kg)</label>
                                                        <input type="number" class="form-control" id="weight" name="weight" step="0.01" min="0" 
                                                               value="<?php echo isset($_POST['weight']) ? htmlspecialchars($_POST['weight']) : ''; ?>">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="card mb-3">
                                                <div class="card-header">
                                                    <h5 class="card-title mb-0">Colors</h5>
                                                </div>
                                                <div class="card-body" style="max-height: 200px; overflow-y: auto;">
                                                    <?php foreach ($colors as $color): ?>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="colors[]" 
                                                                   value="<?php echo $color['id']; ?>" id="color_<?php echo $color['id']; ?>"
                                                                   <?php echo (isset($_POST['colors']) && in_array($color['id'], $_POST['colors'])) ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="color_<?php echo $color['id']; ?>">
                                                                <?php echo htmlspecialchars($color['name']); ?>
                                                            </label>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="card mb-3">
                                                <div class="card-header">
                                                    <h5 class="card-title mb-0">Materials</h5>
                                                </div>
                                                <div class="card-body" style="max-height: 200px; overflow-y: auto;">
                                                    <?php foreach ($materials as $material): ?>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="materials[]" 
                                                                   value="<?php echo $material['id']; ?>" id="material_<?php echo $material['id']; ?>"
                                                                   <?php echo (isset($_POST['materials']) && in_array($material['id'], $_POST['materials'])) ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="material_<?php echo $material['id']; ?>">
                                                                <?php echo htmlspecialchars($material['name']); ?>
                                                            </label>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="card mb-3">
                                                <div class="card-header">
                                                    <h5 class="card-title mb-0">Marbles</h5>
                                                </div>
                                                <div class="card-body" style="max-height: 200px; overflow-y: auto;">
                                                    <?php foreach ($marbles as $marble): ?>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="marbles[]" 
                                                                   value="<?php echo $marble['id']; ?>" id="marble_<?php echo $marble['id']; ?>"
                                                                   <?php echo (isset($_POST['marbles']) && in_array($marble['id'], $_POST['marbles'])) ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="marble_<?php echo $marble['id']; ?>">
                                                                <?php echo htmlspecialchars($marble['name']); ?>
                                                            </label>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="card mb-3">
                                                <div class="card-header">
                                                    <h5 class="card-title mb-0">Wooden</h5>     
                                                </div>
                                                <div class="card-body" style="max-height: 200px; overflow-y: auto;">
                                                    <?php foreach ($wooden as $wooden): ?>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="wooden[]" 
                                                                   value="<?php echo $wooden['id']; ?>" id="wooden_<?php echo $wooden['id']; ?>"
                                                                   <?php echo (isset($_POST['wooden']) && in_array($wooden['id'], $_POST['wooden'])) ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="wooden_<?php echo $wooden['id']; ?>">
                                                                <?php echo htmlspecialchars($wooden['name']); ?>
                                                            </label>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                    <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-body">
                                            <h5 class="card-title">Images</h5>
                                            <div class="mb-3">
                                                <label for="images" class="form-label">Upload Images</label>
                                                <input type="file" class="form-control" id="images" name="images[]" accept="image/*" multiple>
                                                <div class="form-text">Optional. Allowed formats: JPG, JPEG, PNG, GIF</div>
                                            </div>
                                            <div id="imagePreviewContainer" class="row g-2 mt-2"></div>
                                        </div>
                                    </div>
                                    </div></div>

                                    <div class="mb-3">
                                        <label class="form-label d-block">Status</label>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="status" name="status" 
                                                   value="active" <?php echo !isset($_POST['status']) || $_POST['status'] == 'active' ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="status">Active</label>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- <div class="col-md-4">
                                    
                                </div> -->
                            </div>
                            
                            <div class="mt-4 border-top pt-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Save Product
                                </button>
                                <a href="p-index.php" class="btn btn-secondary ms-2">
                                    <i class="fas fa-times me-2"></i>Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
        <?php include 'components/footer.php'; ?>
    </div>
</div>

<script>
// Handle subcategories based on selected category
const subcategories = <?php echo json_encode($subcategories); ?>;
const subcategorySelect = document.getElementById('subcategory_id');
const categorySelect = document.getElementById('category_id');

function updateSubcategories() {
    const categoryId = categorySelect.value;
    subcategorySelect.innerHTML = '<option value="">Select Subcategory</option>';
    
    if (categoryId) {
        const filteredSubcategories = subcategories.filter(s => s.category_id == categoryId);
        filteredSubcategories.forEach(sub => {
            const option = document.createElement('option');
            option.value = sub.id;
            option.textContent = sub.name;
            subcategorySelect.appendChild(option);
        });
    }
}

categorySelect.addEventListener('change', updateSubcategories);
updateSubcategories(); // Initial load

// Image preview functionality
document.getElementById('images').addEventListener('change', function(e) {
    const container = document.getElementById('imagePreviewContainer');
    container.innerHTML = '';
    
    Array.from(e.target.files).forEach((file, index) => {
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const col = document.createElement('div');
                col.className = 'col-6';
                
                const img = document.createElement('img');
                img.src = e.target.result;
                img.className = 'img-thumbnail w-100';
                img.style.height = '150px';
                img.style.objectFit = 'cover';
                
                col.appendChild(img);
                container.appendChild(col);
            }
            reader.readAsDataURL(file);
        }
    });
});
</script> 