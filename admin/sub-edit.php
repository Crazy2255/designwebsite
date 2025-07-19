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

// Get subcategory ID from URL
$subcategory_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$subcategory_id) {
    header('Location: sub-index.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $category_id = (int)$_POST['category_id'];
    $status = $_POST['status'];

    // Validate input
    if (empty($name)) {
        $error_message = "Subcategory name is required";
    } elseif ($category_id <= 0) {
        $error_message = "Please select a valid category";
    } else {
        try {
            // Update subcategory
            $stmt = $conn->prepare("UPDATE subcategories SET name = ?, category_id = ?, status = ? WHERE id = ?");
            $stmt->bind_param("sisi", $name, $category_id, $status, $subcategory_id);
            
            if ($stmt->execute()) {
                $success_message = "Subcategory updated successfully!";
            } else {
                $error_message = "Error updating subcategory: " . $conn->error;
            }
        } catch (Exception $e) {
            $error_message = "Error: " . $e->getMessage();
        }
    }
}

// Fetch subcategory data
try {
    $stmt = $conn->prepare("SELECT * FROM subcategories WHERE id = ?");
    $stmt->bind_param("i", $subcategory_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        header('Location: sub-index.php');
        exit;
    }
    
    $subcategory = $result->fetch_assoc();
} catch (Exception $e) {
    $error_message = "Error fetching subcategory: " . $e->getMessage();
}

// Fetch categories for dropdown
$categories = [];
try {
    $stmt = $conn->prepare("SELECT id, name FROM categories WHERE status = 'active' ORDER BY name");
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
} catch (Exception $e) {
    $error_message = "Error fetching categories: " . $e->getMessage();
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
                        <h1 class="h3 mb-2">Edit Subcategory</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="dashboard.php" class="text-decoration-none">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="sub-index.php" class="text-decoration-none">Subcategories</a></li>
                                <li class="breadcrumb-item active">Edit Subcategory</li>
                            </ol>
                        </nav>
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

                <div class="card shadow-sm">
                    <div class="card-body">
                        <form method="POST" action="" class="max-width-600">
                            <div class="mb-3">
                                <label for="name" class="form-label">Subcategory Name</label>
                                <input type="text" class="form-control form-control-sm" id="name" name="name" 
                                       value="<?php echo htmlspecialchars($subcategory['name']); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="category_id" class="form-label">Parent Category</label>
                                <select class="form-select form-select-sm" id="category_id" name="category_id" required>
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['id']; ?>" 
                                            <?php echo ($subcategory['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($category['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label d-block">Status</label>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="status" 
                                           id="statusActive" value="active" 
                                           <?php echo ($subcategory['status'] === 'active') ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="statusActive">Active</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="status" 
                                           id="statusInactive" value="inactive"
                                           <?php echo ($subcategory['status'] === 'inactive') ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="statusInactive">Inactive</label>
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="fas fa-save me-1"></i> Update Subcategory
                                </button>
                                <a href="sub-index.php" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-times me-1"></i> Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
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
}

.max-width-600 {
    max-width: 600px;
}
#layoutSidenav_content {
    flex: 1 0 auto;
    min-width: 0;
    display: flex;
    flex-direction: column;
}
.form-label {
    font-size: 0.875rem;
    font-weight: 500;
    margin-bottom: 0.5rem;
}

.form-control-sm, .form-select-sm {
    font-size: 0.875rem;
    padding: 0.25rem 0.5rem;
}

.form-check-label {
    font-size: 0.875rem;
}

.btn-sm {
    padding: 0.25rem 0.75rem;
    font-size: 0.875rem;
}

.h3 {
    font-size: 1.5rem;
    font-weight: 500;
}

.alert {
    margin-bottom: 1rem;
    font-size: 0.875rem;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    form.addEventListener('submit', function(event) {
        const name = document.getElementById('name').value.trim();
        const category = document.getElementById('category_id').value;
        
        if (!name) {
            event.preventDefault();
            alert('Please enter a subcategory name');
            return;
        }
        
        if (!category) {
            event.preventDefault();
            alert('Please select a parent category');
            return;
        }
    });
});
</script>

<?php include 'components/footer.php'; ?> 