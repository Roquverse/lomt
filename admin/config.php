<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');     // Your MySQL username
define('DB_PASS', '');         // Your MySQL password
define('DB_NAME', 'lomt_db');  // Your database name

// Create database connection
function getDBConnection() {
    try {
        // First try to connect without database to create it if it doesn't exist
        $conn = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Create database if it doesn't exist
        $sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
        $conn->exec($sql);
        
        // Now connect to the specific database
        $conn = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
            DB_USER,
            DB_PASS,
            array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'")
        );
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch(PDOException $e) {
        error_log("Database connection error: " . $e->getMessage());
        die("Connection failed: " . $e->getMessage());
    }
}

// Create database and tables if they don't exist
function initializeDatabase() {
    try {
        $conn = getDBConnection();
        
        // Create admin_users table
        $sql = "CREATE TABLE IF NOT EXISTS admin_users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL,
            full_name VARCHAR(100) NOT NULL,
            role ENUM('admin', 'editor') NOT NULL DEFAULT 'editor',
            last_login DATETIME,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        $conn->exec($sql);

        // Create registrations table
        $sql = "CREATE TABLE IF NOT EXISTS registrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            registration_date DATETIME NOT NULL,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL,
            phone VARCHAR(50) NOT NULL,
            business_name VARCHAR(255) NOT NULL,
            business_description TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        $conn->exec($sql);

        // Create lomt5_registrations table
        $sql = "CREATE TABLE IF NOT EXISTS lomt5_registrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            registration_date DATETIME NOT NULL,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL,
            phone VARCHAR(50) NOT NULL,
            business_name VARCHAR(255) NOT NULL,
            business_description TEXT NOT NULL,
            social_media_handles TEXT,
            website VARCHAR(255),
            business_stage ENUM('idea', 'startup', 'growing', 'established') NOT NULL,
            challenges TEXT,
            expectations TEXT,
            payment_status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
            payment_reference VARCHAR(100),
            amount_paid DECIMAL(10,2),
            payment_date DATETIME,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        $conn->exec($sql);

        // Check if admin user exists
        $stmt = $conn->prepare("SELECT COUNT(*) FROM admin_users WHERE username = 'admin'");
        $stmt->execute();
        
        if ($stmt->fetchColumn() == 0) {
            // Create default admin user
            $defaultPassword = password_hash('LOMT2024!', PASSWORD_DEFAULT);
            $sql = "INSERT INTO admin_users (username, password, email, full_name, role) 
                    VALUES ('admin', :password, 'lomtnigeria@gmail.com', 'LOMT Administrator', 'admin')";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':password', $defaultPassword);
            $stmt->execute();
            
            // Verify admin user was created
            $stmt = $conn->prepare("SELECT COUNT(*) FROM admin_users WHERE username = 'admin'");
            $stmt->execute();
            if ($stmt->fetchColumn() == 0) {
                throw new Exception("Failed to create admin user");
            }
        }
        
        return true;
    } catch(Exception $e) {
        error_log("Database initialization error: " . $e->getMessage());
        die("Database initialization failed: " . $e->getMessage());
    }
}

// Initialize database on first run
initializeDatabase();
?> 