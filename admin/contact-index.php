<?php
session_start();
require_once 'config/database.php';

// Check if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $delete_query = "DELETE FROM contacts WHERE id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Contact deleted successfully!";
    } else {
        $_SESSION['error'] = "Error deleting contact: " . $conn->error;
    }
    header('Location: contact-index.php');
    exit();
}

// Fetch all contacts
$query = "SELECT * FROM contacts ORDER BY created_at DESC";
$result = $conn->query($query);
$contacts = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $contacts[] = $row;
    }
}

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
                        <li class="breadcrumb-item active" aria-current="page">Contact Enquiries</li>
                    </ol>
                </nav>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h1 class="h3 mb-0">Contact Enquiries</h1>
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
                            <table id="contactsTable" class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th width="80">ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Subject</th>
                                        <th width="100">Message</th>
                                        <th width="180">Date</th>
                                        <th width="100">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($contacts as $contact): ?>
                                        <tr>
                                            <td><?php echo $contact['id']; ?></td>
                                            <td><?php echo htmlspecialchars($contact['name']); ?></td>
                                            <td><?php echo htmlspecialchars($contact['email']); ?></td>
                                            <td><?php echo htmlspecialchars($contact['phone']); ?></td>
                                            <td><?php echo htmlspecialchars($contact['subject']); ?></td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-info" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#messageModal<?php echo $contact['id']; ?>">
                                                    View
                                                </button>
                                            </td>
                                            <td><?php echo date('Y-m-d H:i:s', strtotime($contact['created_at'])); ?></td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-sm btn-danger" 
                                                            onclick="confirmDelete(<?php echo $contact['id']; ?>)" 
                                                            title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>

                                        <!-- Message Modal -->
                                        <div class="modal fade" id="messageModal<?php echo $contact['id']; ?>" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Message from <?php echo htmlspecialchars($contact['name']); ?></h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <strong>Subject:</strong>
                                                            <p><?php echo htmlspecialchars($contact['subject']); ?></p>
                                                        </div>
                                                        <div>
                                                            <strong>Message:</strong>
                                                            <p><?php echo nl2br(htmlspecialchars($contact['message'])); ?></p>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
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
    $('#contactsTable').DataTable({
        dom: 'Bfrtip',
        buttons: [
            'copy', 'csv', 'excel', 'pdf', 'print'
        ],
        pageLength: 10,
        order: [[0, 'desc']],
        columnDefs: [
            { orderable: false, targets: [5, 7] },
            { searchable: false, targets: [5, 7] }
        ],
        responsive: true
    });
});

// Delete confirmation
function confirmDelete(id) {
    if (confirm('Are you sure you want to delete this contact?')) {
        window.location.href = 'contact-index.php?delete=' + id;
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

/* Main content area */
main {
    flex-grow: 1;
    padding: 0;
}

.container-fluid {
    padding-right: 1.5rem;
    padding-left: 1.5rem;
}

/* Table styles */
.table {
    margin-bottom: 0;
    font-size: 0.875rem;
}

.table th {
    font-weight: 500;
    background-color: #f8f9fa;
    white-space: nowrap;
}

.table td {
    vertical-align: middle;
}

.table-hover tbody tr:hover {
    background-color: rgba(0, 0, 0, 0.02);
}

/* DataTable styles */
.dataTables_wrapper {
    padding: 0;
}

.dataTables_length,
.dataTables_filter {
    margin-bottom: 1rem;
}

.dataTables_info,
.dataTables_paginate {
    margin-top: 1rem;
}

.dt-buttons {
    margin-bottom: 1rem;
}

.dt-button {
    background-color: #fff !important;
    border: 1px solid #dee2e6 !important;
    border-radius: 0.25rem !important;
    padding: 0.25rem 0.5rem !important;
    font-size: 0.875rem !important;
    line-height: 1.5 !important;
    color: #495057 !important;
}

.dt-button:hover {
    background-color: #e9ecef !important;
    border-color: #dee2e6 !important;
    color: #495057 !important;
}

/* Card styles */
.card {
    margin-bottom: 1.5rem;
    border: 1px solid #e9ecef;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.card-body {
    padding: 1.25rem;
}

/* Button styles */
.btn {
    font-size: 0.875rem;
    padding: 0.375rem 0.75rem;
}

.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
}
</style> 