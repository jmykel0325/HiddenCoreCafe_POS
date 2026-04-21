<?php

include('includes/header.php');
include('../../config/dbcon.php');

$orderStatusColumnExists = false;
$statusCheckResult = mysqli_query($conn, "SHOW COLUMNS FROM orders LIKE 'order_status'");
if ($statusCheckResult && mysqli_num_rows($statusCheckResult) > 0) {
    $orderStatusColumnExists = true;
}
$nonCancelledWhere = $orderStatusColumnExists
    ? "WHERE LOWER(COALESCE(order_status, 'pending')) <> 'cancelled'"
    : "";
$nonCancelledWhereAlias = $orderStatusColumnExists
    ? "AND LOWER(COALESCE(o.order_status, 'pending')) <> 'cancelled'"
    : "";

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

$sessionRole = strtolower(trim((string)($_SESSION['loggedInUser']['role'] ?? '')));
$sessionPosition = strtolower(trim((string)($_SESSION['loggedInUser']['position'] ?? '')));
$isCashierView = ($sessionRole === 'staff' || $sessionRole === 'cashier' || $sessionPosition === 'cashier' || $sessionPosition === 'staff');

$todays_sales = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(total) as todays_sales FROM orders WHERE DATE(created_at) = CURDATE() " . ($orderStatusColumnExists ? "AND LOWER(COALESCE(order_status, 'pending')) <> 'cancelled'" : "")))['todays_sales'] ?? 0;
$order_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as order_count FROM orders $nonCancelledWhere"))['order_count'] ?? 0;
$total_items_sold = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT SUM(oi.quantity) as total_items
    FROM order_items oi
    INNER JOIN orders o ON o.id = oi.order_id
    WHERE 1=1 $nonCancelledWhereAlias
"))['total_items'] ?? 0;
$total_cashiers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total_cashiers FROM cashier_staff"))['total_cashiers'] ?? 0;
$total_categories = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total_categories FROM categories"))['total_categories'] ?? 0;
$total_products = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total_products FROM products"))['total_products'] ?? 0;

$daily_sales_result = mysqli_query($conn, "
    SELECT DATE(created_at) as sale_date, SUM(total) as daily_total
    FROM orders
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
      " . ($orderStatusColumnExists ? "AND LOWER(COALESCE(order_status, 'pending')) <> 'cancelled'" : "") . "
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
    " . ($orderStatusColumnExists ? "WHERE LOWER(COALESCE(order_status, 'pending')) <> 'cancelled'" : "") . "
    GROUP BY payment_mode
");
$payment_modes = [];
$payment_counts = [];
while ($row = mysqli_fetch_assoc($payment_mode_result)) {
    $payment_modes[] = ucfirst($row['payment_mode']);
    $payment_counts[] = (int) $row['mode_count'];
}

// Cashier daily sales report (today-only, sales-like orders)
$todayOrderFilter = "DATE(created_at) = CURDATE() AND (order_status = 'completed' OR order_status = 'paid' OR order_status = 'served')";
$todayOrderFilterAlias = "DATE(o.created_at) = CURDATE() AND (o.order_status = 'completed' OR o.order_status = 'paid' OR o.order_status = 'served')";
$todaySalesRow = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(total) AS total_sales, COUNT(*) AS total_orders FROM orders WHERE {$todayOrderFilter}"));
$todaySalesTotal = (float)($todaySalesRow['total_sales'] ?? 0);
$todayOrdersTotal = (int)($todaySalesRow['total_orders'] ?? 0);

$todayItemsRow = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT SUM(oi.quantity) AS total_items
    FROM order_items oi
    INNER JOIN orders o ON o.id = oi.order_id
    WHERE {$todayOrderFilterAlias}
"));
$todayItemsTotal = (int)($todayItemsRow['total_items'] ?? 0);

$todayModeResult = mysqli_query($conn, "
    SELECT payment_mode, SUM(total) AS mode_total
    FROM orders
    WHERE {$todayOrderFilter}
    GROUP BY payment_mode
");
$todayPaymentBreakdown = [];
if ($todayModeResult) {
    while ($mode = mysqli_fetch_assoc($todayModeResult)) {
        $key = trim((string)($mode['payment_mode'] ?? 'Unknown'));
        if ($key === '') {
            $key = 'Unknown';
        }
        $todayPaymentBreakdown[$key] = (float)($mode['mode_total'] ?? 0);
    }
}
$todayCashTotal = (float)($todayPaymentBreakdown['Cash'] ?? 0);
$todayGcashTotal = (float)($todayPaymentBreakdown['GCash'] ?? 0);

$todayTopProducts = [];
$todayTopProductsResult = mysqli_query($conn, "
    SELECT oi.product_name, SUM(oi.quantity) AS total_qty
    FROM order_items oi
    INNER JOIN orders o ON o.id = oi.order_id
    WHERE {$todayOrderFilterAlias}
    GROUP BY oi.product_name
    ORDER BY total_qty DESC
    LIMIT 3
");
if ($todayTopProductsResult) {
    while ($prod = mysqli_fetch_assoc($todayTopProductsResult)) {
        $todayTopProducts[] = $prod;
    }
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

<style>
    .hc-dashboard.hc-dashboard-ref {
        --dash-bg: #f7f2ec;
        --dash-surface: #ffffff;
        --dash-primary: #f4a06b;
        --dash-primary-deep: #e68b50;
        --dash-border: #e9e3dc;
        --dash-text: #3a342e;
        --dash-muted: #8a8178;
        max-width: 1540px;
        margin: 0 auto;
        padding-top: .35rem;
        color: var(--dash-text);
    }

    .hc-dashboard-ref .hc-dashboard-hero {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 1rem;
        align-items: center;
        padding: 1.5rem 1.6rem;
        border-radius: 30px;
        border: 1px solid var(--dash-border);
        background: linear-gradient(140deg, #ffffff 0%, #fff7ef 100%);
        box-shadow: 0 14px 28px rgba(58, 52, 46, 0.08);
    }

    .hc-dashboard-ref .hc-dashboard-kicker {
        color: var(--dash-primary-deep) !important;
        letter-spacing: .12em;
    }

    .hc-dashboard-ref .hc-dashboard-title {
        margin: 0;
        color: var(--dash-text) !important;
        font-size: clamp(1.7rem, 2.7vw, 2.3rem);
        font-weight: 900;
    }

    .hc-dashboard-ref .hc-dashboard-subtitle {
        margin-top: .45rem;
        color: var(--dash-muted) !important;
        max-width: 780px;
    }

    .hc-dashboard-ref .hc-dashboard-hero-badge {
        min-width: 220px;
        border-radius: 22px;
        border: 1px solid #2f3747;
        background: linear-gradient(160deg, #2f3747 0%, #242a37 100%);
        box-shadow: 0 14px 24px rgba(36, 42, 55, .28);
    }

    .hc-dashboard-ref .hc-dashboard-badge-label {
        color: #ffbf95 !important;
    }

    .hc-dashboard-ref .hc-dashboard-hero-badge strong {
        color: #f8f7f4 !important;
    }

    .hc-dashboard-ref .hc-dashboard-stats {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: .95rem;
        margin-top: 1rem;
    }

    .hc-dashboard-ref .hc-dashboard-stat {
        min-height: 168px;
        padding: 1.15rem !important;
        border-radius: 24px !important;
        border: 1px solid var(--dash-border) !important;
        background: #fff !important;
        box-shadow: 0 12px 22px rgba(58, 52, 46, .06) !important;
    }

    .hc-dashboard-ref .hc-dashboard-stat::after {
        width: 108px;
        height: 108px;
        right: -18px;
        bottom: -24px;
        background: rgba(244, 160, 107, .10);
    }

    .hc-dashboard-ref .hc-dashboard-stat.is-primary {
        background: linear-gradient(165deg, #2f3747 0%, #242a37 100%) !important;
        border-color: #2f3747 !important;
        box-shadow: 0 18px 30px rgba(36, 42, 55, .24) !important;
    }

    .hc-dashboard-ref .hc-dashboard-stat.is-primary .hc-dashboard-stat-icon {
        background: rgba(255, 255, 255, .14);
        color: #ffc39c;
    }

    .hc-dashboard-ref .hc-dashboard-stat.is-primary .hc-dashboard-value,
    .hc-dashboard-ref .hc-dashboard-stat.is-primary .hc-dashboard-meta {
        color: #f8f7f4 !important;
    }

    .hc-dashboard-ref .hc-dashboard-stat.is-primary .hc-dashboard-stat-chip {
        background: #fff3e8;
        color: #a35d2f !important;
        border: 1px solid #ffd7bd;
    }

    .hc-dashboard-ref .hc-dashboard-stat-icon {
        width: 42px;
        height: 42px;
        border-radius: 13px;
        background: #fff2e7;
        color: var(--dash-primary-deep);
    }

    .hc-dashboard-ref .hc-dashboard-stat-chip {
        min-height: 30px;
        border-radius: 999px;
        background: #fff4eb;
        color: #5b524a !important;
        border: 1px solid #f2d8c4;
        font-size: .76rem;
        font-weight: 700;
    }

    .hc-dashboard-ref .hc-dashboard-stat-chip,
    .hc-dashboard-ref .hc-dashboard-stat-chip * {
        color: inherit !important;
    }

    .hc-dashboard-ref .hc-dashboard-value {
        margin-top: 1rem;
        color: var(--dash-text) !important;
        font-size: 2rem;
        font-weight: 900;
    }

    .hc-dashboard-ref .hc-dashboard-meta {
        color: var(--dash-muted) !important;
        font-size: .9rem;
    }

    .hc-dashboard-ref .hc-dashboard-panels {
        display: grid;
        grid-template-columns: minmax(0, 1.65fr) minmax(320px, 1fr);
        gap: .95rem;
        margin-top: .1rem;
    }

    .hc-dashboard-ref .hc-dashboard-panel {
        border-radius: 24px !important;
        border: 1px solid var(--dash-border) !important;
        background: #fff !important;
        box-shadow: 0 12px 24px rgba(58, 52, 46, .06) !important;
        padding: 1.1rem !important;
    }

    .hc-dashboard-ref .hc-dashboard-panel-head {
        margin-bottom: .75rem;
        padding-bottom: .7rem;
        border-bottom: 1px solid #f0e9e2;
    }

    .hc-dashboard-ref .hc-dashboard-panel-title {
        color: var(--dash-text);
        font-size: 1.2rem;
        font-weight: 800;
    }

    .hc-dashboard-ref .hc-dashboard-panel-subtitle {
        color: var(--dash-muted) !important;
        margin-top: .2rem;
        font-size: .9rem;
    }

    .hc-dashboard-ref .hc-dashboard-chart-wrap {
        height: 340px;
    }

    .hc-dashboard-ref .hc-dashboard-chart-wrap-donut {
        height: 340px;
    }

    .hc-dashboard-ref .hc-cashier-report {
        margin-top: .15rem;
        display: grid;
        grid-template-columns: minmax(0, 1.6fr) minmax(320px, 1fr);
        gap: .95rem;
    }

    .hc-dashboard-ref .hc-cashier-card {
        border-radius: 24px;
        border: 1px solid var(--dash-border);
        background: #fff;
        box-shadow: 0 12px 24px rgba(58, 52, 46, .06);
        padding: 1.1rem;
    }

    .hc-dashboard-ref .hc-cashier-card h3 {
        margin: 0;
        font-size: 1.15rem;
        font-weight: 800;
        color: var(--dash-text);
    }

    .hc-dashboard-ref .hc-cashier-sub {
        margin: .25rem 0 0;
        color: var(--dash-muted);
        font-size: .9rem;
    }

    .hc-dashboard-ref .hc-cashier-grid {
        margin-top: .8rem;
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: .65rem;
    }

    .hc-dashboard-ref .hc-cashier-metric {
        border: 1px solid #f0e8df;
        background: #fffaf5;
        border-radius: 16px;
        padding: .75rem .8rem;
    }

    .hc-dashboard-ref .hc-cashier-metric-label {
        color: var(--dash-muted);
        font-size: .78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .04em;
    }

    .hc-dashboard-ref .hc-cashier-metric-value {
        margin-top: .38rem;
        color: var(--dash-text);
        font-size: 1.1rem;
        font-weight: 800;
    }

    .hc-dashboard-ref .hc-cashier-metric-value.is-cash {
        color: #1f8d4e;
    }

    .hc-dashboard-ref .hc-cashier-metric-value.is-gcash {
        color: #1f5ea8;
    }

    .hc-dashboard-ref .hc-mode-list {
        margin-top: .8rem;
        display: grid;
        gap: .42rem;
    }

    .hc-dashboard-ref .hc-mode-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border: 1px solid #f1e9e2;
        border-radius: 12px;
        padding: .5rem .65rem;
        background: #fff;
    }

    .hc-dashboard-ref .hc-mode-row span {
        color: var(--dash-muted);
        font-weight: 600;
    }

    .hc-dashboard-ref .hc-mode-row strong {
        color: var(--dash-text);
        font-weight: 800;
    }

    .hc-dashboard-ref .hc-cash-form {
        margin-top: .8rem;
        display: grid;
        gap: .65rem;
    }

    .hc-dashboard-ref .hc-cash-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: .8rem;
    }

    .hc-dashboard-ref .hc-cash-row span {
        color: var(--dash-muted);
        font-weight: 700;
    }

    .hc-dashboard-ref .hc-cash-row strong {
        color: var(--dash-text);
        font-weight: 900;
    }

    .hc-dashboard-ref .hc-cash-input {
        min-height: 46px;
        border-radius: 14px;
        border: 1px solid #eadfce;
        padding: .55rem .8rem;
        font-weight: 700;
    }

    .hc-dashboard-ref .hc-cash-status {
        display: inline-flex;
        align-items: center;
        min-height: 30px;
        padding: .35rem .7rem;
        border-radius: 999px;
        font-size: .78rem;
        font-weight: 800;
        width: max-content;
    }

    .hc-dashboard-ref .hc-cash-status.is-balanced {
        background: rgba(34, 197, 94, .16);
        color: #15803d;
    }

    .hc-dashboard-ref .hc-cash-status.is-shortage {
        background: rgba(239, 68, 68, .16);
        color: #b91c1c;
    }

    .hc-dashboard-ref .hc-cash-status.is-over {
        background: rgba(244, 160, 107, .20);
        color: #a85f31;
    }

    .hc-dashboard-ref .hc-perf-list {
        margin-top: .75rem;
        display: grid;
        gap: .45rem;
    }

    .hc-dashboard-ref .hc-perf-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: .8rem;
        border: 1px solid #f0e8df;
        border-radius: 12px;
        padding: .5rem .65rem;
        background: #fffdfb;
    }

    .hc-dashboard-ref .hc-perf-name {
        color: var(--dash-text);
        font-weight: 700;
    }

    .hc-dashboard-ref .hc-perf-qty {
        color: #a85f31;
        font-weight: 800;
        white-space: nowrap;
    }

    .hc-dashboard-ref .hc-shortage-alert {
        margin-top: .65rem;
        display: none;
        align-items: center;
        gap: .35rem;
        padding: .45rem .6rem;
        border-radius: 12px;
        background: rgba(239, 68, 68, .12);
        color: #b91c1c;
        font-weight: 700;
        font-size: .82rem;
    }

    .hc-dashboard-ref .hc-shortage-alert.is-visible {
        display: inline-flex;
    }

    @media (max-width: 1199px) {
        .hc-dashboard-ref .hc-dashboard-stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .hc-dashboard-ref .hc-dashboard-panels {
            grid-template-columns: 1fr;
        }
        .hc-dashboard-ref .hc-cashier-report {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 767px) {
        .hc-dashboard-ref .hc-dashboard-hero {
            grid-template-columns: 1fr;
            border-radius: 24px;
            padding: 1.15rem;
        }
        .hc-dashboard-ref .hc-dashboard-stats {
            grid-template-columns: 1fr;
        }
        .hc-dashboard-ref .hc-dashboard-stat,
        .hc-dashboard-ref .hc-dashboard-panel {
            border-radius: 20px !important;
        }
        .hc-dashboard-ref .hc-dashboard-chart-wrap,
        .hc-dashboard-ref .hc-dashboard-chart-wrap-donut {
            height: 300px;
        }
        .hc-dashboard-ref .hc-cashier-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="container-fluid px-4 hc-dashboard hc-dashboard-ref">
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

    <?php if ($isCashierView): ?>
    <section class="hc-cashier-report">
        <article class="hc-cashier-card">
            <h3>Daily Sales Summary</h3>
            <p class="hc-cashier-sub">Today only, based on completed sales.</p>

            <div class="hc-cashier-grid">
                <div class="hc-cashier-metric">
                    <div class="hc-cashier-metric-label">Total Sales</div>
                    <div class="hc-cashier-metric-value">PHP <?= number_format($todaySalesTotal, 2) ?></div>
                </div>
                <div class="hc-cashier-metric">
                    <div class="hc-cashier-metric-label">Orders</div>
                    <div class="hc-cashier-metric-value"><?= number_format($todayOrdersTotal) ?></div>
                </div>
                <div class="hc-cashier-metric">
                    <div class="hc-cashier-metric-label">Items Sold</div>
                    <div class="hc-cashier-metric-value"><?= number_format($todayItemsTotal) ?></div>
                </div>
                <div class="hc-cashier-metric">
                    <div class="hc-cashier-metric-label">Cash Received</div>
                    <div class="hc-cashier-metric-value is-cash">PHP <?= number_format($todayCashTotal, 2) ?></div>
                </div>
                <div class="hc-cashier-metric">
                    <div class="hc-cashier-metric-label">GCash Received</div>
                    <div class="hc-cashier-metric-value is-gcash">PHP <?= number_format($todayGcashTotal, 2) ?></div>
                </div>
                <div class="hc-cashier-metric">
                    <div class="hc-cashier-metric-label">Inventory Deductions</div>
                    <div class="hc-cashier-metric-value"><?= number_format($todayItemsTotal) ?> items</div>
                </div>
            </div>

            <div class="hc-mode-list">
                <?php if (!empty($todayPaymentBreakdown)): ?>
                    <?php foreach ($todayPaymentBreakdown as $modeName => $modeTotal): ?>
                        <div class="hc-mode-row">
                            <span><?= htmlspecialchars($modeName) ?></span>
                            <strong>PHP <?= number_format((float)$modeTotal, 2) ?></strong>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="hc-mode-row">
                        <span>No payment records yet today.</span>
                        <strong>PHP 0.00</strong>
                    </div>
                <?php endif; ?>
            </div>
        </article>

        <article class="hc-cashier-card">
            <h3>Cash Accountability</h3>
            <p class="hc-cashier-sub">Compare expected cash vs actual cash counted.</p>

            <div class="hc-cash-form">
                <div class="hc-cash-row">
                    <span>Expected Cash on Hand</span>
                    <strong>PHP <span id="expectedCash"><?= number_format($todayCashTotal, 2) ?></span></strong>
                </div>

                <label for="actualCashCount" class="hc-cashier-sub">Actual Cash Counted</label>
                <input type="number" min="0" step="0.01" id="actualCashCount" class="hc-cash-input" placeholder="Enter counted cash amount">

                <div class="hc-cash-row">
                    <span>Difference</span>
                    <strong>PHP <span id="cashDiff">0.00</span></strong>
                </div>

                <div id="cashStatusBadge" class="hc-cash-status is-balanced">Balanced</div>
                <div id="shortageAlert" class="hc-shortage-alert">
                    <i class="fas fa-triangle-exclamation"></i>
                    Shortage detected. Recount before end-of-day turnover.
                </div>
            </div>

            <h3 style="margin-top:1rem;">Today's Performance</h3>
            <p class="hc-cashier-sub">Top 3 sold products today.</p>
            <div class="hc-perf-list">
                <?php if (!empty($todayTopProducts)): ?>
                    <?php foreach ($todayTopProducts as $topProd): ?>
                        <div class="hc-perf-row">
                            <div class="hc-perf-name"><?= htmlspecialchars((string)($topProd['product_name'] ?? 'Unknown Product')) ?></div>
                            <div class="hc-perf-qty"><?= (int)($topProd['total_qty'] ?? 0) ?> sold</div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="hc-perf-row">
                        <div class="hc-perf-name">No sold products yet today.</div>
                        <div class="hc-perf-qty">0 sold</div>
                    </div>
                <?php endif; ?>
            </div>
        </article>
    </section>
    <?php endif; ?>
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
                        color: '#8a8178',
                        font: {
                            weight: 600
                        }
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(138, 129, 120, 0.14)'
                    },
                    border: {
                        display: false
                    },
                    ticks: {
                        color: '#8a8178',
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
                        color: '#8a8178',
                        font: {
                            size: 12,
                            weight: 600
                        }
                    }
                }
            }
        }
    });

    <?php if ($isCashierView): ?>
    (function() {
        const expectedCash = <?= json_encode((float)$todayCashTotal) ?>;
        const actualInput = document.getElementById('actualCashCount');
        const diffEl = document.getElementById('cashDiff');
        const statusBadge = document.getElementById('cashStatusBadge');
        const shortageAlert = document.getElementById('shortageAlert');

        if (!actualInput || !diffEl || !statusBadge || !shortageAlert) return;

        function updateCashStatus() {
            const actual = parseFloat(actualInput.value || '0');
            const diff = Number.isFinite(actual) ? (actual - expectedCash) : -expectedCash;
            diffEl.textContent = diff.toFixed(2);

            statusBadge.classList.remove('is-balanced', 'is-shortage', 'is-over');
            shortageAlert.classList.remove('is-visible');

            if (Math.abs(diff) < 0.005) {
                statusBadge.classList.add('is-balanced');
                statusBadge.textContent = 'Balanced';
            } else if (diff < 0) {
                statusBadge.classList.add('is-shortage');
                statusBadge.textContent = 'Shortage';
                shortageAlert.classList.add('is-visible');
            } else {
                statusBadge.classList.add('is-over');
                statusBadge.textContent = 'Over';
            }
        }

        actualInput.addEventListener('input', updateCashStatus);
        updateCashStatus();
    })();
    <?php endif; ?>
</script>

<?php include('includes/footer.php'); ?>
