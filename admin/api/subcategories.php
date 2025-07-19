<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET');

require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : null;
    
    if ($category_id === null) {
        throw new Exception('Category ID is required');
    }

    $query = "SELECT id, name, status FROM subcategories WHERE category_id = ? AND status = 'active' ORDER BY id ASC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $subcategories = [];
    while ($row = $result->fetch_assoc()) {
        $subcategories[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'status' => $row['status']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'subcategories' => $subcategories
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 