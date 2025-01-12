<?php
session_start();
require_once "asset/php/config.php";

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$response = ['items' => []];
$cart = $_SESSION['cart'] ?? [];

if (!empty($cart)) {
    $item_ids = array_keys($cart);
    $ids_string = implode(',', array_map('intval', $item_ids));
    
    $sql = "SELECT id, name, price, image_url FROM menu_items WHERE id IN ($ids_string)";
    $result = mysqli_query($conn, $sql);

    while ($item = mysqli_fetch_assoc($result)) {
        $item['quantity'] = $cart[$item['id']];
        $response['items'][] = $item;
    }
}

header('Content-Type: application/json');
echo json_encode($response);
?>