<?php
require 'auth_admin.php';
require 'config.php';

    // PUT PHP CODES HERE

        // ===================== DEFAULT FILTERS =====================
    $revRange   = intval($_GET['rev'] ?? 1);
    $catRange   = intval($_GET['cat'] ?? 1);
    $orderRange = intval($_GET['ord'] ?? 1);
    $typeRange  = intval($_GET['type'] ?? 1);
    $trendRange = $_GET['trend'] ?? 'week';

    // ===================== MAIN QUERIES =====================
    // TOTAL ORDERS
    $orderQuery = $db->query("SELECT COUNT(*) AS total_orders FROM orders");
    $totalOrders = $orderQuery->fetch_assoc()['total_orders'];

    // ORDERS ON PROCESS
    $onProcessQuery = $db->query("SELECT COUNT(*) AS orders_on_process FROM orders WHERE order_status = 'on process'");
    $totalOrdersOnProcess = $onProcessQuery->fetch_assoc()['orders_on_process'];

    // ORDERS COMPLETED
    $completedQuery = $db->query("SELECT COUNT(*) AS orders_completed FROM orders WHERE order_status = 'completed'");
    $totalOrdersCompleted = $completedQuery->fetch_assoc()['orders_completed'];

    // ORDERS CANCELED
    $canceledQuery = $db->query("SELECT COUNT(*) AS orders_canceled FROM orders WHERE order_status = 'canceled'");
    $totalOrdersCanceled = $canceledQuery->fetch_assoc()['orders_canceled'];

    // ORDERS OVERVIEW
    $ordersOverviewQuery = $db->query("
        SELECT COUNT(*) AS total 
        FROM orders
        WHERE order_date >= DATE_SUB(NOW(), INTERVAL $orderRange MONTH)
    ");
    $ordersOverview = $ordersOverviewQuery->fetch_assoc()['total'] ?? 0;

    // ORDER TYPES
    $orderTypesQuery = $db->query("
        SELECT order_type, COUNT(*) AS total
        FROM orders
        WHERE order_date >= DATE_SUB(NOW(), INTERVAL $typeRange MONTH)
        GROUP BY order_type
    ");

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

    $statusFilter = $_GET['status'] ?? 'all';
    $search = $_GET['search'] ?? '';

    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;

    $offset = ($page - 1) * $limit;

    $where = "WHERE 1=1";

    if ($statusFilter != 'all') {
        $where .= " AND o.order_status = '$statusFilter'";
    }

    if (!empty($search)) {
        $where .= " AND (
            o.order_id LIKE '%$search%' OR
            o.full_name LIKE '%$search%' OR
            o.order_type LIKE '%$search%'
        )";
    }

    $orderTableQuery = $db->query("
    SELECT 
        o.order_id,
        o.order_date,
        o.full_name,
        o.order_type,
        o.address,
        o.total_amount,
        o.order_status,
        SUM(oi.quantity) as total_qty
    FROM orders o
    LEFT JOIN order_items oi ON o.order_id = oi.order_id
    $where
    GROUP BY o.order_id
    ORDER BY o.order_date DESC
    LIMIT $limit OFFSET $offset
    ");

    $totalQuery = $db->query("
    SELECT COUNT(*) as total 
    FROM orders o
    $where
    ");

    $totalRows = $totalQuery->fetch_assoc()['total'];
    $totalPages = ceil($totalRows / $limit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Administrator Orders</title>
    <link rel="stylesheet" href="../css/fonts.css">
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/orders.css">

</head>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <body>

        <?php include 'sidebar.php'; ?>

        <section id="content">
            <main class="dashboard">

                <!-- HEADER -->
                <div class="header">
                    <div class="header-left">
                        <h2 class="font-surimi-title">Orders</h2>
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

                <!-- PUT HTML AND CSS CODES HERE -->

                <div class="three-columns">
                    <div class="four-boxes">
                        <div class="box stat-box">
                            <h2><?= $totalOrders ?></h2>
                            <p>Total Orders</p>
                        </div>

                        <div class="box stat-box">
                            <h2><?= $totalOrdersOnProcess ?></h2>
                            <p>On Process</p>
                        </div>

                        <div class="box stat-box">
                            <h2><?= $totalOrdersCompleted ?></h2>
                            <p>Completed</p>
                        </div>

                        <div class="box stat-box">
                            <h2><?= $totalOrdersCanceled ?></h2>
                            <p>Canceled</p>
                        </div>
                    </div>

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

                <!-- bottom orders tab -->
                 <div class="orders-table box">

                    <div class="table-header">

                        <div class="table-tabs">
                            <a href="?status=all" class="<?= $statusFilter=='all'?'active':'' ?>">All</a>
                            <a href="?status=on process" class="<?= $statusFilter=='on process'?'active':'' ?>">On Process</a>
                            <a href="?status=completed" class="<?= $statusFilter=='completed'?'active':'' ?>">Completed</a>
                            <a href="?status=canceled" class="<?= $statusFilter=='canceled'?'active':'' ?>">Canceled</a>
                        </div>

                        <form method="GET" class="table-actions">

                            <input type="hidden" name="status" value="<?= $statusFilter ?>">

                            <div class="search-wrapper">
                                <i class='bx bx-search'></i>
                                <input 
                                    type="text" 
                                    name="search"
                                    value="<?= $search ?>"
                                    placeholder="Search orders">
                            </div>

                            <select class="font-poppins-select" name="rev" onchange="this.form.submit()">
                                <option value="1" <?= $revRange==1?'selected':'' ?>>This Month</option>
                                <option value="3" <?= $revRange==3?'selected':'' ?>>Last 3 Months</option>
                                <option value="6" <?= $revRange==6?'selected':'' ?>>Last 6 Months</option>
                            </select>

                            </form>

                    </div>

                    <table class="orders-data">

                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Date</th>
                                <th>Customer</th>
                                <th>Order Type</th>
                                <th>Address</th>
                                <th>Qty</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php while($order = $orderTableQuery->fetch_assoc()): ?>

                                <?php 
                                    $statusClass = strtolower(str_replace(' ', '-', $order['order_status']));
                                ?>

                                <tr onclick="openOrderModal(<?= $order['order_id'] ?>)">
                                    <td>#<?= $order['order_id'] ?></td>
                                    <td><?= date('M d, Y', strtotime($order['order_date'])) ?></td>
                                    <td><?= $order['full_name'] ?></td>
                                    <td><?= ucfirst($order['order_type']) ?></td>
                                    <td><?= $order['address'] ?></td>
                                    <td><?= $order['total_qty'] ?></td>
                                    <td>₱<?= number_format($order['total_amount'],2) ?></td>
                                    <td>
                                        <span class="status <?= $statusClass ?>">
                                            <?= ucfirst($order['order_status']) ?>
                                        </span>
                                    </td>
                                </tr>

                            <?php endwhile; ?>
                        </tbody>

                    </table>

                    <div class="table-footer">

                        <div>
                            Showing 
                            <form method="GET" style="display:inline;">
                                <input type="hidden" name="status" value="<?= $statusFilter ?>">
                                <input type="hidden" name="search" value="<?= $search ?>">
                                <input type="hidden" name="rev" value="<?= $revRange ?>">

                                <select name="limit" onchange="this.form.submit()">
                                    <option value="10" <?= $limit==10?'selected':'' ?>>10</option>
                                    <option value="20" <?= $limit==20?'selected':'' ?>>20</option>
                                </select>
                            </form>
                            out of <?= $totalRows ?>
                        </div>

                        <div class="pagination">

                            <?php if($page > 1): ?>
                            <a href="?page=<?= $page-1 ?>&status=<?= $statusFilter ?>&limit=<?= $limit ?>&search=<?= $search ?>&rev=<?= $revRange ?>">‹</a>
                            <?php endif; ?>

                            <?php for($i=1; $i<=$totalPages; $i++): ?>
                            <a href="?page=<?= $i ?>&status=<?= $statusFilter ?>&limit=<?= $limit ?>&search=<?= $search ?>&rev=<?= $revRange ?>" class="<?= $page==$i?'active':'' ?>">
                                <?= $i ?>
                            </a>
                            <?php endfor; ?>

                            <?php if($page < $totalPages): ?>
                            <a href="?page=<?= $page+1 ?>&status=<?= $statusFilter ?>&limit=<?= $limit ?>&search=<?= $search ?>&rev=<?= $revRange ?>">›</a>
                            <?php endif; ?>

                        </div>

                    </div>

                </div>

            </main>
        </section>

        <!-- ORDER MODAL -->
        <div id="orderModal" class="modal">
            <div class="modal-content">

                <!-- CLOSE BUTTON -->
                <button onclick="closeOrderModal()" class="close-btn">×</button>

                <!-- ORDER HEADER -->
                <div class="modal-header">
                    <h3>Order #<span id="modalOrderID"></span></h3>
                    <p><strong>Customer:</strong> <span id="modalCustomer"></span></p>
                    <p><strong>Date:</strong> <span id="modalDate"></span></p>
                    <p><strong>Status:</strong> <span id="modalStatus"></span></p>
                </div>

                <!-- ORDER ITEMS / DETAILS -->
                <div id="orderDetails" class="modal-body"></div>

                <!-- ACTIONS -->
                <div class="modal-actions">
                    <button onclick="updateOrderStatus('completed')" class="complete-btn">
                        Mark as Completed
                    </button>
                    <button onclick="updateOrderStatus('canceled')" class="cancel-btn">
                        Cancel Order
                    </button>
                </div>

            </div>
        </div>

    </body>
</html>

<!-- PUT JAVASCRIPT CODES HERE -->

<script>
    new Chart(document.getElementById('ordersChart'), {
        type: 'bar',
        data: {
            labels: <?= json_encode($orderDates); ?>,
            datasets: [{
                label: 'Orders',
                data: <?= json_encode($orderTotals); ?>,
                backgroundColor: '#F9A11D',
                borderRadius: 6,
                hoverBackgroundColor: '#ffb347'
            }]
        },
        options: {

            interaction: {
                mode: 'index',
                intersect: false
            },

            plugins: {
                legend: {
                    labels: {
                        color: '#B27212',
                        font: {
                            family: 'Poppins, sans-serif',
                            size: 12
                        }
                    }
                },

                tooltip: {
                    backgroundColor: '#2f2f2f',
                    titleColor: '#ffffff',
                    bodyColor: '#ffffff',

                    borderColor: '#F9A11D',
                    borderWidth: 2,

                    cornerRadius: 8,
                    padding: 12,
                    displayColors: true,
                    caretSize: 6,

                    titleFont: {
                        size: 13,
                        weight: '600',
                        family: 'Poppins'
                    },

                    bodyFont: {
                        size: 12,
                        family: 'Poppins'
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

    let currentOrderID = null;

    function openOrderModal(orderId) {
        currentOrderID = orderId;

        fetch("get_order_details.php?id=" + orderId)
        .then(res => res.json())
        .then(data => {
            if (data.error) {
                alert(data.error);
                return;
            }

            // Fill modal header
            document.getElementById("modalOrderID").innerText = data.order_id;
            document.getElementById("modalCustomer").innerText = data.full_name;
            document.getElementById("modalDate").innerText = new Date(data.order_date).toLocaleDateString();
            document.getElementById("modalStatus").innerText = data.order_status;

            // Build items table (your existing code)
            let detailsHtml = `<table style="width:100%; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th style="text-align:left; padding:5px;">Product</th>
                        <th style="text-align:right; padding:5px;">Qty</th>
                        <th style="text-align:right; padding:5px;">Price</th>
                        <th style="text-align:right; padding:5px;">Subtotal</th>
                    </tr>
                </thead>
                <tbody>`;

            let totalAmount = 0;
            data.items.forEach(item => {
                const subtotal = item.quantity * item.price;
                totalAmount += subtotal;
                detailsHtml += `
                    <tr>
                        <td style="padding:5px;">${item.product_name ?? 'Menu Item #' + item.menu_item_id}</td>
                        <td style="padding:5px; text-align:right;">${item.quantity}</td>
                        <td style="padding:5px; text-align:right;">₱${parseFloat(item.price).toFixed(2)}</td>
                        <td style="padding:5px; text-align:right;">₱${subtotal.toFixed(2)}</td>
                    </tr>`;
            });

            detailsHtml += `</tbody></table>`;
            detailsHtml += `<p style="text-align:right; margin-top:10px;"><strong>Total: ₱${totalAmount.toFixed(2)}</strong></p>`;
            document.getElementById("orderDetails").innerHTML = detailsHtml;

            // Show modal
            const modal = document.getElementById("orderModal");
            modal.style.display = "flex";

            // Handle buttons
            const completeBtn = modal.querySelector(".complete-btn");
            const cancelBtn = modal.querySelector(".cancel-btn");

            if (data.order_status === 'completed' || data.order_status === 'canceled') {
                completeBtn.classList.add('disabled');
                cancelBtn.classList.add('disabled');
            } else {
                completeBtn.classList.remove('disabled');
                cancelBtn.classList.remove('disabled');
            }
        });
    }

    function closeOrderModal() {
        document.getElementById("orderModal").style.display = "none";
    }

    function updateOrderStatus(status) {

        fetch("update_order.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: `order_id=${currentOrderID}&status=${status}`
        })
        .then(res => res.text())
        .then(() => {
            location.reload();
        });
    }
</script>
