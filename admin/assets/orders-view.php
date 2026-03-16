<?php

include('includes/header.php');
include('../../config/dbcon.php');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<div class='alert alert-danger'>Invalid order ID.</div>";
    include('includes/footer.php');
    exit();
}

$order_id = $_GET['id'];

$order_query = "SELECT * FROM orders WHERE id = $order_id";
$order_result = mysqli_query($conn, $order_query);

if (mysqli_num_rows($order_result) == 0) {
    echo "<div class='alert alert-warning'>Order not found.</div>";
    include('includes/footer.php');
    exit();
}

$order = mysqli_fetch_assoc($order_result);

$invoice_number = "INV-" . date("Ymd") . "-" . rand(1000, 9999);

$items_query = "SELECT * FROM order_items WHERE order_id = $order_id";
$items_result = mysqli_query($conn, $items_query);
$order_items = mysqli_fetch_all($items_result, MYSQLI_ASSOC);

$order_date = date("F j, Y h:i A", strtotime($order['created_at']));
?>

<style>
    @media print {
        body * {
            visibility: hidden;
        }
        #receipt, #receipt * {
            visibility: visible;
        }
        #receipt {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }
        .no-print {
            display: none;
        }
    }

    .receipt-container {
        background: #fff;
        border: 1px solid #ccc;
        padding: 25px;
        max-width: 500px;
        margin: auto;
        font-family: monospace;
    }

    .receipt-header {
        text-align: center;
        margin-bottom: 15px;
    }

    .receipt-header img {
        height: 60px;
        margin-bottom: 10px;
    }

    .receipt-items li {
        margin-bottom: 5px;
    }

    .receipt-total {
        border-top: 1px dashed #000;
        margin-top: 10px;
        padding-top: 10px;
        font-weight: bold;
    }
</style>

<div class="container mt-4">
    <div class="text-end no-print">
        <button class="btn btn-secondary" onclick="window.print()">🖨️ Print Receipt</button>
    </div>

    <div id="receipt">
        <div class="receipt-container">
            <div class="receipt-header">
                <img src="/HiddenCoreCafe_POS/LOGO.jpg" alt="Hidden Core Cafe Logo" style="width: 80px; height: auto;">
                <h4 style="margin: 0;">Hidden Core Cafe</h4>
                <p style="margin: 0;">Roxas East Ave., 9500 General Santos City, Philippines</p>
                <hr>
                <p>Invoice #: <?= $invoice_number ?></p>
                <p>Date: <?= $order_date ?></p>
                <p>Order ID #: <?= $order['id'] ?></p>
            </div>

            <p><strong>Customer:</strong> <?= htmlspecialchars($order['customer_name']) ?></p>
            <p><strong>Payment Mode:</strong> <?= ucfirst($order['payment_mode']) ?></p>
            <p><strong>Status:</strong> Placed</p>
            

            <hr>
            <h5>Items:</h5>
            <ul class="receipt-items">
                <?php if (!empty($order_items)): ?>
                    <?php foreach ($order_items as $item): ?>
                        <li>
                            <?= htmlspecialchars($item['product_name']) ?> 
                            (<?= htmlspecialchars($item['category']) ?>)
                            - <?= $item['quantity'] ?> x ₱<?= number_format($item['price'], 2) ?>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li>No items found.</li>
                <?php endif; ?>
            </ul>

            <p class="receipt-total">Total: ₱<?= number_format($order['total'], 2) ?></p>

            <?php if (strtolower($order['payment_mode']) === 'cash'): ?>
                <p><strong>Cash:</strong> ₱<?= number_format($order['cash_received'], 2) ?></p>
                <p><strong>Change Due:</strong> ₱<?= number_format($order['change_due'], 2) ?></p>
            <?php endif; ?>
            <p style="text-align: center; margin-top: 30px;">Thank you for your purchase!</p>
        </div>
    </div>
</div>

<?php include('includes/footer.php'); ?>