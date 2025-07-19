<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Handle color deletion
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    
    // Get color image before deletion
    $stmt = $conn->prepare("SELECT image FROM colors WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $color = $result->fetch_assoc();
    
    // Delete color
    $stmt = $conn->prepare("DELETE FROM colors WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        // Delete color image if exists
        if (!empty($color['image'])) {
            $image_path = 'uploads/colors/' . $color['image'];
            if (file_exists($image_path)) {
                unlink($image_path);
            }
        }
        $_SESSION['success'] = "Color deleted successfully!";
    } else {
        $_SESSION['error'] = "Failed to delete color!";
    }
    
    header("Location: co-index.php");
    exit();
}

// Fetch all colors with category names
$query = "SELECT c.*, cat.name as category_name 
          FROM colors c 
          LEFT JOIN categories cat ON c.category_id = cat.id 
          ORDER BY c.created_at ASC";
$result = $conn->query($query);
$colors = $result->fetch_all(MYSQLI_ASSOC);

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
                        <li class="breadcrumb-item active" aria-current="page">Colors</li>
                    </ol>
                </nav>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h1 class="h3 mb-0">Colors</h1>
                    <a href="co-create.php" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Add New Color
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
                            <table id="colorsTable" class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th width="80">ID</th>
                                        <th width="100">Image</th>
                                        <th>Name</th>
                                        <th>Category</th>
                                        <th width="100">Color Code</th>
                                        <th width="100">Status</th>
                                        <th width="180">Created At</th>
                                        <th width="150">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($colors as $color): ?>
                                        <tr>
                                            <td><?php echo $color['id']; ?></td>
                                            <td class="text-center">
                                                <?php if (!empty($color['image']) && file_exists('uploads/colors/' . $color['image'])): ?>
                                                    <img src="uploads/colors/<?php echo $color['image']; ?>" 
                                                         alt="<?php echo htmlspecialchars($color['name']); ?>" 
                                                         class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover;">
                                                <?php else: ?>
                                                    <div class="color-preview" style="width: 50px; height: 50px; background-color: <?php echo $color['color_code']; ?>; border: 1px solid #dee2e6;"></div>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($color['name']); ?></td>
                                            <td><?php echo htmlspecialchars($color['category_name']); ?></td>
                                            <td>
                                                <?php if (!empty($color['color_code'])): ?>
                                                    <div class="d-flex align-items-center">
                                                        <div class="color-preview me-2" style="width: 20px; height: 20px; background-color: <?php echo $color['color_code']; ?>; border: 1px solid #dee2e6;"></div>
                                                        <?php echo $color['color_code']; ?>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge <?php echo $color['status'] == 'active' ? 'bg-success' : 'bg-danger'; ?>">
                                                    <?php echo ucfirst($color['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('Y-m-d H:i:s', strtotime($color['created_at'])); ?></td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="co-edit.php?id=<?php echo $color['id']; ?>" 
                                                       class="btn btn-sm btn-primary" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-danger" 
                                                            onclick="deleteColor(<?php echo $color['id']; ?>)" title="Delete">
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
    $('#colorsTable').DataTable({
        dom: 'Bfrtip',
        buttons: [
            'copy', 'csv', 'excel', 'pdf', 'print'
        ],
        pageLength: 10,
        order: [[0, 'desc']],
        columnDefs: [
            { orderable: false, targets: [1, 7] },
            { searchable: false, targets: [1, 7] }
        ],
        responsive: true
    });
});

// Delete confirmation
function deleteColor(id) {
    if (confirm('Are you sure you want to delete this color?')) {
        window.location.href = 'co-index.php?delete=' + id;
    }
}
</script> 