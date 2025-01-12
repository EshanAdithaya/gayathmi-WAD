<?php
session_start();
require_once "asset/php/config.php";

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

if (empty($_SESSION['cart'])) {
    header("location: order.php");
    exit;
}

// Process Checkout
$success = $error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['id'];
    $delivery_address = trim($_POST['delivery_address']);
    $phone = trim($_POST['phone']);
    $total_amount = 0;
    
    // Calculate total and validate cart items
    $cart = $_SESSION['cart'];
    $item_ids = array_keys($cart);
    $ids_string = implode(',', array_map('intval', $item_ids));
    
    $sql = "SELECT id, price FROM menu_items WHERE id IN ($ids_string)";
    $result = mysqli_query($conn, $sql);
    
    while ($item = mysqli_fetch_assoc($result)) {
        $total_amount += $item['price'] * $cart[$item['id']];
    }
    
    if (empty($delivery_address)) {
        $error = "Please enter your delivery address.";
    } elseif (empty($phone)) {
        $error = "Please enter your phone number.";
    } else {
        // Insert order
        $sql = "INSERT INTO orders (user_id, delivery_address, phone, total_amount, status) 
                VALUES (?, ?, ?, ?, 'pending')";
        
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "issd", $user_id, $delivery_address, $phone, $total_amount);
            
            if (mysqli_stmt_execute($stmt)) {
                $order_id = mysqli_insert_id($conn);
                
                // Insert order items
                $sql = "INSERT INTO order_items (order_id, menu_item_id, quantity, price) VALUES (?, ?, ?, ?)";
                $stmt = mysqli_prepare($conn, $sql);
                
                foreach ($cart as $item_id => $quantity) {
                    $price = 0;
                    $sql_price = "SELECT price FROM menu_items WHERE id = ?";
                    if ($stmt_price = mysqli_prepare($conn, $sql_price)) {
                        mysqli_stmt_bind_param($stmt_price, "i", $item_id);
                        mysqli_stmt_execute($stmt_price);
                        mysqli_stmt_bind_result($stmt_price, $price);
                        mysqli_stmt_fetch($stmt_price);
                        mysqli_stmt_close($stmt_price);
                    }
                    
                    mysqli_stmt_bind_param($stmt, "iiid", $order_id, $item_id, $quantity, $price);
                    mysqli_stmt_execute($stmt);
                }
                
                // Clear cart and send confirmation email
                $_SESSION['cart'] = array();
                
                // Send confirmation email
                $to = $_SESSION['email'];
                $subject = "Order Confirmation - Chan's Food";
                $message = "
                    <html>
                    <body>
                        <h2>Thank you for your order!</h2>
                        <p>Your order number is: #$order_id</p>
                        <p>Total amount: $" . number_format($total_amount, 2) . "</p>
                        <p>Delivery address: $delivery_address</p>
                        <p>We will contact you at: $phone</p>
                    </body>
                    </html>
                ";
                
                sendEmail($to, $subject, $message);
                
                header("location: order_confirmation.php?id=" . $order_id);
                exit;
            } else {
                $error = "Something went wrong. Please try again.";
            }
        }
    }
}

// Fetch cart items for display
$cart_items = [];
$total = 0;
if (!empty($_SESSION['cart'])) {
    $item_ids = array_keys($_SESSION['cart']);
    $ids_string = implode(',', array_map('intval', $item_ids));
    
    $sql = "SELECT id, name, price, image_url FROM menu_items WHERE id IN ($ids_string)";
    $result = mysqli_query($conn, $sql);
    
    while ($item = mysqli_fetch_assoc($result)) {
        $item['quantity'] = $_SESSION['cart'][$item['id']];
        $item['subtotal'] = $item['price'] * $item['quantity'];
        $total += $item['subtotal'];
        $cart_items[] = $item;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Chan's Food</title>
    <link rel="stylesheet" href="style.css">
    <link href='https://fonts.googleapis.com/css?family=Raleway' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.6.3/css/font-awesome.css">
    <style>
        .checkout-container {
            max-width: 1000px;
            margin: 100px auto;
            padding: 20px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }

        .order-summary, .delivery-form {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .cart-item {
            display: flex;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }

        .cart-item img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
            margin-right: 15px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #666;
        }

        .form-group input, .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .checkout-btn {
            background: #de6900;
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
        }

        .checkout-btn:hover {
            background: #c25900;
        }

        .error-message {
            color: #dc3545;
            margin-bottom: 15px;
        }

        @media (max-width: 768px) {
            .checkout-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="inner-width">
            <a href="#" class="logo">Chan's Food</a>
            <div class="navbar-menu">
                <a href="index.php">home</a>
                <a href="menu.php">menu</a>
                <a href="order.php">order</a>
                <a href="contact us.php">contact us</a>
                <?php if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                    <a href="profile.php">my account</a>
                    <a href="logout.php">logout</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="checkout-container">
        <div class="order-summary">
            <h2>Order Summary</h2>
            <?php foreach($cart_items as $item): ?>
            <div class="cart-item">
                <img src="<?php echo $item['image_url']; ?>" alt="<?php echo $item['name']; ?>">
                <div>
                    <h3><?php echo $item['name']; ?></h3>
                    <p>Quantity: <?php echo $item['quantity']; ?></p>
                    <p>$<?php echo number_format($item['subtotal'], 2); ?></p>
                </div>
            </div>
            <?php endforeach; ?>
            <div style="margin-top: 20px; padding-top: 20px; border-top: 2px solid #eee;">
                <h3>Total: $<?php echo number_format($total, 2); ?></h3>
            </div>
        </div>

        <div class="delivery-form">
            <h2>Delivery Information</h2>
            <?php if($error): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <div class="form-group">
                    <label>Name</label>
                    <input type="text" value="<?php echo $_SESSION['name'] ?? ''; ?>" disabled>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" value="<?php echo $_SESSION['email']; ?>" disabled>
                </div>
                <div class="form-group">
                    <label>Phone Number *</label>
                    <input type="tel" name="phone" required>
                </div>
                <div class="form-group">
                    <label>Delivery Address *</label>
                    <textarea name="delivery_address" rows="3" required></textarea>
                </div>
                <button type="submit" class="checkout-btn">Place Order</button>
            </form>
        </div>
    </div>
</body>
</html>