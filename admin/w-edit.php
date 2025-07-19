<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Check if ID is provided
if (!isset($_GET['id'])) {
    header("Location: w-index.php");
    exit();
}

$id = intval($_GET['id']);

// Fetch wooden item
$stmt = $conn->prepare("SELECT * FROM wooden WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$wooden = $result->fetch_assoc();

if (!$wooden) {
    $_SESSION['error'] = "Wooden item not found!";
    header("Location: w-index.php");
    exit();
}

// Fetch active categories for dropdown
$categories = [];
$stmt = $conn->prepare("SELECT id, name FROM categories WHERE status = 'active' ORDER BY name");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $categories[] = $row;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $category_id = intval($_POST['category_id']);
    $status = isset($_POST['status']) ? 'active' : 'inactive';
    
    // Validate input
    if (empty($name)) {
        $_SESSION['error'] = "Name is required!";
    } else {
        $image = $wooden['image']; // Keep existing image by default
        
        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['image']['name'];
            $filetype = pathinfo($filename, PATHINFO_EXTENSION);
            
            if (!in_array(strtolower($filetype), $allowed)) {
                $_SESSION['error'] = "Only JPG, JPEG, PNG & GIF files are allowed!";
            } else {
                $new_image = time() . '.' . $filetype;
                $target = 'uploads/wooden/' . $new_image;
                
                // Create directory if it doesn't exist
                if (!file_exists('uploads/wooden')) {
                    mkdir('uploads/wooden', 0777, true);
                }
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
                    // Delete old image if exists
                    if (!empty($wooden['image'])) {
                        $old_image = 'uploads/wooden/' . $wooden['image'];
                        if (file_exists($old_image)) {
                            unlink($old_image);
                        }
                    }
                    $image = $new_image;
                } else {
                    $_SESSION['error'] = "Failed to upload image!";
                }
            }
        }
        
        if (!isset($_SESSION['error'])) {
            // Update wooden item
            $stmt = $conn->prepare("UPDATE wooden SET name = ?, category_id = ?, image = ?, status = ? WHERE id = ?");
            $stmt->bind_param("sissi", $name, $category_id, $image, $status, $id);
            
            if ($stmt->execute()) {
                $_SESSION['success'] = "Wooden item updated successfully!";
                header("Location: w-index.php");
                exit();
            } else {
                $_SESSION['error'] = "Failed to update wooden item!";
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
                        <li class="breadcrumb-item"><a href="w-index.php">Wooden Items</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Edit Wooden Item</li>
                    </ol>
                </nav>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h1 class="h3 mb-0">Edit Wooden Item</h1>
                    <a href="w-index.php" class="btn btn-secondary">
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
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="name" name="name" 
                                               value="<?php echo htmlspecialchars($wooden['name']); ?>" 
                                               required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="category_id" class="form-label">Category <span class="text-danger">*</span></label>
                                        <select class="form-select" id="category_id" name="category_id" required>
                                            <option value="">Select Category</option>
                                            <?php foreach ($categories as $category): ?>
                                                <option value="<?php echo $category['id']; ?>" 
                                                    <?php echo $wooden['category_id'] == $category['id'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($category['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label for="image" class="form-label">Image</label>
                                        <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                        <div class="form-text">Allowed formats: JPG, JPEG, PNG, GIF</div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label d-block">Status</label>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="status" name="status" 
                                                   value="active" <?php echo $wooden['status'] == 'active' ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="status">Active</label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="card">
                                        <div class="card-body">
                                            <h5 class="card-title">Preview</h5>
                                            <div class="text-center my-3">
                                                <img id="imagePreview" 
                                                     src="<?php echo !empty($wooden['image']) ? 'uploads/wooden/' . $wooden['image'] : 'assets/img/no-image.png'; ?>" 
                                                     alt="Preview" class="img-fluid rounded" style="max-width: 200px;">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-4 border-top pt-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Update Wooden Item
                                </button>
                                <a href="w-index.php" class="btn btn-secondary ms-2">
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
// Image preview functionality
document.getElementById('image').addEventListener('change', function(e) {
    const preview = document.getElementById('imagePreview');
    const file = e.target.files[0];
    
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
        }
        reader.readAsDataURL(file);
    }
});
</script> 