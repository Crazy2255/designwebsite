<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET');

require_once '../config/database.php';

try {
    $subcategory = isset($_GET['subcategory']) ? $_GET['subcategory'] : null;
    $id = isset($_GET['id']) ? intval($_GET['id']) : null;
    $conn->set_charset("utf8mb4");

    $query = "SELECT p.*, 
              c.name as category_name,
              s.name as subcategory_name,
              GROUP_CONCAT(DISTINCT pi.image) as images,
              GROUP_CONCAT(DISTINCT CONCAT(col.name, ':', col.image)) as colors,
              GROUP_CONCAT(DISTINCT CONCAT(m.name, ':', m.image)) as materials,
              GROUP_CONCAT(DISTINCT CONCAT(mar.name, ':', mar.image)) as marbles,
              GROUP_CONCAT(DISTINCT CONCAT(w.name, ':', w.image)) as wooden
              FROM products p
              LEFT JOIN categories c ON p.category_id = c.id
              LEFT JOIN subcategories s ON p.subcategory_id = s.id
              LEFT JOIN product_images pi ON p.id = pi.product_id
              LEFT JOIN product_colors pc ON p.id = pc.product_id
              LEFT JOIN colors col ON pc.color_id = col.id
              LEFT JOIN product_materials pm ON p.id = pm.product_id
              LEFT JOIN materials m ON pm.material_id = m.id
              LEFT JOIN product_marbles pmar ON p.id = pmar.product_id
              LEFT JOIN marbles mar ON pmar.marble_id = mar.id
              LEFT JOIN product_wooden pw ON p.id = pw.product_id
              LEFT JOIN wooden w ON pw.wooden_id = w.id
              WHERE p.status = 'active'";

    if ($id) {
        $query .= " AND p.id = ?";
        $stmt = $conn->prepare($query . " GROUP BY p.id");
        $stmt->bind_param("i", $id);
    } elseif ($subcategory) {
        $query .= " AND s.name = ?";
        $stmt = $conn->prepare($query . " GROUP BY p.id ORDER BY p.created_at DESC");
        $stmt->bind_param("s", $subcategory);
    } else {
        $stmt = $conn->prepare($query . " GROUP BY p.id ORDER BY p.created_at DESC");
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $products = [];

    while ($row = $result->fetch_assoc()) {
        // Process images
        $images = $row['images'] ? explode(',', $row['images']) : [];
        
        // Process colors with images
        $colors = [];
        if ($row['colors']) {
            foreach (explode(',', $row['colors']) as $color) {
                $parts = explode(':', $color);
                if (count($parts) === 2) {
                    $colors[] = [
                        'name' => $parts[0],
                        'image' => $parts[1]
                    ];
                }
            }
        }

        // Process materials with images
        $materials = [];
        if ($row['materials']) {
            foreach (explode(',', $row['materials']) as $material) {
                $parts = explode(':', $material);
                if (count($parts) === 2) {
                    $materials[] = [
                        'name' => $parts[0],
                        'image' => $parts[1]
                    ];
                }
            }
        }

        // Process marbles with images
        $marbles = [];
        if ($row['marbles']) {
            foreach (explode(',', $row['marbles']) as $marble) {
                $parts = explode(':', $marble);
                if (count($parts) === 2) {
                    $marbles[] = [
                        'name' => $parts[0],
                        'image' => $parts[1]
                    ];
                }
            }
        }

        // Process wooden with images
        $wooden = [];
        if ($row['wooden']) {
            foreach (explode(',', $row['wooden']) as $wood) {
                $parts = explode(':', $wood);
                if (count($parts) === 2) {
                    $wooden[] = [
                        'name' => $parts[0],
                        'image' => $parts[1]
                    ];
                }
            }
        }

        $product = [
            'id' => $row['id'],
            'name' => $row['name'],
            'description' => $row['description'],
            'price' => $row['price'],
            'sku' => $row['sku'],
            'quantity' => $row['quantity'],
            'category_name' => $row['category_name'],
            'subcategory_name' => $row['subcategory_name'],
            'images' => $images,
            'colors' => array_values(array_unique($colors, SORT_REGULAR)),
            'materials' => array_values(array_unique($materials, SORT_REGULAR)),
            'marbles' => array_values(array_unique($marbles, SORT_REGULAR)),
            'wooden' => array_values(array_unique($wooden, SORT_REGULAR)),
            'created_at' => $row['created_at']
        ];

        $products[] = $product;
    }

    if ($id && empty($products)) {
        http_response_code(404);
        echo json_encode(['error' => 'Product not found']);
    } else {
        echo json_encode($id ? $products[0] : $products);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
?> 