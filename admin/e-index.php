<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Handle deletion
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM enquiries WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Enquiry deleted successfully!";
    } else {
        $_SESSION['error'] = "Failed to delete enquiry!";
    }
    
    header("Location: e-index.php");
    exit();
}

// Handle status update
if (isset($_GET['status']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $status = $_GET['status'];
    
    if (in_array($status, ['pending', 'contacted', 'completed'])) {
        $stmt = $conn->prepare("UPDATE enquiries SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Status updated to " . ucfirst($status);
        } else {
            $_SESSION['error'] = "Failed to update status!";
        }
    }
    
    header("Location: e-index.php");
    exit();
}

// Fetch all enquiries with sorting
$result = $conn->query("SELECT * FROM enquiries ORDER BY created_at DESC");
$enquiries = $result->fetch_all(MYSQLI_ASSOC);

// Set the current directory for proper includes
$currentDirectory = '';
?>

<?php include 'components/header.php'; ?>

<div id="layoutSidenav">
    <?php include 'components/sidebar.php'; ?>
    <div id="layoutSidenav_content">
        <main>
            <div class="container-fluid px-4">
                <h1 class="mt-4">Enquiries Management</h1>
                <ol class="breadcrumb mb-4">
                    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item active">Enquiries</li>
                </ol>
                
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-envelope me-1"></i>
                            Manage Enquiries
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-primary btn-sm" onclick="exportToExcel()">
                                <i class="fas fa-file-excel me-1"></i> Export to Excel
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
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

                        <div class="table-responsive">
                            <table id="enquiriesTable" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Contact Info</th>
                                        <th>Product Details</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($enquiries as $enquiry): ?>
                                        <tr>
                                            <td><?php echo $enquiry['id']; ?></td>
                                            <td>
                                                <div class="fw-bold"><?php echo htmlspecialchars($enquiry['name']); ?></div>
                                            </td>
                                            <td>
                                                <div>
                                                    <a href="mailto:<?php echo htmlspecialchars($enquiry['email']); ?>" class="text-decoration-none">
                                                        <i class="fas fa-envelope me-1"></i><?php echo htmlspecialchars($enquiry['email']); ?>
                                                    </a>
                                                </div>
                                                <div>
                                                    <a href="tel:<?php echo htmlspecialchars($enquiry['phone']); ?>" class="text-decoration-none">
                                                        <i class="fas fa-phone me-1"></i><?php echo htmlspecialchars($enquiry['phone']); ?>
                                                    </a>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="fw-bold"><?php echo htmlspecialchars($enquiry['product_name']); ?></div>
                                                <div class="text-muted">SKU: <?php echo htmlspecialchars($enquiry['product_sku']); ?></div>
                                            </td>
                                            <td>
                                                <div class="dropdown">
                                                    <button class="btn btn-sm dropdown-toggle status-btn status-<?php echo $enquiry['status']; ?>" 
                                                            type="button" 
                                                            data-bs-toggle="dropdown">
                                                        <?php echo ucfirst($enquiry['status']); ?>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li>
                                                            <a class="dropdown-item <?php echo $enquiry['status'] === 'pending' ? 'active' : ''; ?>" 
                                                               href="e-index.php?id=<?php echo $enquiry['id']; ?>&status=pending">
                                                                <i class="fas fa-clock me-2"></i>Pending
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item <?php echo $enquiry['status'] === 'contacted' ? 'active' : ''; ?>" 
                                                               href="e-index.php?id=<?php echo $enquiry['id']; ?>&status=contacted">
                                                                <i class="fas fa-phone me-2"></i>Contacted
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item <?php echo $enquiry['status'] === 'completed' ? 'active' : ''; ?>" 
                                                               href="e-index.php?id=<?php echo $enquiry['id']; ?>&status=completed">
                                                                <i class="fas fa-check me-2"></i>Completed
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </td>
                                            <td>
                                                <?php echo date('M d, Y H:i', strtotime($enquiry['created_at'])); ?>
                                            </td>
                                            <td>
                                                <div class="d-flex gap-1">
                                                    <button 
                                                        onclick="viewEnquiry(<?php echo htmlspecialchars(json_encode($enquiry)); ?>)" 
                                                        class="btn btn-primary btn-sm"
                                                        title="View Details"
                                                    >
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button 
                                                        onclick="deleteEnquiry(<?php echo $enquiry['id']; ?>)" 
                                                        class="btn btn-danger btn-sm"
                                                        title="Delete"
                                                    >
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

<!-- View Enquiry Modal -->
<div class="modal fade" id="viewEnquiryModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Enquiry Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card mb-3">
                            <div class="card-header">
                                <i class="fas fa-user me-1"></i> Contact Information
                            </div>
                            <div class="card-body">
                                <p><strong>Name:</strong> <span id="modalName"></span></p>
                                <p>
                                    <strong>Email:</strong> 
                                    <a href="#" id="modalEmailLink"><span id="modalEmail"></span></a>
                                </p>
                                <p>
                                    <strong>Phone:</strong> 
                                    <a href="#" id="modalPhoneLink"><span id="modalPhone"></span></a>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card mb-3">
                            <div class="card-header">
                                <i class="fas fa-box me-1"></i> Product Information
                            </div>
                            <div class="card-body">
                                <p><strong>Product:</strong> <span id="modalProduct"></span></p>
                                <p><strong>SKU:</strong> <span id="modalSku"></span></p>
                                <p>
                                    <strong>Status:</strong> 
                                    <div class="dropdown d-inline-block ms-2">
                                        <button class="btn btn-sm dropdown-toggle status-btn status-" 
                                                id="modalStatusButton"
                                                type="button" 
                                                data-bs-toggle="dropdown">
                                            <span id="modalStatus" class="status-text"></span>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a class="dropdown-item" href="#" onclick="updateStatus(this)" data-status="pending">
                                                    <i class="fas fa-clock me-2"></i>Pending
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="#" onclick="updateStatus(this)" data-status="contacted">
                                                    <i class="fas fa-phone me-2"></i>Contacted
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="#" onclick="updateStatus(this)" data-status="completed">
                                                    <i class="fas fa-check me-2"></i>Completed
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </p>
                                <p><strong>Date:</strong> <span id="modalDate"></span></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-comment me-1"></i> Message
                    </div>
                    <div class="card-body">
                        <p id="modalMessage"></p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
.status-btn {
    min-width: 100px;
}
.status-pending {
    background-color: #ffc107;
    border-color: #ffc107;
    color: #000;
}
.status-contacted {
    background-color: #17a2b8;
    border-color: #17a2b8;
    color: #fff;
}
.status-completed {
    background-color: #28a745;
    border-color: #28a745;
    color: #fff;
}
.badge.status-pending {
    background-color: #ffc107;
    color: #000;
}
.badge.status-contacted {
    background-color: #17a2b8;
}
.badge.status-completed {
    background-color: #28a745;
}

.dropdown-item.active {
    background-color: #e9ecef;
    color: #000;
}

.dropdown-item:hover {
    background-color: #f8f9fa;
}

#modalStatusButton {
    min-width: 120px;
}

#modalStatusButton:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

.status-text {
    display: inline-block;
    min-width: 70px;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.fa-spinner {
    animation: spin 1s linear infinite;
}
</style>

<script>
let currentEnquiryId = null;

function viewEnquiry(enquiry) {
    currentEnquiryId = enquiry.id;
    document.getElementById('modalName').textContent = enquiry.name;
    document.getElementById('modalEmail').textContent = enquiry.email;
    document.getElementById('modalEmailLink').href = 'mailto:' + enquiry.email;
    document.getElementById('modalPhone').textContent = enquiry.phone;
    document.getElementById('modalPhoneLink').href = 'tel:' + enquiry.phone;
    document.getElementById('modalProduct').textContent = enquiry.product_name;
    document.getElementById('modalSku').textContent = enquiry.product_sku;
    document.getElementById('modalMessage').textContent = enquiry.message;
    document.getElementById('modalDate').textContent = new Date(enquiry.created_at).toLocaleString();
    
    const statusButton = document.getElementById('modalStatusButton');
    const statusText = document.getElementById('modalStatus');
    
    // Update status button appearance
    statusButton.className = 'btn btn-sm dropdown-toggle status-btn status-' + enquiry.status;
    statusText.textContent = enquiry.status.charAt(0).toUpperCase() + enquiry.status.slice(1);
    
    // Update active state in dropdown
    document.querySelectorAll('#viewEnquiryModal .dropdown-item').forEach(item => {
        item.classList.remove('active');
        if(item.getAttribute('data-status') === enquiry.status) {
            item.classList.add('active');
        }
    });
    
    new bootstrap.Modal(document.getElementById('viewEnquiryModal')).show();
}

function updateStatus(element) {
    const status = element.getAttribute('data-status');
    if (!currentEnquiryId) return;

    // Show loading state
    const statusButton = document.getElementById('modalStatusButton');
    const originalText = statusButton.innerHTML;
    statusButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
    statusButton.disabled = true;

    // Update status
    window.location.href = `e-index.php?id=${currentEnquiryId}&status=${status}`;
}

function deleteEnquiry(id) {
    if (confirm('Are you sure you want to delete this enquiry? This action cannot be undone.')) {
        window.location.href = 'e-index.php?delete=' + id;
    }
}

function exportToExcel() {
    const table = $('#enquiriesTable').DataTable();
    table.button('.buttons-excel').trigger();
}
</script> 