<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Check if ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "Invalid blog ID";
    header("Location: b-index.php");
    exit();
}

$id = $_GET['id'];

// Fetch existing blog data
$stmt = $conn->prepare("SELECT * FROM blogs WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = "Blog not found";
    header("Location: b-index.php");
    exit();
}

$blog = $result->fetch_assoc();

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

    // Handle image upload if new image is provided
    $image_name = $blog['image']; // Keep existing image by default
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
            $new_image_name = uniqid() . '.' . $file_extension;
            $upload_path = $upload_dir . $new_image_name;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                // Delete old image if upload successful
                if (!empty($blog['image']) && file_exists($upload_dir . $blog['image'])) {
                    unlink($upload_dir . $blog['image']);
                }
                $image_name = $new_image_name;
            } else {
                $errors[] = "Failed to upload image";
            }
        }
    }

    if (empty($errors)) {
        // Update database
        $stmt = $conn->prepare("UPDATE blogs SET heading = ?, description = ?, image = ?, author = ?, publish_date = ?, status = ? WHERE id = ?");
        $stmt->bind_param("ssssssi", $heading, $description, $image_name, $author, $publish_date, $status, $id);

        if ($stmt->execute()) {
            $_SESSION['success'] = "Blog updated successfully!";
            header("Location: b-index.php");
            exit();
        } else {
            // If database update fails and we uploaded a new image, delete it
            if ($image_name !== $blog['image'] && file_exists('uploads/blogs/' . $image_name)) {
                unlink('uploads/blogs/' . $image_name);
            }
            $errors[] = "Failed to update blog";
        }
    }
} else {
    // Pre-fill form with existing data
    $_POST = $blog;
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
                        <li class="breadcrumb-item active" aria-current="page">Edit Blog</li>
                    </ol>
                </nav>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3 mb-0">Edit Blog</h1>
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
                                       value="<?php echo htmlspecialchars($blog['heading']); ?>" 
                                       required>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="description" name="description" rows="10"><?php echo htmlspecialchars($blog['description']); ?></textarea>
                                <small class="text-muted">You can use HTML tags (h1-h6, p, span) for formatting</small>
                            </div>

                            <div class="mb-3">
                                <label for="image" class="form-label">Image</label>
                                <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                <small class="text-muted d-block">Leave empty to keep the current image</small>
                                <div class="mt-2">
                                    <?php if (!empty($blog['image']) && file_exists('uploads/blogs/' . $blog['image'])): ?>
                                        <img src="uploads/blogs/<?php echo htmlspecialchars($blog['image']); ?>" 
                                             alt="Current image" 
                                             class="current-image"
                                             style="max-width: 200px;">
                                    <?php endif; ?>
                                    <img id="imagePreview" src="#" alt="Preview" style="max-width: 200px; display: none;">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="author" class="form-label">Author Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="author" name="author" 
                                       value="<?php echo htmlspecialchars($blog['author']); ?>" 
                                       required>
                            </div>

                            <div class="mb-3">
                                <label for="publish_date" class="form-label">Publish Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="publish_date" name="publish_date" 
                                       value="<?php echo $blog['publish_date']; ?>" 
                                       required>
                            </div>

                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="status" name="status"
                                           <?php echo $blog['status'] === 'active' ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="status">Active</label>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <a href="b-index.php" class="btn btn-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary">Update</button>
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
            // Hide current image when showing preview
            const currentImage = document.querySelector('.current-image');
            if (currentImage) {
                currentImage.style.display = 'none';
            }
        }
        reader.readAsDataURL(file);
    } else {
        preview.style.display = 'none';
        // Show current image when no new image selected
        const currentImage = document.querySelector('.current-image');
        if (currentImage) {
            currentImage.style.display = 'block';
        }
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
#imagePreview, .current-image {
    border-radius: 4px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: transform 0.2s;
}

#imagePreview:hover, .current-image:hover {
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