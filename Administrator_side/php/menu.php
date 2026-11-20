<?php
require 'auth_admin.php';
require 'config.php';

    /* CATEGORY FILTER */
    $category_id = $_GET['category'] ?? 'All';

    if ($category_id !== "All") {
        $category_id = (int)$category_id;
    }

    /* PRICE FILTER */
    $priceFilters = $_GET['price'] ?? [];

    /* BUILD PRICE CONDITION */
    $priceCondition = "";

    if(!empty($priceFilters)){

        $ranges = [];

        foreach($priceFilters as $range){

            list($min,$max) = explode('-', $range);

            $ranges[] = "(m.price BETWEEN $min AND $max)";
        }

        $priceCondition = " AND (" . implode(" OR ", $ranges) . ")";
    }

    /* PAGINATION */
    $items_per_page = 9;

    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

    if ($page < 1) {
        $page = 1;
    }

    $offset = ($page - 1) * $items_per_page;


    /* MENU QUERY */
    if ($category_id == "All") {

        $totalQuery = $db->query("
        SELECT COUNT(*) as total 
        FROM menu_items m
        WHERE 1 $priceCondition
        ");
        $total = $totalQuery->fetch_assoc()['total'];

        $menuQuery = $db->query("
            SELECT 
                m.menu_item_id, 
                m.name, 
                m.price, 
                m.image_url, 
                c.name AS category,
                AVG(r.rating) AS avg_rating,
                COUNT(r.review_id) AS total_reviews
            FROM menu_items m
            LEFT JOIN menu_categories c ON m.category_id = c.category_id
            LEFT JOIN reviews r ON m.menu_item_id = r.menu_item_id
            WHERE 1 $priceCondition
            GROUP BY m.menu_item_id
            LIMIT $items_per_page OFFSET $offset
        ");

    } else {

        $stmtTotal = $db->prepare("
        SELECT COUNT(*) as total
        FROM menu_items m
        WHERE m.category_id = ? $priceCondition
    ");
    $stmtTotal->bind_param("i", $category_id);
        $stmtTotal->execute();
        $total = $stmtTotal->get_result()->fetch_assoc()['total'];

        $stmt = $db->prepare("
            SELECT 
                m.menu_item_id, 
                m.name, 
                m.price, 
                m.image_url, 
                c.name AS category,
                AVG(r.rating) AS avg_rating,
                COUNT(r.review_id) AS total_reviews
            FROM menu_items m
            LEFT JOIN menu_categories c ON m.category_id = c.category_id
            LEFT JOIN reviews r ON m.menu_item_id = r.menu_item_id
            WHERE m.category_id = ? $priceCondition
            GROUP BY m.menu_item_id
            LIMIT ? OFFSET ?
        ");

        $stmt->bind_param("iii", $category_id, $items_per_page, $offset);
        $stmt->execute();
        $menuQuery = $stmt->get_result();
    }

    /* TOTAL PAGES */
    $total_pages = ceil($total / $items_per_page);

    /* FETCH ALL CATEGORIES */
    $categoryQuery = $db->query("
        SELECT category_id, name 
        FROM menu_categories
        ORDER BY name ASC
    ");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Administrator Menu</title>
    <link rel="stylesheet" href="../css/fonts.css">
    <link rel="stylesheet" href="../css/menu.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
    <body>

        <?php include 'sidebar.php'; ?>

        <section id="content">
            <main class="dashboard">

                <!-- HEADER -->
                <div class="header">

                    <div class="header-left">
                        <h2 class="font-surimi-title">Menu</h2>
                        <p>Manage your menu items</p>
                    </div>

                    <div class="header-right">

                        <div class="search-wrapper">
                            <i class='bx bx-search'></i>
                            <input type="text" class="search-bar" placeholder="Search anything...">
                        </div>

                        <div class="account-details">
                            <h2><?= $_SESSION['fname'] . ' ' . $_SESSION['lname']; ?></h2>
                            <p>Admin</p>
                        </div>

                        <img src="/Fizz_and_Bits_Website/images/admin-pfp.png">

                    </div>

                </div>


                <div class="menu-layout">

                    <!-- FILTER SIDEBAR -->
                    <div class="menu-filter">

                        <h3>Filter</h3>

                        <form method="GET">

                            <h4>Category</h4>

                            <!-- ALL OPTION -->
                            <label>
                                <input type="radio" name="category" value="All"
                                    <?= ($category_id == "All") ? "checked" : "" ?>
                                    onchange="this.form.submit()">
                                <span></span>
                                All
                            </label>

                            <?php while($cat = $categoryQuery->fetch_assoc()): ?>

                                <label>
                                    <input type="radio" name="category"
                                        value="<?= $cat['category_id']; ?>"
                                        <?= ($category_id == $cat['category_id']) ? "checked" : "" ?>
                                        onchange="this.form.submit()">

                                    <span></span>
                                    <?= $cat['name']; ?>
                                </label>

                            <?php endwhile; ?>


                            <h4>Price Range</h4>

                            <?php 
                            $priceOptions = ["10-100", "100-200", "200-300", "300-400"];
                            foreach ($priceOptions as $option): 
                                $checked = (!empty($priceFilters) && in_array($option, $priceFilters)) ? "checked" : "";
                            ?>
                                <label>
                                    <input type="checkbox" name="price[]" value="<?= $option; ?>" <?= $checked; ?> onchange="this.form.submit()">
                                    <span></span>
                                    ₱<?= str_replace("-", " - ₱", $option); ?>
                                </label>
                            <?php endforeach; ?>

                        </form>

                    </div>


                    <!-- PRODUCTS PANEL -->
                    <div class="menu-panel">

                        <!-- PRODUCT GRID -->
                        <div class="menu-grid">

                            <?php while ($row = $menuQuery->fetch_assoc()): ?>

                                <div class="menu-card">

                                    <div class="menu-img">
                                        <img src="/Fizz_and_Bits_Website/images/<?= $row['image_url']; ?>">
                                    </div>

                                    <div class="menu-info">

                                        <h3><?php echo $row['name']; ?></h3>

                                        <p class="category"><?php echo $row['category']; ?></p>

                                        <div class="menu-bottom">

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
                                                    <?= number_format($row['avg_rating'] ?? 0, 1); ?>/5 
                                                    (<?= $row['total_reviews'] ?? 0 ?>)
                                                </small>
                                            </div>

                                            <span class="price">₱<?php echo $row['price']; ?></span>

                                        </div>

                                    </div>

                                </div>

                            <?php endwhile; ?>

                        </div>


                        <!-- FOOTER -->
                        <div class="menu-footer">

                            <div>
                                Sorting <span class="badge"><?php echo $page; ?></span> out of <?php echo $total_pages; ?>
                            </div>

                            <div class="pagination">

                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>

                                    <button
                                        onclick="window.location.href='?category=<?php echo $category_id ?>&page=<?php echo $i ?>'"
                                        <?php if ($page == $i) echo 'class="active"'; ?>
                                    >

                                        <?php echo $i ?>

                                    </button>

                                <?php endfor; ?>

                            </div>

                        </div>

                    </div>

                </div>

            </main>
        </section>
    <body>
</html>

<!-- PUT JAVASCRIPT CODES HERE -->