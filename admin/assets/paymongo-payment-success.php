<?php
include('../../config/dbcon.php');
require_once('../../config/function.php');

// Check if order details are in session
if (!isset($_SESSION['order_details'])) {
    redirect('orders-create.php', 'No order details found. Your session may have expired.');
}

$order_details = $_SESSION['order_details'];
$customer_name = $order_details['customer_name'];
$total_amount = $order_details['total'];
$payment_mode = ($order_details['payment_mode'] == 'PayMongo') ? 'GCash' : $order_details['payment_mode'];
$products = $order_details['products'];

// --- Create the Order in the database ---
$orderData = [
    'customer_name' => $customer_name,
    'total' => $total_amount,
    'payment_mode' => $payment_mode,
    'cash_received' => 0,
    'change_due' => 0,
];

if (insert('orders', $orderData)) {
    $order_id = mysqli_insert_id($conn);

    // --- Create Order Items and Update Product Stock ---
    foreach ($products as $prod) {
        // Get product category
        $product_data = getById('products', $prod['id']);
        $category_id = $product_data['data']['category_id'] ?? null;
        $category_data = $category_id ? getById('categories', $category_id) : null;
        $category_name = $category_data['data']['name'] ?? 'N/A';

        // Insert order item
        $orderItemData = [
            'order_id' => $order_id,
            'product_name' => $prod['name'],
            'price' => $prod['price'],
            'quantity' => $prod['quantity'],
            'category' => $category_name,
        ];
        insert('order_items', $orderItemData);

        // Update product stock
        $new_quantity = $product_data['data']['quantity'] - $prod['quantity'];
        update('products', $prod['id'], ['quantity' => $new_quantity]);
    }

    // Clear session data
    unset($_SESSION['order_details']);
    unset($_SESSION['source_id']);

} else {
    redirect('orders-create.php', 'Failed to save the order. Please contact support.');
}

?>

<!-- Success Page UI -->
<?php include('includes/header.php'); ?>

<style>
    body {
        background-color: #f0f2f5;
    }
    .receipt-container {
        max-width: 420px;
        margin: 40px auto;
        background-color: #fff;
        border-radius: 15px;
        box-shadow: 0 6px 20px rgba(0,0,0,0.08);
        overflow: hidden;
        font-family: Arial, sans-serif;
    }
    .receipt-header {
        background-color: #005cff;
        color: white;
        padding: 25px;
        text-align: center;
    }
    .receipt-header img.logo {
        width: 100px;
        margin-bottom: 15px;
    }
    .receipt-header .amount {
        font-size: 2.5rem;
        font-weight: bold;
        margin: 5px 0;
    }
    .receipt-body {
        padding: 25px;
    }
    .receipt-body h5 {
        color: #005cff;
        font-weight: bold;
        margin-bottom: 20px;
        text-align: center;
    }
    .details-table {
        width: 100%;
        margin-bottom: 20px;
    }
    .details-table td {
        padding: 8px 0;
        border-bottom: 1px solid #eee;
        font-size: 15px;
    }
    .details-table td:first-child {
        color: #6c757d;
    }
    .details-table td:last-child {
        text-align: right;
        font-weight: 500;
        color: #333;
    }
    .product-list {
        border-top: 1px solid #eee;
        padding-top: 15px;
    }
    .product-list .product-item {
        display: flex;
        justify-content: space-between;
        margin-bottom: 5px;
    }
    .receipt-footer {
        text-align: center;
        padding: 20px;
        background-color: #f8f9fa;
    }
</style>

<?php if ($payment_mode == 'GCash'): ?>
    <div class="receipt-container">
        <div class="receipt-header">
            <img src="https://1000logos.net/wp-content/uploads/2023/05/GCash-Logo.png" alt="GCash Logo" class="logo">
            <p>You have paid</p>
            <h2 class="amount">PHP <?= number_format($total_amount, 2) ?></h2>
            <p>to Kopikuys</p>
        </div>
        <div class="receipt-body">
            <h5>Payment Successful</h5>
            <table class="details-table">
                <tr>
                    <td>Payment Method</td>
                    <td><?= htmlspecialchars($payment_mode) ?></td>
                </tr>
                <tr>
                    <td>Merchant</td>
                    <td>Kopikuys</td>
                </tr>
                <tr>
                    <td>Amount</td>
                    <td>PHP <?= number_format($total_amount, 2) ?></td>
                </tr>
                <tr>
                    <td>Date</td>
                    <td><?= date('F j, Y, g:i a') ?></td>
                </tr>
                <tr>
                    <td>Reference No.</td>
                    <td>ORD-<?= $order_id ?></td>
                </tr>
            </table>
            <div class="product-list">
                <strong>Order Summary:</strong>
                <?php foreach ($products as $prod): ?>
                    <div class="product-item">
                        <span><?= htmlspecialchars($prod['name']) ?> (x<?= $prod['quantity'] ?>)</span>
                        <span>PHP <?= number_format($prod['price'] * $prod['quantity'], 2) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="receipt-footer">
            <button id="confirmBtn" class="btn btn-primary w-100">Confirm</button>
        </div>
    </div>
<?php else: ?>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8 offset-md-2 text-center">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h2 class="text-success">Payment Successful!</h2>
                        <p>Your order (ID: #<?= $order_id ?>) has been placed and is now being processed.</p>
                        <p>You will be redirected to the orders page shortly.</p>
                        <a href="orders.php" class="btn btn-primary">View Your Orders</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const confirmBtn = document.getElementById('confirmBtn');
        if (confirmBtn) {
            // GCash receipt is shown, so we wait for a click
            confirmBtn.addEventListener('click', function() {
                window.location.href = 'orders.php';
            });
        } else {
            // GCash receipt is not shown, so we auto-redirect
            setTimeout(function() {
                window.location.href = 'orders.php';
            }, 5000); // 5 seconds
        }
    });
</script>

<?php include('includes/footer.php'); ?>