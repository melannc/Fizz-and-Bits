<?php
require 'auth_admin.php';
require 'config.php';

    /* SESSION ARRAY FOR RESTOCK COUNT */
    if (!isset($_SESSION['restock_count'])) {
        $_SESSION['restock_count'] = [];
    }

    /* ADD INVENTORY ITEM */
    if (isset($_POST['add_item'])) {

        $name = $_POST['name'];
        $description = $_POST['description'];
        $price = floatval($_POST['price']);
        $quantity = intval($_POST['quantity']);
        $category_id = intval($_POST['category_id']);
        $new_category = trim($_POST['new_category']);

        // ================= CATEGORY =================
        if (!empty($new_category)) {
            $stmt = $db->prepare("INSERT INTO menu_categories(name) VALUES (?)");
            $stmt->bind_param("s", $new_category);
            $stmt->execute();
            $category_id = $db->insert_id;
        }

        // ================= IMAGE UPLOAD =================
        $image_name = basename($_FILES['image']['name']);
        $folder = "../../images/";
        $upload_path = $folder . $image_name;

        // prevent duplicate names
        $counter = 1;
        $path_parts = pathinfo($image_name);

        while (file_exists($upload_path)) {
            $image_name = $path_parts['filename'] . "_" . $counter . "." . $path_parts['extension'];
            $upload_path = $folder . $image_name;
            $counter++;
        }

        $tmp_name = $_FILES['image']['tmp_name'];

        if (!move_uploaded_file($tmp_name, $upload_path)) {
            die("Image upload failed. Check folder path.");
        }

        // ================= INSERT MENU =================
        $stmt = $db->prepare("
            INSERT INTO menu_items 
            (name, description, category_id, price, availability_status, image_url, is_featured)
            VALUES (?, ?, ?, ?, 'available', ?, 0)
        ");

        $stmt->bind_param("ssids", 
            $name, 
            $description, 
            $category_id, 
            $price, 
            $image_name
        );
                $stmt->execute();

        $menu_item_id = $db->insert_id;

        // ================= INSERT INVENTORY =================
        $stmt = $db->prepare("
            INSERT INTO inventory (menu_item_id, quantity_in_stock, reorder_level)
            VALUES (?, ?, 5)
        ");

        $stmt->bind_param("ii", $menu_item_id, $quantity);
        $stmt->execute();

        header("Location: inventory.php");
        exit;
    }

    /* DELETE ITEM */
    if (isset($_GET['delete'])) {

        $id = intval($_GET['delete']);

        // 1. Get menu_item_id from inventory
        $get = $db->prepare("SELECT menu_item_id FROM inventory WHERE inventory_id=?");
        $get->bind_param("i", $id);
        $get->execute();
        $res = $get->get_result();

        if ($res->num_rows === 0) {
            die("Item not found.");
        }

        $row = $res->fetch_assoc();
        $menu_id = $row['menu_item_id'];

        // 2. DELETE from menu_items ONLY
        $stmt = $db->prepare("DELETE FROM menu_items WHERE menu_item_id=?");
        $stmt->bind_param("i", $menu_id);
        $stmt->execute();

        // 🔥 inventory will auto-delete because of CASCADE

        header("Location: inventory.php");
        exit;
    }

    /* UPDATE QUANTITY (RESTOCK) */
    if (isset($_POST['update_item'])) {

        $id = intval($_POST['inventory_id']);
        $quantity = intval($_POST['quantity']);

        if ($quantity < 0) {
            die("Quantity cannot be negative");
        }

        // 1. Update inventory quantity
        $stmt = $db->prepare("
            UPDATE inventory
            SET quantity_in_stock=?
            WHERE inventory_id=?
        ");
        $stmt->bind_param("ii", $quantity, $id);
        $stmt->execute();

        // 2. Get menu_item_id (IMPORTANT!)
        $get = $db->prepare("SELECT menu_item_id FROM inventory WHERE inventory_id=?");
        $get->bind_param("i", $id);
        $get->execute();
        $res = $get->get_result();
        $row = $res->fetch_assoc();

        $menu_id = $row['menu_item_id'];

        // 3. AUTO UPDATE AVAILABILITY 
        if ($quantity == 0) {

            $stmt2 = $db->prepare("
                UPDATE menu_items
                SET availability_status='not available'
                WHERE menu_item_id=?
            ");

        } else {

            $stmt2 = $db->prepare("
                UPDATE menu_items
                SET availability_status='available'
                WHERE menu_item_id=?
            ");
        }

        $stmt2->bind_param("i", $menu_id);
        $stmt2->execute();

        // 4. Track restock count (your existing logic)
        if (!isset($_SESSION['restock_count'][$id])) {
            $_SESSION['restock_count'][$id] = 0;
        }

        $_SESSION['restock_count'][$id]++;

        header("Location: inventory.php");
        exit;
    }

    /* INVENTORY QUERY */
    $inventoryQuery = $db->query("
        SELECT
            i.inventory_id,
            i.menu_item_id,
            m.name,
            m.price,
            m.image_url,
            i.quantity_in_stock
        FROM inventory i
        INNER JOIN menu_items m
            ON i.menu_item_id = m.menu_item_id
        ORDER BY m.name ASC
    ");

    /* MENU ITEMS FOR ADD DROPDOWN */
    $menuItems = $db->query("
        SELECT menu_item_id, name
        FROM menu_items
    ");
?>

<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="UTF-8">
    <title>Administrator Inventory</title>

    <link rel="stylesheet" href="../css/fonts.css">
    <link rel="stylesheet" href="../css/menu.css">
    <link rel="stylesheet" href="../css/inventory.css">


    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

</head>
    <body>

    <?php include 'sidebar.php'; ?>

    <section id="content">

        <main class="dashboard">

            <!-- HEADER -->
            <div class="header">
                <div class="header-left">
                    <h2 class="font-surimi-title">Dashboard</h2>
                    <p>Hello Axel, welcome back!</p>
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

            <div class="menu-layout font-poppins">

                <div class="menu-panel">

                    <div class="menu-toolbar">
                        <button class="add-btn font-poppins-select" onclick="openModal()">+ Add Item</button>
                    </div>

                    <div class="menu-grid">

                        <?php while ($row = $inventoryQuery->fetch_assoc()): ?>

                            <?php
                                $id = $row['inventory_id'];
                                $restock_count = $_SESSION['restock_count'][$id] ?? 0;

                                /* AUTOMATIC REORDER LEVEL */
                                if ($restock_count <= 3) {
                                    $reorder_level = 5;
                                } elseif ($restock_count <= 7) {
                                    $reorder_level = 10;
                                } else {
                                    $reorder_level = 20;
                                }
                            ?>

                            <div class="menu-card">

                                <div class="menu-img">
                                    <img src="/Fizz_and_Bits_Website/images/<?= $row['image_url']; ?>">
                                </div>

                                <div class="menu-info">

                                    <h3><?php echo $row['name']; ?></h3>

                                    <p>Stock: <?php echo $row['quantity_in_stock']; ?></p>
                                    <p>Restocks: <?php echo $restock_count; ?></p>
                                    <p>Reorder Level: <?php echo $reorder_level; ?></p>

                                    <div class="menu-bottom font-poppins-select">

                                        <?php
                                            if ($row['quantity_in_stock'] <= $reorder_level) {
                                                echo "<span class='low-stock'>Low Stock</span>";
                                            } else {
                                                echo "<span class='in-stock'>Available</span>";
                                            }
                                        ?>

                                        <span class="price">₱<?php echo $row['price']; ?></span>

                                    </div>

                                    <div class="inventory-actions">

                                        <button class="font-poppins-select" onclick="openRestockModal(
                                            <?php echo $row['inventory_id']; ?>,
                                            <?php echo $row['quantity_in_stock']; ?>
                                        )">
                                            Restock
                                        </button>

                                        <button class="delete-btn font-poppins-select" onclick="openDeleteModal(<?= $row['inventory_id']; ?>)">
                                            Delete
                                        </button>

                                    </div>

                                </div>

                            </div>

                        <?php endwhile; ?>

                    </div>
                </div>
            </div>

        </main>
    </section>


    <!-- ADD MODAL -->

     <div id="addModal" class="modal">

        <div class="modal-content">
            <button class="modal-x" onclick="closeModal()">×</button>

            <h3>Add Inventory Item</h3>

            <form method="POST" enctype="multipart/form-data">

                <label>Item Name</label>
                <input type="text" name="name" required>

                <label>Description</label>
                <textarea name="description" required></textarea>

                <label>Category</label>
                <select name="category_id" required>
                    <?php
                    $cats = $db->query("SELECT * FROM menu_categories");
                    while ($c = $cats->fetch_assoc()):
                    ?>
                        <option value="<?= $c['category_id']; ?>">
                            <?= $c['name']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>

                <label>Or Add New Category</label>
                <input type="text" name="new_category" placeholder="Optional">

                <label>Price</label>
                <input type="number" step="0.01" name="price" required>

                <label>Image</label>
                <input type="file" name="image" accept="image/*" required>

                <label>Quantity</label>
                <input type="number" name="quantity" required>

                <button type="submit" name="add_item" class="font-poppins-select">Add Item</button>

            </form>

  

        </div>
    </div>


    <!-- EDIT MODAL -->

    <div id="restockModal" class="modal">
        <div class="modal-content">
            <button class="modal-x" onclick="closeRestockModal()">×</button>

            <h3>Restock Item</h3>

            <form method="POST">

                <input type="hidden" name="inventory_id" id="restock_id">

                <label>New Quantity</label>
                <input type="number" name="quantity" id="restock_quantity" required>

                <button type="submit" name="update_item">Update Stock</button>

            </form>


        </div>
    </div>

    <!-- DELETE MODAL -->
    <div id="deleteModal" class="modal">
    <div class="modal-content delete-modal">

            <h3>Confirm Deletion</h3>
            <p>Are you sure you want to delete this item?</p>

            <div style="display:flex; gap:10px; justify-content:center; margin-top:15px;">
                <button onclick="confirmDelete()">Yes, Delete</button>
                <button onclick="closeDeleteModal()">Cancel</button>
            </div>

        </div>
    </div>
    </body>
</html>

<!-- PUT JAVASCRIPT CODES HERE -->
 
<script>
    function confirmDelete(id) {
        if (confirm("Are you sure you want to delete this item?")) {
            window.location.href = "inventory.php?delete=" + id;
        }
    }

    function openModal() {
        document.getElementById("addModal").style.display = "flex";
    }

    function closeModal() {
        document.getElementById("addModal").style.display = "none";
    }

    function openRestockModal(id, quantity) {
        document.getElementById("restockModal").style.display = "flex";
        document.getElementById("restock_id").value = id;
        document.getElementById("restock_quantity").value = quantity;
    }

    function closeRestockModal() {
        document.getElementById("restockModal").style.display = "none";
    }

    let deleteId = null;

    function openDeleteModal(id) {
        deleteId = id;
        document.getElementById("deleteModal").style.display = "flex";
    }

    function closeDeleteModal() {
        document.getElementById("deleteModal").style.display = "none";
        deleteId = null;
    }

    function confirmDelete() {
        if (deleteId !== null) {
            window.location.href = "inventory.php?delete=" + deleteId;
        }
    }

</script>