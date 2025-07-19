<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Handle hero section deletion
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    
    // Get hero section image before deletion
    $stmt = $conn->prepare("SELECT banner_image FROM hero_sections WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $hero = $result->fetch_assoc();
    
    // Delete hero section
    $stmt = $conn->prepare("DELETE FROM hero_sections WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        // Delete banner image if exists
        if (!empty($hero['banner_image'])) {
            $image_path = 'uploads/hero/' . $hero['banner_image'];
            if (file_exists($image_path)) {
                unlink($image_path);
            }
        }
        $_SESSION['success'] = "Hero section deleted successfully!";
    } else {
        $_SESSION['error'] = "Failed to delete hero section!";
    }
    
    header("Location: h-index.php");
    exit();
}

// Fetch all hero sections
$result = $conn->query("SELECT * FROM hero_sections ORDER BY created_at ASC");
$hero_sections = $result->fetch_all(MYSQLI_ASSOC);

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
                        <li class="breadcrumb-item active" aria-current="page">Hero Sections</li>
                    </ol>
                </nav>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h1 class="h3 mb-0">Hero Sections</h1>
                    <a href="h-create.php" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Add New Hero Section
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
                        <div class="table-responsive">
                            <table id="heroSectionsTable" class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th width="80">ID</th>
                                        <th width="150">Banner</th>
                                        <th>Name</th>
                                        <th>Description</th>
                                        <th width="100">Status</th>
                                        <th width="180">Created At</th>
                                        <th width="150">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($hero_sections as $hero): ?>
                                        <tr>
                                            <td><?php echo $hero['id']; ?></td>
                                            <td class="text-center">
                                                <?php if (!empty($hero['banner_image']) && file_exists('uploads/hero/' . $hero['banner_image'])): ?>
                                                    <img src="uploads/hero/<?php echo $hero['banner_image']; ?>" 
                                                         alt="<?php echo htmlspecialchars($hero['name']); ?>" 
                                                         class="img-thumbnail" style="width: 100px; height: 60px; object-fit: cover;">
                                                <?php else: ?>
                                                    <i class="fas fa-image fa-2x text-muted"></i>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($hero['name']); ?></td>
                                            <td><?php echo htmlspecialchars(substr($hero['description'], 0, 100)) . '...'; ?></td>
                                            <td>
                                                <span class="badge <?php echo $hero['status'] == 'active' ? 'bg-success' : 'bg-danger'; ?>">
                                                    <?php echo ucfirst($hero['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('Y-m-d H:i:s', strtotime($hero['created_at'])); ?></td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="h-edit.php?id=<?php echo $hero['id']; ?>" 
                                                       class="btn btn-sm btn-primary" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-danger" 
                                                            onclick="deleteHeroSection(<?php echo $hero['id']; ?>)" title="Delete">
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
// Initialize DataTable
$(document).ready(function() {
    $('#heroSectionsTable').DataTable({
        dom: 'Bfrtip',
        buttons: [
            'copy', 'csv', 'excel', 'pdf', 'print'
        ],
        pageLength: 10,
        order: [[0, 'desc']],
        columnDefs: [
            { orderable: false, targets: [1, 6] },
            { searchable: false, targets: [1, 6] }
        ],
        responsive: true
    });
});

// Delete confirmation
function deleteHeroSection(id) {
    if (confirm('Are you sure you want to delete this hero section?')) {
        window.location.href = 'h-index.php?delete=' + id;
    }
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

/* Table styles */
.table img {
    transition: transform 0.2s;
}

.table img:hover {
    transform: scale(1.5);
    cursor: pointer;
}
</style> 