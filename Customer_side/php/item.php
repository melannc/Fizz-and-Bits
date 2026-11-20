<?php
    session_name("customer_session");
    session_start();

    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
    include "config.php";

    if (!isset($_GET['id'])) {
        header("Location: menu.php");
        exit();
    }

    $id = (int) $_GET['id'];

    $query = $db->query("
        SELECT menu_items.*, menu_categories.name AS category
        FROM menu_items
        JOIN menu_categories
        ON menu_items.category_id = menu_categories.category_id
        WHERE menu_item_id = $id;
    ");

    $item = $query->fetch_assoc();

    if (!$item) {
        echo "Item not found.";
        exit();
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/menu.css">
    <style>
    body {
        margin: 0;
        background: transparent;
    }
    </style>
</head>
<body>
    <?php if ($item): ?>
        <div class="popup-container">
            <div class="popup">
                <a href="menu.php" class="close-btn">✕</a>
                <img src="../images/<?= $item['image_url'] ?>" class="popup-img">
                <h2><?= $item['name'] ?></h2>
                <p class="price">₱<?= $item['price'] ?></p>
                <p class="desc">₱<?= $item['description'] ?></p>
                <label>Notes</label>
                <textarea placeholder="e.g. No mayo"></textarea>
                <div class="qty">
                    <button>-</button>
                    <span>1</span>
                    <button>+</button>
                </div>
                <button class="add-btn">Add to Cart</button>
            </div>
        </div>
    <?php endif; ?>
</body>
</html>