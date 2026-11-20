<?php
require 'auth_admin.php';
require 'config.php';

    // ===================== DEFAULT FILTERS =====================
    $revRange   = intval($_GET['rev'] ?? 1);
    $catRange   = intval($_GET['cat'] ?? 1);
    $orderRange = intval($_GET['ord'] ?? 1);
    $typeRange  = intval($_GET['type'] ?? 1);
    $trendRange = $_GET['trend'] ?? 'week';

    // ===================== MAIN QUERIES =====================

    // TOTAL REVENUE
    $revenueQuery = $db->query("
        SELECT SUM(total_amount) AS total 
        FROM orders 
        WHERE order_status = 'completed'
    ");
    $revenue = $revenueQuery->fetch_assoc()['total'] ?? 0;

    // TOTAL ORDERS
    $orderQuery = $db->query("SELECT COUNT(*) AS total_orders FROM orders");
    $totalOrders = $orderQuery->fetch_assoc()['total_orders'];

    //TOTAL CUSTOMERS
    $customersQuery = $db->query("SELECT COUNT(*) AS total_customers FROM users WHERE role = 'customer'");
    $totalCustomers = $customersQuery->fetch_assoc()['total_customers'];

    // FILTERED REVENUE
    $filteredRevenueQuery = $db->query("
        SELECT SUM(total_amount) AS total 
        FROM orders 
        WHERE order_status = 'completed'
        AND order_date >= DATE_SUB(NOW(), INTERVAL $revRange MONTH)
    ");
    $filteredRevenue = $filteredRevenueQuery->fetch_assoc()['total'] ?? 0;

    // ORDERS OVERVIEW
    $ordersOverviewQuery = $db->query("
        SELECT COUNT(*) AS total 
        FROM orders
        WHERE order_date >= DATE_SUB(NOW(), INTERVAL $orderRange MONTH)
    ");
    $ordersOverview = $ordersOverviewQuery->fetch_assoc()['total'] ?? 0;

    // TOP CATEGORIES
    $topCategoriesQuery = $db->query("
        SELECT m.name, SUM(oi.quantity) AS total_sold
        FROM order_items oi
        JOIN menu_items m ON oi.menu_item_id = m.menu_item_id
        JOIN orders o ON oi.order_id = o.order_id
        WHERE o.order_date >= DATE_SUB(NOW(), INTERVAL $catRange MONTH)
        GROUP BY m.name
        ORDER BY total_sold DESC
        LIMIT 3
    ");

    // ORDER TYPES
    $orderTypesQuery = $db->query("
        SELECT order_type, COUNT(*) AS total
        FROM orders
        WHERE order_date >= DATE_SUB(NOW(), INTERVAL $typeRange MONTH)
        GROUP BY order_type
    ");

    // TREND FILTER
    $trendCondition = match ($trendRange) {
        'week'  => 'INTERVAL 7 DAY',
        'month' => 'INTERVAL 1 MONTH',
        'year'  => 'INTERVAL 1 YEAR',
    };

    // TRENDING
    $trendingQuery = $db->query("
        SELECT m.name, m.image_url, SUM(oi.quantity) AS total_sold
        FROM order_items oi
        JOIN menu_items m ON oi.menu_item_id = m.menu_item_id
        JOIN orders o ON oi.order_id = o.order_id
        WHERE o.order_date >= DATE_SUB(NOW(), $trendCondition)
        GROUP BY oi.menu_item_id
        ORDER BY total_sold DESC
        LIMIT 3
    ");

    // RECENT ORDERS
    $recentOrders = $db->query("
        SELECT order_id, full_name, total_amount, order_status
        FROM orders
        ORDER BY order_date DESC
        LIMIT 5
    ");

    // REVIEWS
    $reviews = $db->query("
        SELECT r.comment, r.rating, m.name
        FROM reviews r
        JOIN menu_items m ON r.menu_item_id = m.menu_item_id
        ORDER BY r.created_at DESC
        LIMIT 2
    ");

    // CHART - FOR JS - REVENUE OVERVIEW
    $revenueChartQuery = $db->query("
        SELECT DATE(order_date) as date, SUM(total_amount) as total
        FROM orders
        WHERE order_status = 'completed'
        AND order_date >= DATE_SUB(NOW(), INTERVAL $revRange MONTH)
        GROUP BY DATE(order_date)
        ORDER BY date ASC
    ");

    $revDates = [];
    $revTotals = [];

    while($row = $revenueChartQuery->fetch_assoc()){
        $revDates[] = $row['date'];
        $revTotals[] = $row['total'];
    }

    // CHART - FOR JS - ORDERS OVERVIEW
    $ordersChartQuery = $db->query("
        SELECT DATE(order_date) as date, COUNT(*) as total
        FROM orders
        WHERE order_date >= DATE_SUB(NOW(), INTERVAL $orderRange MONTH)
        GROUP BY DATE(order_date)
        ORDER BY date ASC
    ");

    $orderDates = [];
    $orderTotals = [];

    while($row = $ordersChartQuery->fetch_assoc()){
        $orderDates[] = $row['date'];
        $orderTotals[] = $row['total'];
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Administrator Dashboard</title>
    <link rel="stylesheet" href="../css/fonts.css">
    <link rel="stylesheet" href="../css/dashboard.css">
</head>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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

            <!-- MAIN GRID -->
            <div class="main-grid">

                <!-- LEFT -->
                <div class="left">
                    <!-- SUMMARY -->
                    <div class="summary-row">
                        <div class="two-rows-summary">
                            <div class="summary-orange-box">
                                <div>
                                    <i class='bx bxs-cart'></i>
                                </div>
                            </div>
                            <div>
                                <h4>Total Orders</h4>
                                <h2 class="summary-numbers"><?= $totalOrders; ?></h2>
                            </div>
                        </div>

                        <div class="two-rows-summary">
                            <div class="summary-orange-box">
                                <div>
                                    <i class='bx bxs-user'></i>
                                </div>
                            </div>
                            <div>
                                <h4>Total Customers</h4>
                                <h2 class="summary-numbers"><?= $totalCustomers; ?></h2>
                            </div>
                        </div>

                        <div class="two-rows-summary">
                            <div class="summary-orange-box">
                                <div>
                                    <i class='bx bxs-wallet'></i>
                                </div>
                            </div>
                            <div>
                                <h4>Total Revenue</h4>
                                <h2 class="summary-numbers">₱<?= number_format($revenue, 2); ?></h2>
                            </div>
                        </div>
                    </div>

                    <!-- ROW 1 -->
                    <div class="box-row">
                        <div class="box">
                            <div class="two-row">
                                <h4>Total Revenue</h4>
                                <form method="GET">
                                    <input type="hidden" name="cat" value="<?= $catRange ?>">
                                    <input type="hidden" name="ord" value="<?= $orderRange ?>">
                                    <input type="hidden" name="type" value="<?= $typeRange ?>">
                                    <input type="hidden" name="trend" value="<?= $trendRange ?>">

                                    <select class="font-poppins-select" name="rev" onchange="this.form.submit()">
                                        <option value="1" <?= $revRange==1?'selected':'' ?>>This Month</option>
                                        <option value="3" <?= $revRange==3?'selected':'' ?>>Last 3 Months</option>
                                        <option value="6" <?= $revRange==6?'selected':'' ?>>Last 6 Months</option>
                                    </select>
                                </form>
                            </div>
                            <h2>₱<?= number_format($filteredRevenue, 2); ?></h2>
                            <div class="chart-container">
                                <canvas id="revenueChart"></canvas>
                            </div>
                        </div>

                        <div class="box">
                            <div class="two-row">
                                <h4>Top Categories</h4>

                                <form method="GET">
                                    <input type="hidden" name="rev" value="<?= $revRange ?>">
                                    <input type="hidden" name="ord" value="<?= $orderRange ?>">
                                    <input type="hidden" name="type" value="<?= $typeRange ?>">
                                    <input type="hidden" name="trend" value="<?= $trendRange ?>">

                                    <select class="font-poppins-select" name="cat" onchange="this.form.submit()">
                                        <option value="1" <?= $catRange==1?'selected':'' ?>>This Month</option>
                                        <option value="3" <?= $catRange==3?'selected':'' ?>>Last 3 Months</option>
                                        <option value="6" <?= $catRange==6?'selected':'' ?>>Last 6 Months</option>
                                    </select>
                                </form>
                            </div>

                            <?php while ($row = $topCategoriesQuery->fetch_assoc()): ?>
                                <div class="list-item">
                                    <span class="item-name"><?= $row['name']; ?></span>
                                    <span class="item-value"><?= $row['total_sold']; ?> sold</span>
                                </div>
                            <?php endwhile; ?>
                        </div>

                    </div>

                    <!-- ROW 2 -->
                    <div class="box-row">
                        <div class="box">
                            <div class="two-row">
                                <h4>Orders Overview</h4>

                                <form method="GET">
                                    <input type="hidden" name="rev" value="<?= $revRange ?>">
                                    <input type="hidden" name="cat" value="<?= $catRange ?>">
                                    <input type="hidden" name="type" value="<?= $typeRange ?>">
                                    <input type="hidden" name="trend" value="<?= $trendRange ?>">

                                    <select class="font-poppins-select" name="ord" onchange="this.form.submit()">
                                        <option value="1" <?= $orderRange==1?'selected':'' ?>>This Month</option>
                                        <option value="3" <?= $orderRange==3?'selected':'' ?>>Last 3 Months</option>
                                        <option value="6" <?= $orderRange==6?'selected':'' ?>>Last 6 Months</option>
                                    </select>
                                </form>
                            </div>
                            <h2><?= $ordersOverview; ?> orders</h2>
                            <div class="chart-container">
                                <canvas id="ordersChart"></canvas>
                            </div>
                        </div>

                        <div class="box">
                            <div class="two-row">
                                <h4>Order Types</h4>

                                <form method="GET">
                                    <input type="hidden" name="rev" value="<?= $revRange ?>">
                                    <input type="hidden" name="cat" value="<?= $catRange ?>">
                                    <input type="hidden" name="ord" value="<?= $orderRange ?>">
                                    <input type="hidden" name="trend" value="<?= $trendRange ?>">

                                    <select class="font-poppins-select" name="type" onchange="this.form.submit()">
                                        <option value="1" <?= $typeRange==1?'selected':'' ?>>This Month</option>
                                        <option value="3" <?= $typeRange==3?'selected':'' ?>>Last 3 Months</option>
                                        <option value="6" <?= $typeRange==6?'selected':'' ?>>Last 6 Months</option>
                                    </select>
                                </form>
                            </div>

                            <?php while ($row = $orderTypesQuery->fetch_assoc()): ?>
                                <div class="list-item">
                                    <span class="item-name"><?= $row['order_type']; ?></span>
                                    <span class="item-value"><?= $row['total']; ?></span>
                                </div>
                            <?php endwhile; ?>
                        </div>

                    </div>

                    <!-- RECENT ORDERS -->
                    <div class="box large">
                        <h4>Recent Orders</h4>

                        <div class="orders-list">
                            <div class="order-item-title">
                                <span>Order ID</span>
                                <span>Customer</span>
                                <span>Amount</span>
                                <span>Status</span>
                            </div>
                        </div>

                        <div class="orders-list">
                            <?php while ($row = $recentOrders->fetch_assoc()): ?>
                                <div class="order-item">
                                    <span>#<?= $row['order_id']; ?></span>
                                    <span><?= $row['full_name']; ?></span>
                                    <span>₱<?= $row['total_amount']; ?></span>
                                    <span><?= $row['order_status']; ?></span>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>

                    <!-- REVIEWS -->
                    <h3>Customer Reviews</h3>

                    <div class="two-row-customer-reviews">
                        <?php while ($row = $reviews->fetch_assoc()): ?>
                            <div class="box">
                                <h4><?= $row['name']; ?></h4>
                                <p><?= $row['comment']; ?></p>
                                <div class="stars">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <?php if ($i <= $row['rating']): ?>
                                            <i class='bx bxs-star'></i>
                                        <?php else: ?>
                                            <i class='bx bx-star'></i>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                    <small><?= $row['rating']; ?>/5</small>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>

                </div>

                <!-- RIGHT -->
                <div class="right">

                    <div class="two-row">
                        <h3>Trending Menus</h3>

                        <form method="GET">
                            <input type="hidden" name="rev" value="<?= $revRange ?>">
                            <input type="hidden" name="cat" value="<?= $catRange ?>">
                            <input type="hidden" name="ord" value="<?= $orderRange ?>">
                            <input type="hidden" name="type" value="<?= $typeRange ?>">

                            <select class="select-trend" name="trend" onchange="this.form.submit()">
                                <option value="week" <?= $trendRange=='week'?'selected':'' ?>>This Week</option>
                                <option value="month" <?= $trendRange=='month'?'selected':'' ?>>This Month</option>
                                <option value="year" <?= $trendRange=='year'?'selected':'' ?>>This Year</option>
                            </select>
                        </form>
                    </div>

                    <?php while ($row = $trendingQuery->fetch_assoc()): ?>
                        <div class="trending-item">
                            <img src="/Fizz_and_Bits_Website/images/<?= $row['image_url']; ?>">
                            <!-- <img src="/images/<?= $row['image_url']; ?>"> -->
                            <div class="trending-info">
                                <strong><?= $row['name']; ?></strong><br>
                                <?= $row['total_sold']; ?> sold
                            </div>
                        </div>
                    <?php endwhile; ?>

                </div>

            </div>

        </main>
    </section>
</body>
</html>

<script>
    new Chart(document.getElementById('revenueChart'), {
        type: 'line',
        data: {
            labels: <?= json_encode($revDates); ?>,
            datasets: [{
                label: 'Revenue',
                data: <?= json_encode($revTotals); ?>,
                tension: 0.3,
                fill: true,
                borderColor: '#F9A11D',
                backgroundColor: 'rgba(249, 161, 29, 0.2)'
            }]
        },
        options: {
            plugins: {
                legend: {
                    labels: {
                        color: '#B27212',
                        font: {
                            family: 'Poppins, sans-serif',
                            size: 12
                        }
                    }
                }
            },
            scales: {
                x: {
                    ticks: {
                        color: '#B27212',
                        font: {
                            family: 'Poppins, sans-serif'
                        }
                    }
                },
                y: {
                    ticks: {
                        color: '#B27212',
                        font: {
                            family: 'Poppins, sans-serif'
                        }
                    }
                }
            }
        }
    });
</script>

<script>
    new Chart(document.getElementById('ordersChart'), {
        type: 'bar',
        data: {
            labels: <?= json_encode($orderDates); ?>,
            datasets: [{
                label: 'Orders',
                data: <?= json_encode($orderTotals); ?>,
                backgroundColor: '#F9A11D',
                borderRadius: 6
            }]
        },
        options: {
            plugins: {
                legend: {
                    labels: {
                        color: '#B27212',
                        font: {
                            family: 'Poppins, sans-serif',
                            size: 12
                        }
                    }
                }
            },
            scales: {
                x: {
                    ticks: {
                        color: '#B27212',
                        font: {
                            family: 'Poppins, sans-serif'
                        }
                    }
                },
                y: {
                    ticks: {
                        color: '#B27212',
                        font: {
                            family: 'Poppins, sans-serif'
                        }
                    }
                }
            }
        }
    });
</script>