<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config/database.php';

// Check if user is not logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$success_message = '';
$error_message = '';

// Handle deletion
if (isset($_POST['delete_id'])) {
    try {
        $stmt = $conn->prepare("DELETE FROM subcategories WHERE id = ?");
        $stmt->bind_param("i", $_POST['delete_id']);
        
        if ($stmt->execute()) {
            $success_message = "Subcategory deleted successfully!";
        } else {
            $error_message = "Error deleting subcategory: " . $conn->error;
        }
    } catch (Exception $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}

// Fetch subcategories with their parent category names
try {
    $query = "SELECT s.*, c.name as category_name 
              FROM subcategories s 
              LEFT JOIN categories c ON s.category_id = c.id 
              ORDER BY s.created_at ASC";
    $result = $conn->query($query);
    $subcategories = [];
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $subcategories[] = $row;
        }
    }
} catch (Exception $e) {
    $error_message = "Error fetching subcategories: " . $e->getMessage();
}

include 'components/header.php';
?>

<div id="layoutSidenav">
    <?php include 'components/sidebar.php'; ?>
    
    <div id="layoutSidenav_content">
        <main class="p-4">
            <div class="container-fluid p-0">
                <!-- Header Section -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h1 class="h3 mb-2">Subcategories</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="dashboard.php" class="text-decoration-none">Dashboard</a></li>
                                <li class="breadcrumb-item active">Subcategories</li>
                            </ol>
                        </nav>
                    </div>
                    <div>
                        <a href="sub-create.php" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus me-2"></i>Add New Subcategory
                        </a>
                    </div>
                </div>

                <?php if ($success_message): ?>
                    <div class="alert alert-success alert-dismissible fade show py-2" role="alert">
                        <?php echo $success_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if ($error_message): ?>
                    <div class="alert alert-danger alert-dismissible fade show py-2" role="alert">
                        <?php echo $error_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <!-- Table Card -->
                <div class="card shadow-sm">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table id="subcategoriesTable" class="table table-bordered table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="text-center" style="width: 60px;">ID</th>
                                        <th>NAME</th>
                                        <th>PARENT CATEGORY</th>
                                        <th class="text-center" style="width: 100px;">STATUS</th>
                                        <th class="text-center" style="width: 140px;">CREATED AT</th>
                                        <th class="text-center" style="width: 100px;">ACTIONS</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($subcategories)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-3">No subcategories found</td>
                                    </tr>
                                    <?php else: ?>
                                        <?php foreach ($subcategories as $subcategory): ?>
                                        <tr>
                                            <td class="text-center"><?php echo $subcategory['id']; ?></td>
                                            <td><?php echo htmlspecialchars($subcategory['name']); ?></td>
                                            <td><?php echo htmlspecialchars($subcategory['category_name']); ?></td>
                                            <td class="text-center">
                                                <span class="badge <?php echo $subcategory['status'] === 'active' ? 'bg-success' : 'bg-danger'; ?> rounded-pill">
                                                    <?php echo ucfirst($subcategory['status']); ?>
                                                </span>
                                            </td>
                                            <td class="text-center"><?php echo date('Y-m-d H:i:s', strtotime($subcategory['created_at'])); ?></td>
                                            <td class="text-center">
                                                <a href="sub-edit.php?id=<?php echo $subcategory['id']; ?>" 
                                                   class="btn btn-sm btn-primary btn-icon me-1" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" 
                                                        class="btn btn-sm btn-danger btn-icon delete-subcategory" 
                                                        data-id="<?php echo $subcategory['id']; ?>"
                                                        data-name="<?php echo htmlspecialchars($subcategory['name']); ?>"
                                                        title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete the subcategory "<span id="subcategoryName"></span>"?
            </div>
            <div class="modal-footer py-1">
                <form method="POST" action="">
                    <input type="hidden" name="delete_id" id="deleteId">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
/* Custom styles for the page */
main {
    background: #f8f9fa;
}

.breadcrumb {
    margin: 0;
    padding: 0;
    background: transparent;
}

.breadcrumb-item a {
    color: #0d6efd;
    font-size: 0.875rem;
}

.breadcrumb-item.active {
    font-size: 0.875rem;
}

.card {
    border: none;
    margin-bottom: 0;
}

#layoutSidenav_content {
    flex: 1 0 auto;
    min-width: 0;
    display: flex;
    flex-direction: column;
}

.table {
    margin-bottom: 0;
}

.table th {
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.75rem;
    border-top: none;
    white-space: nowrap;
    padding: 0.75rem;
}

.table td {
    vertical-align: middle;
    font-size: 0.875rem;
    padding: 0.75rem;
}

.badge {
    font-size: 0.75rem;
    font-weight: 500;
    padding: 0.4em 0.8em;
}

.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

.btn-icon {
    padding: 0.25rem 0.5rem;
    line-height: 1;
}

.dataTables_wrapper .dataTables_length select {
    padding: 0.25rem 1.5rem 0.25rem 0.5rem;
    font-size: 0.875rem;
    border-radius: 0.25rem;
    border: 1px solid #ced4da;
    background-color: #fff;
}

.dataTables_wrapper .dataTables_filter input {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
    border-radius: 0.25rem;
    border: 1px solid #ced4da;
    background-color: #fff;
}

.dataTables_wrapper .dataTables_info {
    font-size: 0.875rem;
    padding-top: 0.5rem;
}

.dataTables_wrapper .dataTables_paginate .paginate_button {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
    margin: 0 0.125rem;
    border-radius: 0.25rem;
}

.h3 {
    font-size: 1.5rem;
    font-weight: 500;
}

.alert {
    margin-bottom: 1rem;
}

.modal-sm {
    max-width: 300px;
}

.modal-header {
    border-bottom: 1px solid #dee2e6;
}

.modal-footer {
    border-top: 1px solid #dee2e6;
}
</style>

<script>
$(document).ready(function() {
    // Initialize DataTable with explicit column definitions
    var table = $('#subcategoriesTable').DataTable({
        columns: [
            { data: null, width: "60px" },  // ID
            { data: null },                 // NAME
            { data: null },                 // PARENT CATEGORY
            { data: null, width: "100px" }, // STATUS
            { data: null, width: "140px" }, // CREATED AT
            { data: null, width: "100px", orderable: false } // ACTIONS
        ],
        order: [[0, 'desc']],
        pageLength: 10,
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
        dom: '<"row align-items-center"<"col-md-6"l><"col-md-6"f>>rtip',
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search subcategories...",
            lengthMenu: "_MENU_ per page",
            info: "Showing _START_ to _END_ of _TOTAL_ entries",
            paginate: {
                first: '<i class="fas fa-angle-double-left"></i>',
                last: '<i class="fas fa-angle-double-right"></i>',
                next: '<i class="fas fa-angle-right"></i>',
                previous: '<i class="fas fa-angle-left"></i>'
            },
            emptyTable: "No subcategories found"
        }
    });

    // Handle delete button clicks
    $(document).on('click', '.delete-subcategory', function() {
        var id = $(this).data('id');
        var name = $(this).data('name');
        $('#subcategoryName').text(name);
        $('#deleteId').val(id);
        $('#deleteModal').modal('show');
    });
});
</script>

<?php include 'components/footer.php'; ?> 