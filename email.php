<?php
require_once "asset/php/config.php";

// Test email
$to = "cocpissa12@gmail.com"; // Replace with your email
$subject = "Test Email";
$message = "This is a test email from your website";

$result = sendEmail($to, $subject, $message);
if($result === true) {
    echo "Email sent successfully!";
} else {
    echo "Email sending failed: " . $result;
}
?>