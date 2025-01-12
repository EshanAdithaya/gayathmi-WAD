<?php
session_start();

// Check if user is logged in and is admin
include_once 'adminSession.php';

require_once "../../asset/php/config.php";

// Process status update if form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['new_status'];
    
    $update_sql = "UPDATE orders SET status = ? WHERE id = ?";
    if($stmt = mysqli_prepare($conn, $update_sql)) {
        mysqli_stmt_bind_param($stmt, "si", $new_status, $order_id);
        
        if(mysqli_stmt_execute($stmt)) {
            $_SESSION['success_msg'] = "Order status updated successfully";
        } else {
            $_SESSION['error_msg'] = "Error updating order status";
        }
        mysqli_stmt_close($stmt);
    }
    header("Location: admin_orders.php");
    exit;
}

// Fetch all orders with customer details, ordered by most recent first
$sql = "SELECT o.*, 
               COUNT(oi.id) as items_count,
               GROUP_CONCAT(CONCAT(oi.quantity, 'x ', mi.name) SEPARATOR ', ') as order_items
        FROM orders o
        LEFT JOIN order_items oi ON o.id = oi.order_id
        LEFT JOIN menu_items mi ON oi.menu_item_id = mi.id
        GROUP BY o.id
        ORDER BY o.created_at DESC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders - Admin Dashboard</title>
    <link rel="stylesheet" href="../../asset/css/style.css">
    <link href='https://fonts.googleapis.com/css?family=Raleway' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.6.3/css/font-awesome.css">
    <style>
        .admin-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 20px;
        }

        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .admin-title {
            font-size: 24px;
            color: #333;
        }

        .orders-grid {
            display: grid;
            gap: 20px;
        }

        .order-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            padding: 20px;
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .order-id {
            font-weight: 600;
            color: #de6900;
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

        .order-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 15px;
        }

        .info-group {
            font-size: 14px;
        }

        .info-label {
            color: #666;
            margin-bottom: 5px;
        }

        .info-value {
            color: #333;
            font-weight: 500;
        }

        .order-items {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .order-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 15px;
        }

        .status-select {
            padding: 8px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-family: 'Raleway', sans-serif;
        }

        .update-btn {
            padding: 8px 20px;
            background: #de6900;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-family: 'Raleway', sans-serif;
            transition: background 0.3s;
        }

        .update-btn:hover {
            background: #c25900;
        }

        .success-msg {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .error-msg {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .filters {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }

        .filter-select {
            padding: 8px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-family: 'Raleway', sans-serif;
        }

        @media (max-width: 768px) {
            .order-header {
                flex-direction: column;
                gap: 10px;
                align-items: flex-start;
            }

            .order-info {
                grid-template-columns: 1fr;
            }

            .order-actions {
                flex-direction: column;
            }

            .filters {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="admin-container">
        <div class="admin-header">
            <h1 class="admin-title">Manage Orders</h1>
        </div>

        <?php if(isset($_SESSION['success_msg'])): ?>
            <div class="success-msg">
                <?php 
                    echo $_SESSION['success_msg'];
                    unset($_SESSION['success_msg']);
                ?>
            </div>
        <?php endif; ?>

        <?php if(isset($_SESSION['error_msg'])): ?>
            <div class="error-msg">
                <?php 
                    echo $_SESSION['error_msg'];
                    unset($_SESSION['error_msg']);
                ?>
            </div>
        <?php endif; ?>

        <div class="filters">
            <select id="statusFilter" class="filter-select" onchange="filterOrders()">
                <option value="all">All Status</option>
                <option value="pending">Pending</option>
                <option value="processing">Processing</option>
                <option value="completed">Completed</option>
                <option value="cancelled">Cancelled</option>
            </select>

            <select id="sortOrder" class="filter-select" onchange="filterOrders()">
                <option value="newest">Newest First</option>
                <option value="oldest">Oldest First</option>
            </select>
        </div>

        <div class="orders-grid">
            <?php if(mysqli_num_rows($result) > 0): ?>
                <?php while($order = mysqli_fetch_assoc($result)): ?>
                    <div class="order-card" data-status="<?php echo htmlspecialchars($order['status']); ?>">
                        <div class="order-header">
                            <span class="order-id">Order #<?php echo htmlspecialchars($order['id']); ?></span>
                            <span class="order-date">
                                <?php echo date('F j, Y, g:i a', strtotime($order['created_at'])); ?>
                            </span>
                            <span class="order-status status-<?php echo htmlspecialchars($order['status']); ?>">
                                <?php echo ucfirst(htmlspecialchars($order['status'])); ?>
                            </span>
                        </div>

                        <div class="order-info">
                            <div class="info-group">
                                <div class="info-label">Customer Name</div>
                                <div class="info-value"><?php echo htmlspecialchars($order['customer_name']); ?></div>
                            </div>
                            <div class="info-group">
                                <div class="info-label">Contact</div>
                                <div class="info-value">
                                    <?php echo htmlspecialchars($order['customer_phone']); ?><br>
                                    <?php echo htmlspecialchars($order['customer_email']); ?>
                                </div>
                            </div>
                            <div class="info-group">
                                <div class="info-label">Delivery Address</div>
                                <div class="info-value"><?php echo htmlspecialchars($order['delivery_address']); ?></div>
                            </div>
                            <div class="info-group">
                                <div class="info-label">Total Amount</div>
                                <div class="info-value">$<?php echo number_format($order['total_amount'], 2); ?></div>
                            </div>
                        </div>

                        <div class="order-items">
                            <div class="info-label">Order Items</div>
                            <div class="info-value"><?php echo htmlspecialchars($order['order_items']); ?></div>
                        </div>

                        <form class="order-actions" method="POST">
                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                            <select name="new_status" class="status-select">
                                <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="processing" <?php echo $order['status'] == 'processing' ? 'selected' : ''; ?>>Processing</option>
                                <option value="completed" <?php echo $order['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                <option value="cancelled" <?php echo $order['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                            <button type="submit" name="update_status" class="update-btn">Update Status</button>
                        </form>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="order-card">
                    <p>No orders found.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function filterOrders() {
            const statusFilter = document.getElementById('statusFilter').value;
            const sortOrder = document.getElementById('sortOrder').value;
            const orders = document.querySelectorAll('.order-card');
            const ordersGrid = document.querySelector('.orders-grid');
            const ordersArray = Array.from(orders);

            ordersArray.forEach(order => {
                if (statusFilter === 'all' || order.dataset.status === statusFilter) {
                    order.style.display = 'block';
                } else {
                    order.style.display = 'none';
                }
            });

            // Sort orders if needed
            if (sortOrder === 'oldest') {
                ordersArray.sort((a, b) => {
                    const dateA = new Date(a.querySelector('.order-date').textContent);
                    const dateB = new Date(b.querySelector('.order-date').textContent);
                    return dateA - dateB;
                });
            } else {
                ordersArray.sort((a, b) => {
                    const dateA = new Date(a.querySelector('.order-date').textContent);
                    const dateB = new Date(b.querySelector('.order-date').textContent);
                    return dateB - dateA;
                });
            }

            // Re-append sorted orders
            ordersArray.forEach(order => {
                ordersGrid.appendChild(order);
            });
        }
    </script>
</body>
</html>