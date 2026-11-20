<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>


<link rel="stylesheet" href="../css/navbar.css">

<header class="navbar">
    <div class="nav-text left">
        <!-- <i class='bx bxs-note'></i> -->
        <a href="aboutus.php">About Us</a>
        <a href="contactus.php">Contact Us</a>
    </div>

    <div class="nav-logo">
        <a href="front.php" class="brand">
            <img src="/Fizz_and_Bits_Website/images/imagotype_logo.png" alt="Fizz & Bits Logo">
        </a>
    </div>

    <div class="nav-icons right">
    
        <a href="menu.php"><i class='bx bx-restaurant'></i></a>

        <div class="user-dropdown">
            <i class='bx bxs-user user-icon' onclick="toggleUserMenu()"></i>

            <div class="user-menu" id="userMenu">
                <p class="user-name">
                    <?php if (isset($_SESSION['fname']) && isset($_SESSION['lname'])): ?>
                        <?= $_SESSION['fname'] . ' ' . $_SESSION['lname']; ?>
                    <?php else: ?>
                        <?= $_SESSION['username'] ?? 'User'; ?>
                    <?php endif; ?>
                </p>

                <a href="logout.php" class="logout-btn">Log out</a>
            </div>
        </div>

        <a href="cart.php"><i class='bx bxs-cart'></i></a>

    </div>
</header>

<script>
    function toggleUserMenu() {
        document.getElementById("userMenu").classList.toggle("active");
    }
    document.addEventListener("click", function(event) {
        const dropdown = document.querySelector(".user-dropdown");
        const menu = document.getElementById("userMenu");

        if (!dropdown.contains(event.target)) {
            menu.classList.remove("active");
        }
    });
</script>