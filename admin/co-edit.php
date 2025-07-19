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
    header("Location: co-index.php");
    exit();
}

$id = intval($_GET['id']);

// Fetch color
$stmt = $conn->prepare("SELECT * FROM colors WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$color = $result->fetch_assoc();

if (!$color) {
    $_SESSION['error'] = "Color not found!";
    header("Location: co-index.php");
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
    $color_code = !empty($_POST['color_code']) ? trim($_POST['color_code']) : null;
    $status = isset($_POST['status']) ? 'active' : 'inactive';
    
    // Validate input
    if (empty($name)) {
        $_SESSION['error'] = "Name is required!";
    } else {
        $image = $color['image']; // Keep existing image by default
        
        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['image']['name'];
            $filetype = pathinfo($filename, PATHINFO_EXTENSION);
            
            if (!in_array(strtolower($filetype), $allowed)) {
                $_SESSION['error'] = "Only JPG, JPEG, PNG & GIF files are allowed!";
            } else {
                $new_image = time() . '.' . $filetype;
                $target = 'uploads/colors/' . $new_image;
                
                // Create directory if it doesn't exist
                if (!file_exists('uploads/colors')) {
                    mkdir('uploads/colors', 0777, true);
                }
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
                    // Delete old image if exists
                    if (!empty($color['image'])) {
                        $old_image = 'uploads/colors/' . $color['image'];
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
            // Update color
            $stmt = $conn->prepare("UPDATE colors SET name = ?, category_id = ?, color_code = ?, image = ?, status = ? WHERE id = ?");
            $stmt->bind_param("sisssi", $name, $category_id, $color_code, $image, $status, $id);
            
            if ($stmt->execute()) {
                $_SESSION['success'] = "Color updated successfully!";
                header("Location: co-index.php");
                exit();
            } else {
                $_SESSION['error'] = "Failed to update color!";
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
                        <li class="breadcrumb-item"><a href="co-index.php">Colors</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Edit Color</li>
                    </ol>
                </nav>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h1 class="h3 mb-0">Edit Color</h1>
                    <a href="co-index.php" class="btn btn-secondary">
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
                                               value="<?php echo htmlspecialchars($color['name']); ?>" 
                                               required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="category_id" class="form-label">Category <span class="text-danger">*</span></label>
                                        <select class="form-select" id="category_id" name="category_id" required>
                                            <option value="">Select Category</option>
                                            <?php foreach ($categories as $category): ?>
                                                <option value="<?php echo $category['id']; ?>" 
                                                    <?php echo $color['category_id'] == $category['id'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($category['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label for="color_code" class="form-label">Color Code</label>
                                        <div class="input-group">
                                            <input type="color" class="form-control form-control-color" id="color_picker" 
                                                   value="<?php echo !empty($color['color_code']) ? $color['color_code'] : '#000000'; ?>"
                                                   title="Choose your color">
                                            <input type="text" class="form-control" id="color_code" name="color_code" 
                                                   value="<?php echo $color['color_code']; ?>" 
                                                   placeholder="#000000" pattern="^#[0-9A-Fa-f]{6}$">
                                        </div>
                                        <div class="form-text">Optional. Format: #RRGGBB (e.g., #FF0000 for red)</div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="image" class="form-label">Image</label>
                                        <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                        <div class="form-text">Optional. Allowed formats: JPG, JPEG, PNG, GIF</div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label d-block">Status</label>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="status" name="status" 
                                                   value="active" <?php echo $color['status'] == 'active' ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="status">Active</label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="card">
                                        <div class="card-body">
                                            <h5 class="card-title">Preview</h5>
                                            <div class="text-center my-3">
                                                <?php if (!empty($color['image']) && file_exists('uploads/colors/' . $color['image'])): ?>
                                                    <img id="imagePreview" src="uploads/colors/<?php echo $color['image']; ?>" 
                                                         alt="Preview" class="img-fluid rounded" style="max-width: 200px;">
                                                <?php else: ?>
                                                    <div class="color-preview mb-3" style="width: 200px; height: 200px; background-color: <?php echo $color['color_code']; ?>; border: 1px solid #dee2e6; margin: 0 auto;"></div>
                                                    <img id="imagePreview" src="assets/img/no-image.png" 
                                                         alt="Preview" class="img-fluid rounded" style="max-width: 200px; display: none;">
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-4 border-top pt-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Update Color
                                </button>
                                <a href="co-index.php" class="btn btn-secondary ms-2">
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
            preview.style.display = 'block';
            preview.src = e.target.result;
            document.querySelector('.color-preview').style.display = 'none';
        }
        reader.readAsDataURL(file);
    }
});

// Color picker functionality
document.getElementById('color_picker').addEventListener('input', function(e) {
    document.getElementById('color_code').value = e.target.value;
    if (!document.getElementById('imagePreview').src.includes('no-image.png')) {
        document.querySelector('.color-preview').style.backgroundColor = e.target.value;
    }
});

document.getElementById('color_code').addEventListener('input', function(e) {
    const value = e.target.value;
    if (/^#[0-9A-Fa-f]{6}$/.test(value)) {
        document.getElementById('color_picker').value = value;
        if (!document.getElementById('imagePreview').src.includes('no-image.png')) {
            document.querySelector('.color-preview').style.backgroundColor = value;
        }
    }
});
</script> 