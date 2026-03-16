<?php

include('includes/header.php');
include('../../config/dbcon.php');

$currentAdminName = $_SESSION['loggedInUser']['username'] ?? 'Admin';
$currentUserId = (int)($_SESSION['loggedInUser']['user_id'] ?? 0);
if ($currentUserId > 0) {
    $userResult = mysqli_query($conn, "SELECT first_name, last_name, username FROM cashier_staff WHERE id = {$currentUserId} LIMIT 1");
    if ($userResult && mysqli_num_rows($userResult) === 1) {
        $userRow = mysqli_fetch_assoc($userResult);
        $fullName = trim(($userRow['first_name'] ?? '') . ' ' . ($userRow['last_name'] ?? ''));
        if ($fullName !== '') {
            $currentAdminName = $fullName;
        } elseif (!empty($userRow['username'])) {
            $currentAdminName = $userRow['username'];
        }
    }
}
$currentHour = (int) date('G');
$greeting = 'Good evening';
if ($currentHour < 12) {
    $greeting = 'Good morning';
} elseif ($currentHour < 18) {
    $greeting = 'Good afternoon';
}

$todays_sales = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(total) as todays_sales FROM orders WHERE DATE(created_at) = CURDATE()"))['todays_sales'] ?? 0;
$order_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as order_count FROM orders"))['order_count'] ?? 0;
$total_items_sold = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(quantity) as total_items FROM order_items"))['total_items'] ?? 0;
$total_cashiers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total_cashiers FROM cashier_staff"))['total_cashiers'] ?? 0;
$total_categories = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total_categories FROM categories"))['total_categories'] ?? 0;
$total_products = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total_products FROM products"))['total_products'] ?? 0;

$daily_sales_result = mysqli_query($conn, "
    SELECT DATE(created_at) as sale_date, SUM(total) as daily_total
    FROM orders
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
    GROUP BY sale_date
    ORDER BY sale_date ASC
");
$daily_dates = [];
$daily_totals = [];
while ($row = mysqli_fetch_assoc($daily_sales_result)) {
    $daily_dates[] = date('M j', strtotime($row['sale_date']));
    $daily_totals[] = (float) $row['daily_total'];
}

$payment_mode_result = mysqli_query($conn, "
    SELECT payment_mode, COUNT(*) as mode_count
    FROM orders
    GROUP BY payment_mode
");
$payment_modes = [];
$payment_counts = [];
while ($row = mysqli_fetch_assoc($payment_mode_result)) {
    $payment_modes[] = ucfirst($row['payment_mode']);
    $payment_counts[] = (int) $row['mode_count'];
}

$stats = [
    [
        'label' => "Today's Sales",
        'value' => 'PHP ' . number_format((float) $todays_sales, 2),
        'meta' => 'Revenue recorded today',
        'icon' => 'fas fa-wallet',
        'accent' => 'is-primary',
    ],
    [
        'label' => 'Orders',
        'value' => number_format((int) $order_count),
        'meta' => 'Total orders processed',
        'icon' => 'fas fa-receipt',
        'accent' => 'is-neutral',
    ],
    [
        'label' => 'Items Sold',
        'value' => number_format((int) $total_items_sold),
        'meta' => 'Products moved so far',
        'icon' => 'fas fa-cubes',
        'accent' => 'is-neutral',
    ],
    [
        'label' => 'Cashier/Staff',
        'value' => number_format((int) $total_cashiers),
        'meta' => 'Active team records',
        'icon' => 'fas fa-users',
        'accent' => 'is-neutral',
    ],
    [
        'label' => 'Categories',
        'value' => number_format((int) $total_categories),
        'meta' => 'Menu groupings available',
        'icon' => 'fas fa-tags',
        'accent' => 'is-soft',
    ],
    [
        'label' => 'Products',
        'value' => number_format((int) $total_products),
        'meta' => 'Products in inventory',
        'icon' => 'fas fa-mug-hot',
        'accent' => 'is-soft',
    ],
];
?>

<div class="container-fluid px-4 hc-dashboard">
    <section class="hc-dashboard-hero">
        <div class="hc-dashboard-hero-copy">
            <p class="hc-dashboard-kicker">Operations Overview</p>
            <h1 class="hc-dashboard-title"><?= htmlspecialchars($greeting) ?>, <?= htmlspecialchars($currentAdminName) ?></h1>
            <p class="hc-dashboard-subtitle">A quick look at sales, orders, and store activity for <?= htmlspecialchars(date('l, F j, Y')) ?>.</p>
        </div>
        <div class="hc-dashboard-hero-badge">
            <span class="hc-dashboard-badge-label">Live Store Status</span>
            <strong>Open and tracking</strong>
        </div>
    </section>

    <section class="hc-dashboard-stats">
        <?php foreach ($stats as $stat): ?>
            <article class="dashboard-card hc-dashboard-stat <?= $stat['accent'] ?>">
                <div class="hc-dashboard-stat-top">
                    <div class="hc-dashboard-stat-icon">
                        <i class="<?= htmlspecialchars($stat['icon']) ?>"></i>
                    </div>
                    <span class="hc-dashboard-stat-chip"><?= htmlspecialchars($stat['label']) ?></span>
                </div>
                <div class="dashboard-value hc-dashboard-value"><?= htmlspecialchars($stat['value']) ?></div>
                <div class="dashboard-title hc-dashboard-meta"><?= htmlspecialchars($stat['meta']) ?></div>
            </article>
        <?php endforeach; ?>
    </section>

    <section class="hc-dashboard-panels">
        <article class="report-card hc-dashboard-panel hc-dashboard-panel-wide">
            <div class="hc-dashboard-panel-head">
                <div>
                    <h2 class="hc-dashboard-panel-title">Daily Sales</h2>
                    <p class="hc-dashboard-panel-subtitle">Last 7 days revenue trend</p>
                </div>
            </div>
            <div class="hc-dashboard-chart-wrap">
                <canvas id="dailySalesChart"></canvas>
            </div>
        </article>

        <article class="report-card hc-dashboard-panel">
            <div class="hc-dashboard-panel-head">
                <div>
                    <h2 class="hc-dashboard-panel-title">Payment Modes</h2>
                    <p class="hc-dashboard-panel-subtitle">Breakdown of order payments</p>
                </div>
            </div>
            <div class="hc-dashboard-chart-wrap hc-dashboard-chart-wrap-donut">
                <canvas id="paymentModeChart"></canvas>
            </div>
        </article>
    </section>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    new Chart(document.getElementById('dailySalesChart').getContext('2d'), {
        type: 'bar',
        data: {
            labels: <?= json_encode($daily_dates) ?>,
            datasets: [{
                label: 'PHP Sales',
                data: <?= json_encode($daily_totals) ?>,
                backgroundColor: ['#ff7a1a', '#ff8c33', '#ffa24f', '#ffb971', '#ffd4a5', '#ffb971', '#ff8c33'],
                borderRadius: 14,
                borderSkipped: false,
                maxBarThickness: 46
            }]
        },
        options: {
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: '#64748b',
                        font: {
                            weight: 600
                        }
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(148, 163, 184, 0.16)'
                    },
                    border: {
                        display: false
                    },
                    ticks: {
                        color: '#64748b',
                        callback: function(value) {
                            return 'PHP ' + value;
                        }
                    }
                }
            }
        }
    });

    new Chart(document.getElementById('paymentModeChart').getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: <?= json_encode($payment_modes) ?>,
            datasets: [{
                data: <?= json_encode($payment_counts) ?>,
                backgroundColor: ['#ff7a1a', '#ff9d4d', '#ffc58f', '#1f2937'],
                borderWidth: 0,
                hoverOffset: 8
            }]
        },
        options: {
            maintainAspectRatio: false,
            cutout: '72%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        pointStyle: 'circle',
                        padding: 18,
                        color: '#475569',
                        font: {
                            size: 12,
                            weight: 600
                        }
                    }
                }
            }
        }
    });
</script>

<?php include('includes/footer.php'); ?>
