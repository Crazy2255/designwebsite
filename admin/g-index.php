<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Handle gallery deletion
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    
    // Get gallery image before deletion
    $stmt = $conn->prepare("SELECT image FROM gallery WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $gallery = $result->fetch_assoc();
    
    // Delete gallery item
    $stmt = $conn->prepare("DELETE FROM gallery WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        // Delete image file if exists
        if (!empty($gallery['image'])) {
            $image_path = 'uploads/gallery/' . $gallery['image'];
            if (file_exists($image_path)) {
                unlink($image_path);
            }
        }
        $_SESSION['success'] = "Gallery item deleted successfully!";
    } else {
        $_SESSION['error'] = "Failed to delete gallery item!";
    }
    
    header("Location: g-index.php");
    exit();
}

// Fetch all gallery items
$result = $conn->query("SELECT * FROM gallery ORDER BY created_at ASC");
$gallery_items = $result->fetch_all(MYSQLI_ASSOC);

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
                        <li class="breadcrumb-item active" aria-current="page">Gallery</li>
                    </ol>
                </nav>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h1 class="h3 mb-0">Gallery</h1>
                    <a href="g-create.php" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Add New Image
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
                            <table id="galleryTable" class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th width="80">ID</th>
                                        <th width="150">Image</th>
                                        <th>Name</th>
                                        <th>Description</th>
                                        <th width="100">Status</th>
                                        <th width="180">Created At</th>
                                        <th width="150">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($gallery_items as $item): ?>
                                        <tr>
                                            <td><?php echo $item['id']; ?></td>
                                            <td class="text-center">
                                                <?php if (!empty($item['image']) && file_exists('uploads/gallery/' . $item['image'])): ?>
                                                    <img src="uploads/gallery/<?php echo $item['image']; ?>" 
                                                         alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                                         class="img-thumbnail" style="width: 100px; height: 60px; object-fit: cover;">
                                                <?php else: ?>
                                                    <i class="fas fa-image fa-2x text-muted"></i>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                                            <td><?php echo !empty($item['description']) ? htmlspecialchars(substr($item['description'], 0, 100)) . '...' : '-'; ?></td>
                                            <td>
                                                <span class="badge <?php echo $item['status'] == 'active' ? 'bg-success' : 'bg-danger'; ?>">
                                                    <?php echo ucfirst($item['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('Y-m-d H:i:s', strtotime($item['created_at'])); ?></td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="g-edit.php?id=<?php echo $item['id']; ?>" 
                                                       class="btn btn-sm btn-primary" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-danger" 
                                                            onclick="deleteGalleryItem(<?php echo $item['id']; ?>)" title="Delete">
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
    $('#galleryTable').DataTable({
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
function deleteGalleryItem(id) {
    if (confirm('Are you sure you want to delete this gallery item?')) {
        window.location.href = 'g-index.php?delete=' + id;
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
    transform: scale(2);
    cursor: pointer;
}
</style> 