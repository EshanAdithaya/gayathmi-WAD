<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Chan's Food - Experience authentic Asian cuisine with our carefully crafted dishes and modern dining experience">
    <title>Chan's Food | Modern Asian Dining</title>
    <link rel="stylesheet" href="asset/css/style.css">
    <link href='https://fonts.googleapis.com/css?family=Raleway:400,500,600,700' rel='stylesheet'>
    <link href='http://fonts.googleapis.com/css?family=Great+Vibes' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.6.3/css/font-awesome.css">
    <style>
        /* Additional styles to enhance existing CSS */
        .hero-section {
            height: 100vh;
            background: linear-gradient(rgba(248, 242, 231, 0.9), rgba(248, 242, 231, 0.9)), url('image/hero-bg.jpg');
            background-size: cover;
            background-position: center;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
        }

        .hero-content {
            max-width: 800px;
            padding: 0 20px;
        }

        .hero-title {
            font-size: 3.5rem;
            color: #222222;
            margin-bottom: 1rem;
            font-family: 'Great Vibes', cursive;
        }

        .hero-subtitle {
            font-size: 1.2rem;
            color: #666;
            margin-bottom: 2rem;
            font-family: 'Raleway', sans-serif;
        }

        .cta-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            margin-top: 2rem;
        }

        .primary-btn {
            background: #de6900;
            color: white;
            padding: 15px 35px;
            border-radius: 30px;
            text-transform: uppercase;
            letter-spacing: 2px;
            font-size: 14px;
            transition: all 0.3s ease;
            font-weight: 600;
        }

        .secondary-btn {
            background: transparent;
            color: #de6900;
            border: 2px solid #de6900;
            padding: 15px 35px;
            border-radius: 30px;
            text-transform: uppercase;
            letter-spacing: 2px;
            font-size: 14px;
            transition: all 0.3s ease;
            font-weight: 600;
        }

        .quick-actions {
            position: fixed;
            bottom: 30px;
            right: 30px;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .action-btn {
            background: white;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            color: #de6900;
        }

        .action-btn:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .features {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
            margin-top: 4rem;
        }

        .feature-card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            text-align: center;
            transition: all 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-10px);
        }

        .feature-icon {
            font-size: 2rem;
            color: #de6900;
            margin-bottom: 1rem;
        }

        @media (max-width: 768px) {
            .features {
                grid-template-columns: 1fr;
            }
            
            .hero-title {
                font-size: 2.5rem;
            }
            
            .cta-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <!-- Enhanced Navigation -->
    <nav class="navbar">
        <div class="inner-width">
            <a href="index.php" class="logo">Chan's Food</a>
            <div class="navbar-menu">
                <a href="index.php" class="active">Home</a>
                <a href="menu.php">Menu</a>
                <a href="reservations.php">Reservations</a>
                <a href="aboutus.php">About Us</a>
                <a href="contactus.php">Contact Us</a>
                <?php if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                    <a href="account.php">My Account</a>
                    <a href="orders.php">Orders</a>
                    <a href="Dashboard/logout.php">Sign Out</a>
                <?php else: ?>
                    <a href="login.php">Sign In</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>