<?php
// Disable error reporting for production
error_reporting(0);
ini_set('display_errors', 0);

// Set JSON content type
header('Content-Type: application/json; charset=UTF-8');

// Include database configuration
require_once('../config/db.php');

try {
    // Check database connection
    if (!$conn) {
        throw new Exception("Database connection failed");
    }

    // Select database
    if (!mysqli_select_db($conn, $dbname)) {
        throw new Exception("Database selection failed");
    }

    // Check if blogs table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'blogs'");
    if ($tableCheck->num_rows === 0) {
        // Create blogs table if it doesn't exist
        $createTable = "CREATE TABLE blogs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            heading VARCHAR(255) NOT NULL,
            description TEXT,
            image VARCHAR(255),
            author_id INT,
            status ENUM('active', 'inactive') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (author_id) REFERENCES users(id)
        )";
        
        if (!$conn->query($createTable)) {
            throw new Exception("Failed to create blogs table: " . $conn->error);
        }
        
        echo json_encode([
            "status" => "success",
            "message" => "Blogs table created successfully"
        ]);
        exit();
    }

    // Get table structure
    $result = $conn->query("DESCRIBE blogs");
    $columns = [];
    while ($row = $result->fetch_assoc()) {
        $columns[$row['Field']] = $row;
    }

    // Check required columns
    $requiredColumns = [
        'id' => 'INT',
        'heading' => 'VARCHAR',
        'description' => 'TEXT',
        'image' => 'VARCHAR',
        'author_id' => 'INT',
        'status' => 'ENUM',
        'created_at' => 'TIMESTAMP',
        'updated_at' => 'TIMESTAMP'
    ];

    $missingColumns = [];
    foreach ($requiredColumns as $column => $type) {
        if (!isset($columns[$column])) {
            $missingColumns[] = $column;
        }
    }

    if (!empty($missingColumns)) {
        echo json_encode([
            "status" => "error",
            "message" => "Missing columns in blogs table",
            "missing_columns" => $missingColumns
        ]);
        exit();
    }

    // Check if there are any blogs
    $result = $conn->query("SELECT COUNT(*) as count FROM blogs WHERE status = 'active'");
    $count = $result->fetch_assoc()['count'];

    echo json_encode([
        "status" => "success",
        "message" => "Blogs table structure is correct",
        "total_blogs" => $count,
        "columns" => array_keys($columns)
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}

// Close the connection
if (isset($conn)) {
    mysqli_close($conn);
}
?> 