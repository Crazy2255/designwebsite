<?php
// Allow cross-origin requests
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection
require_once '../config/database.php';

// Handle GET request
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        // Fetch active categories
        $stmt = $conn->prepare("SELECT id, name, image, status, created_at FROM categories ORDER BY id ASC");
        $stmt->execute();
        $result = $stmt->get_result();
        
        if (!$result) {
            throw new Exception("Database query failed: " . $conn->error);
        }
        
        $categories = [];
        while ($row = $result->fetch_assoc()) {
            // Format the data
            $categories[] = [
                'id' => (int)$row['id'],
                'name' => $row['name'],
                'image' => $row['image'],
                'status' => $row['status'],
                'created_at' => $row['created_at']
            ];
        }
        
        // Return success response
        echo json_encode([
            'status' => 'success',
            'data' => $categories,
            'message' => 'Categories fetched successfully'
        ]);
        
    } catch (Exception $e) {
        // Log the error
        error_log("Categories API Error: " . $e->getMessage());
        
        // Return error response
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to fetch categories',
            'error' => $e->getMessage()
        ]);
    }
} else {
    // Return method not allowed error
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Method not allowed'
    ]);
} 