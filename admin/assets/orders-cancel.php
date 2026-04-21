<?php
include('../../config/dbcon.php');

$loggedInRole = $_SESSION['loggedInUser']['role'] ?? '';
if ($loggedInRole === 'staff') {
    header('Location: orders.php?error=forbidden');
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: orders.php?error=invalid_id');
    exit;
}

$statusColumnCheck = mysqli_query($conn, "SHOW COLUMNS FROM orders LIKE 'order_status'");
if ($statusColumnCheck && mysqli_num_rows($statusColumnCheck) === 0) {
    mysqli_query($conn, "ALTER TABLE orders ADD COLUMN order_status VARCHAR(20) NOT NULL DEFAULT 'pending' AFTER payment_mode");
}

$order_id = (int)$_GET['id'];
$orderRes = mysqli_query($conn, "SELECT id, order_status FROM orders WHERE id = $order_id LIMIT 1");
if (!$orderRes || mysqli_num_rows($orderRes) === 0) {
    header('Location: orders.php?error=not_found');
    exit;
}

$order = mysqli_fetch_assoc($orderRes);
$currentStatus = strtolower((string)($order['order_status'] ?? 'pending'));
if ($currentStatus === 'completed') {
    header('Location: orders.php?error=completed_locked');
    exit;
}

if ($currentStatus !== 'cancelled') {
    $items_query = "SELECT * FROM order_items WHERE order_id = $order_id";
    $items_result = mysqli_query($conn, $items_query);

    while ($item = mysqli_fetch_assoc($items_result)) {
        $product_name = mysqli_real_escape_string($conn, $item['product_name']);
        $quantity = (int)$item['quantity'];
        mysqli_query($conn, "UPDATE products SET quantity = quantity + $quantity WHERE name = '$product_name'");
    }
}

$cancel_query = "UPDATE orders SET order_status = 'cancelled' WHERE id = $order_id LIMIT 1";
if (mysqli_query($conn, $cancel_query)) {
    header('Location: orders.php?msg=cancelled');
} else {
    header('Location: orders.php?msg=error');
}
exit;
?>
