<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Handle case study deletion
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT images FROM case_studies WHERE id = $id"));
    $images = json_decode($row['images'], true);
    mysqli_query($conn, "DELETE FROM case_studies WHERE id = $id");
    // Delete images from uploads
    if (!empty($images) && is_array($images)) {
        foreach ($images as $img) {
            $img_path = 'uploads/case-studies/' . $img;
            if (file_exists($img_path)) {
                unlink($img_path);
            }
        }
    }
    $_SESSION['success'] = "Case study deleted successfully!";
    header("Location: cs-index.php");
    exit();
}

$result = mysqli_query($conn, "SELECT * FROM case_studies ORDER BY id DESC");
$case_studies = [];
while ($row = mysqli_fetch_assoc($result)) {
    $case_studies[] = $row;
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
                        <li class="breadcrumb-item active" aria-current="page">Case Studies</li>
                    </ol>
                </nav>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h1 class="h3 mb-0">Case Studies</h1>
                    <a href="cs-create.php" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Add New Case Study
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
                                        <th>Sqft</th>
                                        <th>Type</th>
                                        <th>Location</th>
                                        <th>Timescale</th>
                                        <th>Scope</th>
                                        <th>Image</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($case_studies as $row): ?>
                                    <tr>
                                        <td><?= $row['id'] ?></td>
                                        <td><?= htmlspecialchars($row['name']) ?></td>
                                        <td><?= htmlspecialchars($row['sqft']) ?></td>
                                        <td><?= htmlspecialchars($row['type']) ?></td>
                                        <td><?= htmlspecialchars($row['location']) ?></td>
                                        <td><?= htmlspecialchars($row['timescale']) ?></td>
                                        <td><?= htmlspecialchars($row['scope']) ?></td>
                                        <td>
                                            <?php 
                                                $images = json_decode($row['images'], true);
                                                if (!empty($images) && is_array($images)) {
                                                    echo '<img src="uploads/case-studies/' . htmlspecialchars($images[0]) . '" width="60" height="40" style="object-fit:cover;">';
                                                }
                                            ?>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="cs-edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-primary" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-danger" onclick="deleteCaseStudy(<?= $row['id'] ?>)" title="Delete">
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
function deleteCaseStudy(id) {
    if (confirm('Are you sure you want to delete this case study?')) {
        window.location.href = 'cs-index.php?delete=' + id;
    }
}
</script> 