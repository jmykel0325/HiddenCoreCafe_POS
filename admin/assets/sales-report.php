<?php


include('includes/header.php');
include('../../config/dbcon.php');
?>

<div class="container-fluid px-2 px-md-4">
    <div class="card mt-4 shadow-sm">
        <div class="card-header">
            <h4 class="mb-0"><i class="fas fa-chart-line"></i> Sales Report</h4>
        </div>
        <div class="card-body">

<?php
// Total Sales Amount
$total_sales_query = "SELECT SUM(total) as total_sales FROM orders";
$total_sales_result = mysqli_query($conn, $total_sales_query);
$total_sales = mysqli_fetch_assoc($total_sales_result)['total_sales'] ?? 0;

// Number of Orders
$order_count_query = "SELECT COUNT(*) as order_count FROM orders";
$order_count_result = mysqli_query($conn, $order_count_query);
$order_count = mysqli_fetch_assoc($order_count_result)['order_count'] ?? 0;

// Total Items Sold
$items_sold_query = "SELECT SUM(quantity) as total_items FROM order_items";
$items_sold_result = mysqli_query($conn, $items_sold_query);
$total_items_sold = mysqli_fetch_assoc($items_sold_result)['total_items'] ?? 0;

// Sales by Payment Mode
$payment_modes_query = "SELECT payment_mode, SUM(total) as mode_total FROM orders GROUP BY payment_mode";
$payment_modes_result = mysqli_query($conn, $payment_modes_query);

// Daily Sales for Chart (last 7 days)
$daily_sales_query = "
    SELECT DATE(created_at) as sale_date, SUM(total) as daily_total 
    FROM orders 
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
    GROUP BY sale_date
    ORDER BY sale_date ASC
";
$daily_sales_result = mysqli_query($conn, $daily_sales_query);

$daily_dates = [];
$daily_totals = [];

while($row = mysqli_fetch_assoc($daily_sales_result)){
    $daily_dates[] = date('M j', strtotime($row['sale_date']));
    $daily_totals[] = $row['daily_total'];
}

// Today's Sales
$today_sales_query = "
    SELECT SUM(total) as today_sales 
    FROM orders 
    WHERE DATE(created_at) = CURDATE()
";
$today_sales_result = mysqli_query($conn, $today_sales_query);
$today_sales = mysqli_fetch_assoc($today_sales_result)['today_sales'] ?? 0;

// Today's Items Sold
$today_items_query = "
    SELECT SUM(oi.quantity) as today_items
    FROM order_items oi
    JOIN orders o ON oi.order_id = o.id
    WHERE DATE(o.created_at) = CURDATE()
";
$today_items_result = mysqli_query($conn, $today_items_query);
$today_items = mysqli_fetch_assoc($today_items_result)['today_items'] ?? 0;

// Determine visibility based on number of distinct sale days within current week and current month
$weekly_sale_days_row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(DISTINCT DATE(created_at)) AS weekly_sale_days FROM orders WHERE YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1)"));
$monthly_sale_days_row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(DISTINCT DATE(created_at)) AS monthly_sale_days FROM orders WHERE YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE())"));
$weekly_sale_days = (int)($weekly_sale_days_row['weekly_sale_days'] ?? 0);
$monthly_sale_days = (int)($monthly_sale_days_row['monthly_sale_days'] ?? 0);
$show_weekly = $weekly_sale_days >= 7;
$show_monthly = $monthly_sale_days >= 30;

// Weekly Sales (current week: Monday to today)
$weekly_sales_query = "
    SELECT SUM(total) as weekly_sales 
    FROM orders 
    WHERE YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1)
";
$weekly_sales_result = mysqli_query($conn, $weekly_sales_query);
$weekly_sales = mysqli_fetch_assoc($weekly_sales_result)['weekly_sales'] ?? 0;

// Weekly Items Sold
$weekly_items_query = "
    SELECT SUM(oi.quantity) as weekly_items
    FROM order_items oi
    JOIN orders o ON oi.order_id = o.id
    WHERE YEARWEEK(o.created_at, 1) = YEARWEEK(CURDATE(), 1)
";
$weekly_items_result = mysqli_query($conn, $weekly_items_query);
$weekly_items = mysqli_fetch_assoc($weekly_items_result)['weekly_items'] ?? 0;

// Enforce minimum elapsed time for weekly metrics
if (!$show_weekly) {
    $weekly_sales = 0;
    $weekly_items = 0;
}

// Monthly Sales (current month)
$monthly_sales_query = "
    SELECT SUM(total) as monthly_sales 
    FROM orders 
    WHERE YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE())
";
$monthly_sales_result = mysqli_query($conn, $monthly_sales_query);
$monthly_sales = mysqli_fetch_assoc($monthly_sales_result)['monthly_sales'] ?? 0;

// Monthly Items Sold
$monthly_items_query = "
    SELECT SUM(oi.quantity) as monthly_items
    FROM order_items oi
    JOIN orders o ON oi.order_id = o.id
    WHERE YEAR(o.created_at) = YEAR(CURDATE()) AND MONTH(o.created_at) = MONTH(CURDATE())
";
$monthly_items_result = mysqli_query($conn, $monthly_items_query);
$monthly_items = mysqli_fetch_assoc($monthly_items_result)['monthly_items'] ?? 0;

// Enforce minimum elapsed time for monthly metrics
if (!$show_monthly) {
    $monthly_sales = 0;
    $monthly_items = 0;
}

// Top Selling Items (Ranking) - group by product_name
$top_items_query = "
    SELECT product_name, SUM(quantity) as total_sold
    FROM order_items
    GROUP BY product_name
    ORDER BY total_sold DESC
    LIMIT 5
";
$top_items_result = mysqli_query($conn, $top_items_query);

// Weekly Sales Chart Data (last 7 weeks)
$weekly_sales_chart_query = "
    SELECT YEAR(created_at) as yr, WEEK(created_at, 1) as wk, 
           CONCAT('Week ', WEEK(created_at, 1)) as week_label, 
           SUM(total) as week_total
    FROM orders
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 WEEK)
    GROUP BY yr, wk
    ORDER BY yr, wk ASC
";
$weekly_sales_chart_result = mysqli_query($conn, $weekly_sales_chart_query);
$weekly_labels = [];
$weekly_totals = [];
while($row = mysqli_fetch_assoc($weekly_sales_chart_result)){
    $weekly_labels[] = $row['week_label'];
    $weekly_totals[] = $row['week_total'];
}

// Monthly Sales Chart Data (current year)
$monthly_sales_chart_query = "
    SELECT DATE_FORMAT(created_at, '%b') as month_label, 
           SUM(total) as month_total
    FROM orders
    WHERE YEAR(created_at) = YEAR(CURDATE())
    GROUP BY MONTH(created_at)
    ORDER BY MONTH(created_at)
";
$monthly_sales_chart_result = mysqli_query($conn, $monthly_sales_chart_query);
$monthly_labels = [];
$monthly_totals = [];
while($row = mysqli_fetch_assoc($monthly_sales_chart_result)){
    $monthly_labels[] = $row['month_label'];
    $monthly_totals[] = $row['month_total'];
}
?>

        <div class="row">
            <div class="col-12 col-md-4 mb-3">
                <div class="report-card text-center">
                    <div class="report-title">Total Sales</div>
                    <div class="report-value">₱<?= number_format($total_sales, 2) ?></div>
                </div>
            </div>
            <div class="col-12 col-md-4 mb-3">
                <div class="report-card text-center">
                    <div class="report-title">Number of Orders</div>
                    <div class="report-value"><?= $order_count ?></div>
                </div>
            </div>
            <div class="col-12 col-md-4 mb-3">
                <div class="report-card text-center">
                    <div class="report-title">Total Items Sold</div>
                    <div class="report-value"><?= $total_items_sold ?></div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12 col-md-4 mb-3">
                <div class="report-card text-center">
                    <div class="report-title">Today's Sales</div>
                    <div class="report-value">₱<?= number_format($today_sales, 2) ?></div>
                    <div style="font-size:1rem;color:#555555;">Items Sold: <?= $today_items ?? 0 ?></div>
                </div>
            </div>
            <div class="col-12 col-md-4 mb-3">
                <div class="report-card text-center">
                    <div class="report-title">Weekly Sales</div>
                    <div class="report-value">₱<?= number_format($weekly_sales, 2) ?></div>
                    <div style="font-size:1rem;color:#555555;">Items Sold: <?= $weekly_items ?? 0 ?></div>
                </div>
            </div>
            <div class="col-12 col-md-4 mb-3">
                <div class="report-card text-center">
                    <div class="report-title">Monthly Sales</div>
                    <div class="report-value">₱<?= number_format($monthly_sales, 2) ?></div>
                    <div style="font-size:1rem;color:#555555;">Items Sold: <?= $monthly_items ?? 0 ?></div>
                </div>
            </div>
        </div>

        <div class="mt-4">
            <h5 class="mb-3">Sales by Payment Method</h5>
            <ul class="list-group">
                <?php if(mysqli_num_rows($payment_modes_result) > 0): ?>
                    <?php while($row = mysqli_fetch_assoc($payment_modes_result)): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <?= ucfirst($row['payment_mode']) ?>
                            <span>₱<?= number_format($row['mode_total'], 2) ?></span>
                        </li>
                    <?php endwhile; ?>
                <?php else: ?>
                    <li class="list-group-item">No Sales Found</li>
                <?php endif; ?>
            </ul>
        </div>

        <div class="mt-4">
            <h5 class="mb-3">Top 5 Best-Selling Items</h5>
            <ul class="list-group">
                <?php if(mysqli_num_rows($top_items_result) > 0): $rank = 1; ?>
                    <?php while($row = mysqli_fetch_assoc($top_items_result)): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>
                                <strong>#<?= $rank++; ?></strong> <?= htmlspecialchars($row['product_name']) ?>
                            </span>
                            <span><?= $row['total_sold'] ?> sold</span>
                        </li>
                    <?php endwhile; ?>
                <?php else: ?>
                    <li class="list-group-item">No items sold yet.</li>
                <?php endif; ?>
            </ul>
        </div>

        <div class="mt-5">
            <h5 class="mb-3">Daily Sales (Last 7 Days)</h5>
            <div style="width:100%;overflow-x:auto;">
                <canvas id="dailySalesChart"></canvas>
            </div>
        </div>

        <?php if ($show_weekly): ?>
        <div class="mt-5">
            <h5 class="mb-3">Weekly Sales (Last 7 Weeks)</h5>
            <div style="width:100%;overflow-x:auto;">
                <canvas id="weeklySalesChart"></canvas>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($show_monthly): ?>
        <div class="mt-5">
            <h5 class="mb-3">Monthly Sales (<?= date('Y') ?>)</h5>
            <div style="width:100%;overflow-x:auto;">
                <canvas id="monthlySalesChart"></canvas>
            </div>
        </div>
        <?php endif; ?>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Daily Sales Chart
    const dailyCanvas = document.getElementById('dailySalesChart');
    if (dailyCanvas) {
    const ctx = dailyCanvas.getContext('2d');
    const dailySalesChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?= json_encode($daily_dates) ?>,
            datasets: [{
                label: '₱ Sales',
                data: <?= json_encode($daily_totals) ?>,
                backgroundColor: '#000000',
                borderRadius: 5,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '₱' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });
    }

    // Weekly Sales Chart
    const weeklyCanvas = document.getElementById('weeklySalesChart');
    if (weeklyCanvas) {
    const ctxWeekly = weeklyCanvas.getContext('2d');
    const weeklySalesChart = new Chart(ctxWeekly, {
        type: 'bar',
        data: {
            labels: <?= json_encode($weekly_labels) ?>,
            datasets: [{
                label: '₱ Sales',
                data: <?= json_encode($weekly_totals) ?>,
                backgroundColor: '#1A1A1A',
                borderRadius: 5,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '₱' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });

    // Monthly Sales Chart
    const monthlyCanvas = document.getElementById('monthlySalesChart');
    if (monthlyCanvas) {
    const ctxMonthly = monthlyCanvas.getContext('2d');
    const monthlySalesChart = new Chart(ctxMonthly, {
        type: 'bar',
        data: {
            labels: <?= json_encode($monthly_labels) ?>,
            datasets: [{
                label: '₱ Sales',
                data: <?= json_encode($monthly_totals) ?>,
                backgroundColor: '#7b5e57',
                borderRadius: 5,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '₱' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });
</script>

<?php include('includes/footer.php'); ?>