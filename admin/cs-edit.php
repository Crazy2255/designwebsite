<?php
// cs-edit.php - Edit Case Study Admin Page
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$case = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM case_studies WHERE id=$id"));
$images = json_decode($case['images'], true) ?: [];
$errors = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $sqft = intval($_POST['sqft']);
    $type = trim($_POST['type']);
    $location = trim($_POST['location']);
    $timescale = trim($_POST['timescale']);
    $scope = trim($_POST['scope']);

    // Handle new image uploads
    $uploaded_images = $images; // Keep existing images
    if (!empty($_FILES['images']['name'][0])) {
        $upload_dir = 'uploads/case-studies/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
            $file_name = uniqid() . '_' . basename($_FILES['images']['name'][$key]);
            $target_file = $upload_dir . $file_name;
            if (move_uploaded_file($tmp_name, $target_file)) {
                $uploaded_images[] = $file_name;
            }
        }
    }

    if (empty($errors)) {
        $images_json = json_encode($uploaded_images);
        $stmt = $conn->prepare("UPDATE case_studies SET name=?, description=?, sqft=?, type=?, location=?, timescale=?, scope=?, images=? WHERE id=?");
        $stmt->bind_param("ssisssssi", $name, $description, $sqft, $type, $location, $timescale, $scope, $images_json, $id);
        if ($stmt->execute()) {
            $_SESSION['success'] = "Case study updated successfully!";
            header("Location: cs-index.php");
            exit();
        } else {
            $errors[] = "Failed to update case study: " . $stmt->error;
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
                <!-- Breadcrumb -->
                <nav aria-label="breadcrumb" class="mt-4">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="cs-index.php">Case Studies</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Edit Case Study</li>
                    </ol>
                </nav>
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3 mb-0">Edit Case Study</h1>
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
                                <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($case['name']) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="5" required><?= htmlspecialchars($case['description']) ?></textarea>
                                <small class="form-text text-muted">You can use HTML tags for formatting.</small>
                            </div>
                            <div class="mb-3">
                                <label for="sqft" class="form-label">Sqft <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="sqft" name="sqft" value="<?= htmlspecialchars($case['sqft']) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="type" class="form-label">Type <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="type" name="type" value="<?= htmlspecialchars($case['type']) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="location" class="form-label">Location <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="location" name="location" value="<?= htmlspecialchars($case['location']) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="timescale" class="form-label">Timescale <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="timescale" name="timescale" value="<?= htmlspecialchars($case['timescale']) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="scope" class="form-label">Scope <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="scope" name="scope" value="<?= htmlspecialchars($case['scope']) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Existing Images</label><br>
                                <?php foreach ($images as $img): ?>
                                    <img src="uploads/case-studies/<?= htmlspecialchars($img) ?>" width="80" height="60" style="object-fit:cover; margin-right:8px;">
                                <?php endforeach; ?>
                            </div>
                            <div class="mb-3">
                                <label for="images" class="form-label">Add More Images</label>
                                <input type="file" class="form-control" id="images" name="images[]" multiple>
                                <small class="form-text text-muted">You can upload more images. Existing images will be kept.</small>
                            </div>
                            <div class="d-flex justify-content-end gap-2">
                                <a href="cs-index.php" class="btn btn-secondary">Cancel</a>
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