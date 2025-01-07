<?php
// Include config file
require_once "asset/php/config.php";

// Define variables and initialize with empty values
$email = $password = $confirm_password = "";
$email_err = $password_err = $confirm_password_err = "";

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
    
    // Validate email
    if(empty(trim($_POST["email"]))){
        $email_err = "Please enter an email.";
    } else {
        // Prepare a select statement
        $sql = "SELECT id FROM users WHERE email = ?";
        
        if($stmt = mysqli_prepare($conn, $sql)){
            mysqli_stmt_bind_param($stmt, "s", $param_email);
            
            $param_email = trim($_POST["email"]);
            
            if(mysqli_stmt_execute($stmt)){
                mysqli_stmt_store_result($stmt);
                
                if(mysqli_stmt_num_rows($stmt) == 1){
                    $email_err = "This email is already registered.";
                } else{
                    $email = trim($_POST["email"]);
                }
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }

            mysqli_stmt_close($stmt);
        }
    }
    
    // Validate password
    if(empty(trim($_POST["password"]))){
        $password_err = "Please enter a password.";     
    } elseif(strlen(trim($_POST["password"])) < 6){
        $password_err = "Password must have at least 6 characters.";
    } else{
        $password = trim($_POST["password"]);
    }
    
    // Validate confirm password
    if(empty(trim($_POST["confirm_password"]))){
        $confirm_password_err = "Please confirm password.";     
    } else{
        $confirm_password = trim($_POST["confirm_password"]);
        if(empty($password_err) && ($password != $confirm_password)){
            $confirm_password_err = "Password did not match.";
        }
    }
    
    // Check input errors before inserting in database
    if(empty($email_err) && empty($password_err) && empty($confirm_password_err)){
        
        // Generate verification token
        $verification_token = bin2hex(random_bytes(32));
        
        // Prepare an insert statement
        $sql = "INSERT INTO users (email, password, verification_token, is_verified) VALUES (?, ?, ?, 0)";
         
        if($stmt = mysqli_prepare($conn, $sql)){
            mysqli_stmt_bind_param($stmt, "sss", $param_email, $param_password, $verification_token);
            
            // Set parameters
            $param_email = $email;
            $param_password = password_hash($password, PASSWORD_DEFAULT);
            
            if(mysqli_stmt_execute($stmt)){
                // Send verification email
                $verification_link = "http://localhost/gayathmi-WAD/verify.php?token=" . $verification_token;
                $to = $email;
                $subject = "Email Verification - Chan's Food";
                $message = "
                <html>
                <head>
                    <title>Verify Your Email</title>
                </head>
                <body>
                    <h2>Welcome to Chan's Food!</h2>
                    <p>Thank you for registering. Please click the link below to verify your email address:</p>
                    <p><a href='{$verification_link}'>{$verification_link}</a></p>
                    <p>If you didn't create this account, please ignore this email.</p>
                </body>
                </html>
                ";
                
                if(sendEmail($to, $subject, $message)){
                    header("location: login.php?registration=success");
                } else {
                    echo "Error sending verification email.";
                }
            } else{
                echo "Something went wrong. Please try again later.";
            }

            mysqli_stmt_close($stmt);
        }
    }
    
    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <title>Chan's Food</title>
    <link rel="stylesheet" href="style.css">
    <link href='https://fonts.googleapis.com/css?family=Raleway' rel='stylesheet'>
    <link href='http://fonts.googleapis.com/css?family=Great+Vibes' rel='stylesheet' type='text/css'>
</head>
<body>
    <nav class="navbar">
        <div class="inner-width">
            <a href="#" class="logo">Chan's Food</a>
            <div class="navbar-menu">
                <a href="Index.html">home</a>
                <a href="aboutus.html">about us</a>
                <a href="contact us.html">contact us</a>
                <a href="login.html">login</a>
                <a href="Privacy policy.html">Privacy policy</a>
            </div>
        </div>
    </nav>
    <div class="center">
        <h1>Register Now</h1>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="txt_field <?php echo (!empty($email_err)) ? 'error' : ''; ?>">
                <input type="email" name="email" value="<?php echo $email; ?>" required>
                <span></span>
                <label>Email</label>
                <?php if(!empty($email_err)){ echo '<span class="error">' . $email_err . '</span>'; } ?>
            </div>
            <div class="txt_field <?php echo (!empty($password_err)) ? 'error' : ''; ?>">
                <input type="password" name="password" value="<?php echo $password; ?>" required>
                <span></span>
                <label>Password</label>
                <?php if(!empty($password_err)){ echo '<span class="error">' . $password_err . '</span>'; } ?>
            </div>
            <div class="txt_field <?php echo (!empty($confirm_password_err)) ? 'error' : ''; ?>">
                <input type="password" name="confirm_password" value="<?php echo $confirm_password; ?>" required>
                <span></span>
                <label>Repeat Password</label>
                <?php if(!empty($confirm_password_err)){ echo '<span class="error">' . $confirm_password_err . '</span>'; } ?>
            </div>
            <input type="submit" value="Register">
            <div class="signup_link">
                Already have an account? <a href="login.php">Login</a>
            </div>
        </form>
    </div>
    <section id="home">
        <div class="social-container">
            <ul class="social-icons">
                <li><a href="#"><i class="fa fa-facebook-f"></i></a></li>
                <li><a href="#"><i class="fa fa-instagram"></i></a></li>
                <li><a href="#"><i class="fa fa-twitter"></i></a></li>
            </ul>
        </div>
    </section>
</body>
</html>