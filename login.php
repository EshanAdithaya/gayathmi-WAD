<?php
// Initialize the session
session_start();

// Check if user is already logged in
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    header("location: index.php");
    exit;
}

// Include config file
require_once "asset/php/config.php";

// Define variables and initialize with empty values
$email = $password = "";
$email_err = $password_err = $login_err = "";

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){

    // Check if email is empty
    if(empty(trim($_POST["email"]))){
        $email_err = "Please enter email.";
    } else{
        $email = trim($_POST["email"]);
    }
    
    // Check if password is empty
    if(empty(trim($_POST["password"]))){
        $password_err = "Please enter your password.";
    } else{
        $password = trim($_POST["password"]);
    }
    
    // Validate credentials
    if(empty($email_err) && empty($password_err)){
        // Modified query to also fetch is_admin
        $sql = "SELECT id, email, password, is_admin FROM users WHERE email = ?";
        
        if($stmt = mysqli_prepare($conn, $sql)){
            mysqli_stmt_bind_param($stmt, "s", $param_email);
            $param_email = $email;
            
            if(mysqli_stmt_execute($stmt)){
                mysqli_stmt_store_result($stmt);
                
                if(mysqli_stmt_num_rows($stmt) == 1){
                    mysqli_stmt_bind_result($stmt, $id, $email, $hashed_password, $is_admin);
                    if(mysqli_stmt_fetch($stmt)){
                        if(password_verify($password, $hashed_password)){
                            // Password is correct, start a new session
                            session_start();
                            
                            // Store data in session variables
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["email"] = $email;
                            $_SESSION["is_admin"] = $is_admin;
                            
                            // Redirect based on user type
                            if($is_admin == 1){
                                header("location: Dashboard/admin/adminDashboard.php");
                            } else {
                                header("location: index.php");
                            }
                            exit;
                        } else{
                            $login_err = "Invalid email or password.";
                        }
                    }
                } else{
                    $login_err = "Invalid email or password.";
                }
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }
            mysqli_stmt_close($stmt);
        }
    }
    mysqli_close($conn);
}
?>

<?php 
include 'navbar.php';
?>
    <div class="center">
        <h1>Login</h1>
        <?php 
        if(!empty($login_err)){
            echo '<div class="error">' . $login_err . '</div>';
        }        
        ?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="txt_field <?php echo (!empty($email_err)) ? 'error' : ''; ?>">
                <input type="email" name="email" value="<?php echo $email; ?>" required>
                <span></span>
                <label>Email</label>
                <?php if(!empty($email_err)){ echo '<span class="error">' . $email_err . '</span>'; } ?>
            </div>
            <div class="txt_field <?php echo (!empty($password_err)) ? 'error' : ''; ?>">
                <input type="password" name="password" required>
                <span></span>
                <label>Password</label>
                <?php if(!empty($password_err)){ echo '<span class="error">' . $password_err . '</span>'; } ?>
            </div>
            <div class="pass">Forgot Password?</div>
            <input type="submit" value="Login">
            <div class="signup_link">
                Not a member? <a href="register.php">Signup</a>
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