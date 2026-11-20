<?php
    session_name("customer_session");
    session_start();

    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
require 'config.php';

if (!isset($_SESSION['user_id'])) exit;

$user_id = $_SESSION['user_id'];

$cartQuery = $db->query("SELECT * FROM cart WHERE user_id = $user_id");
if ($cartQuery->num_rows == 0) exit;

$cart = $cartQuery->fetch_assoc();
$cart_id = $cart['cart_id'];

$action = $_POST['action'];
$item_id = (int) $_POST['item_id'];

if ($action == "increase") {
    $db->query("UPDATE cart_items SET quantity = quantity + 1 WHERE cart_item_id = $item_id");
}

if ($action == "decrease") {
    $db->query("UPDATE cart_items SET quantity = quantity - 1 WHERE cart_item_id = $item_id AND quantity > 1");
}

if ($action == "remove") {
    $db->query("DELETE FROM cart_items WHERE cart_item_id = $item_id");
}

/* return updated totals */
$result = $db->query("
    SELECT ci.quantity, mi.price
    FROM cart_items ci
    JOIN menu_items mi ON ci.menu_item_id = mi.menu_item_id
    WHERE ci.cart_id = $cart_id
");

$total = 0;
while ($row = $result->fetch_assoc()) {
    $total += $row['quantity'] * $row['price'];
}

echo json_encode(["total" => $total]);