<?php
// Disable error reporting in production
error_reporting(0);
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);

// Ensure JSON content type
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../config/database.php';

$response = ["success" => false, "data" => [], "error" => null];

try {
    // Ensure database connection is valid
    if (!$conn || $conn->connect_error) {
        throw new Exception("Database connection failed: " . ($conn ? $conn->connect_error : "Connection not established"));
    }

    // Ensure database is selected
    if (!$conn->select_db(DB_NAME)) {
        throw new Exception("Could not select database: " . $conn->error);
    }

    // Check if case_studies table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'case_studies'");
    if ($tableCheck->num_rows === 0) {
        throw new Exception("case_studies table does not exist");
    }

    // Check if specific ID is requested
    if (isset($_GET['id'])) {
        $id = intval($_GET['id']);
        $stmt = $conn->prepare("SELECT * FROM case_studies WHERE id = ?");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("i", $id);
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        $result = $stmt->get_result();
    } else {
        $result = $conn->query("SELECT * FROM case_studies ORDER BY id DESC");
        if (!$result) {
            throw new Exception("Query failed: " . $conn->error);
        }
    }

    $data = [];
    $upload_dir = '../uploads/case-studies/';
    
    while ($row = $result->fetch_assoc()) {
        // Safely decode JSON images
        $images = json_decode($row['images'], true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $images = [];
        }
        
        // Ensure images is an array
        $images = is_array($images) ? $images : [];
        
        // Filter out any non-existent images
        $valid_images = array_filter($images, function($img) use ($upload_dir) {
            return file_exists($upload_dir . $img);
        });
        
        $row['images'] = array_values($valid_images); // Reset array keys
        $data[] = $row;
    }
    
    if (empty($data) && isset($_GET['id'])) {
        throw new Exception("Case study not found");
    }
    
    $response['success'] = true;
    $response['data'] = $data;

} catch (Exception $e) {
    $response['error'] = $e->getMessage();
    $response['success'] = false;
    http_response_code(isset($_GET['id']) && $e->getMessage() === "Case study not found" ? 404 : 500);
}

echo json_encode($response);
exit;
