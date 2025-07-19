<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Get category ID from URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id === 0) {
    $_SESSION['error'] = "Invalid category ID!";
    header("Location: c-index.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $status = isset($_POST['status']) ? 'active' : 'inactive';
    $current_image = $_POST['current_image'];
    
    // Validate name
    if (empty($name)) {
        $_SESSION['error'] = "Category name is required!";
    } else {
        $image = $current_image;
        
        // Handle image upload if new image is selected
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['image']['name'];
            $filetype = pathinfo($filename, PATHINFO_EXTENSION);
            
            if (!in_array(strtolower($filetype), $allowed)) {
                $_SESSION['error'] = "Only JPG, JPEG, PNG & GIF files are allowed!";
            } else {
                // Create uploads directory if it doesn't exist
                if (!file_exists('uploads/categories')) {
                    mkdir('uploads/categories', 0777, true);
                }
                
                // Generate unique filename
                $image = uniqid() . '.' . $filetype;
                $destination = 'uploads/categories/' . $image;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
                    // Delete old image if exists
                    if (!empty($current_image) && file_exists('uploads/categories/' . $current_image)) {
                        unlink('uploads/categories/' . $current_image);
                    }
                } else {
                    $_SESSION['error'] = "Failed to upload image!";
                }
            }
        }
        
        if (!isset($_SESSION['error'])) {
            // Update category
            $stmt = $conn->prepare("UPDATE categories SET name = ?, image = ?, status = ? WHERE id = ?");
            $stmt->bind_param("sssi", $name, $image, $status, $id);
            
            if ($stmt->execute()) {
                $_SESSION['success'] = "Category updated successfully!";
                header("Location: c-index.php");
                exit();
            } else {
                $_SESSION['error'] = "Failed to update category!";
            }
        }
    }
}

// Get category data
$stmt = $conn->prepare("SELECT * FROM categories WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$category = $result->fetch_assoc();

if (!$category) {
    $_SESSION['error'] = "Category not found!";
    header("Location: c-index.php");
    exit();
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
                        <li class="breadcrumb-item"><a href="c-index.php">Categories</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Edit Category</li>
                    </ol>
                </nav>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h1 class="h3 mb-0">Edit Category</h1>
                    <a href="c-index.php" class="btn btn-secondary">
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
                        <form action="c-edit.php?id=<?php echo $id; ?>" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="current_image" value="<?php echo $category['image']; ?>">
                            
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Category Name</label>
                                        <input type="text" class="form-control" id="name" name="name" 
                                               value="<?php echo htmlspecialchars($category['name']); ?>" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="image" class="form-label">Category Image</label>
                                        <input type="file" class="form-control" id="image" name="image">
                                        <div class="form-text">Allowed formats: JPG, JPEG, PNG, GIF</div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label d-block">Status</label>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="status" name="status" 
                                                   value="active" <?php echo $category['status'] == 'active' ? 'checked' : ''; ?>>
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
                                                     src="<?php echo !empty($category['image']) ? 'uploads/categories/' . $category['image'] : 'assets/img/no-image.png'; ?>" 
                                                     alt="Preview" class="img-fluid rounded" 
                                                     style="max-width: 200px;">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-4 border-top pt-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Update Category
                                </button>
                                <a href="c-index.php" class="btn btn-secondary ms-2">
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

<style>
/* Reset and base styles */
body {
    overflow-x: hidden;
}

/* Layout structure */
#layoutSidenav {
    display: flex;
}

#layoutSidenav_content {
    flex: 1 0 auto;
    min-width: 0;
    display: flex;
    flex-direction: column;
     /* Match sidebar width */
}

/* Main content area */
main {
    flex-grow: 1;
    padding: 0;
}

.container-fluid {
    padding-right: 1.5rem;
    padding-left: 1.5rem;
}

/* Form styles */
.form-control {
    border: 1px solid #dee2e6;
    border-radius: 0.25rem;
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
}

.form-control:focus {
    border-color: #86b7fe;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

.form-text {
    color: #6c757d;
    font-size: 0.75rem;
    margin-top: 0.25rem;
}

.form-label {
    font-weight: 500;
    color: #495057;
    margin-bottom: 0.5rem;
}

/* Switch styles */
.form-switch .form-check-input {
    width: 3em;
    height: 1.5em;
    margin-top: 0.25em;
}

.form-switch .form-check-input:checked {
    background-color: #198754;
    border-color: #198754;
}

/* Card styles */
.card {
    margin-bottom: 1.5rem;
    border: 1px solid #e9ecef;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.card-body {
    padding: 1.25rem;
}

.card-title {
    font-size: 1rem;
    font-weight: 500;
    color: #495057;
    margin-bottom: 1rem;
}

/* Button styles */
.btn {
    font-size: 0.875rem;
    padding: 0.375rem 0.75rem;
}

.btn-primary {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

.btn-primary:hover {
    background-color: #0b5ed7;
    border-color: #0a58ca;
}

/* Responsive adjustments */
@media (max-width: 991.98px) {
    #layoutSidenav_content {
        padding-left: 0;
    }
}
</style> 