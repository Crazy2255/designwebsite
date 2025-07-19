<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Handle design idea deletion
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT images FROM design_ideas WHERE id = $id"));
    $images = json_decode($row['images'], true);
    
    // Delete images from uploads
    if (!empty($images) && is_array($images)) {
        foreach ($images as $img) {
            $img_path = 'uploads/design-ideas/' . $img;
            if (file_exists($img_path)) {
                unlink($img_path);
            }
        }
    }
    
    mysqli_query($conn, "DELETE FROM design_ideas WHERE id = $id");
    $_SESSION['success'] = "Design idea deleted successfully!";
    header("Location: di-index.php");
    exit();
}

$result = mysqli_query($conn, "SELECT * FROM design_ideas ORDER BY id DESC");
$ideas = [];
while ($row = mysqli_fetch_assoc($result)) {
    $ideas[] = $row;
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
                        <li class="breadcrumb-item active" aria-current="page">Design Ideas</li>
                    </ol>
                </nav>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3 mb-0">Design Ideas</h1>
                    <a href="di-create.php" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Add New Design Idea
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

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Description</th>
                                        <th>Image</th>
                                        <th>Created At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($ideas as $idea): ?>
                                        <?php 
                                        $work_images = json_decode($idea['work_images'], true) ?: [];
                                        $work_images_count = count($work_images);
                                        ?>
                                        <tr>
                                            <td><?= $idea['id'] ?></td>
                                            <td><?= htmlspecialchars($idea['name']) ?></td>
                                            <td>
                                                <?php
                                                $desc = strip_tags($idea['description']);
                                                echo strlen($desc) > 100 ? substr($desc, 0, 100) . '...' : $desc;
                                                ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($idea['main_image'])): ?>
                                                    <img src="uploads/design-ideas/<?= htmlspecialchars($idea['main_image']) ?>" 
                                                         alt="<?= htmlspecialchars($idea['name']) ?>"
                                                         style="width: 100px; height: 60px; object-fit: cover;">
                                                    <div class="small text-muted mt-1">
                                                        Work Images: <?= $work_images_count ?>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= date('Y-m-d H:i:s', strtotime($idea['created_at'])) ?></td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="di-edit.php?id=<?= $idea['id'] ?>" 
                                                       class="btn btn-sm btn-primary" 
                                                       title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" 
                                                            class="btn btn-sm btn-danger" 
                                                            onclick="deleteIdea(<?= $idea['id'] ?>)" 
                                                            title="Delete">
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
function deleteIdea(id) {
    if (confirm('Are you sure you want to delete this design idea? All associated images will be deleted.')) {
        window.location.href = 'di-index.php?delete=' + id;
    }
}
</script> 