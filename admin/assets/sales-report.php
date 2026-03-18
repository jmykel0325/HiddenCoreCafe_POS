<?php
include('includes/header.php');
include('../../config/dbcon.php');
?>

<style>
.hc-sales-report{
    display:grid;
    gap:18px;
}
.hc-sales-hero{
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    gap:16px;
    padding:20px 22px;
    background:var(--hc-surface);
    border:1px solid var(--hc-border);
    border-radius:var(--hc-radius);
    box-shadow:var(--hc-shadow-sm);
}
.hc-sales-hero-title{
    margin:0;
    font-size:2rem;
    line-height:1.05;
    font-weight:800;
    color:var(--hc-text);
}
.hc-sales-hero-sub{
    margin:8px 0 0;
    color:var(--hc-muted);
    font-size:1rem;
}
.hc-sales-chip{
    display:inline-flex;
    align-items:center;
    gap:8px;
    padding:8px 12px;
    border-radius:999px;
    font-weight:700;
    background:var(--hc-primary-50);
    color:var(--hc-primary-600);
    white-space:nowrap;
}
.hc-sales-grid{
    display:grid;
    gap:14px;
    grid-template-columns:repeat(3,minmax(0,1fr));
}
.hc-sales-metric{
    background:var(--hc-surface);
    border:1px solid var(--hc-border);
    border-radius:var(--hc-radius);
    box-shadow:var(--hc-shadow-sm);
    padding:16px 18px;
}
.hc-sales-metric-kicker{
    color:var(--hc-muted);
    font-size:.8rem;
    text-transform:uppercase;
    letter-spacing:.05em;
    font-weight:700;
}
.hc-sales-metric-value{
    margin-top:8px;
    font-size:2rem;
    line-height:1;
    font-weight:800;
    color:var(--hc-text);
}
.hc-sales-metric-sub{
    margin-top:8px;
    color:var(--hc-muted);
    font-weight:600;
}
.hc-sales-sections{
    display:grid;
    gap:14px;
    grid-template-columns:repeat(2,minmax(0,1fr));
}
.hc-sales-panel{
    background:var(--hc-surface);
    border:1px solid var(--hc-border);
    border-radius:var(--hc-radius);
    box-shadow:var(--hc-shadow-sm);
    padding:14px 16px;
}
.hc-sales-panel h5{
    margin:2px 0 12px;
    font-weight:800;
}
.hc-sales-panel .list-group-item{
    border-left:0;
    border-right:0;
    padding:.7rem .1rem;
}
.hc-sales-panel .list-group-item:first-child{border-top:0;}
.hc-sales-panel .list-group-item:last-child{border-bottom:0;}
.hc-sales-panel .metric-name{
    color:var(--hc-text);
    font-weight:600;
}
.hc-sales-panel .metric-value{
    color:var(--hc-primary-600);
    font-weight:800;
}
.hc-sales-chart{
    background:var(--hc-surface);
    border:1px solid var(--hc-border);
    border-radius:var(--hc-radius);
    box-shadow:var(--hc-shadow-sm);
    padding:14px 16px;
}
.hc-sales-chart h5{
    margin:2px 0 12px;
    font-weight:800;
}
.hc-sales-chart-wrap{
    width:100%;
    height:320px;
}
@media (max-width: 1199px){
    .hc-sales-grid{grid-template-columns:repeat(2,minmax(0,1fr));}
}
@media (max-width: 991px){
    .hc-sales-sections{grid-template-columns:1fr;}
}
@media (max-width: 767px){
    .hc-sales-hero{flex-direction:column;}
    .hc-sales-hero-title{font-size:1.6rem;}
    .hc-sales-grid{grid-template-columns:1fr;}
    .hc-sales-chart-wrap{height:260px;}
}
</style>

<div class="container-fluid px-2 px-md-4">

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

<div class="hc-sales-report mt-4">
    <section class="hc-sales-hero">
        <div>
            <h1 class="hc-sales-hero-title">Sales Report</h1>
            <p class="hc-sales-hero-sub">Track performance across revenue, orders, items, payment mode, and top products.</p>
        </div>
        <div class="hc-sales-chip"><i class="fas fa-calendar-day"></i> <?= date('l, F j, Y') ?></div>
    </section>

    <section class="hc-sales-grid">
        <article class="hc-sales-metric">
            <div class="hc-sales-metric-kicker">Total Sales</div>
            <div class="hc-sales-metric-value">&#8369;<?= number_format($total_sales, 2) ?></div>
        </article>
        <article class="hc-sales-metric">
            <div class="hc-sales-metric-kicker">Orders Processed</div>
            <div class="hc-sales-metric-value"><?= $order_count ?></div>
        </article>
        <article class="hc-sales-metric">
            <div class="hc-sales-metric-kicker">Items Sold</div>
            <div class="hc-sales-metric-value"><?= $total_items_sold ?></div>
        </article>
        <article class="hc-sales-metric">
            <div class="hc-sales-metric-kicker">Today</div>
            <div class="hc-sales-metric-value">&#8369;<?= number_format($today_sales, 2) ?></div>
            <div class="hc-sales-metric-sub">Items Sold: <?= $today_items ?? 0 ?></div>
        </article>
        <article class="hc-sales-metric">
            <div class="hc-sales-metric-kicker">This Week</div>
            <div class="hc-sales-metric-value">&#8369;<?= number_format($weekly_sales, 2) ?></div>
            <div class="hc-sales-metric-sub">Items Sold: <?= $weekly_items ?? 0 ?></div>
        </article>
        <article class="hc-sales-metric">
            <div class="hc-sales-metric-kicker">This Month</div>
            <div class="hc-sales-metric-value">&#8369;<?= number_format($monthly_sales, 2) ?></div>
            <div class="hc-sales-metric-sub">Items Sold: <?= $monthly_items ?? 0 ?></div>
        </article>
    </section>

    <section class="hc-sales-sections">
        <article class="hc-sales-panel">
            <h5>Sales by Payment Method</h5>
            <ul class="list-group">
                <?php if(mysqli_num_rows($payment_modes_result) > 0): ?>
                    <?php while($row = mysqli_fetch_assoc($payment_modes_result)): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="metric-name"><?= ucfirst($row['payment_mode']) ?></span>
                            <span class="metric-value">&#8369;<?= number_format($row['mode_total'], 2) ?></span>
                        </li>
                    <?php endwhile; ?>
                <?php else: ?>
                    <li class="list-group-item">No Sales Found</li>
                <?php endif; ?>
            </ul>
        </article>
        <article class="hc-sales-panel">
            <h5>Top 5 Best-Selling Items</h5>
            <ul class="list-group">
                <?php if(mysqli_num_rows($top_items_result) > 0): $rank = 1; ?>
                    <?php while($row = mysqli_fetch_assoc($top_items_result)): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="metric-name"><strong>#<?= $rank++; ?></strong> <?= htmlspecialchars($row['product_name']) ?></span>
                            <span class="metric-value"><?= $row['total_sold'] ?> sold</span>
                        </li>
                    <?php endwhile; ?>
                <?php else: ?>
                    <li class="list-group-item">No items sold yet.</li>
                <?php endif; ?>
            </ul>
        </article>
    </section>

    <section class="hc-sales-chart">
        <h5>Daily Sales (Last 7 Days)</h5>
        <div class="hc-sales-chart-wrap">
            <canvas id="dailySalesChart"></canvas>
        </div>
    </section>

    <?php if ($show_weekly): ?>
    <section class="hc-sales-chart">
        <h5>Weekly Sales (Last 7 Weeks)</h5>
        <div class="hc-sales-chart-wrap">
            <canvas id="weeklySalesChart"></canvas>
        </div>
    </section>
    <?php endif; ?>

    <?php if ($show_monthly): ?>
    <section class="hc-sales-chart">
        <h5>Monthly Sales (<?= date('Y') ?>)</h5>
        <div class="hc-sales-chart-wrap">
            <canvas id="monthlySalesChart"></canvas>
        </div>
    </section>
    <?php endif; ?>
</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function formatPeso(value) {
    return 'PHP ' + Number(value).toLocaleString();
}

function buildSalesChart(canvasId, labels, values, color) {
    const el = document.getElementById(canvasId);
    if (!el) return;
    new Chart(el.getContext('2d'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Sales',
                data: values,
                backgroundColor: color,
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return formatPeso(value);
                        }
                    }
                }
            }
        }
    });
}

buildSalesChart('dailySalesChart', <?= json_encode($daily_dates) ?>, <?= json_encode($daily_totals) ?>, '#0f172a');
buildSalesChart('weeklySalesChart', <?= json_encode($weekly_labels) ?>, <?= json_encode($weekly_totals) ?>, '#1e293b');
buildSalesChart('monthlySalesChart', <?= json_encode($monthly_labels) ?>, <?= json_encode($monthly_totals) ?>, '#7c2d12');
</script>

<?php include('includes/footer.php'); ?>
