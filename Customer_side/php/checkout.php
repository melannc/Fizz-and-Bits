<?php
session_name("customer_session");
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include 'config.php';

$conn = $db;
$order_id = 1; // Assuming you are working with Order ID #1 for this session

// --- 1. Handle Calculations for Display ---
$shipping_cost = isset($_POST['shipping']) ? (float)$_POST['shipping'] : 0;
$user_id = $_SESSION['user_id']; // Use the logged-in user's ID

// NEW QUERY: Fetch items from the CART, not order_items
$query = "
    SELECT cart_items.*, menu_items.name, menu_items.price, menu_items.image_url, 
           menu_categories.name AS category_name
    FROM cart_items
    JOIN cart ON cart_items.cart_id = cart.cart_id
    JOIN menu_items ON cart_items.menu_item_id = menu_items.menu_item_id
    JOIN menu_categories ON menu_items.category_id = menu_categories.category_id
    WHERE cart.user_id = ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$subtotal = 0;
$items = [];
while ($row = $result->fetch_assoc()) {
    $items[] = $row;
    $subtotal += ($row['price'] * $row['quantity']);
}

$grand_total = $subtotal + $shipping_cost;

// --- 2. Handle Database Update (Complete Order) ---
// --- 2. Handle Database Insertion (Create New Receipt) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['complete_order'])) {

    // Check if any required fields are empty (Server-side safety)
    $required_fields = ['contact', 'fname', 'lname', 'address', 'postal', 'city', 'region', 'phone'];
    $is_valid = true;

    foreach ($required_fields as $field) {
        if (empty(trim($_POST[$field]))) {
            $is_valid = false;
            break;
        }
    }

    if (!$is_valid) {
        echo "<script>alert('Please fill out all required fields.');</script>";
    } else {
        // Proceed with insertion...
        $user_id = $_SESSION['user_id'];
        $order_date = date('Y-m-d H:i:s');
        $full_name = htmlspecialchars($_POST['fname'] . ' ' . $_POST['lname']);
        $address = htmlspecialchars($_POST['address'] . ', ' . $_POST['suite']);
        $city = htmlspecialchars($_POST['city']);
        $region = htmlspecialchars($_POST['region']);
        $postal = htmlspecialchars($_POST['postal']);
        $phone = htmlspecialchars($_POST['phone']);
        $order_type = ($shipping_cost > 0) ? 'delivery' : 'pickup';

        $insert_query = "
            INSERT INTO orders 
            (user_id, order_date, order_status, total_amount, delivery_city, delivery_region, postal_code, phone, full_name, address, order_type)
            VALUES (?, ?, 'on process', ?, ?, ?, ?, ?, ?, ?, ?)
        ";
        $insert_stmt = $conn->prepare($insert_query);
        $insert_stmt->bind_param(
            "isdsssssss",
            $user_id, $order_date, $grand_total, $city, $region, $postal, $phone, $full_name, $address, $order_type
        );

        if ($insert_stmt->execute()) {
            $new_order_id = $conn->insert_id;

            // --- NEW: Move items from Cart to Order Items ---
            $user_id = $_SESSION['user_id'];

            // 1. Copy items from cart_items to order_items
            $move_items_query = "
                INSERT INTO order_items (order_id, menu_item_id, quantity, price)
                SELECT ?, ci.menu_item_id, ci.quantity, mi.price
                FROM cart_items ci
                JOIN cart c ON ci.cart_id = c.cart_id
                JOIN menu_items mi ON ci.menu_item_id = mi.menu_item_id
                WHERE c.user_id = ?
            ";
            $move_stmt = $conn->prepare($move_items_query);
            $move_stmt->bind_param("ii", $new_order_id, $user_id);
            $move_stmt->execute();

            // 2. Clear the user's cart so it's empty for next time
            $clear_cart_query = "
                DELETE ci
                FROM cart_items ci
                JOIN cart c ON ci.cart_id = c.cart_id
                WHERE c.user_id = ?
            ";
            $clear_stmt = $conn->prepare($clear_cart_query);
            $clear_stmt->bind_param("i", $user_id);
            $clear_stmt->execute();

            header("Location: receipt.php?order_id=" . $new_order_id);
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery Information</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../css/checkout_style.css?v=1.2">
    <link rel="stylesheet" href="../css/fonts.css?v=1.2">
    <link rel="stylesheet" href="../css/navbar.css">
    <link rel="stylesheet" href="../css/menu.css">
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
            <div class="line"></div>
            <div class="step">E-Receipt</div>
        </div>
    </section>

    <section class="container">
        <form action="checkout.php" method="POST" class="checkout-grid">
            <div class="form-section font-poppins">
                <section>
                    <h3 class="font-surimi-subheading">Contact</h3>
                    <input type="text" name="contact" class="pill-input" placeholder="Email or mobile phone number" 
                        value="<?php echo isset($_POST['contact']) ? htmlspecialchars($_POST['contact']) : ''; ?>" required>
                </section>

                <section>
                    <h3 class="font-surimi-subheading">Delivery</h3>
                    <select name="country" class="pill-input">
                        <?php
                        $countries = [
                            "AF" => "Afghanistan","AL" => "Albania","DZ" => "Algeria","AS" => "American Samoa",
                            "AD" => "Andorra","AO" => "Angola","AI" => "Anguilla","AR" => "Argentina",
                            "AM" => "Armenia","AU" => "Australia","AT" => "Austria","AZ" => "Azerbaijan",
                            "BS" => "Bahamas","BH" => "Bahrain","BD" => "Bangladesh","BB" => "Barbados",
                            "BE" => "Belgium","BZ" => "Belize","BJ" => "Benin","BM" => "Bermuda",
                            "BT" => "Bhutan","BO" => "Bolivia","BR" => "Brazil","BN" => "Brunei",
                            "BG" => "Bulgaria","KH" => "Cambodia","CA" => "Canada","CL" => "Chile",
                            "CN" => "China","CO" => "Colombia","CR" => "Costa Rica","HR" => "Croatia",
                            "CU" => "Cuba","CY" => "Cyprus","CZ" => "Czech Republic","DK" => "Denmark",
                            "DO" => "Dominican Republic","EC" => "Ecuador","EG" => "Egypt","SV" => "El Salvador",
                            "FI" => "Finland","FR" => "France","DE" => "Germany","GR" => "Greece",
                            "GT" => "Guatemala","HK" => "Hong Kong","HU" => "Hungary","IS" => "Iceland",
                            "IN" => "India","ID" => "Indonesia","IR" => "Iran","IQ" => "Iraq",
                            "IE" => "Ireland","IL" => "Israel","IT" => "Italy","JP" => "Japan",
                            "JO" => "Jordan","KZ" => "Kazakhstan","KE" => "Kenya","KR" => "Korea",
                            "KW" => "Kuwait","LA" => "Laos","LB" => "Lebanon","MY" => "Malaysia",
                            "MX" => "Mexico","MM" => "Myanmar","NP" => "Nepal","NL" => "Netherlands",
                            "NZ" => "New Zealand","NO" => "Norway","PK" => "Pakistan","PA" => "Panama",
                            "PE" => "Peru","PH" => "Philippines","PL" => "Poland","PT" => "Portugal",
                            "QA" => "Qatar","RO" => "Romania","RU" => "Russia","SA" => "Saudi Arabia",
                            "SG" => "Singapore","ES" => "Spain","LK" => "Sri Lanka","SE" => "Sweden",
                            "CH" => "Switzerland","TW" => "Taiwan","TH" => "Thailand","TR" => "Turkey",
                            "UA" => "Ukraine","AE" => "United Arab Emirates","GB" => "United Kingdom",
                            "US" => "United States","VN" => "Vietnam"
                        ];
                        foreach ($countries as $code => $name) {
                            $selected = ($code == "PH") ? "selected" : "";
                            echo "<option value=\"$code\" $selected>$name</option>";
                        }
                        ?>
                    </select>

                    <div class="row">
                        <input type="text" name="fname" class="pill-input" placeholder="First name" required
                            value="<?php echo isset($_POST['fname']) ? htmlspecialchars($_POST['fname']) : ''; ?>">
                        <input type="text" name="lname" class="pill-input" placeholder="Last name" required
                            value="<?php echo isset($_POST['lname']) ? htmlspecialchars($_POST['lname']) : ''; ?>">
                    </div>

                    <input type="text" name="address" class="pill-input" placeholder="Address" required
                        value="<?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?>">
                    <input type="text" name="suite" class="pill-input" placeholder="Apartment, suite, etc. (optional)"
                        value="<?php echo isset($_POST['suite']) ? htmlspecialchars($_POST['suite']) : ''; ?>">

                    <div class="row">
                        <input type="text" name="postal" class="pill-input" placeholder="Postal Code: XXXX" required
                            value="<?php echo isset($_POST['postal']) ? htmlspecialchars($_POST['postal']) : ''; ?>">
                        <input type="text" name="city" class="pill-input" placeholder="City" required
                            value="<?php echo isset($_POST['city']) ? htmlspecialchars($_POST['city']) : ''; ?>">
                    </div>

                    <input type="text" name="region" class="pill-input" placeholder="Region" required
                        value="<?php echo isset($_POST['region']) ? htmlspecialchars($_POST['region']) : ''; ?>">

                    <div class="phone-container">
                        <input type="tel" name="phone" class="pill-input" placeholder="Phone" required
                            value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                    </div>
                </section>

                <section class="shipping-section">
                    <h3 class="font-surimi-subheading">Shipping method</h3>
                    <div class="shipping-container">
                        <label class="shipping-item font-poppins">
                            <div class="shipping-info">
                                <input type="radio" name="shipping" value="0" <?php echo ($shipping_cost == 0) ? 'checked' : ''; ?>>
                                <span>Pick Up</span>
                            </div>
                            <span class="shipping-price">Free</span>
                        </label>

                        <label class="shipping-item font-poppins" style="margin-top: 10px;">
                            <div class="shipping-info">
                                <input type="radio" name="shipping" value="50" <?php echo ($shipping_cost == 50) ? 'checked' : ''; ?>>
                                <span>Delivery</span>
                            </div>
                            <span class="shipping-price">₱50.00</span>
                        </label>
                    </div>

                    <button type="submit" name="update_totals" formnovalidate class="btn-update font-poppins">
                        Update Totals
                    </button>
                </section>

                <section id="payment-section">
                    <h3 class="font-surimi-subheading">Payment</h3>
                    <div class="payment-container" style="display: flex; flex-direction: column; gap: 12px;">
                        <label class="shipping-item font-poppins <?php echo (isset($_POST['pay']) && $_POST['pay'] == '1') ? 'selected' : ''; ?>">
                            <div class="shipping-info">
                                <input type="radio" name="pay" value="1" <?php echo (isset($_POST['pay']) && $_POST['pay'] == '1') ? 'checked' : ''; ?>>
                                <span>COD</span>
                            </div>
                        </label>

                        <?php $is_pay2 = (!isset($_POST['pay']) || $_POST['pay'] == '2'); ?>
                        <label class="shipping-item font-poppins <?php echo $is_pay2 ? 'selected' : ''; ?>">
                            <div class="shipping-info">
                                <input type="radio" name="pay" value="2" <?php echo $is_pay2 ? 'checked' : ''; ?>>
                                <span>Online Payment</span>
                            </div>
                        </label>
                    </div>
                </section>

                <button type="submit" name="complete_order" class="btn-complete font-poppins">Complete Order</button>

            </div>

            <aside class="summary-section">
                <h3 class="font-poppins">Order Summary</h3>
                <?php foreach ($items as $item): $item_total = $item['price'] * $item['quantity']; ?>
                    <div class="order-item">
                        <div class="img-container">
                            <img src="../../images/<?php echo $item['image_url']; ?>" class="item-img">
                            <span class="qty-badge"><?php echo $item['quantity']; ?></span>
                        </div>
                        <div class="item-info">
                            <p class="font-poppins"><?php echo htmlspecialchars($item['name']); ?></p>
                            <span class="category"><?php echo htmlspecialchars($item['category_name']); ?></span>
                        </div>
                        <span class="price">₱<?php echo number_format($item_total, 2); ?></span>
                    </div>
                <?php endforeach; ?>

                <div class="totals font-poppins">
                    <div class="total-row">
                        <span>Subtotal</span>
                        <span>₱<?php echo number_format($subtotal, 2); ?></span>
                    </div>
                    <div class="total-row">
                        <span>Shipping</span>
                        <span><?php echo ($shipping_cost > 0) ? "₱" . number_format($shipping_cost, 2) : "FREE"; ?></span>
                    </div>
                    <div class="total-row grand-total">
                        <strong>Total</strong>
                        <strong>₱<?php echo number_format($grand_total, 2); ?></strong>
                    </div>
                </div>
            </aside>
        </form>
    </section>

    <section class="btns-bottom">
        <div class="back-to-cart-container">
            <a href="cart.php" class="browse-btn">
                <i class='bx bx-arrow-back'></i> Back to Cart
            </a>
        </div>
    </section>
</body>
</html>