<?php
session_start();
require_once "asset/php/config.php";

// Check if user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Check if cart is empty
if (empty($_SESSION['cart'])) {
    header("location: cart.php");
    exit;
}

// Process Checkout
$success = $error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['id'];
    $customer_name = $_SESSION['email']; // Using email as name since it's available
    $customer_email = $_SESSION['email'];
    $customer_phone = trim($_POST['phone']);
    $delivery_address = trim($_POST['delivery_address']);
    
    // Calculate total and validate cart items
    $cart = $_SESSION['cart'];
    $total_amount = 0;
    $items_count = 0;

    foreach ($cart as $item) {
        $items_count += $item['quantity'];
        $total_amount += $item['price'] * $item['quantity'];
    }
    
    if (empty($delivery_address)) {
        $error = "Please enter your delivery address.";
    } elseif (empty($customer_phone)) {
        $error = "Please enter your phone number.";
    } else {
        // Start transaction
        mysqli_begin_transaction($conn);
        
        try {
            // Insert order
            $sql = "INSERT INTO orders (user_id, customer_name, customer_email, customer_phone, delivery_address, 
                    items_count, total_amount, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')";
            
            if ($stmt = mysqli_prepare($conn, $sql)) {
                mysqli_stmt_bind_param($stmt, "issssis", 
                    $user_id,
                    $customer_name,
                    $customer_email,
                    $customer_phone,
                    $delivery_address,
                    $items_count,
                    $total_amount
                );
                
                if (mysqli_stmt_execute($stmt)) {
                    $order_id = mysqli_insert_id($conn);
                    
                    // Insert order items
                    $sql = "INSERT INTO order_items (order_id, menu_item_id, quantity, price) VALUES (?, ?, ?, ?)";
                    $stmt = mysqli_prepare($conn, $sql);
                    
                    foreach ($cart as $item_id => $item) {
                        mysqli_stmt_bind_param($stmt, "iiid", 
                            $order_id, 
                            $item_id, 
                            $item['quantity'], 
                            $item['price']
                        );
                        mysqli_stmt_execute($stmt);
                    }
                    
                    // If everything is successful, commit transaction
                    mysqli_commit($conn);
                    
                    // Clear cart
                    unset($_SESSION['cart']);
                    unset($_SESSION['cart_count']);
                    unset($_SESSION['cart_total']);
                    
                    // Redirect to confirmation page
                    header("location: order_confirmation.php?order_id=" . $order_id);
                    exit;
                }
            }
        } catch (Exception $e) {
            mysqli_rollback($conn);
            $error = "An error occurred while processing your order. Please try again.";
        }
    }
}

// Get cart items for display
$cart_items = [];
$total = 0;

foreach ($_SESSION['cart'] as $item_id => $item) {
    $item['subtotal'] = $item['price'] * $item['quantity'];
    $total += $item['subtotal'];
    $cart_items[] = $item;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Chan's Food</title>
    <link rel="stylesheet" href="asset/css/style.css">
    <link href='https://fonts.googleapis.com/css?family=Raleway' rel='stylesheet'>
    <link href='http://fonts.googleapis.com/css?family=Great+Vibes' rel='stylesheet'>
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
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        h2 {
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #eee;
        }

        .cart-item {
            display: flex;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }

        .cart-item img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            margin-right: 15px;
        }

        .item-details h3 {
            margin: 0 0 5px 0;
            color: #333;
        }

        .item-details p {
            margin: 0;
            color: #666;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 500;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-family: 'Raleway', sans-serif;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            border-color: #de6900;
            outline: none;
            box-shadow: 0 0 0 2px rgba(222, 105, 0, 0.1);
        }

        .total-section {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #eee;
        }

        .total-section h3 {
            display: flex;
            justify-content: space-between;
            color: #333;
            margin: 0;
        }

        .checkout-btn {
            background: #de6900;
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            font-weight: 600;
            transition: background-color 0.3s ease;
        }

        .checkout-btn:hover {
            background: #c25900;
        }

        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .disabled-input {
            background: #f8f9fa;
            color: #6c757d;
        }

        @media (max-width: 768px) {
            .checkout-container {
                grid-template-columns: 1fr;
            }
            
            .cart-item img {
                width: 60px;
                height: 60px;
            }
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="checkout-container">
        <div class="order-summary">
            <h2>Order Summary</h2>
            <?php foreach($cart_items as $item): ?>
                <div class="cart-item">
                    <img src="<?php echo !empty($item['image_url']) ? htmlspecialchars($item['image_url']) : 'image/default-dish.jpg'; ?>" 
                         alt="<?php echo htmlspecialchars($item['name']); ?>">
                    <div class="item-details">
                        <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                        <p>Quantity: <?php echo $item['quantity']; ?></p>
                        <p class="price">$<?php echo number_format($item['subtotal'], 2); ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <div class="total-section">
                <h3>
                    <span>Total Amount:</span>
                    <span>$<?php echo number_format($total, 2); ?></span>
                </h3>
            </div>
        </div>

        <div class="delivery-form">
            <h2>Delivery Information</h2>
            <?php if($error): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" value="<?php echo htmlspecialchars($_SESSION['email']); ?>" class="disabled-input" disabled>
                </div>
                
                <div class="form-group">
                    <label>Phone Number *</label>
                    <input type="tel" name="phone" required 
                           value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>"
                           placeholder="Enter your phone number">
                </div>
                
                <div class="form-group">
                    <label>Delivery Address *</label>
                    <textarea name="delivery_address" rows="3" required 
                              placeholder="Enter your complete delivery address"><?php echo isset($_POST['delivery_address']) ? htmlspecialchars($_POST['delivery_address']) : ''; ?></textarea>
                </div>

                <button type="submit" class="checkout-btn">Place Order</button>
            </form>
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