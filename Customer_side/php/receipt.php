<?php
    session_name("customer_session");
    session_start();

    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
    include 'config.php';

    $conn = $db;

    if (!isset($_GET['order_id'])) {
        header("Location: menu.php");
        exit();
    }

    $order_id = intval($_GET['order_id']);

    // 1. Fetch Order General Info (Total, Date, Type)
    $order_query = "SELECT * FROM orders WHERE order_id = ?";
    $stmt = $conn->prepare($order_query);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $order_data = $stmt->get_result()->fetch_assoc();

    if (!$order_data) {
        die("Order not found.");
    }

    // 2. Fetch Items for this specific Order (The crucial part for the table)
    // This links your order_items table to menu_items to get the name
    $items_query = "SELECT oi.quantity, oi.price, mi.name 
                    FROM order_items oi
                    JOIN menu_items mi ON oi.menu_item_id = mi.menu_item_id 
                    WHERE oi.order_id = ?";
    $stmt_items = $conn->prepare($items_query);
    $stmt_items->bind_param("i", $order_id);
    $stmt_items->execute();
    $items_result = $stmt_items->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>E-Receipt - Fizz & Bits</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../css/checkout_style.css">
    <link rel="stylesheet" href="../css/fonts.css">
    <link rel="stylesheet" href="../css/menu.css">
    <link rel="stylesheet" href="../css/navbar.css">
    <link rel="stylesheet" href="../css/receipt.css">
    <link rel="stylesheet" href="../css/delivery.css">

</head>
<body>
    <?php include 'navbar.php'; ?>

    <section class="menu-header">
        <h1 class="font-surimi-title">DELIVERY INFORMATION</h1>

        <div class="progress-container font-poppins">
            <div class="step active">My Cart</div>
            <div class="line active"></div>
            <div class="step active">Delivery Information</div>
            <div class="line active"></div>
            <div class="step active">E Receipt</div>
        </div>

    </section>

    <section>
        <div class="receipt-container font-poppins">
            <div class="receipt-header">
                <p>Order ID: #<?php echo $order_data['order_id']; ?></p>
                <p><?php echo date("m/d/Y", strtotime($order_data['order_date'])); ?></p>
                <img src="../../images/receipt_car.png" alt="Logo" style="width: 150px;">
                <h3>Fizz & Bits</h3>
                <p><strong>Your order is on its way!</strong></p>
            </div>

            <hr>

            <table style="width:100%; text-align:left; border-collapse: collapse;">
                <thead>
                    <tr style="font-size: 0.8rem; color: #666;">
                        <th>ITEMS</th>
                        <th>QTY</th>
                        <th style="text-align:right;">PRICE</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $items_result->data_seek(0); // Reset pointer just in case
                    while($item = $items_result->fetch_assoc()): 
                        $row_total = $item['price'] * $item['quantity'];
                    ?>
                    <tr style="border-bottom: 1px solid #f9f9f9;">
                        <td style="font-size: 0.9rem;">
                            <strong><?php echo htmlspecialchars($item['name']); ?></strong><br>
                            <span style="font-size: 0.75rem; color: #888;">₱<?php echo number_format($item['price'], 2); ?> each</span>
                        </td>
                        <td style="vertical-align: top; text-align: center;"><?php echo $item['quantity']; ?></td>
                        <td style="vertical-align: top; text-align: right;">₱<?php echo number_format($row_total, 2); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <hr>

            <div class="total-row">
                <span>Delivery</span>
                <span><?php echo ($order_data['order_type'] == 'delivery') ? "₱50.00" : "FREE"; ?></span>
            </div>
            <div class="total-row" style="font-weight: bold; font-size: 1.2rem; margin-top: 10px;">
                <span>TOTAL</span>
                <span>₱<?php echo number_format($order_data['total_amount'], 2); ?></span>
            </div>

            <div style="text-align: center; margin-top: 30px;">
                <img src="https://bwipjs-api.metafloor.com/?bcid=code128&text=<?php echo str_pad($order_data['order_id'], 10, '0', STR_PAD_LEFT); ?>&includetext&scale=2&height=15" 
                    alt="Order Barcode" 
                    style="max-width: 100%; height: auto;">
            </div>
        </div>
    </section>

    <section class="btns-bottom">
        <div class="back-to-cart-container">
            <a href="menu.php" class="browse-btn">
                <i class='bx bx-arrow-back'></i> Back to Browsing
            </a>
        </div>
    </section>

</body>
</html>