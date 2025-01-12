<?php
// Database configuration
include_once 'db.php';

// Use statements for better code organization
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Google SMTP configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'testfeeldbroken10@gmail.com');
define('SMTP_PASSWORD', 'suul aqjj ltju klrn');
define('SMTP_FROM_EMAIL', 'testfeeldbroken10@gmail.com');
define('SMTP_FROM_NAME', "Chan's Food");

// Function to send email using Gmail SMTP
function sendEmail($to, $subject, $message) {
    // Validate required PHPMailer files exist
    $required_files = [
        __DIR__ . '/PHPMailer/PHPMailer.php',
        __DIR__ . '/PHPMailer/SMTP.php',
        __DIR__ . '/PHPMailer/Exception.php'
    ];

    foreach ($required_files as $file) {
        if (!file_exists($file)) {
            error_log("Missing required file: $file");
            return false;
        }
    }


    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->SMTPDebug = 0;                      // Disable debug output
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;

        // Recipients
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($to);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $message;
        $mail->AltBody = strip_tags($message);     // Plain text version of email

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email sending failed: {$mail->ErrorInfo}");
        return false;
    }
}

// Optional: Test email configuration
function testEmailConfig() {
    try {
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;
        return true;
    } catch (Exception $e) {
        error_log("SMTP configuration test failed: {$e->getMessage()}");
        return false;
    }
}
?>