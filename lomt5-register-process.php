<?php
echo json_encode(['debug' => 'lomt5-register-process.php started']);
exit;

require_once 'admin/config.php';
require_once 'admin/env.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => "PHP Error [$errno]: $errstr in $errfile on line $errline"
    ]);
    exit;
});
set_exception_handler(function($e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Uncaught Exception: ' . $e->getMessage()
    ]);
    exit;
});

header('Content-Type: application/json');

$response = array(
    'success' => false,
    'message' => ''
);

// Log the incoming request
error_log("Received registration request: " . print_r($_POST, true));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $business = $_POST['business'] ?? '';
    $description = $_POST['message'] ?? '';
    $social_media = $_POST['social_media'] ?? '';
    $website = $_POST['website'] ?? '';
    $business_stage = $_POST['business_stage'] ?? '';
    $challenges = $_POST['challenges'] ?? '';
    $expectations = $_POST['expectations'] ?? '';

    // Log the processed data
    error_log("Processed form data: " . print_r([
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'business' => $business,
        'description' => $description,
        'social_media' => $social_media,
        'website' => $website,
        'business_stage' => $business_stage,
        'challenges' => $challenges,
        'expectations' => $expectations
    ], true));

    // Validate required fields
    if (empty($name) || empty($email) || empty($phone) || empty($business) || empty($description) || empty($business_stage)) {
        $response['message'] = 'Please fill in all required fields';
        echo json_encode($response);
        exit;
    }

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Please enter a valid email address';
        echo json_encode($response);
        exit;
    }

    try {
        // Use $conn from config.php directly
        // Prepare SQL statement
        $sql = "INSERT INTO lomt5_registrations (
            registration_date, name, email, phone, business_name, 
            business_description, social_media_handles, website, 
            business_stage, challenges, expectations
        ) VALUES (
            NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
        )";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param(
            "ssssssssss",
            $name,
            $email,
            $phone,
            $business,
            $description,
            $social_media,
            $website,
            $business_stage,
            $challenges,
            $expectations
        );
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
        // PHPMailer setup
        require_once __DIR__ . '/admin/phpmailer/PHPMailer.php';
        require_once __DIR__ . '/admin/phpmailer/SMTP.php';
        require_once __DIR__ . '/admin/phpmailer/Exception.php';
        use PHPMailer\PHPMailer\PHPMailer;
        use PHPMailer\PHPMailer\Exception;

        // Admin email configuration
        $adminEmail = 'damicoledj@gmail.com';
        $adminSubject = 'New LOMT5 Registration';
        
        // Admin email headers
        $adminHeaders = array(
            'From: ' . $name . ' <' . $email . '>',
            'Reply-To: ' . $email,
            'X-Mailer: PHP/' . phpversion(),
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8'
        );

        // Admin email content
        $adminMessage = "
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; }
                    .container { padding: 20px; }
                    .header { background: #fe346e; color: white; padding: 10px; }
                    .content { padding: 20px; }
                    .footer { font-size: 12px; color: #666; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h2>New LOMT5 Registration</h2>
                    </div>
                    <div class='content'>
                        <p><strong>Name:</strong> " . htmlspecialchars($name) . "</p>
                        <p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>
                        <p><strong>Phone:</strong> " . htmlspecialchars($phone) . "</p>
                        <p><strong>Business Name:</strong> " . htmlspecialchars($business) . "</p>
                        <p><strong>Business Stage:</strong> " . htmlspecialchars(ucfirst($business_stage)) . "</p>
                        <p><strong>Business Description:</strong></p>
                        <p>" . nl2br(htmlspecialchars($description)) . "</p>";
        
        if ($social_media) {
            $adminMessage .= "<p><strong>Social Media:</strong> " . htmlspecialchars($social_media) . "</p>";
        }
        if ($website) {
            $adminMessage .= "<p><strong>Website:</strong> " . htmlspecialchars($website) . "</p>";
        }
        if ($challenges) {
            $adminMessage .= "<p><strong>Challenges:</strong></p><p>" . nl2br(htmlspecialchars($challenges)) . "</p>";
        }
        if ($expectations) {
            $adminMessage .= "<p><strong>Expectations:</strong></p><p>" . nl2br(htmlspecialchars($expectations)) . "</p>";
        }
        
        $adminMessage .= "
                    </div>
                    <div class='footer'>
                        <p>This registration was submitted through the LOMT5 Business Incubation Program registration form.</p>
                    </div>
                </div>
            </body>
            </html>
        ";

        // User email configuration
        $userSubject = 'Welcome to LOMT5 Business Incubation Program';
        
        // User email headers
        $userHeaders = array(
            'From: LOMT <lomtnigeria@gmail.com>',
            'Reply-To: lomtnigeria@gmail.com',
            'X-Mailer: PHP/' . phpversion(),
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8'
        );

        // User email content
        $userMessage = "
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; }
                    .container { padding: 20px; }
                    .header { background: #fe346e; color: white; padding: 10px; }
                    .content { padding: 20px; }
                    .footer { font-size: 12px; color: #666; }
                    .whatsapp-btn {
                        display: inline-block;
                        background: #25D366;
                        color: white;
                        padding: 10px 20px;
                        text-decoration: none;
                        border-radius: 5px;
                        margin-top: 20px;
                    }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h2>Welcome to LOMT5 Business Incubation Program!</h2>
                    </div>
                    <div class='content'>
                        <p>Dear " . htmlspecialchars($name) . ",</p>
                        <p>Thank you for registering for our Business Incubation Program! We're excited to have you join our community of entrepreneurs and business owners.</p>
                        <p><strong>Next Steps:</strong></p>
                        <ol>
                            <li>Join our WhatsApp group to connect with other participants and receive important updates</li>
                            <li>Prepare for the first session on July 12</li>
                            <li>Bring your business challenges and questions</li>
                        </ol>
                        <p><strong>Program Details:</strong></p>
                        <ul>
                            <li>Duration: 6 Weeks</li>
                            <li>Dates: July 12, 19, 26, August 2, 9 & 16</li>
                            <li>Format: Live training sessions with real-time feedback</li>
                        </ul>
                        <a href='https://chat.whatsapp.com/L0xKBsg1oMQK6MrXcnnNOv' class='whatsapp-btn'>Join WhatsApp Group</a>
                        <p>If you have any questions, feel free to reply to this email or contact us at lomtnigeria@gmail.com</p>
                    </div>
                    <div class='footer'>
                        <p>Best regards,<br>The LOMT Team</p>
                    </div>
                </div>
            </body>
            </html>
        ";

        // Create PHPMailer instance for admin
        $mailAdmin = new PHPMailer(true);
        try {
            $mailAdmin->isSMTP();
            $mailAdmin->Host = $_ENV['SMTP_HOST'];
            $mailAdmin->SMTPAuth = true;
            $mailAdmin->Username = $_ENV['SMTP_USER'];
            $mailAdmin->Password = $_ENV['SMTP_PASS'];
            $mailAdmin->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mailAdmin->Port = $_ENV['SMTP_PORT'];
            $mailAdmin->setFrom($_ENV['SMTP_FROM'], $_ENV['SMTP_FROM_NAME']);
            $mailAdmin->addAddress($adminEmail);
            $mailAdmin->addReplyTo($email, $name);
            $mailAdmin->isHTML(true);
            $mailAdmin->Subject = $adminSubject;
            $mailAdmin->Body = $adminMessage;
            $mailAdmin->send();
            $adminMailSent = true;
        } catch (Exception $e) {
            $adminMailSent = false;
            error_log('PHPMailer Admin Error: ' . $mailAdmin->ErrorInfo);
        }

        // Create PHPMailer instance for user
        $mailUser = new PHPMailer(true);
        try {
            $mailUser->isSMTP();
            $mailUser->Host = $_ENV['SMTP_HOST'];
            $mailUser->SMTPAuth = true;
            $mailUser->Username = $_ENV['SMTP_USER'];
            $mailUser->Password = $_ENV['SMTP_PASS'];
            $mailUser->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mailUser->Port = $_ENV['SMTP_PORT'];
            $mailUser->setFrom($_ENV['SMTP_FROM'], $_ENV['SMTP_FROM_NAME']);
            $mailUser->addAddress($email, $name);
            $mailUser->addReplyTo($_ENV['SMTP_FROM'], $_ENV['SMTP_FROM_NAME']);
            $mailUser->isHTML(true);
            $mailUser->Subject = $userSubject;
            $mailUser->Body = $userMessage;
            $mailUser->send();
            $userMailSent = true;
        } catch (Exception $e) {
            $userMailSent = false;
            error_log('PHPMailer User Error: ' . $mailUser->ErrorInfo);
        }

        if ($adminMailSent && $userMailSent) {
            $response['success'] = true;
            $response['message'] = 'Registration successful! Please check your email for next steps.';
        } else {
            $response['message'] = 'Registration saved but email delivery failed. Please check your email address.';
        }
        
    } catch (PDOException $e) {
        $response['message'] = 'Registration failed: ' . $e->getMessage();
        error_log("Registration error: " . $e->getMessage());
        echo json_encode($response);
        exit;
    } catch (Exception $e) {
        $response['message'] = 'Registration failed: ' . $e->getMessage();
        error_log("Registration error: " . $e->getMessage());
        echo json_encode($response);
        exit;
    }
}

echo json_encode($response);
?> 