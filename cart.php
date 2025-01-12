<?php
session_start();

// Check if user is logged in
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

require_once "asset/php/config.php";

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

// Handle AJAX cart operations
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    header('Content-Type: application/json');
    $response = array('success' => false);

    switch ($_POST['action']) {
        case 'add':
            if (isset($_POST['item_id'])) {
                $item_id = (int)$_POST['item_id'];
                $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

                // Fetch item details from database
                $sql = "SELECT name, price FROM menu_items WHERE id = ? AND status = 'active'";
                if ($stmt = mysqli_prepare($conn, $sql)) {
                    mysqli_stmt_bind_param($stmt, "i", $item_id);
                    if (mysqli_stmt_execute($stmt)) {
                        $result = mysqli_stmt_get_result($stmt);
                        if ($item = mysqli_fetch_assoc($result)) {
                            if (!isset($_SESSION['cart'][$item_id])) {
                                $_SESSION['cart'][$item_id] = array(
                                    'name' => $item['name'],
                                    'price' => $item['price'],
                                    'quantity' => $quantity
                                );
                            } else {
                                $_SESSION['cart'][$item_id]['quantity'] += $quantity;
                            }
                            updateCartTotals();
                            $response['success'] = true;
                            $response['cart_count'] = $_SESSION['cart_count'];
                            $response['cart_total'] = $_SESSION['cart_total'];
                        }
                    }
                    mysqli_stmt_close($stmt);
                }
            }
            break;

        case 'update':
            if (isset($_POST['item_id']) && isset($_POST['quantity'])) {
                $item_id = (int)$_POST['item_id'];
                $quantity = (int)$_POST['quantity'];

                if ($quantity > 0) {
                    if (isset($_SESSION['cart'][$item_id])) {
                        $_SESSION['cart'][$item_id]['quantity'] = $quantity;
                        updateCartTotals();
                        $response['success'] = true;
                        $response['cart_count'] = $_SESSION['cart_count'];
                        $response['cart_total'] = $_SESSION['cart_total'];
                    }
                }
            }
            break;

        case 'remove':
            if (isset($_POST['item_id'])) {
                $item_id = (int)$_POST['item_id'];
                if (isset($_SESSION['cart'][$item_id])) {
                    unset($_SESSION['cart'][$item_id]);
                    updateCartTotals();
                    $response['success'] = true;
                    $response['cart_count'] = $_SESSION['cart_count'];
                    $response['cart_total'] = $_SESSION['cart_total'];
                }
            }
            break;

        case 'clear':
            $_SESSION['cart'] = array();
            updateCartTotals();
            $response['success'] = true;
            $response['cart_count'] = 0;
            $response['cart_total'] = 0.00;
            break;
    }

    echo json_encode($response);
    exit;
}

// Function to update cart totals
function updateCartTotals() {
    $total = 0;
    $count = 0;
    foreach ($_SESSION['cart'] as $item) {
        $count += $item['quantity'];
        $total += $item['price'] * $item['quantity'];
    }
    $_SESSION['cart_count'] = $count;
    $_SESSION['cart_total'] = $total;
}

// Update totals before displaying page
updateCartTotals();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Chan's Food</title>
    <link rel="stylesheet" href="asset/css/style.css">
    <link href='https://fonts.googleapis.com/css?family=Raleway' rel='stylesheet'>
    <link href='http://fonts.googleapis.com/css?family=Great+Vibes' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.6.3/css/font-awesome.css">
    <style>
        .cart-container {
            max-width: 1000px;
            margin: 100px auto;
            padding: 0 20px;
        }

        .cart-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .cart-header h1 {
            font-family: 'Great Vibes', cursive;
            font-size: 48px;
            color: #222222;
            margin-bottom: 15px;
        }

        .cart-table {
            width: 100%;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .cart-table th,
        .cart-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .cart-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }

        .item-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 5px;
        }

        .item-name {
            font-weight: 500;
            color: #333;
        }

        .quantity-input {
            width: 60px;
            padding: 5px;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-align: center;
        }

        .remove-btn {
            background: none;
            border: none;
            color: #dc3545;
            cursor: pointer;
            padding: 5px;
            font-size: 16px;
        }

        .price {
            font-weight: 500;
            color: #de6900;
        }

        .cart-summary {
            margin-top: 30px;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
        }

        .summary-row.total {
            border-top: 2px solid #eee;
            margin-top: 10px;
            padding-top: 20px;
            font-weight: 600;
            font-size: 18px;
        }

        .cart-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
            gap: 20px;
        }

        .action-btn {
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-family: 'Raleway', sans-serif;
            font-weight: 500;
            transition: background 0.3s;
        }

        .continue-btn {
            background: #6c757d;
            color: white;
        }

        .checkout-btn {
            background: #de6900;
            color: white;
            flex: 1;
            max-width: 200px;
        }

        .clear-btn {
            background: #dc3545;
            color: white;
        }

        .empty-cart {
            text-align: center;
            padding: 50px 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .empty-cart i {
            font-size: 48px;
            color: #ddd;
            margin-bottom: 20px;
        }

        @media (max-width: 768px) {
            .cart-table {
                display: block;
                overflow-x: auto;
            }

            .cart-actions {
                flex-direction: column;
            }

            .checkout-btn {
                max-width: none;
            }
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="cart-container">
        <div class="cart-header">
            <h1>Shopping Cart</h1>
            <p>Review your items and proceed to checkout</p>
        </div>

        <?php if(!empty($_SESSION['cart'])): ?>
            <div class="cart-table-container">
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Subtotal</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($_SESSION['cart'] as $item_id => $item): ?>
                            <tr>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 15px;">
                                        <img src="image/default-dish.jpg" alt="<?php echo htmlspecialchars($item['name']); ?>" class="item-image">
                                        <span class="item-name"><?php echo htmlspecialchars($item['name']); ?></span>
                                    </div>
                                </td>
                                <td class="price">$<?php echo number_format($item['price'], 2); ?></td>
                                <td>
                                    <input type="number" class="quantity-input" value="<?php echo $item['quantity']; ?>" 
                                           min="1" onchange="updateQuantity(<?php echo $item_id; ?>, this.value)">
                                </td>
                                <td class="price">$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                <td>
                                    <button class="remove-btn" onclick="removeItem(<?php echo $item_id; ?>)">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="cart-summary">
                <div class="summary-row total">
                    <span>Total Amount:</span>
                    <span>$<?php echo number_format($_SESSION['cart_total'], 2); ?></span>
                </div>
            </div>

            <div class="cart-actions">
                <a href="menu.php" class="action-btn continue-btn">Continue Shopping</a>
                <button onclick="clearCart()" class="action-btn clear-btn">Clear Cart</button>
                <a href="checkout.php" class="action-btn checkout-btn">Proceed to Checkout</a>
            </div>
        <?php else: ?>
            <div class="empty-cart">
                <i class="fa fa-shopping-cart"></i>
                <h2>Your cart is empty</h2>
                <p>Browse our menu and add some delicious items to your cart!</p>
                <a href="menu.php" class="action-btn continue-btn" style="display: inline-block; margin-top: 20px;">
                    View Menu
                </a>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function updateQuantity(itemId, quantity) {
            fetch('cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=update&item_id=${itemId}&quantity=${quantity}`
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    location.reload();
                }
            });
        }

        function removeItem(itemId) {
            if(confirm('Are you sure you want to remove this item?')) {
                fetch('cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=remove&item_id=${itemId}`
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        location.reload();
                    }
                });
            }
        }

        function clearCart() {
            if(confirm('Are you sure you want to clear your cart?')) {
                fetch('cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=clear'
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        location.reload();
                    }
                });
            }
        }
    </script>

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