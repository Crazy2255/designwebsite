<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

require_once 'config/database.php';

// Get counts from database
$counts = [];

// Categories count
$result = $conn->query("SELECT COUNT(*) as count FROM categories");
$counts['categories'] = $result->fetch_assoc()['count'];

// Wooden Items count
$result = $conn->query("SELECT COUNT(*) as count FROM wooden");
$counts['wooden'] = $result->fetch_assoc()['count'];

// Marbles count
$result = $conn->query("SELECT COUNT(*) as count FROM marbles");
$counts['marbles'] = $result->fetch_assoc()['count'];

// Materials count
$result = $conn->query("SELECT COUNT(*) as count FROM materials");
$counts['materials'] = $result->fetch_assoc()['count'];

// Colors count
$result = $conn->query("SELECT COUNT(*) as count FROM colors");
$counts['colors'] = $result->fetch_assoc()['count'];

// Products count
$result = $conn->query("SELECT COUNT(*) as count FROM products");
$counts['products'] = $result->fetch_assoc()['count'];

// Blogs count
$result = $conn->query("SELECT COUNT(*) as count FROM blogs");
$counts['blogs'] = $result->fetch_assoc()['count'];

// Testimonials count
$result = $conn->query("SELECT COUNT(*) as count FROM testimonials");
$counts['testimonials'] = $result->fetch_assoc()['count'];

// Gallery count
$result = $conn->query("SELECT COUNT(*) as count FROM gallery");
$counts['gallery'] = $result->fetch_assoc()['count'];

// Set the current directory for proper includes
$currentDirectory = '';

// Include header
include 'components/header.php';
?>

<div id="layoutSidenav">
    <?php include 'components/sidebar.php'; ?>
    
    <div id="layoutSidenav_content">
        <main>
            <div class="container px-4">
                <!-- Welcome Section -->
                <div class="welcome-section mt-4 mb-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h1 class="h3 mb-2">Welcome back, admin!</h1>
                            <p class="text-muted mb-0">Here's what's happening with your store today.</p>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <button class="btn btn-light">
                                <i class="fas fa-download me-2"></i>Download Report
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Statistics Cards -->
                <div class="row g-4 mb-4">
                    <!-- Categories -->
                    <div class="col-xl-4 col-md-6">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="stats-icon bg-gradient-primary">
                                        <i class="fas fa-tags"></i>
                                    </div>
                                    <div class="text-end">
                                        <h6 class="text-muted mb-1">Categories</h6>
                                        <h4 class="mb-0"><?php echo $counts['categories']; ?></h4>
                                    </div>
                                </div>
                                <a href="c-index.php" class="text-decoration-none">
                                    <div class="d-flex align-items-center text-primary">
                                        <span class="me-2">View All</span>
                                        <i class="fas fa-arrow-right"></i>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Wooden Items -->
                    <div class="col-xl-4 col-md-6">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="stats-icon bg-gradient-success">
                                        <i class="fas fa-tree"></i>
                                    </div>
                                    <div class="text-end">
                                        <h6 class="text-muted mb-1">Wooden Items</h6>
                                        <h4 class="mb-0"><?php echo $counts['wooden']; ?></h4>
                                    </div>
                                </div>
                                <a href="w-index.php" class="text-decoration-none">
                                    <div class="d-flex align-items-center text-success">
                                        <span class="me-2">View All</span>
                                        <i class="fas fa-arrow-right"></i>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Marbles -->
                    <div class="col-xl-4 col-md-6">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="stats-icon bg-gradient-danger">
                                        <i class="fas fa-gem"></i>
                                    </div>
                                    <div class="text-end">
                                        <h6 class="text-muted mb-1">Marbles</h6>
                                        <h4 class="mb-0"><?php echo $counts['marbles']; ?></h4>
                                    </div>
                                </div>
                                <a href="mr-index.php" class="text-decoration-none">
                                    <div class="d-flex align-items-center text-danger">
                                        <span class="me-2">View All</span>
                                        <i class="fas fa-arrow-right"></i>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Materials -->
                    <div class="col-xl-4 col-md-6">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="stats-icon bg-gradient-info">
                                        <i class="fas fa-box"></i>
                                    </div>
                                    <div class="text-end">
                                        <h6 class="text-muted mb-1">Materials</h6>
                                        <h4 class="mb-0"><?php echo $counts['materials']; ?></h4>
                                    </div>
                                </div>
                                <a href="m-index.php" class="text-decoration-none">
                                    <div class="d-flex align-items-center text-info">
                                        <span class="me-2">View All</span>
                                        <i class="fas fa-arrow-right"></i>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Colors -->
                    <div class="col-xl-4 col-md-6">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="stats-icon bg-gradient-warning">
                                        <i class="fas fa-palette"></i>
                                    </div>
                                    <div class="text-end">
                                        <h6 class="text-muted mb-1">Colors</h6>
                                        <h4 class="mb-0"><?php echo $counts['colors']; ?></h4>
                                    </div>
                                </div>
                                <a href="co-index.php" class="text-decoration-none">
                                    <div class="d-flex align-items-center text-warning">
                                        <span class="me-2">View All</span>
                                        <i class="fas fa-arrow-right"></i>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Products -->
                    <div class="col-xl-4 col-md-6">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="stats-icon bg-gradient-purple">
                                        <i class="fas fa-gift"></i>
                                    </div>
                                    <div class="text-end">
                                        <h6 class="text-muted mb-1">Products</h6>
                                        <h4 class="mb-0"><?php echo $counts['products']; ?></h4>
                                    </div>
                                </div>
                                <a href="p-index.php" class="text-decoration-none">
                                    <div class="d-flex align-items-center text-purple">
                                        <span class="me-2">View All</span>
                                        <i class="fas fa-arrow-right"></i>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Blogs -->
                    <div class="col-xl-4 col-md-6">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="stats-icon bg-gradient-pink">
                                        <i class="fas fa-blog"></i>
                                    </div>
                                    <div class="text-end">
                                        <h6 class="text-muted mb-1">Blogs</h6>
                                        <h4 class="mb-0"><?php echo $counts['blogs']; ?></h4>
                                    </div>
                                </div>
                                <a href="b-index.php" class="text-decoration-none">
                                    <div class="d-flex align-items-center text-pink">
                                        <span class="me-2">View All</span>
                                        <i class="fas fa-arrow-right"></i>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Testimonials -->
                    <div class="col-xl-4 col-md-6">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="stats-icon bg-gradient-orange">
                                        <i class="fas fa-quote-right"></i>
                                    </div>
                                    <div class="text-end">
                                        <h6 class="text-muted mb-1">Testimonials</h6>
                                        <h4 class="mb-0"><?php echo $counts['testimonials']; ?></h4>
                                    </div>
                                </div>
                                <a href="t-index.php" class="text-decoration-none">
                                    <div class="d-flex align-items-center text-orange">
                                        <span class="me-2">View All</span>
                                        <i class="fas fa-arrow-right"></i>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Gallery -->
                    <div class="col-xl-4 col-md-6">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="stats-icon bg-gradient-teal">
                                        <i class="fas fa-images"></i>
                                    </div>
                                    <div class="text-end">
                                        <h6 class="text-muted mb-1">Gallery</h6>
                                        <h4 class="mb-0"><?php echo $counts['gallery']; ?></h4>
                                    </div>
                                </div>
                                <a href="g-index.php" class="text-decoration-none">
                                    <div class="d-flex align-items-center text-teal">
                                        <span class="me-2">View All</span>
                                        <i class="fas fa-arrow-right"></i>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
        
        <?php include 'components/footer.php'; ?>
    </div>
</div>

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

/* Card styles */
.card {
    border: none;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    transition: transform 0.2s, box-shadow 0.2s;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.card-body {
    padding: 1.5rem;
}

/* Stats icon styles */
.stats-icon {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 0.5rem;
}

.stats-icon i {
    font-size: 1.5rem;
    color: #fff;
}

/* Gradient backgrounds */
.bg-gradient-primary {
    background: linear-gradient(45deg, #4e73df, #224abe);
}

.bg-gradient-success {
    background: linear-gradient(45deg, #2dce89, #2dcecc);
}

.bg-gradient-danger {
    background: linear-gradient(45deg, #e74a3b, #e74a8b);
}

.bg-gradient-info {
    background: linear-gradient(45deg, #36b9cc, #1a8efd);
}

.bg-gradient-warning {
    background: linear-gradient(45deg, #f6c23e, #f6923e);
}

.bg-gradient-purple {
    background: linear-gradient(45deg, #6f42c1, #a742c1);
}

.bg-gradient-pink {
    background: linear-gradient(45deg, #e83e8c, #e83e5a);
}

.bg-gradient-orange {
    background: linear-gradient(45deg, #fd7e14, #fd5514);
}

.bg-gradient-teal {
    background: linear-gradient(45deg, #20c997, #20c9c9);
}

/* Text colors */
.text-purple {
    color: #6f42c1;
}

.text-pink {
    color: #e83e8c;
}

.text-orange {
    color: #fd7e14;
}

.text-teal {
    color: #20c997;
}

/* Card hover effect */
.card a {
    opacity: 0.8;
    transition: opacity 0.2s;
}

.card:hover a {
    opacity: 1;
}

/* Responsive adjustments */
@media (max-width: 991.98px) {
    .card {
        margin-bottom: 1rem;
    }
}
</style> 
</style> 