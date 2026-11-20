<?php
    session_name("customer_session");
    session_start();

    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
    include "config.php";

    $bestQuery = $db->query("
        SELECT * FROM menu_items
        WHERE availability_status = 'available'
        ORDER BY menu_item_id ASC
        LIMIT 4
    ")
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fizz & Bits</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../css/fonts.css">
    <link rel="stylesheet" href="../css/menu.css">
    <link rel="stylesheet" href="../css/storefront.css">
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="container">
        <div class="content">
            <div class="slider">
                <div class="slides">
                    <img src="../../images/mozza.jpg" class="slide active">
                    <img src="../../images/fries.jpg" class="slide">
                    <img src="../../images/icedtea.jpg" class="slide">
                </div>
                <div class="dots">
                    <span class="dot active"></span>
                    <span class="dot"></span>
                    <span class="dot"></span>
                </div>
            </div>
            <section class="best">
                <h2 class="best-title">BEST SELLERS</h2>
                <div class="best-container">
                    <?php while ($row = $bestQuery->fetch_assoc()): ?>
                        <div class="best-card">
                            <a href="menu.php?item=<?= $row['menu_item_id'] ?>" class="card">
                                <div class="image-box">
                                    <img src="/Fizz_and_Bits_Website/images/<?= htmlspecialchars($row['image_url']) ?>">
                                </div>
                                <div class="info-card">
                                    <h4><?= $row['name'] ?></h4>
                                    <small>₱<?= $row['price'] ?></small>
                                </div>
                            </a>
                            <div class="best-text">
                                <p class="best-desc">
                                    Popular menu item loved by our customers!
                                </p>
                                <a href="menu.php?item=<?= $row['menu_item_id'] ?>" class="order-btn">
                                    Order Now
                                </a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </section>
        </div>
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", () => {

            let slides = document.querySelectorAll(".slide");
            let dots = document.querySelectorAll(".dot");

            let index = 0;

            function showSlide(){
                slides.forEach(slide => slide.classList.remove("active"));
                dots.forEach(dot => dot.classList.remove("active"));

                slides[index].classList.add("active");
                dots[index].classList.add("active");

                index++;

                if(index >= slides.length){
                    index = 0;
                }
            }

            setInterval(showSlide, 3000); // faster for testing

        });
    </script>
    <?php include 'footer.php'; ?>
</body>
</html>