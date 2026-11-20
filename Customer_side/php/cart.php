<?php
    session_name("customer_session");
    session_start();

    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
    
    require 'config.php';

    if (!isset($_SESSION['user_id'])) {
        header("Location: index.php");
        exit;
    }

    $user_id = $_SESSION['user_id'];

    // Get cart
    $cartQuery = $db->query("SELECT * FROM cart WHERE user_id = $user_id");

    $cartItems = [];
    $total = 0;

    if ($cartQuery->num_rows > 0) {
        $cart = $cartQuery->fetch_assoc();
        $cart_id = $cart['cart_id'];

        // Get items inside cart
        $itemsQuery = $db->query("
            SELECT ci.*, mi.name, mi.price, mi.image_url
            FROM cart_items ci
            JOIN menu_items mi ON ci.menu_item_id = mi.menu_item_id
            WHERE ci.cart_id = $cart_id
        ");

        while ($item = $itemsQuery->fetch_assoc()) {
            $cartItems[] = $item;
            $total += $item['price'] * $item['quantity'];
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Cart</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../css/fonts.css">
    <link rel="stylesheet" href="../css/cart.css">
    <link rel="stylesheet" href="../css/checkout_style.css?v=1.2">
    <link rel="stylesheet" href="../css/menu.css">

</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <section class="menu-header">
        <h1 class="font-surimi-title">MY CART</h1>
        <div class="progress-container font-poppins">
            <div class="step active">My Cart</div>
            <div class="line"></div>
            <div class="step">Delivery Information</div>
            <div class="line"></div>
            <div class="step">E-Receipt</div>
        </div>
    </section>

    <section class="cart-container">
        <?php if (!empty($cartItems)): ?>

            <div class="cart-list">
                <?php foreach ($cartItems as $item): ?>
                    <div class="cart-item" data-id="<?= $item['cart_item_id'] ?>">

                        <img src="/Fizz_and_Bits_Website/images/<?= $item['image_url'] ?>" class="cart-img">

                    <div class="cart-info">
                        <h3><?= $item['name'] ?></h3>
                        <p>₱<?= $item['price'] ?></p>

                        <div class="cart-qty">
                            <button onclick="updateCart(this, 'decrease')">-</button>
                            <span class="qty"><?= $item['quantity'] ?></span>
                            <button onclick="updateCart(this, 'increase')">+</button>
                        </div>
                    </div>

                    <div class="cart-total price">
                        ₱<?= $item['price'] * $item['quantity'] ?>
                    </div>

                    <i class='bx bx-trash remove-btn' onclick="updateCart(this, 'remove')"></i>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="cart-summary">

                <h3>Total: ₱<?= $total ?></h3>

                <div class="cart-actions">
                    <!-- LEFT SIDE -->
                    <a href="menu.php" class="browse-btn">
                        <i class='bx bx-arrow-back'></i> Browse Menu
                    </a>

                    <!-- RIGHT SIDE -->
                    <a href="checkout.php" class="checkout-btn">
                        Proceed to Checkout
                    </a>
                </div>

            </div>

        <?php else: ?>

            <p class="empty-cart">Your cart is empty</p>

        <?php endif; ?>

    </section>
    
    <?php include 'footer.php'; ?>
</body>
</html>

<script id="cartjs03">
function updateCart(el, action) {
    let item = el.closest(".cart-item");
    let id = item.dataset.id;

    fetch("cart_action.php", {
        method: "POST",
        headers: {"Content-Type": "application/x-www-form-urlencoded"},
        body: `action=${action}&item_id=${id}`
    })
    .then(res => res.json())
    .then(data => {

        if (action === "remove") {
            item.remove();
        } else {
            let qtyEl = item.querySelector(".qty");
            let priceEl = item.querySelector(".price");
            let price = parseFloat(priceEl.innerText.replace("₱","")) / qtyEl.innerText;

            let qty = parseInt(qtyEl.innerText);

            if (action === "increase") qty++;
            if (action === "decrease" && qty > 1) qty--;

            qtyEl.innerText = qty;
            priceEl.innerText = "₱" + (price * qty);
        }

        document.querySelector(".cart-summary h3").innerText = "Total: ₱" + data.total;
    });
}
</script>