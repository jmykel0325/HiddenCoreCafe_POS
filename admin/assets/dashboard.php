<?php

include('includes/header.php');
include('../../config/dbcon.php');
?>

<style>
    .dashboard-card {
        background-color: #F5F5F5;
        border: 1px solid #E0E0E0;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        margin-bottom: 20px;
        text-align: center;
    }
    .dashboard-title {
        font-size: 1.3rem;
        color: #000000;
        margin-bottom: 10px;
    }
    .dashboard-value {
        font-size: 2rem;
        font-weight: bold;
        color: #000000;
    }
</style>

<div class="container-fluid px-4">
    <h4 class="mt-4"><i class="fas fa-tachometer-alt"></i> Dashboard</h4>
    <div class="row mt-4">

<?php

// Change: Get today's sales only
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
while($row = mysqli_fetch_assoc($daily_sales_result)){
    $daily_dates[] = date('M j', strtotime($row['sale_date']));
    $daily_totals[] = $row['daily_total'];
}

// Remove monthly sales query and variables
/*
$monthly_sales_result = mysqli_query($conn, "
    SELECT DATE_FORMAT(created_at, '%M') as sale_month, SUM(total) as monthly_total 
    FROM orders 
    WHERE YEAR(created_at) = YEAR(CURDATE())
    GROUP BY MONTH(created_at)
    ORDER BY MONTH(created_at)
");
$monthly_labels = [];
$monthly_totals = [];
while($row = mysqli_fetch_assoc($monthly_sales_result)){
    $monthly_labels[] = $row['sale_month'];
    $monthly_totals[] = $row['monthly_total'];
}
*/

$payment_mode_result = mysqli_query($conn, "
    SELECT payment_mode, COUNT(*) as mode_count
    FROM orders
    GROUP BY payment_mode
");
$payment_modes = [];
$payment_counts = [];
while($row = mysqli_fetch_assoc($payment_mode_result)){
    $payment_modes[] = ucfirst($row['payment_mode']);
    $payment_counts[] = $row['mode_count'];
}
?>

        <div class="col-md-3">
            <div class="dashboard-card">
                <div class="dashboard-title">Today's Sales</div>
                <div class="dashboard-value">₱<?= number_format($todays_sales, 2) ?></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="dashboard-card">
                <div class="dashboard-title">Orders</div>
                <div class="dashboard-value"><?= $order_count ?></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="dashboard-card">
                <div class="dashboard-title">Items Sold</div>
                <div class="dashboard-value"><?= $total_items_sold ?></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="dashboard-card">
                <div class="dashboard-title">Cashier/Staff</div>
                <div class="dashboard-value"><?= $total_cashiers ?></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="dashboard-card">
                <div class="dashboard-title">Categories</div>
                <div class="dashboard-value"><?= $total_categories ?></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="dashboard-card">
                <div class="dashboard-title">Products</div>
                <div class="dashboard-value"><?= $total_products ?></div>
            </div>
        </div>

        <div class="col-md-12 mt-5">
            <h5 class="mb-3">Daily Sales (Last 7 Days)</h5>
            <canvas id="dailySalesChart"></canvas>
        </div>

        <!-- Remove Monthly Sales Chart Section -->
        <!--
        <div class="col-md-6 mt-5">
            <h5 class="mb-3" style="color: #5a4a42;">Monthly Sales (<?= date('Y') ?>)</h5>
            <canvas id="monthlySalesChart"></canvas>
        </div>
        -->

        <div class="col-md-6 mt-5">
            <h5 class="mb-3">Payment Mode Breakdown</h5>
            <canvas id="paymentModeChart"></canvas>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>

    new Chart(document.getElementById('dailySalesChart').getContext('2d'), {
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
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    // Remove Monthly Sales Chart JS
    /*
    new Chart(document.getElementById('monthlySalesChart').getContext('2d'), {
        type: 'line',
        data: {
            labels: <?= json_encode($monthly_labels) ?>,
            datasets: [{
                label: '₱ Monthly Sales',
                data: <?= json_encode($monthly_totals) ?>,
                backgroundColor: 'rgba(140, 115, 85, 0.2)',
                borderColor: '#8c7355',
                fill: true,
                tension: 0.4,
            }]
        },
        options: {
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
    */

    new Chart(document.getElementById('paymentModeChart').getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: <?= json_encode($payment_modes) ?>,
            datasets: [{
                data: <?= json_encode($payment_counts) ?>,
                backgroundColor: ['#000000', '#1A1A1A', '#555555', '#E0E0E0'],
            }]
        },
        options: {
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });
</script>

<?php include('includes/footer.php'); ?>