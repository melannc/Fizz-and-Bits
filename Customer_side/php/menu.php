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

$filters = $_GET['filters'] ?? [];
$price_ranges = $_GET['price_ranges'] ?? [];
$ratings = $_GET['ratings'] ?? [];
$sort = $_GET['sort'] ?? "";

// Default: all categories selected if empty
if (empty($filters)) {
    $catResult = $db->query("SELECT name FROM menu_categories");
    while ($c = $catResult->fetch_assoc()) {
        $filters[] = $c['name'];
    }
}

$sql = "
    SELECT 
        menu_items.*, 
        menu_categories.name AS category_name,
        IFNULL(AVG(reviews.rating), 0) AS avg_rating
    FROM menu_items
    JOIN menu_categories 
        ON menu_items.category_id = menu_categories.category_id
    LEFT JOIN reviews 
        ON menu_items.menu_item_id = reviews.menu_item_id
    WHERE availability_status = 'available'
";

// Category filter
if (!empty($filters)) {
    $safeFilters = array_map(function($f) use ($db) {
        return "'" . $db->real_escape_string($f) . "'";
    }, $filters);
    $sql .= " AND menu_categories.name IN (" . implode(",", $safeFilters) . ")";
}

// Price filter
if (!empty($price_ranges)) {
    $priceConditions = [];
    foreach ($price_ranges as $range) {
        list($min, $max) = explode('-', $range);
        $min = (float)$min;
        $max = (float)$max;
        $priceConditions[] = "(menu_items.price >= $min AND menu_items.price <= $max)";
    }
    $sql .= " AND (" . implode(" OR ", $priceConditions) . ")";
}

$sql .= " GROUP BY menu_items.menu_item_id";

// Ratings filter
if (!empty($ratings)) {
    $safeRatings = array_map('intval', $ratings);
    $sql .= " HAVING ROUND(avg_rating) IN (" . implode(",", $safeRatings) . ")";
}

// Sort
if ($sort === "az") {
    $sql .= " ORDER BY menu_items.name ASC";
} elseif ($sort === "za") {
    $sql .= " ORDER BY menu_items.name DESC";
}

// Execute query
$result = $db->query($sql);

// Selected item popup
$selectedItem = null;
if (isset($_GET['item'])) {
    $id = (int) $_GET['item'];
    $itemQuery = $db->query("SELECT * FROM menu_items WHERE menu_item_id = $id");
    if ($itemQuery->num_rows > 0) {
        $selectedItem = $itemQuery->fetch_assoc();
    }
}

// Add to cart
if (isset($_POST['add_to_cart'])) {
    $menu_item_id = (int) $_POST['menu_item_id'];
    $quantity = (int) $_POST['quantity'];
    $user_id = $_SESSION['user_id']; 

    $cartQuery = $db->query("SELECT * FROM cart WHERE user_id = $user_id");
    if ($cartQuery->num_rows > 0) {
        $cart = $cartQuery->fetch_assoc();
        $cart_id = $cart['cart_id'];
    } else {
        $db->query("INSERT INTO cart (user_id) VALUES ($user_id)");
        $cart_id = $db->insert_id;
    }

    $checkItem = $db->query("SELECT * FROM cart_items WHERE cart_id = $cart_id AND menu_item_id = $menu_item_id");
    if ($checkItem->num_rows > 0) {
        $db->query("UPDATE cart_items SET quantity = quantity + $quantity WHERE cart_id = $cart_id AND menu_item_id = $menu_item_id");
    } else {
        $db->query("INSERT INTO cart_items (cart_id, menu_item_id, quantity) VALUES ($cart_id, $menu_item_id, $quantity)");
    }

    header("Location: menu.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fizz & Bits Menu</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../css/fonts.css">
    <link rel="stylesheet" href="../css/menu.css">
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <section class="menu-header">
        <h1 class="font-surimi-title">MENU</h1>
        <div class="categories">
            <form method="GET">
                <?php
                $catQuery = $db->query("SELECT * FROM menu_categories");
                while ($cat = $catQuery->fetch_assoc()):
                    $isActive = in_array($cat['name'], $filters) ? "active" : "";
                ?>
                    <button type="submit" name="filters[]" value="<?= $cat['name'] ?>" class="font-poppins <?= $isActive ?>">
                        <?= $cat['name'] ?>
                    </button>
                <?php endwhile; ?>
            </form>
        </div>
    </section>
    <form method="GET">
        <section class="container">
            <aside class="sidebar">
                <h3 class="filter-title">
                    Filters <i class='bx bx-filter'></i>
                </h3>
                <?php
                $catQuery = $db->query("SELECT * FROM menu_categories");

                while ($cat = $catQuery->fetch_assoc()):
                ?>
                    <label class="custom-checkbox">
                        <input type="checkbox" name="filters[]" value="<?= $cat['name'] ?>"
                            onchange="this.form.submit()"
                            <?= in_array($cat['name'], $filters) ? "checked" : "" ?>>
                        <span class="checkmark"></span>
                        <span class="cat_name"><?= $cat['name'] ?></span>
                    </label>
                <?php endwhile; ?>
                
                <h3 class="filter-title">Price Range <i class='bx bx-dollar'></i></h3>
                <?php
                    $priceRanges = [
                        "10-100" => "₱10 - ₱100",
                        "100-200" => "₱100 - ₱200",
                        "200-300" => "₱200 - ₱300",
                        "300-400" => "₱300 - ₱400"
                    ];
                    foreach ($priceRanges as $value => $label):
                        $isChecked = isset($_GET['price_ranges']) && in_array($value, $_GET['price_ranges']);
                    ?>
                    <label class="custom-checkbox">
                        <input type="checkbox" name="price_ranges[]" value="<?= $value ?>" onchange="this.form.submit()" <?= $isChecked ? "checked" : "" ?>>
                        <span class="checkmark"></span>
                        <span class="cat_name"><?= $label ?></span>
                    </label>
                    <?php endforeach; ?>

                    <h3 class="filter-title">Rating <i class='bx bx-star'></i></h3>
                    <?php
                    $ratings = [5,4,3,2,1,0];
                    foreach ($ratings as $r):
                        $isChecked = isset($_GET['ratings']) && in_array($r, $_GET['ratings']);
                    ?>
                    <label class="custom-checkbox">
                        <input type="checkbox" name="ratings[]" value="<?= $r ?>" onchange="this.form.submit()" <?= $isChecked ? "checked" : "" ?>>
                        <span class="checkmark"></span>
                        <span class="cat_name"><?php echo str_repeat('★', $r); ?></span>
                    </label>
                <?php endforeach; ?>

                <a href="menu.php" class="clear">Clear Filter</a>
            </aside>
            <div class="content">
                <div class="top-bar">
                    <div class="tags">
                        <?php
                        foreach ($filters as $f):
                            $newFilters = array_diff($filters, [$f]);

                            $query = http_build_query([
                                "filters" => $newFilters,
                                "sort" => $sort
                            ]);
                        ?>
                        <a href="?<?= $query ?>" class="tag">
                            <?= $f ?> ✕
                        </a>
                        <?php endforeach; ?>
                    </div>
                    <div class="sort">
                        Sort by:
                        <select class="font-poppins" name="sort" onchange="this.form.submit()">
                            <option value="">Default</option>
                            <option value="az" <?= $sort=="az" ? "selected" : "" ?>>A-Z</option>
                            <option value="za" <?= $sort=="za" ? "selected" : "" ?>>Z-A</option>
                        </select>
                    </div>
                </div>
                <div class="grid">
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <a href="menu.php?item=<?= $row['menu_item_id'] ?>" class="card">
                                <div class="image-box">
                                    <img src="/Fizz_and_Bits_Website/images/<?= htmlspecialchars($row['image_url']) ?>" alt="<?= htmlspecialchars($row['name']) ?>">
                                </div>
                                <div class="item-card">
                                    <h4><?= $row['name'] ?></h4>
                                    <small>₱<?= $row['price'] ?></small>

                                    <div class="stars">
                                        <?php 
                                            $rating = round($row['avg_rating']); 
                                            for ($i = 1; $i <= 5; $i++): 
                                        ?>
                                            <?php if ($i <= $rating): ?>
                                                <i class='bx bxs-star'></i>
                                            <?php else: ?>
                                                <i class='bx bx-star'></i>
                                            <?php endif; ?>
                                        <?php endfor; ?>

                                        <small>
                                            <?= number_format($row['avg_rating'], 1); ?>/5
                                        </small>
                                    </div>
                                </div>
                            </a>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p>No items found.</p>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </form>
    <?php include 'footer.php'; ?>
</body>
</html>

<?php if ($selectedItem): ?>
    <div class="popup-container">
        <a href="menu.php" class="popup-overlay"></a>
        <div class="popup">
            <a href="menu.php" class="close-btn">✕</a>
            <img src="/Fizz_and_Bits_Website/images/<?= $selectedItem['image_url'] ?>" class="popup-img">
            <h2><?= $selectedItem['name'] ?></h2>
            <p>₱<?= $selectedItem['price'] ?></p>
            <p><?= $selectedItem['description'] ?></p>
            
            <form method="POST">
                <input type="hidden" name="menu_item_id" value="<?= $selectedItem['menu_item_id'] ?>">
                <input type="hidden" name="quantity" id="qtyInput" value="1">

                <label>Notes</label>
                <textarea name="notes" placeholder="e.g. No mayo"></textarea>

                <div class="qty">
                    <button type="button" onclick="changeQty(-1)">-</button>
                    <span id="qtyDisplay">1</span>
                    <button type="button" onclick="changeQty(1)">+</button>
                </div>

                <button type="submit" name="add_to_cart" class="add-btn font-poppins">
                    Add to Cart
                </button>
            </form>

        </div>
    </div>
<?php endif; ?>

<script>
let qty = 1;

function changeQty(amount) {
    qty += amount;

    if (qty <= 1 && amount < 0) return;

    document.getElementById("qtyDisplay").innerText = qty;
    document.getElementById("qtyInput").value = qty;
}
</script>