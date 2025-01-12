<?php
session_start();

// Check if user is logged in and is admin
include_once 'adminSession.php';

require_once "../../asset/php/config.php";

// Fetch quick stats
$stats = [
    'total_orders' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM orders"))['count'],
    'total_users' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users"))['count'],
    'total_menu_items' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM menu_items WHERE status = 'active'"))['count'],
    'recent_orders' => mysqli_query($conn, "SELECT * FROM orders ORDER BY created_at DESC LIMIT 5")
];

include_once 'navbar.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Chan's Food</title>
    <link rel="stylesheet" href="../../asset/css/style.css">
    <link href='https://fonts.googleapis.com/css?family=Raleway' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.6.3/css/font-awesome.css">
    <style>
        .dashboard-container {
            max-width: 1200px;
            margin: 100px auto;
            padding: 0 20px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            text-align: center;
        }

        .stat-card h3 {
            color: #666;
            margin-bottom: 10px;
        }

        .stat-card .number {
            font-size: 2em;
            color: #de6900;
            font-weight: bold;
        }

        .recent-orders {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .recent-orders h2 {
            margin-bottom: 20px;
            color: #333;
        }

        .order-list {
            width: 100%;
            border-collapse: collapse;
        }

        .order-list th,
        .order-list td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .order-list th {
            background: #f5f5f5;
            font-weight: bold;
            color: #333;
        }

        .status-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.9em;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-completed {
            background: #d4edda;
            color: #155724;
        }

        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>


    <div class="dashboard-container">
        <h1>Dashboard Overview</h1>
        
        <!-- Quick Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Orders</h3>
                <div class="number"><?php echo $stats['total_orders']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Total Users</h3>
                <div class="number"><?php echo $stats['total_users']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Menu Items</h3>
                <div class="number"><?php echo $stats['total_menu_items']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Today's Revenue</h3>
                <div class="number">$<?php 
                    $today_revenue = mysqli_fetch_assoc(mysqli_query($conn, 
                        "SELECT SUM(total_amount) as revenue FROM orders 
                         WHERE DATE(created_at) = CURDATE()"
                    ))['revenue'] ?? 0;
                    echo number_format($today_revenue, 2); 
                ?></div>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="recent-orders">
            <h2>Recent Orders</h2>
            <table class="order-list">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($order = mysqli_fetch_assoc($stats['recent_orders'])): ?>
                        <tr>
                            <td>#<?php echo $order['id']; ?></td>
                            <td><?php echo $order['customer_name']; ?></td>
                            <td><?php echo $order['items_count']; ?> items</td>
                            <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo strtolower($order['status']); ?>">
                                    <?php echo $order['status']; ?>
                                </span>
                            </td>
                            <td>
                                <button onclick="viewOrder(<?php echo $order['id']; ?>)">View</button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function viewOrder(orderId) {
            window.location.href = 'admin_order_details.php?id=' + orderId;
        }
    </script>
</body>
</html>