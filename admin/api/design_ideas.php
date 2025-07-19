<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

$response = array('success' => false, 'data' => [], 'error' => null);

// Define the base URL for images
$base_url = 'http://localhost/design-new';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (isset($_GET['id'])) {
            // Get specific design idea
            $id = intval($_GET['id']);
            $stmt = $conn->prepare("SELECT * FROM design_ideas WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $data = $result->fetch_assoc();
            
            if ($data) {
                // Format the data
                $data['work_images'] = json_decode($data['work_images'], true) ?: [];
                $data['main_image_url'] = !empty($data['main_image']) 
                    ? $base_url . '/admin/uploads/design-ideas/' . $data['main_image']
                    : null;
                $data['work_image_urls'] = array_map(function($img) use ($base_url) {
                    return $base_url . '/admin/uploads/design-ideas/' . $img;
                }, $data['work_images']);
                
                $response['data'] = $data;
                $response['success'] = true;
            } else {
                throw new Exception("Design idea not found");
            }
        } else {
            // Get all design ideas with optional limit
            $limit = isset($_GET['limit']) ? intval($_GET['limit']) : null;
            $query = "SELECT * FROM design_ideas ORDER BY created_at DESC";
            if ($limit) {
                $query .= " LIMIT ?";
            }
            
            $stmt = $conn->prepare($query);
            if ($limit) {
                $stmt->bind_param("i", $limit);
            }
            $stmt->execute();
            $result = $stmt->get_result();
            
            $data = [];
            while ($row = $result->fetch_assoc()) {
                // Format each row
                $row['work_images'] = json_decode($row['work_images'], true) ?: [];
                $row['main_image_url'] = !empty($row['main_image']) 
                    ? $base_url . '/admin/uploads/design-ideas/' . $row['main_image']
                    : null;
                $row['work_image_urls'] = array_map(function($img) use ($base_url) {
                    return $base_url . '/admin/uploads/design-ideas/' . $img;
                }, $row['work_images']);
                
                // Add image counts
                $row['work_images_count'] = count($row['work_images']);
                
                $data[] = $row;
            }
            
            $response['data'] = $data;
            $response['success'] = true;
        }
    } else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Search functionality
        $searchData = json_decode(file_get_contents('php://input'), true);
        $searchTerm = isset($searchData['search']) ? $searchData['search'] : '';
        
        if (!empty($searchTerm)) {
            $searchTerm = "%{$searchTerm}%";
            $stmt = $conn->prepare("SELECT * FROM design_ideas WHERE name LIKE ? OR description LIKE ? ORDER BY created_at DESC");
            $stmt->bind_param("ss", $searchTerm, $searchTerm);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $data = [];
            while ($row = $result->fetch_assoc()) {
                // Format each row
                $row['work_images'] = json_decode($row['work_images'], true) ?: [];
                $row['main_image_url'] = !empty($row['main_image']) 
                    ? $base_url . '/admin/uploads/design-ideas/' . $row['main_image']
                    : null;
                $row['work_image_urls'] = array_map(function($img) use ($base_url) {
                    return $base_url . '/admin/uploads/design-ideas/' . $img;
                }, $row['work_images']);
                
                // Add image counts
                $row['work_images_count'] = count($row['work_images']);
                
                $data[] = $row;
            }
            
            $response['data'] = $data;
            $response['success'] = true;
        } else {
            throw new Exception("Search term is required");
        }
    }
} catch (Exception $e) {
    $response['error'] = $e->getMessage();
} catch (Error $e) {
    $response['error'] = "Server error: " . $e->getMessage();
}

// Add debug information in development
if (isset($_GET['debug'])) {
    $response['debug'] = [
        'query' => isset($query) ? $query : null,
        'method' => $_SERVER['REQUEST_METHOD'],
        'params' => $_GET,
        'base_url' => $base_url,
    ];
}

echo json_encode($response);
?> 