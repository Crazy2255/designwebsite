<?php
// Start the session if needed for login functionality
session_start();

// If user is already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

// Include the login page
include 'login.php';
?> 