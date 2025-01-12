<?php
// Include config file
require_once "asset/php/config.php";

// Check if token is provided
if(!isset($_GET['token']) || empty($_GET['token'])) {
    die('No verification token provided.');
}

$token = $_GET['token'];

// Prepare a select statement
$sql = "SELECT id FROM users WHERE verification_token = ? AND is_verified = 0";

if($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "s", $token);
    
    if(mysqli_stmt_execute($stmt)) {
        mysqli_stmt_store_result($stmt);
        
        if(mysqli_stmt_num_rows($stmt) == 1) {
            // Update user as verified
            $update_sql = "UPDATE users SET is_verified = 1, verification_token = NULL WHERE verification_token = ?";
            
            if($update_stmt = mysqli_prepare($conn, $update_sql)) {
                mysqli_stmt_bind_param($update_stmt, "s", $token);
                
                if(mysqli_stmt_execute($update_stmt)) {
                    echo "Your email has been verified successfully. You can now <a href='login.php'>login</a>.";
                } else {
                    echo "Oops! Something went wrong. Please try again later.";
                }
                
                mysqli_stmt_close($update_stmt);
            }
        } else {
            echo "Invalid verification token or account already verified.";
        }
    } else {
        echo "Oops! Something went wrong. Please try again later.";
    }
    
    mysqli_stmt_close($stmt);
}

mysqli_close($conn);
?>