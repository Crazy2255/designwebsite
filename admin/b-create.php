<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $heading = trim($_POST['heading']);
    $description = trim($_POST['description']);
    $author = trim($_POST['author']);
    $publish_date = trim($_POST['publish_date']);
    $status = isset($_POST['status']) ? 'active' : 'inactive';
    $errors = [];

    // Validate required fields
    if (empty($heading)) {
        $errors[] = "Heading is required";
    }
    if (empty($description)) {
        $errors[] = "Description is required";
    }
    if (empty($author)) {
        $errors[] = "Author name is required";
    }
    if (empty($publish_date)) {
        $errors[] = "Publish date is required";
    }

    // Handle image upload
    $image_name = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = $_FILES['image']['type'];
        
        if (!in_array($file_type, $allowed_types)) {
            $errors[] = "Only JPG, PNG and GIF images are allowed";
        } else {
            $upload_dir = 'uploads/blogs/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $image_name = uniqid() . '.' . $file_extension;
            $upload_path = $upload_dir . $image_name;

            if (!move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $errors[] = "Failed to upload image";
            }
        }
    } else {
        $errors[] = "Image is required";
    }

    if (empty($errors)) {
        // Insert into database
        $stmt = $conn->prepare("INSERT INTO blogs (heading, description, image, author, publish_date, status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $heading, $description, $image_name, $author, $publish_date, $status);

        if ($stmt->execute()) {
            $_SESSION['success'] = "Blog added successfully!";
            header("Location: b-index.php");
            exit();
        } else {
            // If database insert fails, delete uploaded image
            if (!empty($image_name) && file_exists('uploads/blogs/' . $image_name)) {
                unlink('uploads/blogs/' . $image_name);
            }
            $errors[] = "Failed to add blog";
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
                        <li class="breadcrumb-item"><a href="b-index.php">Blogs</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Add New Blog</li>
                    </ol>
                </nav>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3 mb-0">Add New Blog</h1>
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
                                <label for="heading" class="form-label">Heading <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="heading" name="heading" 
                                       value="<?php echo isset($_POST['heading']) ? htmlspecialchars($_POST['heading']) : ''; ?>" 
                                       required>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="description" name="description" rows="10"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                                <small class="text-muted">You can use HTML tags (h1-h6, p, span) for formatting</small>
                            </div>

                            <div class="mb-3">
                                <label for="image" class="form-label">Image <span class="text-danger">*</span></label>
                                <input type="file" class="form-control" id="image" name="image" accept="image/*" required>
                                <div class="mt-2">
                                    <img id="imagePreview" src="#" alt="Preview" style="max-width: 200px; display: none;">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="author" class="form-label">Author Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="author" name="author" 
                                       value="<?php echo isset($_POST['author']) ? htmlspecialchars($_POST['author']) : ''; ?>" 
                                       required>
                            </div>

                            <div class="mb-3">
                                <label for="publish_date" class="form-label">Publish Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="publish_date" name="publish_date" 
                                       value="<?php echo isset($_POST['publish_date']) ? $_POST['publish_date'] : date('Y-m-d'); ?>" 
                                       required>
                            </div>

                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="status" name="status" checked>
                                    <label class="form-check-label" for="status">Active</label>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <a href="b-index.php" class="btn btn-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary">Save</button>
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
// Image preview
document.getElementById('image').addEventListener('change', function(e) {
    const preview = document.getElementById('imagePreview');
    const file = e.target.files[0];
    
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        }
        reader.readAsDataURL(file);
    } else {
        preview.style.display = 'none';
    }
});

// Initialize Summernote editor
$(document).ready(function() {
    $('#description').summernote({
        height: 300,
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'underline', 'clear']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['insert', ['link']],
            ['view', ['fullscreen', 'codeview', 'help']],
            ['headers', ['h1', 'h2', 'h3', 'h4', 'h5', 'h6']]
        ],
        styleTags: ['p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'span'],
        callbacks: {
            onPaste: function (e) {
                var bufferText = ((e.originalEvent || e).clipboardData || window.clipboardData).getData('Text');
                e.preventDefault();
                document.execCommand('insertText', false, bufferText);
            }
        }
    });
});
</script>

<style>
/* Form styles */
.form-label {
    font-weight: 500;
}

/* Image preview */
#imagePreview {
    border-radius: 4px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: transform 0.2s;
}

#imagePreview:hover {
    transform: scale(1.05);
}

/* Summernote customization */
.note-editor .note-toolbar {
    background-color: #f8f9fa;
}

.note-editor.note-frame {
    border-color: #ced4da;
}

.note-editor .note-editing-area {
    background-color: #fff;
}
</style> 