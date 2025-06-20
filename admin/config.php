<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');     // Your MySQL username
define('DB_PASS', '');         // Your MySQL password
define('DB_NAME', 'lomt_db');  // Your database name

// Create database connection
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Set charset to utf8mb4
    $conn->set_charset("utf8mb4");
    
} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Function to sanitize input data
function sanitize_input($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $conn->real_escape_string($data);
}

// Function to check if user is logged in
function is_logged_in() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

// Function to redirect if not logged in
function require_login() {
    if (!is_logged_in()) {
        header("Location: index.php");
        exit();
    }
}
?> 