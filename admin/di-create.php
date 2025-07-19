<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Handle form submission
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    
    // Validate inputs
    if (empty($name)) {
        $errors[] = "Name is required";
    }
    if (empty($description)) {
        $errors[] = "Description is required";
    }
    if (empty($_FILES['main_image']['name'])) {
        $errors[] = "Main image is required";
    }

    // Handle main image upload
    $main_image = '';
    if (!empty($_FILES['main_image']['name'])) {
        $upload_dir = 'uploads/design-ideas/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_name = uniqid() . '_main_' . basename($_FILES['main_image']['name']);
        $target_file = $upload_dir . $file_name;
        
        // Check file type
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        if ($imageFileType != "jpg" && $imageFileType != "jpeg" && $imageFileType != "png") {
            $errors[] = "Only JPG, JPEG, PNG files are allowed for main image";
        } else {
            if (move_uploaded_file($_FILES['main_image']['tmp_name'], $target_file)) {
                $main_image = $file_name;
            } else {
                $errors[] = "Failed to upload main image";
            }
        }
    }

    // Handle gallery images upload
    $gallery_images = [];
    if (!empty($_FILES['work_images']['name'][0])) {
        foreach ($_FILES['work_images']['tmp_name'] as $key => $tmp_name) {
            $file_name = uniqid() . '_gallery_' . basename($_FILES['work_images']['name'][$key]);
            $target_file = $upload_dir . $file_name;
            
            // Check file type
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            if ($imageFileType != "jpg" && $imageFileType != "jpeg" && $imageFileType != "png") {
                $errors[] = "Only JPG, JPEG, PNG files are allowed for gallery images";
                continue;
            }

            if (move_uploaded_file($tmp_name, $target_file)) {
                $gallery_images[] = $file_name;
            } else {
                $errors[] = "Failed to upload gallery image: " . $_FILES['work_images']['name'][$key];
            }
        }
    }

    if (empty($errors)) {
        $work_images_json = json_encode($gallery_images);
        
        $stmt = $conn->prepare("INSERT INTO design_ideas (name, description, main_image, work_images) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $description, $main_image, $work_images_json);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Design idea added successfully!";
            header("Location: di-index.php");
            exit();
        } else {
            $errors[] = "Failed to add design idea: " . $stmt->error;
        }
    }
}

$currentDirectory = '';
?>
<?php include 'components/header.php'; ?>
<div id="layoutSidenav">
    <?php include 'components/sidebar.php'; ?>
    <div id="layoutSidenav_content">
        <main>
            <div class="container-fluid px-4">
                <nav aria-label="breadcrumb" class="mt-4">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="di-index.php">Design Ideas</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Add Design Idea</li>
                    </ol>
                </nav>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3 mb-0">Add Design Idea</h1>
                </div>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php foreach ($errors as $error): ?>
                            <div><?php echo $error; ?></div>
                        <?php endforeach; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body">
                        <form action="" method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="description" name="description" rows="5" required></textarea>
                                <small class="form-text text-muted">You can use HTML tags for formatting.</small>
                            </div>

                            <div class="mb-3">
                                <label for="main_image" class="form-label">Main Image <span class="text-danger">*</span></label>
                                <input type="file" class="form-control" id="main_image" name="main_image" accept="image/*" required>
                                <small class="form-text text-muted">This will be the featured image for the design idea.</small>
                            </div>

                            <div id="mainImagePreview" class="mb-3"></div>

                            <div class="mb-3">
                                <label for="work_images" class="form-label">Work Images</label>
                                <input type="file" class="form-control" id="work_images" name="work_images[]" multiple accept="image/*">
                                <small class="form-text text-muted">Upload additional images showing the work process or details (optional).</small>
                            </div>

                            <div id="workImagesPreview" class="mb-3 d-flex flex-wrap gap-2"></div>

                            <div class="d-flex justify-content-end gap-2">
                                <a href="di-index.php" class="btn btn-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary">Submit</button>
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
// Main image preview
document.getElementById('main_image').addEventListener('change', function(e) {
    const preview = document.getElementById('mainImagePreview');
    preview.innerHTML = '';
    
    if (e.target.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const div = document.createElement('div');
            div.style.width = '300px';
            div.style.height = '200px';
            div.style.position = 'relative';
            div.style.overflow = 'hidden';
            div.style.borderRadius = '8px';
            div.style.border = '1px solid #ddd';
            
            const img = document.createElement('img');
            img.src = e.target.result;
            img.style.width = '100%';
            img.style.height = '100%';
            img.style.objectFit = 'cover';
            
            div.appendChild(img);
            preview.appendChild(div);
        }
        reader.readAsDataURL(e.target.files[0]);
    }
});

// Work images preview
document.getElementById('work_images').addEventListener('change', function(e) {
    const preview = document.getElementById('workImagesPreview');
    preview.innerHTML = '';
    
    for (let i = 0; i < e.target.files.length; i++) {
        const file = e.target.files[i];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const div = document.createElement('div');
                div.style.width = '150px';
                div.style.height = '150px';
                div.style.position = 'relative';
                div.style.overflow = 'hidden';
                div.style.borderRadius = '8px';
                div.style.border = '1px solid #ddd';
                
                const img = document.createElement('img');
                img.src = e.target.result;
                img.style.width = '100%';
                img.style.height = '100%';
                img.style.objectFit = 'cover';
                
                div.appendChild(img);
                preview.appendChild(div);
            }
            reader.readAsDataURL(file);
        }
    }
});
</script> 