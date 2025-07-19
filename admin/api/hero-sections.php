<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET');

require_once '../config/database.php';

try {
    // Fetch all hero sections
    $query = "SELECT id, name, description, banner_image, status FROM hero_sections ORDER BY created_at DESC";
    $result = $conn->query($query);
    
    if ($result) {
        $hero_sections = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode($hero_sections);
    } else {
        throw new Exception("Failed to fetch hero sections");
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ]);
}
?> 