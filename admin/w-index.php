<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Handle wooden item deletion
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    
    // Get wooden item image before deletion
    $stmt = $conn->prepare("SELECT image FROM wooden WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $wooden = $result->fetch_assoc();
    
    // Delete wooden item
    $stmt = $conn->prepare("DELETE FROM wooden WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        // Delete wooden item image if exists
        if (!empty($wooden['image'])) {
            $image_path = 'uploads/wooden/' . $wooden['image'];
            if (file_exists($image_path)) {
                unlink($image_path);
            }
        }
        $_SESSION['success'] = "Wooden item deleted successfully!";
    } else {
        $_SESSION['error'] = "Failed to delete wooden item!";
    }
    
    header("Location: w-index.php");
    exit();
}

// Fetch all wooden items with category names
$query = "SELECT w.*, c.name as category_name 
          FROM wooden w 
          LEFT JOIN categories c ON w.category_id = c.id 
          ORDER BY w.created_at ASC";
$result = $conn->query($query);
$wooden_items = $result->fetch_all(MYSQLI_ASSOC);

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
                        <li class="breadcrumb-item active" aria-current="page">Wooden Items</li>
                    </ol>
                </nav>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h1 class="h3 mb-0">Wooden Items</h1>
                    <a href="w-create.php" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Add New Wooden Item
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
                            <table id="woodenTable" class="table table-bordered table-hover">
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
                                    <?php foreach ($wooden_items as $item): ?>
                                        <tr>
                                            <td><?php echo $item['id']; ?></td>
                                            <td class="text-center">
                                                <?php if (!empty($item['image']) && file_exists('uploads/wooden/' . $item['image'])): ?>
                                                    <img src="uploads/wooden/<?php echo $item['image']; ?>" 
                                                         alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                                         class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover;">
                                                <?php else: ?>
                                                    <i class="fas fa-image fa-2x text-muted"></i>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                                            <td><?php echo htmlspecialchars($item['category_name']); ?></td>
                                            <td>
                                                <span class="badge <?php echo $item['status'] == 'active' ? 'bg-success' : 'bg-danger'; ?>">
                                                    <?php echo ucfirst($item['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('Y-m-d H:i:s', strtotime($item['created_at'])); ?></td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="w-edit.php?id=<?php echo $item['id']; ?>" 
                                                       class="btn btn-sm btn-primary" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-danger" 
                                                            onclick="deleteWoodenItem(<?php echo $item['id']; ?>)" title="Delete">
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
    $('#woodenTable').DataTable({
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
function deleteWoodenItem(id) {
    if (confirm('Are you sure you want to delete this wooden item?')) {
        window.location.href = 'w-index.php?delete=' + id;
    }
}
</script> 