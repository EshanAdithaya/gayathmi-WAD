<?php
session_start();

// Include database connection
require_once "asset/php/config.php";

// Fetch menu categories
$sql_categories = "SELECT * FROM menu_categories WHERE status = 'active' ORDER BY display_order";
$categories = mysqli_query($conn, $sql_categories);

// Store categories in an array for later use
$category_list = array();
while ($category = mysqli_fetch_assoc($categories)) {
    $category_list[] = $category;
}

// Fetch menu items with their categories
$sql_items = "SELECT mi.*, mc.name as category_name 
              FROM menu_items mi 
              LEFT JOIN menu_categories mc ON mi.category_id = mc.id 
              WHERE mi.status = 'active' 
              ORDER BY mc.display_order, mi.name";
$items = mysqli_query($conn, $sql_items);

// Store items in an array
$menu_items = array();
while ($item = mysqli_fetch_assoc($items)) {
    $menu_items[] = $item;
}
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
        /* Keep your existing styles */
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
            min-height: 45px;
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

        .no-items {
            grid-column: 1 / -1;
            text-align: center;
            padding: 50px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
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
    <?php include 'navbar.php'; ?>

    <!-- Menu Section -->
    <div class="menu-container">
        <div class="menu-header">
            <h1>Our Menu</h1>
            <p>Discover our authentic Asian flavors</p>
        </div>

        <!-- Category Navigation -->
        <div class="menu-nav">
            <button class="category-btn active" data-category="all">All</button>
            <?php foreach ($category_list as $category): ?>
                <button class="category-btn" data-category="<?php echo htmlspecialchars($category['id']); ?>">
                    <?php echo htmlspecialchars($category['name']); ?>
                </button>
            <?php endforeach; ?>
        </div>

        <!-- Menu Items Grid -->
        <div class="menu-grid">
            <?php if (empty($menu_items)): ?>
                <div class="no-items">
                    <h2>No menu items available</h2>
                    <p>Please check back later for our updated menu.</p>
                </div>
            <?php else: ?>
                <?php foreach ($menu_items as $item): ?>
                    <div class="menu-item" data-category="<?php echo htmlspecialchars($item['category_id']); ?>">
                        <?php if ($item['image_url']): ?>
                            <img src="<?php echo htmlspecialchars($item['image_url']); ?>" 
                                 alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                 class="item-image">
                        <?php else: ?>
                            <img src="image/default-dish.jpg" 
                                 alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                 class="item-image">
                        <?php endif; ?>
                        
                        <div class="item-info">
                            <div class="item-header">
                                <h3 class="item-name"><?php echo htmlspecialchars($item['name']); ?></h3>
                                <span class="item-price">$<?php echo number_format($item['price'], 2); ?></span>
                            </div>
                            <p class="item-description">
                                <?php echo htmlspecialchars($item['description'] ?? 'No description available.'); ?>
                            </p>
                            <div class="item-actions">
                                <button class="order-btn" onclick="addToCart(<?php echo $item['id']; ?>)">
                                    Add to Cart
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
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

        // Add to Cart Function with AJAX
        function addToCart(itemId) {
            <?php if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                // Use AJAX to add item to cart
                fetch('add_to_cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `item_id=${itemId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Item added to cart!');
                        // Optionally update cart icon/counter
                        if (data.cartCount) {
                            // Update cart count in UI if you have one
                            // document.getElementById('cart-count').textContent = data.cartCount;
                        }
                    } else {
                        alert(data.message || 'Error adding item to cart');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error adding item to cart');
                });
            <?php else: ?>
                window.location.href = 'login.php';
            <?php endif; ?>
        }
    </script>
</body>
</html>