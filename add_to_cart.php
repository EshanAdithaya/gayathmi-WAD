<?php
session_start();

// Check if user is logged in
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

require_once "asset/php/config.php";

// Initialize cart if not exists
if(!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['item_id'])) {
    $item_id = (int)$_POST['item_id'];
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

    // Fetch item details from database
    $sql = "SELECT id, name, price FROM menu_items WHERE id = ? AND status = 'active'";
    
    if($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $item_id);
        
        if(mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            
            if($item = mysqli_fetch_assoc($result)) {
                // Add or update item in cart
                if(!isset($_SESSION['cart'][$item_id])) {
                    $_SESSION['cart'][$item_id] = [
                        'id' => $item['id'],
                        'name' => $item['name'],
                        'price' => $item['price'],
                        'quantity' => $quantity
                    ];
                } else {
                    $_SESSION['cart'][$item_id]['quantity'] += $quantity;
                }

                // Update cart totals
                $cart_count = 0;
                $cart_total = 0;
                foreach($_SESSION['cart'] as $cart_item) {
                    $cart_count += $cart_item['quantity'];
                    $cart_total += $cart_item['price'] * $cart_item['quantity'];
                }
                $_SESSION['cart_count'] = $cart_count;
                $_SESSION['cart_total'] = $cart_total;

                // Return success response
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true, 
                    'message' => 'Item added to cart',
                    'cartCount' => $cart_count,
                    'cartTotal' => number_format($cart_total, 2)
                ]);
                exit;
            }
        }
        mysqli_stmt_close($stmt);
    }
}

// If we get here, something went wrong
header('Content-Type: application/json');
echo json_encode(['success' => false, 'message' => 'Error adding item to cart']);
?>