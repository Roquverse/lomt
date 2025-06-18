<?php
session_start();
require_once 'config.php';

// Check if already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password';
    } else {
        try {
            $conn = getDBConnection();
            
            // Get user from database
            $stmt = $conn->prepare("SELECT id, username, password, full_name, role FROM admin_users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Debug information
            error_log("Login attempt - Username: " . $username);
            error_log("User found: " . ($user ? 'Yes' : 'No'));

            if ($user) {
                error_log("Password verification: " . (password_verify($password, $user['password']) ? 'Success' : 'Failed'));
            }

            if ($user && password_verify($password, $user['password'])) {
                // Update last login
                $updateStmt = $conn->prepare("UPDATE admin_users SET last_login = NOW() WHERE id = ?");
                $updateStmt->execute([$user['id']]);

                // Set session variables
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_username'] = $user['username'];
                $_SESSION['admin_name'] = $user['full_name'];
                $_SESSION['admin_role'] = $user['role'];

                // Log successful login
                error_log("Admin user {$user['username']} logged in successfully");
                
                header('Location: dashboard.php');
                exit;
            } else {
                // Log failed login attempt
                error_log("Failed login attempt for username: $username");
                $error = 'Invalid username or password';
            }
        } catch (PDOException $e) {
            error_log("Database error during login: " . $e->getMessage());
            $error = 'An error occurred. Please try again later.';
        }
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
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .card-header {
            background-color: #fff;
            border-bottom: 2px solid #f8f9fa;
            padding: 20px;
        }
        .btn-primary {
            background-color: #0d6efd;
            border: none;
            padding: 10px;
        }
        .btn-primary:hover {
            background-color: #0b5ed7;
        }
        .alert {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="card">
                <div class="card-header text-center">
                    <h4 class="mb-0">LOMT Admin Login</h4>
                </div>
                <div class="card-body p-4">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
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
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 