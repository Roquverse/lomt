<?php
require_once 'admin/config.php';

// Initialize response array
$response = array(
    'status' => 'error',
    'message' => 'Something went wrong!'
);

// Check if the request is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $business = isset($_POST['business']) ? trim($_POST['business']) : '';
    $message = isset($_POST['message']) ? trim($_POST['message']) : '';

    // Validate form data
    if (empty($name) || empty($email) || empty($phone) || empty($business) || empty($message)) {
        $response['message'] = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Please enter a valid email address.';
    } else {
        try {
            // Get database connection
            $conn = getDBConnection();
            
            // Prepare SQL statement
            $sql = "INSERT INTO registrations (registration_date, name, email, phone, business_name, business_description) 
                    VALUES (:registration_date, :name, :email, :phone, :business_name, :business_description)";
            
            $stmt = $conn->prepare($sql);
            
            // Bind parameters
            $stmt->bindParam(':registration_date', date('Y-m-d H:i:s'));
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':business_name', $business);
            $stmt->bindParam(':business_description', $message);
            
            // Execute the statement
            $stmt->execute();

            // Admin email configuration
            $admin_email = 'lomtnigeria@gmail.com';
            $admin_headers = array(
                'From: ' . $name . ' <' . $email . '>',
                'Reply-To: ' . $email,
                'X-Mailer: PHP/' . phpversion(),
                'MIME-Version: 1.0',
                'Content-Type: text/html; charset=UTF-8'
            );

            // Prepare admin email content
            $admin_email_content = "
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
                            <h2>New Program Registration</h2>
                        </div>
                        <div class='content'>
                            <p><strong>Name:</strong> " . htmlspecialchars($name) . "</p>
                            <p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>
                            <p><strong>Phone:</strong> " . htmlspecialchars($phone) . "</p>
                            <p><strong>Business Name:</strong> " . htmlspecialchars($business) . "</p>
                            <p><strong>Business Description:</strong></p>
                            <p>" . nl2br(htmlspecialchars($message)) . "</p>
                        </div>
                        <div class='footer'>
                            <p>This registration was submitted through the LOMT Business Incubation Program registration form.</p>
                        </div>
                    </div>
                </body>
                </html>
            ";

            // Prepare autoresponder email content
            $autoresponder_content = "
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
                            <h2>Welcome to LOMT Business Incubation Program!</h2>
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

            // Autoresponder email headers
            $autoresponder_headers = array(
                'From: LOMT <lomtnigeria@gmail.com>',
                'Reply-To: lomtnigeria@gmail.com',
                'X-Mailer: PHP/' . phpversion(),
                'MIME-Version: 1.0',
                'Content-Type: text/html; charset=UTF-8'
            );

            // Send admin notification email
            $admin_mail_sent = mail($admin_email, 'LOMT Program Registration: ' . $business, $admin_email_content, implode("\r\n", $admin_headers));

            // Send autoresponder email
            $autoresponder_sent = mail($email, 'Welcome to LOMT Business Incubation Program!', $autoresponder_content, implode("\r\n", $autoresponder_headers));

            if ($admin_mail_sent && $autoresponder_sent) {
                $response['status'] = 'success';
                $response['message'] = 'Thank you for registering! You will be redirected to our WhatsApp group shortly.';
            } else {
                $response['message'] = 'Failed to send confirmation emails. Please try again later.';
            }

        } catch(PDOException $e) {
            $response['message'] = 'Database error: ' . $e->getMessage();
        }
    }
}

// Send JSON response
header('Content-Type: application/json');
echo json_encode($response);
?> 