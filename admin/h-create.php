<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $status = isset($_POST['status']) ? 'active' : 'inactive';
    $banner_image = '';
    
    // Validate inputs
    if (empty($name)) {
        $_SESSION['error'] = "Name is required!";
    } else {
        // Handle banner image upload
        if (isset($_FILES['banner_image']) && $_FILES['banner_image']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['banner_image']['name'];
            $filetype = pathinfo($filename, PATHINFO_EXTENSION);
            
            if (!in_array(strtolower($filetype), $allowed)) {
                $_SESSION['error'] = "Only JPG, JPEG, PNG & GIF files are allowed!";
            } else {
                // Create uploads directory if it doesn't exist
                if (!file_exists('uploads/hero')) {
                    mkdir('uploads/hero', 0777, true);
                }
                
                // Generate unique filename
                $banner_image = uniqid() . '.' . $filetype;
                $destination = 'uploads/hero/' . $banner_image;
                
                if (move_uploaded_file($_FILES['banner_image']['tmp_name'], $destination)) {
                    // File uploaded successfully
                } else {
                    $_SESSION['error'] = "Failed to upload banner image!";
                }
            }
        }
        
        if (!isset($_SESSION['error'])) {
            // Insert hero section
            $stmt = $conn->prepare("INSERT INTO hero_sections (name, description, banner_image, status) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $description, $banner_image, $status);
            
            if ($stmt->execute()) {
                $_SESSION['success'] = "Hero section added successfully!";
                header("Location: h-index.php");
                exit();
            } else {
                $_SESSION['error'] = "Failed to add hero section!";
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
                        <li class="breadcrumb-item"><a href="h-index.php">Hero Sections</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Add New Hero Section</li>
                    </ol>
                </nav>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h1 class="h3 mb-0">Add New Hero Section</h1>
                    <a href="h-index.php" class="btn btn-secondary">
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
                        <form action="h-create.php" method="POST" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Name</label>
                                        <input type="text" class="form-control" id="name" name="name" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea class="form-control" id="description" name="description" rows="4"></textarea>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="banner_image" class="form-label">Banner Image</label>
                                        <input type="file" class="form-control" id="banner_image" name="banner_image">
                                        <div class="form-text">Allowed formats: JPG, JPEG, PNG, GIF</div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label d-block">Status</label>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="status" name="status" value="active" checked>
                                            <label class="form-check-label" for="status">Active</label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="card">
                                        <div class="card-body">
                                            <h5 class="card-title">Preview</h5>
                                            <div class="text-center my-3">
                                                <img id="imagePreview" src="assets/img/no-image.png" 
                                                     alt="Preview" class="img-fluid rounded" 
                                                     style="max-width: 200px; display: none;">
                                                <div id="noImagePreview" class="text-muted">
                                                    <i class="fas fa-image fa-4x"></i>
                                                    <p class="mt-2">No image selected</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-4 border-top pt-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Save Hero Section
                                </button>
                                <a href="h-index.php" class="btn btn-secondary ms-2">
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
document.getElementById('banner_image').addEventListener('change', function(e) {
    const preview = document.getElementById('imagePreview');
    const noPreview = document.getElementById('noImagePreview');
    const file = e.target.files[0];
    
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
            noPreview.style.display = 'none';
        }
        reader.readAsDataURL(file);
    } else {
        preview.style.display = 'none';
        noPreview.style.display = 'block';
    }
});

// Initialize text editor for description
if (typeof ClassicEditor !== 'undefined') {
    ClassicEditor
        .create(document.querySelector('#description'))
        .catch(error => {
            console.error(error);
        });
}
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
}

/* Form styles */
.ck-editor__editable {
    min-height: 200px;
}
</style> 