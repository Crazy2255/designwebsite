<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

require_once '../config/database.php';

try {
    // Fetch only active gallery items, ordered by latest first
    $query = "SELECT id, name, image, description FROM gallery WHERE status = 'active' ORDER BY id DESC";
    $result = $conn->query($query);
    
    $gallery_items = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $gallery_items[] = [
                'id' => $row['id'],
                'name' => $row['name'],
                'image' => $row['image'],
                'description' => $row['description']
            ];
        }
    }
    
    echo json_encode($gallery_items);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch gallery items']);
}

$conn->close();
?> 