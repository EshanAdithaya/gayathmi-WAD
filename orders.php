<?php
// Initialize the session
session_start();

// Check if the user is logged in, if not redirect to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Include config file
require_once "asset/php/config.php";

// Function to get order items
function getOrderItems($conn, $order_id) {
    $items = array();
    $sql = "SELECT oi.*, mi.name, mi.price as menu_price 
            FROM order_items oi 
            JOIN menu_items mi ON oi.menu_item_id = mi.id 
            WHERE oi.order_id = ?";
    
    if($stmt = mysqli_prepare($conn, $sql)){
        mysqli_stmt_bind_param($stmt, "i", $order_id);
        if(mysqli_stmt_execute($stmt)){
            $result = mysqli_stmt_get_result($stmt);
            while($row = mysqli_fetch_assoc($result)){
                $items[] = $row;
            }
        }
        mysqli_stmt_close($stmt);
    }
    return $items;
}

// Get user's orders
$orders = array();
$sql = "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC";

if($stmt = mysqli_prepare($conn, $sql)){
    mysqli_stmt_bind_param($stmt, "i", $_SESSION["id"]);
    if(mysqli_stmt_execute($stmt)){
        $result = mysqli_stmt_get_result($stmt);
        while($row = mysqli_fetch_assoc($result)){
            $row['items'] = getOrderItems($conn, $row['id']);
            $orders[] = $row;
        }
    }
    mysqli_stmt_close($stmt);
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Chan's Food</title>
    <link rel="stylesheet" href="asset/css/style.css">
    <link href='https://fonts.googleapis.com/css?family=Raleway' rel='stylesheet'>
    <link href='http://fonts.googleapis.com/css?family=Great+Vibes' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.6.3/css/font-awesome.css">
    <style>
        .orders-container {
            max-width: 1000px;
            margin: 120px auto 50px;
            padding: 0 20px;
        }

        .orders-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .orders-header h1 {
            font-family: 'Great Vibes', cursive;
            font-size: 48px;
            color: #222222;
            margin-bottom: 15px;
        }

        .order-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            overflow: hidden;
        }

        .order-header {
            background: #f8f9fa;
            padding: 20px;
            border-bottom: 1px solid #eee;
        }

        .order-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .order-number {
            font-size: 18px;
            font-weight: 600;
            color: #222;
        }

        .order-date {
            color: #666;
        }

        .order-status {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-processing {
            background: #cce5ff;
            color: #004085;
        }

        .status-completed {
            background: #d4edda;
            color: #155724;
        }

        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }

        .order-items {
            padding: 20px;
        }

        .item-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }

        .item-details {
            flex: 1;
        }

        .item-name {
            font-weight: 500;
            color: #222;
            margin-bottom: 5px;
        }

        .item-price {
            color: #666;
            font-size: 14px;
        }

        .item-quantity {
            background: #f8f9fa;
            padding: 5px 15px;
            border-radius: 15px;
            margin: 0 20px;
        }

        .order-total {
            padding: 20px;
            background: #f8f9fa;
            border-top: 1px solid #eee;
            text-align: right;
        }

        .total-amount {
            font-size: 18px;
            font-weight: 600;
            color: #de6900;
        }

        .no-orders {
            text-align: center;
            padding: 50px 20px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }

        .no-orders i {
            font-size: 48px;
            color: #ddd;
            margin-bottom: 20px;
        }

        .order-actions {
            padding: 20px;
            background: #f8f9fa;
            border-top: 1px solid #eee;
            text-align: right;
        }

        .reorder-btn {
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

        .reorder-btn:hover {
            background: #c25900;
        }

        @media (max-width: 768px) {
            .orders-header h1 {
                font-size: 36px;
            }

            .order-info {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="orders-container">
        <div class="orders-header">
            <h1>My Orders</h1>
            <p>View your order history and track current orders</p>
        </div>

        <?php if(empty($orders)): ?>
        <div class="no-orders">
            <i class="fa fa-shopping-bag"></i>
            <h2>No Orders Yet</h2>
            <p>You haven't placed any orders yet. Check out our menu to place your first order!</p>
            <a href="menu.php" class="reorder-btn">View Menu</a>
        </div>
        <?php else: ?>
            <?php foreach($orders as $order): ?>
                <div class="order-card">
                    <div class="order-header">
                        <div class="order-info">
                            <span class="order-number">Order #<?php echo $order['id']; ?></span>
                            <span class="order-date"><?php echo date('F j, Y, g:i a', strtotime($order['created_at'])); ?></span>
                            <span class="order-status status-<?php echo strtolower($order['status']); ?>">
                                <?php echo ucfirst($order['status']); ?>
                            </span>
                        </div>
                    </div>

                    <div class="order-items">
                        <?php foreach($order['items'] as $item): ?>
                            <div class="item-row">
                                <div class="item-details">
                                    <div class="item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                                    <div class="item-price">$<?php echo number_format($item['price'], 2); ?></div>
                                </div>
                                <span class="item-quantity">×<?php echo $item['quantity']; ?></span>
                                <span class="item-total">$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="order-total">
                        <span>Total Amount: </span>
                        <span class="total-amount">$<?php echo number_format($order['total_amount'], 2); ?></span>
                    </div>

                    <?php if($order['status'] === 'completed'): ?>
                    <div class="order-actions">
                        <button class="reorder-btn" onclick="reorderItems(<?php echo $order['id']; ?>)">
                            <i class="fa fa-refresh"></i> Reorder
                        </button>
                    </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
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

    <script>
        function reorderItems(orderId) {
            // You can implement the reorder functionality here
            // This could involve making an AJAX call to add items to cart
            alert('Reorder functionality will be implemented here');
        }
    </script>
</body>
</html>