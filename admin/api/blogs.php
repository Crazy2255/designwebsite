<?php
// Disable error reporting in production
error_reporting(0);
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);

// Start output buffering to catch any unwanted output
ob_start();

// Ensure JSON content type
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Function to send JSON response
function sendJsonResponse($data, $statusCode = 200) {
    ob_clean();
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit();
}

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    // Include database configuration
    if (!file_exists('../config/database.php')) {
        throw new Exception('Database configuration file not found');
    }
    require_once('../config/database.php');

    // Check database connection
    if (!isset($conn)) {
        throw new Exception('Database connection variable not set');
    }

    if (!$conn) {
        throw new Exception('Database connection failed: ' . mysqli_connect_error());
    }

    // Test database connection
    if (!$conn->ping()) {
        throw new Exception('Database connection lost');
    }

    // Set UTF-8MB4 charset
    if (!$conn->set_charset("utf8mb4")) {
        throw new Exception('Error setting charset utf8mb4: ' . $conn->error);
    }

    // Check if blogs table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'blogs'");
    if ($tableCheck->num_rows === 0) {
        throw new Exception('Blogs table does not exist');
    }

    // Check if specific blog ID is requested
    if (isset($_GET['id'])) {
        $blog_id = intval($_GET['id']);
        
        // First check if the blog exists
        $checkStmt = $conn->prepare("SELECT COUNT(*) as count FROM blogs WHERE id = ?");
        if (!$checkStmt) {
            throw new Exception('Failed to prepare check statement: ' . $conn->error);
        }
        
        $checkStmt->bind_param("i", $blog_id);
        if (!$checkStmt->execute()) {
            throw new Exception('Failed to execute check query: ' . $checkStmt->error);
        }
        
        $checkResult = $checkStmt->get_result();
        $count = $checkResult->fetch_assoc()['count'];
        
        if ($count === 0) {
            sendJsonResponse([
                'status' => 'error',
                'message' => 'Blog not found'
            ], 404);
        }
        
        // Get blog details
        $stmt = $conn->prepare("SELECT * FROM blogs WHERE id = ? AND status = 'active'");
        if (!$stmt) {
            throw new Exception('Failed to prepare statement: ' . $conn->error);
        }
        
        $stmt->bind_param("i", $blog_id);
        if (!$stmt->execute()) {
            throw new Exception('Failed to execute query: ' . $stmt->error);
        }
        
        $result = $stmt->get_result();
        $blog = $result->fetch_assoc();

        if (!$blog) {
            sendJsonResponse([
                'status' => 'error',
                'message' => 'Blog is not active'
            ], 404);
        }

        sendJsonResponse([
            'status' => 'success',
            'data' => [
                'id' => (int)$blog['id'],
                'heading' => $blog['heading'],
                'description' => $blog['description'],
                'image' => $blog['image'],
                'publish_date' => $blog['created_at']
            ]
        ]);
    }

    // Get all blogs
    $result = $conn->query("SELECT * FROM blogs WHERE status = 'active' ORDER BY created_at DESC");
    if (!$result) {
        throw new Exception('Failed to fetch blogs: ' . $conn->error);
    }

    $blogs = [];
    while ($row = $result->fetch_assoc()) {
        $blogs[] = [
            'id' => (int)$row['id'],
            'heading' => $row['heading'],
            'description' => $row['description'],
            'image' => $row['image'],
            'publish_date' => $row['created_at']
        ];
    }

    sendJsonResponse([
        'status' => 'success',
        'data' => $blogs
    ]);

} catch (Exception $e) {
    error_log("Blog API Error: " . $e->getMessage());
    
    // Get any output that might have been generated
    $output = ob_get_clean();
    if ($output) {
        error_log("Unexpected output before JSON: " . $output);
    }
    
    // In development environment, you might want to show the actual error
    sendJsonResponse([
        'status' => 'error',
        'message' => 'An error occurred while fetching blogs',
        'debug' => $e->getMessage() // Remove this line in production
    ], 500);
}

// Close the connection
if (isset($conn) && $conn) {
    $conn->close();
}

// Clear any remaining output
ob_end_clean();
?> 