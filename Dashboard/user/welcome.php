<?php
// Initialize the session
session_start();

// Check if the user is logged in, if not redirect to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <title>Welcome - Chan's Food</title>
    <link rel="stylesheet" href="asset/css/style.css">
    <link href='https://fonts.googleapis.com/css?family=Raleway' rel='stylesheet'>
    <link href='http://fonts.googleapis.com/css?family=Great+Vibes' rel='stylesheet' type='text/css'>
    <style>
        .welcome-container {
            max-width: 1000px;
            margin: 50px auto;
            padding: 20px;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .welcome-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .welcome-header h1 {
            color: #333;
            font-size: 2.5em;
            margin-bottom: 10px;
        }

        .user-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }

        .user-info p {
            margin: 10px 0;
            font-size: 1.1em;
            color: #555;
        }

        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 30px;
        }

        .action-button {
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1em;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .primary-button {
            background: #4CAF50;
            color: white;
        }

        .danger-button {
            background: #dc3545;
            color: white;
        }

        .action-button:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }

        .quick-links {
            margin-top: 40px;
            text-align: center;
        }

        .quick-links h3 {
            margin-bottom: 15px;
            color: #333;
        }

        .links-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            padding: 20px;
        }

        .link-card {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.3s ease;
        }

        .link-card:hover {
            transform: translateY(-5px);
        }

        .link-card a {
            color: #333;
            text-decoration: none;
            font-weight: bold;
        }

        .welcome-footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="inner-width">
            <a href="#" class="logo">Chan's Food</a>
            <div class="navbar-menu">
                <a href="Index.php">home</a>
                <a href="aboutus.php">about us</a>
                <a href="contact us.php">contact us</a>
                <a href="logout.php">logout</a>
                <a href="Privacy policy.php">Privacy policy</a>
            </div>
        </div>
    </nav>

    <div class="welcome-container">
        <div class="welcome-header">
            <h1>Welcome to Chan's Food</h1>
            <p>Hi, <?php echo htmlspecialchars($_SESSION["email"]); ?>!</p>
        </div>

        <div class="user-info">
            <p><strong>Email:</strong> <?php echo htmlspecialchars($_SESSION["email"]); ?></p>
            <p><strong>Member Since:</strong> <?php 
                // You can add code here to fetch and display the registration date from your database
                echo date("F j, Y"); 
            ?></p>
        </div>

        <div class="quick-links">
            <h3>Quick Links</h3>
            <div class="links-grid">
                <div class="link-card">
                    <a href="menu.php">Our Menu</a>
                </div>
                <div class="link-card">
                    <a href="order.php">Place Order</a>
                </div>
                <div class="link-card">
                    <a href="reservations.php">Book a Table</a>
                </div>
                <div class="link-card">
                    <a href="profile.php">My Profile</a>
                </div>
            </div>
        </div>

        <div class="action-buttons">
            <a href="reset-password.php" class="action-button primary-button">Reset Password</a>
            <a href="../logout.php" class="action-button danger-button">Sign Out</a>
        </div>

        <div class="welcome-footer">
            <p>Thank you for being a valued member of Chan's Food!</p>
        </div>
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