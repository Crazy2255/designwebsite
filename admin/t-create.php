<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $person_name = trim($_POST['person_name']);
    $designation = trim($_POST['designation']);
    $message = trim($_POST['message']);
    $status = isset($_POST['status']) ? 'active' : 'inactive';
    
    // Validate inputs
    if (empty($person_name)) {
        $_SESSION['error'] = "Person name is required!";
    } elseif (empty($designation)) {
        $_SESSION['error'] = "Designation is required!";
    } elseif (empty($message)) {
        $_SESSION['error'] = "Message is required!";
    } else {
        // Insert testimonial
        $stmt = $conn->prepare("INSERT INTO testimonials (person_name, designation, message, status) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $person_name, $designation, $message, $status);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Testimonial added successfully!";
            header("Location: t-index.php");
            exit();
        } else {
            $_SESSION['error'] = "Failed to add testimonial!";
        }
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
                        <li class="breadcrumb-item"><a href="t-index.php">Testimonials</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Add New Testimonial</li>
                    </ol>
                </nav>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h1 class="h3 mb-0">Add New Testimonial</h1>
                    <a href="t-index.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to List
                    </a>
                </div>

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
                        <form action="t-create.php" method="POST">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label for="person_name" class="form-label">Person Name</label>
                                        <input type="text" class="form-control" id="person_name" name="person_name" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="designation" class="form-label">Designation</label>
                                        <input type="text" class="form-control" id="designation" name="designation" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="message" class="form-label">Message</label>
                                        <textarea class="form-control" id="message" name="message" rows="4" required></textarea>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label d-block">Status</label>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="status" name="status" value="active" checked>
                                            <label class="form-check-label" for="status">Active</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-4 border-top pt-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Save Testimonial
                                </button>
                                <a href="t-index.php" class="btn btn-secondary ms-2">
                                    <i class="fas fa-times me-2"></i>Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
        <?php include 'components/footer.php'; ?>
    </div>
</div>

<script>
// Initialize text editor for message
if (typeof ClassicEditor !== 'undefined') {
    ClassicEditor
        .create(document.querySelector('#message'))
        .catch(error => {
            console.error(error);
        });
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

/* Form styles */
.ck-editor__editable {
    min-height: 200px;
}
</style> 