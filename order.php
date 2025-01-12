<?php
session_start();

// Ensure user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

require_once "asset/php/config.php";

// Fetch menu items
$sql = "SELECT m.*, c.name as category_name 
        FROM menu_items m 
        LEFT JOIN menu_categories c ON m.category_id = c.id 
        WHERE m.status = 'active'";
$menu_items = mysqli_query($conn, $sql);

// Initialize cart if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

// Handle AJAX cart operations
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $response = array();
    
    switch ($_POST['action']) {
        case 'add':
            $item_id = $_POST['item_id'];
            if (!isset($_SESSION['cart'][$item_id])) {
                $_SESSION['cart'][$item_id] = 1;
            } else {
                $_SESSION['cart'][$item_id]++;
            }
            $response['success'] = true;
            $response['count'] = array_sum($_SESSION['cart']);
            break;
            
        case 'remove':
            $item_id = $_POST['item_id'];
            if (isset($_SESSION['cart'][$item_id])) {
                unset($_SESSION['cart'][$item_id]);
            }
            $response['success'] = true;
            $response['count'] = array_sum($_SESSION['cart']);
            break;
            
        case 'update':
            $item_id = $_POST['item_id'];
            $quantity = max(0, intval($_POST['quantity']));
            if ($quantity == 0) {
                unset($_SESSION['cart'][$item_id]);
            } else {
                $_SESSION['cart'][$item_id] = $quantity;
            }
            $response['success'] = true;
            $response['count'] = array_sum($_SESSION['cart']);
            break;
    }
    
    if (isset($response)) {
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Online - Chan's Food</title>
    <link rel="stylesheet" href="style.css">
    <link href='https://fonts.googleapis.com/css?family=Raleway' rel='stylesheet'>
    <link href='http://fonts.googleapis.com/css?family=Great+Vibes' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.6.3/css/font-awesome.css">
    <style>
        .order-container {
            max-width: 1200px;
            margin: 100px auto;
            padding: 0 20px;
        }

        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 30px;
            margin-top: 30px;
        }

        .menu-item {
            background: white;
            border-radius: 10px;
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

        .item-details {
            padding: 20px;
        }

        .item-category {
            color: #666;
            font-size: 0.9em;
            margin-bottom: 5px;
        }

        .item-name {
            font-size: 1.2em;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .item-description {
            color: #666;
            font-size: 0.9em;
            margin-bottom: 15px;
            height: 40px;
            overflow: hidden;
        }

        .item-price {
            font-size: 1.1em;
            font-weight: 600;
            color: #de6900;
            margin-bottom: 15px;
        }

        .add-to-cart {
            width: 100%;
            padding: 10px;
            background: #de6900;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .add-to-cart:hover {
            background: #c25900;
        }

        .cart-icon {
            position: fixed;
            right: 30px;
            bottom: 30px;
            background: #de6900;
            color: white;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 3px 10px rgba(0,0,0,0.2);
        }

        .cart-count {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #222;
            color: white;
            width: 25px;
            height: 25px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8em;
        }

        .cart-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
        }

        .cart-content {
            position: fixed;
            right: 0;
            top: 0;
            bottom: 0;
            width: 400px;
            background: white;
            padding: 20px;
            overflow-y: auto;
        }

        .cart-item {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }

        .cart-item-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 5px;
            margin-right: 15px;
        }

        .cart-item-details {
            flex: 1;
        }

        .cart-item-name {
            font-weight: 600;
            margin-bottom: 5px;
        }

        .cart-item-price {
            color: #de6900;
        }

        .cart-item-quantity {
            width: 60px;
            padding: 5px;
            margin: 0 10px;
        }

        .cart-total {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 20px;
            background: white;
            border-top: 1px solid #eee;
        }

        .checkout-btn {
            width: 100%;
            padding: 15px;
            background: #de6900;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
        }

        .category-filter {
            margin-bottom: 30px;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .category-btn {
            padding: 8px 20px;
            border: 2px solid #de6900;
            background: transparent;
            color: #de6900;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .category-btn.active {
            background: #de6900;
            color: white;
        }

        @media (max-width: 768px) {
            .cart-content {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="inner-width">
            <a href="#" class="logo">Chan's Food</a>
            <div class="navbar-menu">
                <a href="index.php">home</a>
                <a href="menu.php">menu</a>
                <a href="order.php" class="active">order</a>
                <a href="contact us.php">contact us</a>
                <?php if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                    <a href="profile.php">my account</a>
                    <a href="logout.php">logout</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="order-container">
        <h1>Order Online</h1>
        
        <!-- Category Filter -->
        <div class="category-filter">
            <button class="category-btn active" data-category="all">All</button>
            <?php
            mysqli_data_seek($menu_items, 0);
            $categories = array();
            while ($item = mysqli_fetch_assoc($menu_items)) {
                if (!in_array($item['category_name'], $categories)) {
                    $categories[] = $item['category_name'];
                    echo '<button class="category-btn" data-category="' . strtolower($item['category_name']) . '">' . 
                         $item['category_name'] . '</button>';
                }
            }
            ?>
        </div>

        <!-- Menu Grid -->
        <div class="menu-grid">
            <?php
            mysqli_data_seek($menu_items, 0);
            while ($item = mysqli_fetch_assoc($menu_items)):
            ?>
            <div class="menu-item" data-category="<?php echo strtolower($item['category_name']); ?>">
                <img src="<?php echo $item['image_url']; ?>" alt="<?php echo $item['name']; ?>" class="item-image">
                <div class="item-details">
                    <div class="item-category"><?php echo $item['category_name']; ?></div>
                    <h3 class="item-name"><?php echo $item['name']; ?></h3>
                    <p class="item-description"><?php echo $item['description']; ?></p>
                    <div class="item-price">$<?php echo number_format($item['price'], 2); ?></div>
                    <button class="add-to-cart" onclick="addToCart(<?php echo $item['id']; ?>)">
                        Add to Cart
                    </button>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>

    <!-- Cart Icon -->
    <div class="cart-icon" onclick="toggleCart()">
        <i class="fa fa-shopping-cart"></i>
        <div class="cart-count"><?php echo array_sum($_SESSION['cart'] ?? []); ?></div>
    </div>

    <!-- Cart Modal -->
    <div class="cart-modal">
        <div class="cart-content">
            <h2>Your Cart</h2>
            <div id="cart-items">
                <!-- Cart items will be loaded here -->
            </div>
            <div class="cart-total">
                <h3>Total: $<span id="cart-total-amount">0.00</span></h3>
                <button class="checkout-btn" onclick="checkout()">Proceed to Checkout</button>
            </div>
        </div>
    </div>

    <script>
        function addToCart(itemId) {
            fetch('order.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=add&item_id=${itemId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateCartCount(data.count);
                    loadCartItems();
                }
            });
        }

        function updateCartCount(count) {
            document.querySelector('.cart-count').textContent = count;
        }

        function toggleCart() {
            const modal = document.querySelector('.cart-modal');
            modal.style.display = modal.style.display === 'block' ? 'none' : 'block';
            if (modal.style.display === 'block') {
                loadCartItems();
            }
        }

        function loadCartItems() {
            fetch('get_cart_items.php')
            .then(response => response.json())
            .then(data => {
                const cartItems = document.getElementById('cart-items');
                cartItems.innerHTML = '';
                let total = 0;

                data.items.forEach(item => {
                    total += item.price * item.quantity;
                    cartItems.innerHTML += `
                        <div class="cart-item">
                            <img src="${item.image_url}" class="cart-item-image">
                            <div class="cart-item-details">
                                <div class="cart-item-name">${item.name}</div>
                                <div class="cart-item-price">$${item.price}</div>
                                <input type="number" class="cart-item-quantity" 
                                       value="${item.quantity}" min="0" 
                                       onchange="updateQuantity(${item.id}, this.value)">
                            </div>
                            <button onclick="removeFromCart(${item.id})">Remove</button>
                        </div>
                    `;
                });

                document.getElementById('cart-total-amount').textContent = total.toFixed(2);
            });
        }

        function updateQuantity(itemId, quantity) {
            fetch('order.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=update&item_id=${itemId}&quantity=${quantity}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateCartCount(data.count);
                    loadCartItems();
                }
            });
        }

        function removeFromCart(itemId) {
            fetch('order.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=remove&item_id=${itemId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateCartCount(data.count);
                    loadCartItems();
                }
            });
        }

        function checkout() {
            window.location.href = 'checkout.php';
        }

        // Category filter
        document.querySelectorAll('.category-btn').forEach(button => {
            button.addEventListener('click', () => {
                document.querySelectorAll('.category-btn').forEach(btn => btn.classList.remove('active'));
                button.classList.add('active');
                
                const category = button.dataset.category;
                document.querySelectorAll('.menu-item').forEach(item => {
                    if (category === 'all' || item.dataset.category === category) {
                        item.style.display = 'block';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        });

        // Close cart when clicking outside
        document.querySelector('.cart-modal').addEventListener('click', (e) => {
            if (e.target.classList.contains('cart-modal')) {
                toggleCart();
            }
        });

        // Initial cart load
        loadCartItems();
    </script>
</body>
</html>