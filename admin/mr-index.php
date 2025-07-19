<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Handle marble deletion
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    
    // Get marble image before deletion
    $stmt = $conn->prepare("SELECT image FROM marbles WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $marble = $result->fetch_assoc();
    
    // Delete marble
    $stmt = $conn->prepare("DELETE FROM marbles WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        // Delete marble image if exists
        if (!empty($marble['image'])) {
            $image_path = 'uploads/marbles/' . $marble['image'];
            if (file_exists($image_path)) {
                unlink($image_path);
            }
        }
        $_SESSION['success'] = "Marble deleted successfully!";
    } else {
        $_SESSION['error'] = "Failed to delete marble!";
    }
    
    header("Location: mr-index.php");
    exit();
}

// Fetch all marbles with category names
$query = "SELECT m.*, cat.name as category_name 
          FROM marbles m 
          LEFT JOIN categories cat ON m.category_id = cat.id 
          ORDER BY m.created_at DESC";
$result = $conn->query($query);
$marbles = $result->fetch_all(MYSQLI_ASSOC);

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
                        <li class="breadcrumb-item active" aria-current="page">Marbles</li>
                    </ol>
                </nav>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h1 class="h3 mb-0">Marbles</h1>
                    <a href="mr-create.php" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Add New Marble
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
                            <table id="marblesTable" class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th width="80">ID</th>
                                        <th width="100">Image</th>
                                        <th>Name</th>
                                        <th>Category</th>
                                        <th width="100">Status</th>
                                        <th width="180">Created At</th>
                                        <th width="150">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($marbles as $marble): ?>
                                        <tr>
                                            <td><?php echo $marble['id']; ?></td>
                                            <td class="text-center">
                                                <?php if (!empty($marble['image']) && file_exists('uploads/marbles/' . $marble['image'])): ?>
                                                    <img src="uploads/marbles/<?php echo $marble['image']; ?>" 
                                                         alt="<?php echo htmlspecialchars($marble['name']); ?>" 
                                                         class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover;">
                                                <?php else: ?>
                                                    <img src="assets/img/no-image.png" 
                                                         alt="No Image" 
                                                         class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover;">
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($marble['name']); ?></td>
                                            <td><?php echo htmlspecialchars($marble['category_name']); ?></td>
                                            <td>
                                                <span class="badge <?php echo $marble['status'] == 'active' ? 'bg-success' : 'bg-danger'; ?>">
                                                    <?php echo ucfirst($marble['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('Y-m-d H:i:s', strtotime($marble['created_at'])); ?></td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="mr-edit.php?id=<?php echo $marble['id']; ?>" 
                                                       class="btn btn-sm btn-primary" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-danger" 
                                                            onclick="deleteMarble(<?php echo $marble['id']; ?>)" title="Delete">
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
    $('#marblesTable').DataTable({
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
function deleteMarble(id) {
    if (confirm('Are you sure you want to delete this marble?')) {
        window.location.href = 'mr-index.php?delete=' + id;
    }
}
</script> 