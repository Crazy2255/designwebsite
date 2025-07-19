<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once '../config/database.php';

// Get POST data
$data = array(
    'fullName' => $_POST['fullName'] ?? '',
    'email' => $_POST['email'] ?? '',
    'phone' => $_POST['phone'] ?? '',
    'subject' => $_POST['subject'] ?? '',
    'message' => $_POST['message'] ?? ''
);

// Validate required fields
$required_fields = ['fullName', 'email', 'phone', 'subject', 'message'];
foreach ($required_fields as $field) {
    if (empty($data[$field])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => ucfirst($field) . ' is required']);
        exit();
    }
}

// Validate email
if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit();
}

try {
    // Insert into database
    $query = "INSERT INTO contacts (name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssss", 
        $data['fullName'],
        $data['email'],
        $data['phone'],
        $data['subject'],
        $data['message']
    );
    
    if ($stmt->execute()) {
        // Send email to admin
        $to = "crazydesign666@gmail.com";
        $subject = "New Contact Form Submission: " . $data['subject'];
        
        $message = "New contact form submission:\n\n";
        $message .= "Name: " . $data['fullName'] . "\n";
        $message .= "Email: " . $data['email'] . "\n";
        $message .= "Phone: " . $data['phone'] . "\n";
        $message .= "Subject: " . $data['subject'] . "\n";
        $message .= "Message: " . $data['message'] . "\n";
        
        $headers = "From: " . $data['email'] . "\r\n";
        $headers .= "Reply-To: " . $data['email'] . "\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();
        
        // Send email to admin
        mail($to, $subject, $message, $headers);
        
        // Send confirmation email to user
        $user_subject = "Design Craft";
        $user_message = "Dear " . $data['fullName'] . ",\n\n";
        $user_message .= "Thank you for contacting us. We have received your message and will get back to you shortly.\n\n";
        $user_message .= "Your message details:\n";
        $user_message .= "Subject: " . $data['subject'] . "\n";
        $user_message .= "Message: " . $data['message'] . "\n\n";
        $user_message .= "Best regards,\nYour Company Name";
        
        $user_headers = "From: " . $to . "\r\n";
        $user_headers .= "Reply-To: " . $to . "\r\n";
        $user_headers .= "X-Mailer: PHP/" . phpversion();
        
        // Send confirmation email to user
        mail($data['email'], $user_subject, $user_message, $user_headers);
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Contact form submitted successfully']);
    } else {
        throw new Exception($conn->error);
    }
} catch (Exception $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Failed to submit form: ' . $e->getMessage()]);
} 