<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Chan's Food - Experience authentic Asian cuisine with our carefully crafted dishes and modern dining experience">
    <title>Chan's Food | Modern Asian Dining</title>
    <link rel="stylesheet" href="style.css">
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
                <a href="#home" class="active">Home</a>
                <a href="menu.php">Menu</a>
                <a href="reservations.php">Reservations</a>
                <?php if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                    <a href="account.php">My Account</a>
                    <a href="orders.php">Orders</a>
                    <a href="logout.php">Sign Out</a>
                <?php else: ?>
                    <a href="login.php">Sign In</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section" id="home">
        <div class="hero-content">
            <h1 class="hero-title">Experience Modern Asian Cuisine</h1>
            <p class="hero-subtitle">
                Discover the perfect blend of traditional flavors and contemporary dining at Chan's Food. 
                Fresh ingredients, authentic recipes, and a warm atmosphere await you.
            </p>
            <div class="cta-buttons">
                <a href="reservations.php" class="primary-btn">Book a Table</a>
                <a href="menu.php" class="secondary-btn">View Menu</a>
            </div>

            <!-- Key Features -->
            <div class="features">
                <div class="feature-card">
                    <i class="fa fa-clock-o feature-icon"></i>
                    <h3>Fast Delivery</h3>
                    <p>30 minutes or free</p>
                </div>
                <div class="feature-card">
                    <i class="fa fa-leaf feature-icon"></i>
                    <h3>Fresh & Healthy</h3>
                    <p>Quality ingredients</p>
                </div>
                <div class="feature-card">
                    <i class="fa fa-star feature-icon"></i>
                    <h3>Best Rated</h3>
                    <p>4.8/5 customer rating</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Quick Action Buttons -->
    <div class="quick-actions">
        <a href="tel:+1234567890" class="action-btn" title="Call Us">
            <i class="fa fa-phone"></i>
        </a>
        <a href="order.php" class="action-btn" title="Order Now">
            <i class="fa fa-shopping-cart"></i>
        </a>
        <a href="#home" class="action-btn" title="Back to Top">
            <i class="fa fa-arrow-up"></i>
        </a>
    </div>

    <!-- Social Links -->
    <div class="social-container">
        <ul class="social-icons">
            <li><a href="#" aria-label="Facebook"><i class="fa fa-facebook-f"></i></a></li>
            <li><a href="#" aria-label="Instagram"><i class="fa fa-instagram"></i></a></li>
            <li><a href="#" aria-label="Twitter"><i class="fa fa-twitter"></i></a></li>
        </ul>
    </div>

    <script>
        // Smooth Scroll
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });

        // Navbar Animation
        const navbar = document.querySelector('.navbar');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                navbar.style.background = 'rgba(255, 255, 255, 0.95)';
                navbar.style.boxShadow = '0 2px 10px rgba(0,0,0,0.1)';
            } else {
                navbar.style.background = 'transparent';
                navbar.style.boxShadow = 'none';
            }
        });
    </script>
</body>
</html>