<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sidebar</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../css/fonts.css"> 
    <link rel="stylesheet" href="../css/sidebar.css"> 

    
</head>
<body>
<section id="sidebar">
    <a href="dashboard.php" class="brand">
        <img src="/Fizz_and_Bits_Website/images/imagotype_logo.png" alt="Fizz & Bits Logo" class="logo">
    </a>
    <ul class="side-menu top">
        <li class="<?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>">
            <a href="dashboard.php">
                <i class='bx bxs-dashboard'></i>
                <span class="font-surimi-sidebar">Dashboard</span>
            </a>
        </li>
        <li class="<?= basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'active' : '' ?>">
            <a href="orders.php">
                <i class='bx bxs-note'></i>
                <span class="font-surimi-sidebar">Orders</span>
            </a>
        </li>
        <li class="<?= basename($_SERVER['PHP_SELF']) == 'menu.php' ? 'active' : '' ?>">
            <a href="menu.php">
                <i class='bx bxs-food-menu'></i>
                <span class="font-surimi-sidebar">Menu</span>
            </a>
        </li>
        <li class="<?= basename($_SERVER['PHP_SELF']) == 'inventory.php' ? 'active' : '' ?>">
            <a href="inventory.php">
                <i class='bx bxs-package'></i>
                <span class="font-surimi-sidebar">Inventory</span>
            </a>
        </li>
    </ul>
    <ul class="side-menu">
        <li>
            <a href="index.php" class="logout">
                <i class='bx bxs-log-out'></i>
                <span class="font-surimi-sidebar">Logout</span>
            </a>
        </li>
    </ul>
</section>
</body>
</html>
