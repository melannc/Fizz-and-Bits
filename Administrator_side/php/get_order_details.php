<?php
require 'auth_admin.php';
require 'config.php';

// Check if user is logged in
if (!isset($_SESSION['fname'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

if (!isset($_GET['id'])) {
    echo json_encode(['error' => 'No order ID provided']);
    exit;
}

$orderId = intval($_GET['id']);

$orderQuery = $db->query("SELECT * FROM orders WHERE order_id = $orderId");
$order = $orderQuery->fetch_assoc();

if (!$order) {
    echo json_encode(['error' => 'Order not found']);
    exit;
}

$itemsQuery = $db->query("
    SELECT oi.*, m.name AS product_name, m.price AS product_price
    FROM order_items oi
    LEFT JOIN menu_items m ON oi.menu_item_id = m.menu_item_id
    WHERE oi.order_id = $orderId
");

$items = [];
while($row = $itemsQuery->fetch_assoc()){
    // Always set a price: use order_items price if exists, otherwise menu price
    $row['price'] = isset($row['price']) ? floatval($row['price']) : floatval($row['product_price']);
    $row['product_name'] = $row['product_name'] ?? 'Menu Item #' . $row['menu_item_id'];
    $items[] = $row;
}

header('Content-Type: application/json');
echo json_encode([
    'order_id' => $order['order_id'],
    'full_name' => $order['full_name'],
    'order_date' => $order['order_date'],
    'order_status' => $order['order_status'],
    'address' => $order['address'],
    'total_amount' => $order['total_amount'],
    'items' => $items
]);