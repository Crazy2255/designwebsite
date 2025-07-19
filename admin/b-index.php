<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Handle blog deletion
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    
    // Get blog image before deletion
    $stmt = $conn->prepare("SELECT image FROM blogs WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $blog = $result->fetch_assoc();
    
    // Delete blog
    $stmt = $conn->prepare("DELETE FROM blogs WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        // Delete image file if exists
        if (!empty($blog['image'])) {
            $image_path = 'uploads/blogs/' . $blog['image'];
            if (file_exists($image_path)) {
                unlink($image_path);
            }
        }
        $_SESSION['success'] = "Blog deleted successfully!";
    } else {
        $_SESSION['error'] = "Failed to delete blog!";
    }
    
    header("Location: b-index.php");
    exit();
}

// Fetch all blogs
$result = $conn->query("SELECT * FROM blogs ORDER BY created_at ASC");
$blogs = $result->fetch_all(MYSQLI_ASSOC);

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
                        <li class="breadcrumb-item active" aria-current="page">Blogs</li>
                    </ol>
                </nav>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h1 class="h3 mb-0">Blogs</h1>
                    <a href="b-create.php" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Add New Blog
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
                            <table id="blogsTable" class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th width="60">ID</th>
                                        <th width="150">Image</th>
                                        <th>Heading</th>
                                        <th>Author</th>
                                        <th width="120">Publish Date</th>
                                        <th width="100">Status</th>
                                        <th width="150">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($blogs as $blog): ?>
                                        <tr>
                                            <td><?php echo $blog['id']; ?></td>
                                            <td class="text-center">
                                                <?php if (!empty($blog['image']) && file_exists('uploads/blogs/' . $blog['image'])): ?>
                                                    <img src="uploads/blogs/<?php echo $blog['image']; ?>" 
                                                         alt="<?php echo htmlspecialchars($blog['heading']); ?>" 
                                                         class="img-thumbnail" style="width: 100px; height: 60px; object-fit: cover;">
                                                <?php else: ?>
                                                    <i class="fas fa-image fa-2x text-muted"></i>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($blog['heading']); ?></td>
                                            <td><?php echo htmlspecialchars($blog['author']); ?></td>
                                            <td><?php echo date('Y-m-d', strtotime($blog['publish_date'])); ?></td>
                                            <td>
                                                <span class="badge <?php echo $blog['status'] == 'active' ? 'bg-success' : 'bg-danger'; ?>">
                                                    <?php echo ucfirst($blog['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="b-edit.php?id=<?php echo $blog['id']; ?>" 
                                                       class="btn btn-sm btn-primary" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-danger" 
                                                            onclick="deleteBlog(<?php echo $blog['id']; ?>)" title="Delete">
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
    $('#blogsTable').DataTable({
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
function deleteBlog(id) {
    if (confirm('Are you sure you want to delete this blog?')) {
        window.location.href = 'b-index.php?delete=' + id;
    }
}
</script>

<style>
.table img {
    transition: transform 0.2s;
}

.table img:hover {
    transform: scale(2);
    cursor: pointer;
}
</style> 