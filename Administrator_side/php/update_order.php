<?php
require 'auth_admin.php';
require 'config.php';

// Validate POST parameters
if (!isset($_POST['order_id'], $_POST['status'])) {
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

$order_id = intval($_POST['order_id']);
$status = $_POST['status'];

// Validate new status
$validStatuses = ['on process', 'completed', 'canceled'];
if (!in_array($status, $validStatuses)) {
    echo json_encode(['error' => 'Invalid status']);
    exit;
}

// Fetch current order status
$orderQuery = $db->query("SELECT order_status FROM orders WHERE order_id = $order_id");
$order = $orderQuery->fetch_assoc();

if (!$order) {
    echo json_encode(['error' => 'Order not found']);
    exit;
}

// Restrict status changes if already completed or canceled
if ($order['order_status'] !== 'on process') {
    echo json_encode(['error' => 'Cannot modify order that is already ' . $order['order_status']]);
    exit;
}

// Update order status
$stmt = $db->prepare("UPDATE orders SET order_status=? WHERE order_id=?");
$stmt->bind_param("si", $status, $order_id);
$stmt->execute();

// If marking as completed, update inventory and availability
if ($status === 'completed') {
    $items = $db->query("
        SELECT menu_item_id, quantity
        FROM order_items
        WHERE order_id = $order_id
    ");

    while ($item = $items->fetch_assoc()) {
        $menu_id = $item['menu_item_id'];
        $qty = $item['quantity'];

        // Deduct stock
        $db->query("
            UPDATE inventory
            SET quantity_in_stock = quantity_in_stock - $qty
            WHERE menu_item_id = $menu_id
        ");

        // Check remaining stock
        $res = $db->query("
            SELECT quantity_in_stock FROM inventory
            WHERE menu_item_id = $menu_id
        ");

        $stock = $res->fetch_assoc()['quantity_in_stock'];

        if ($stock <= 0) {
            $db->query("
                UPDATE menu_items
                SET availability_status='not available'
                WHERE menu_item_id = $menu_id
            ");
        }
    }
}

echo json_encode(['success' => 'Order status updated to ' . $status]);