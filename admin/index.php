<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once 'config.php';

// Check if already logged in
if (is_logged_in()) {
    header("Location: " . dirname($_SERVER['PHP_SELF']) . "/dashboard.php");
    exit();
}

$error = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $username = isset($_POST['username']) ? sanitize_input($_POST['username']) : '';
        $password = isset($_POST['password']) ? sanitize_input($_POST['password']) : '';
        
        if (empty($username) || empty($password)) {
            throw new Exception("Username and password are required");
        }
        
        // Query to check credentials
        $sql = "SELECT id, username, full_name FROM admin_users WHERE username = ? AND password = ? AND is_active = 1";
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            throw new Exception("Database prepare error: " . $conn->error);
        }
        
        $stmt->bind_param("ss", $username, $password);
        
        if (!$stmt->execute()) {
            throw new Exception("Database execute error: " . $stmt->error);
        }
        
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Set session variables
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_username'] = $user['username'];
            $_SESSION['admin_name'] = $user['full_name'];
            
            // Update last login time
            $update_sql = "UPDATE admin_users SET last_login = CURRENT_TIMESTAMP WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            
            if (!$update_stmt) {
                throw new Exception("Database prepare error for update: " . $conn->error);
            }
            
            $update_stmt->bind_param("i", $user['id']);
            
            if (!$update_stmt->execute()) {
                throw new Exception("Database execute error for update: " . $update_stmt->error);
            }
            
            // Ensure session is written before redirect
            session_write_close();
            
            // Redirect to dashboard using absolute path
            header("Location: " . dirname($_SERVER['PHP_SELF']) . "/dashboard.php");
            exit();
        } else {
            $error = "Invalid username or password";
        }
    } catch (Exception $e) {
        error_log("Login error: " . $e->getMessage());
        $error = "An error occurred during login: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LOMT Admin Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .login-container {
            max-width: 400px;
            margin: 100px auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .login-logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-logo img {
            max-width: 150px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="login-logo">
                <img src="../assets/images/logos/logo.png" alt="LOMT Logo">
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                
                <button type="submit" class="btn btn-primary w-100">Login</button>
            </form>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 