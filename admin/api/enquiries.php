<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get posted data
    $data = json_decode(file_get_contents("php://input"));
    
    if (
        !empty($data->name) &&
        !empty($data->email) &&
        !empty($data->phone) &&
        !empty($data->message)
    ) {
        // Prepare the SQL statement
        $stmt = $conn->prepare("INSERT INTO enquiries (name, email, phone, message, product_name, product_sku) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $data->name, $data->email, $data->phone, $data->message, $data->product_name, $data->product_sku);
        
        if ($stmt->execute()) {
            // Send email to admin
            $to = "crazydesign666@gmail.com"; // Replace with your email
            $subject = "New Product Enquiry - " . $data->product_name;
            
            $message = "New enquiry received:\n\n";
            $message .= "Name: " . $data->name . "\n";
            $message .= "Email: " . $data->email . "\n";
            $message .= "Phone: " . $data->phone . "\n";
            $message .= "Product: " . $data->product_name . "\n";
            $message .= "SKU: " . $data->product_sku . "\n";
            $message .= "Message: " . $data->message . "\n";
            
            $headers = "From: " . $data->email . "\r\n";
            $headers .= "Reply-To: " . $data->email . "\r\n";
            $headers .= "X-Mailer: PHP/" . phpversion();
            
            // Send email to admin
            mail($to, $subject, $message, $headers);
            
            // Send confirmation email to user
            $userSubject = "Thank you for your enquiry - " . $data->product_name;
            $userMessage = "Dear " . $data->name . ",\n\n";
            $userMessage .= "Thank you for your enquiry about " . $data->product_name . ".\n";
            $userMessage .= "We have received your message and will contact you shortly.\n\n";
            $userMessage .= "Your enquiry details:\n";
            $userMessage .= "Product: " . $data->product_name . "\n";
            $userMessage .= "SKU: " . $data->product_sku . "\n";
            $userMessage .= "Your Message: " . $data->message . "\n\n";
            $userMessage .= "Best regards,\nDesign Craft Team";
            
            $userHeaders = "From: " . $to . "\r\n";
            $userHeaders .= "Reply-To: " . $to . "\r\n";
            $userHeaders .= "X-Mailer: PHP/" . phpversion();
            
            mail($data->email, $userSubject, $userMessage, $userHeaders);
            
            echo json_encode(array(
                'success' => true,
                'message' => 'Enquiry submitted successfully'
            ));
        } else {
            echo json_encode(array(
                'success' => false,
                'message' => 'Failed to submit enquiry'
            ));
        }
    } else {
        echo json_encode(array(
            'success' => false,
            'message' => 'Missing required fields'
        ));
    }
} else {
    echo json_encode(array(
        'success' => false,
        'message' => 'Invalid request method'
    ));
} 