<?php
session_start();
require_once "asset/php/config.php";

if (!isset($_SESSION["loggedin"]) || !isset($_GET['id'])) {
    header("location: index.php");
    exit;
}

$order_id = $_GET['id'];
$order = null;
$order_items = [];

// Fetch order details
$sql = "SELECT o.*, u.email FROM orders o 
        JOIN users u ON o.user_id = u.id 
        WHERE o.id = ? AND o.user_id = ?";

if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "ii", $order_id, $_SESSION['id']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $order = mysqli_fetch_assoc($result);
    
    if (!$order) {
        header("location: index.php");
        exit;
    }

    // Fetch order items
    $sql = "SELECT oi.*, m.name, m.image_url FROM order_items oi 
            JOIN menu_items m ON oi.menu_item_id = m.id 
            WHERE oi.order_id = ?";
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $order_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        while ($item = mysqli_fetch_assoc($result)) {
            $order_items[] = $item;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - Chan's Food</title>
    <link rel="stylesheet" href="style.css">
    <link href='https://fonts.googleapis.com/css?family=Raleway' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.6.3/css/font-awesome.css">
    <style>
        .confirmation-container {
            max-width: 800px;
            margin: 100px auto;
            padding: 20px;
        }

        .confirmation-box {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }

        .success-icon {
            color: #28a745;
            font-size: 50px;
            margin-bottom: 20px;
        }

        .order-details {
            margin-top: 30px;
            text-align: left;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 5px;
        }

        .order-items {
            margin-top: 20px;
        }

        .order-item {
            display: flex;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }

        .order-item img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
            margin-right: 15px;
        }

        .buttons {
            margin-top: 30px;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            margin: 0 10px;
            border-radius: 5px;
            text-decoration: none;
            transition: background-color 0.3s;
        }

        .btn-primary {
            background: #de6900;
            color: white;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn:hover {
            opacity: 0.9;
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

    <div class="confirmation-container">
        <div class="confirmation-box">
            <i class="fa fa-check-circle success-icon"></i>
            <h1>Thank You for Your Order!</h1>
            <p>Your order has been successfully placed.</p>
            
            <div class="order-details">
                <h3>Order Details</h3>
                <p><strong>Order Number:</strong> #<?php echo $order['id']; ?></p>
                <p><strong>Date:</strong> <?php echo date('F j, Y g:i A', strtotime($order['created_at'])); ?></p>
                <p><strong>Delivery Address:</strong> <?php echo $order['delivery_address']; ?>
                <p><strong>Phone:</strong> <?php echo $order['phone']; ?></p>
                <p><strong>Status:</strong> 
                    <span style="color: #de6900; font-weight: bold;">
                        <?php echo ucfirst($order['status']); ?>
                    </span>
                </p>

                <div class="order-items">
                    <h3>Order Items</h3>
                    <?php foreach($order_items as $item): ?>
                    <div class="order-item">
                        <img src="<?php echo $item['image_url']; ?>" alt="<?php echo $item['name']; ?>">
                        <div>
                            <h4><?php echo $item['name']; ?></h4>
                            <p>Quantity: <?php echo $item['quantity']; ?></p>
                            <p>Price: $<?php echo number_format($item['price'], 2); ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>

                    <div style="margin-top: 20px; padding-top: 20px; border-top: 2px solid #ddd;">
                        <h3>Total Amount: $<?php echo number_format($order['total_amount'], 2); ?></h3>
                    </div>
                </div>
            </div>

            <div class="buttons">
                <a href="order.php" class="btn btn-primary">Place Another Order</a>
                <a href="profile.php" class="btn btn-secondary">View Order History</a>
            </div>

            <div style="margin-top: 20px;">
                <p>A confirmation email has been sent to <?php echo $order['email']; ?></p>
                <p>If you have any questions about your order, please contact us.</p>
            </div>
        </div>
    </div>

    <script>
        // Prevent form resubmission when refreshing
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>
</body>
</html>