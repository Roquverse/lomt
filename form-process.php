<?php
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
    $subject = isset($_POST['subject']) ? trim($_POST['subject']) : '';
    $message = isset($_POST['message']) ? trim($_POST['message']) : '';

    // Validate form data
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $response['message'] = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Please enter a valid email address.';
    } else {
        // Email configuration
        $to = 'lomtnigeria@gmail.com'; // Your Gmail address
        $headers = array(
            'From: ' . $name . ' <' . $email . '>',
            'Reply-To: ' . $email,
            'X-Mailer: PHP/' . phpversion(),
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8'
        );

        // Prepare email content
        $email_content = "
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
                        <h2>New Contact Form Submission</h2>
                    </div>
                    <div class='content'>
                        <p><strong>Name:</strong> " . htmlspecialchars($name) . "</p>
                        <p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>
                        <p><strong>Subject:</strong> " . htmlspecialchars($subject) . "</p>
                        <p><strong>Message:</strong></p>
                        <p>" . nl2br(htmlspecialchars($message)) . "</p>
                    </div>
                    <div class='footer'>
                        <p>This email was sent from the contact form on LOMT website.</p>
                    </div>
                </div>
            </body>
            </html>
        ";

        // Send email
        $mail_sent = mail($to, 'LOMT Contact Form: ' . $subject, $email_content, implode("\r\n", $headers));

        if ($mail_sent) {
            $response['status'] = 'success';
            $response['message'] = 'Thank you for your message. We will get back to you soon!';
        } else {
            $response['message'] = 'Failed to send message. Please try again later.';
        }
    }
}

// Send JSON response
header('Content-Type: application/json');
echo json_encode($response);
?> 