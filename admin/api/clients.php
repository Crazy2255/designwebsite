<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET');

require_once '../config/database.php';

try {
    // Fetch all active clients
    $query = "SELECT id, name, image, status FROM clients WHERE status = 'active' ORDER BY created_at DESC";
    $result = $conn->query($query);
    
    if ($result) {
        $clients = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode($clients);
    } else {
        throw new Exception("Failed to fetch clients");
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ]);
}
?> 