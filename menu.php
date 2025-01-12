<?php
session_start();

// Include database connection
require_once "asset/php/config.php";

// Fetch menu categories
$sql_categories = "SELECT * FROM menu_categories ORDER BY display_order";
$categories = mysqli_query($conn, $sql_categories);

// Fetch menu items
$sql_items = "SELECT * FROM menu_items WHERE status = 'active' ORDER BY category_id, name";
$items = mysqli_query($conn, $sql_items);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu - Chan's Food</title>
    <link rel="stylesheet" href="asset/css/style.css">
    <link href='https://fonts.googleapis.com/css?family=Raleway' rel='stylesheet'>
    <link href='http://fonts.googleapis.com/css?family=Great+Vibes' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.6.3/css/font-awesome.css">
    <style>
        .menu-container {
            max-width: 1200px;
            margin: 120px auto 50px;
            padding: 0 20px;
        }

        .menu-header {
            text-align: center;
            margin-bottom: 50px;
        }

        .menu-header h1 {
            font-family: 'Great Vibes', cursive;
            font-size: 48px;
            color: #222222;
            margin-bottom: 15px;
        }

        .menu-nav {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-bottom: 40px;
            flex-wrap: wrap;
        }

        .category-btn {
            padding: 10px 25px;
            background: transparent;
            border: 2px solid #de6900;
            color: #de6900;
            border-radius: 25px;
            cursor: pointer;
            font-family: 'Raleway', sans-serif;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .category-btn.active, .category-btn:hover {
            background: #de6900;
            color: white;
        }

        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            padding: 20px 0;
        }

        .menu-item {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .menu-item:hover {
            transform: translateY(-5px);
        }

        .item-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .item-info {
            padding: 20px;
        }

        .item-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .item-name {
            font-size: 18px;
            font-weight: 600;
            color: #222222;
        }

        .item-price {
            color: #de6900;
            font-weight: 700;
            font-size: 18px;
        }

        .item-description {
            color: #666;
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 15px;
        }

        .item-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .order-btn {
            padding: 8px 20px;
            background: #de6900;
            color: white;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            font-family: 'Raleway', sans-serif;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .order-btn:hover {
            background: #c25900;
        }

        .dietary-info {
            display: flex;
            gap: 10px;
        }

        .dietary-icon {
            color: #666;
            font-size: 16px;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .menu-container {
                margin-top: 100px;
            }

            .menu-grid {
                grid-template-columns: 1fr;
            }

            .menu-header h1 {
                font-size: 36px;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
<?php 
include 'navbar.php';
?>

    <!-- Menu Section -->
    <div class="menu-container">
        <div class="menu-header">
            <h1>Our Menu</h1>
            <p>Discover our authentic Asian flavors</p>
        </div>

        <!-- Category Navigation -->
        <div class="menu-nav">
            <button class="category-btn active" data-category="all">All</button>
            <button class="category-btn" data-category="appetizers">Appetizers</button>
            <button class="category-btn" data-category="main-course">Main Course</button>
            <button class="category-btn" data-category="desserts">Desserts</button>
            <button class="category-btn" data-category="beverages">Beverages</button>
        </div>

        <!-- Menu Items Grid -->
        <div class="menu-grid">
            <!-- Sample Menu Item -->
            <div class="menu-item" data-category="main-course">
                <img src="image/dish1.jpg" alt="Dish Name" class="item-image">
                <div class="item-info">
                    <div class="item-header">
                        <h3 class="item-name">Special Fried Rice</h3>
                        <span class="item-price">$12.99</span>
                    </div>
                    <p class="item-description">
                        Wok-fried rice with shrimp, chicken, and fresh vegetables in our special sauce.
                    </p>
                    <div class="item-actions">
                        <button class="order-btn" onclick="addToCart(1)">Add to Cart</button>
                        <div class="dietary-info">
                            <i class="fa fa-leaf dietary-icon" title="Vegetarian Option Available"></i>
                            <i class="fa fa-fire dietary-icon" title="Spicy"></i>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Add more menu items dynamically from database -->
        </div>
    </div>

    <script>
        // Category Filter
        document.querySelectorAll('.category-btn').forEach(button => {
            button.addEventListener('click', () => {
                // Remove active class from all buttons
                document.querySelectorAll('.category-btn').forEach(btn => {
                    btn.classList.remove('active');
                });
                // Add active class to clicked button
                button.classList.add('active');
                
                const category = button.dataset.category;
                filterItems(category);
            });
        });

        function filterItems(category) {
            const items = document.querySelectorAll('.menu-item');
            items.forEach(item => {
                if (category === 'all' || item.dataset.category === category) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        }

        // Add to Cart Function
        function addToCart(itemId) {
            // Check if user is logged in
            <?php if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                // Add your cart functionality here
                alert('Item added to cart!');
            <?php else: ?>
                window.location.href = 'login.php';
            <?php endif; ?>
        }
    </script>
</body>
</html>