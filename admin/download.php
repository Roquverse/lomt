<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

try {
    // Get database connection
    $conn = getDBConnection();
    
    // Get all registrations
    $stmt = $conn->query("SELECT * FROM registrations ORDER BY registration_date DESC");
    $registrations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($registrations)) {
        die('No registrations found.');
    }
    
    // Set headers for CSV download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="lomt_registrations_' . date('Y-m-d') . '.csv"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Create output stream
    $output = fopen('php://output', 'w');
    
    // Add UTF-8 BOM for proper Excel encoding
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Add headers
    fputcsv($output, array(
        'Registration Date',
        'Name',
        'Email',
        'Phone',
        'Business Name',
        'Business Description'
    ));
    
    // Add data
    foreach ($registrations as $registration) {
        fputcsv($output, array(
            $registration['registration_date'],
            $registration['name'],
            $registration['email'],
            $registration['phone'],
            $registration['business_name'],
            $registration['business_description']
        ));
    }
    
    fclose($output);
    
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}
?> 