<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

require_once '../config/database.php';


try {
    // Fetch only active testimonials
    $query = "SELECT id, person_name, designation, message FROM testimonials WHERE status = 'active' ORDER BY id DESC";
    $result = $conn->query($query);
    
    $testimonials = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $testimonials[] = [
                'id' => $row['id'],
                'person_name' => $row['person_name'],
                'designation' => $row['designation'],
                'message' => $row['message']
            ];
        }
    }
    
    echo json_encode($testimonials);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch testimonials']);
}

$conn->close();
?> 