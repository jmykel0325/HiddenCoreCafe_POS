<?php
include('../../config/function.php');
include('../../config/dbcon.php');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: orders.php?error=invalid_id');
    exit();
}

$statusColumnCheck = mysqli_query($conn, "SHOW COLUMNS FROM orders LIKE 'order_status'");
if ($statusColumnCheck && mysqli_num_rows($statusColumnCheck) === 0) {
    mysqli_query($conn, "ALTER TABLE orders ADD COLUMN order_status VARCHAR(20) NOT NULL DEFAULT 'pending' AFTER payment_mode");
}

$orderId = (int)$_GET['id'];
$orderRes = mysqli_query($conn, "SELECT id, order_status FROM orders WHERE id = $orderId LIMIT 1");
if (!$orderRes || mysqli_num_rows($orderRes) === 0) {
    header('Location: orders.php?error=not_found');
    exit();
}

$order = mysqli_fetch_assoc($orderRes);
if (strtolower((string)($order['order_status'] ?? 'pending')) === 'completed') {
    header('Location: orders.php?msg=already_completed');
    exit();
}

$updateRes = mysqli_query($conn, "UPDATE orders SET order_status = 'completed' WHERE id = $orderId LIMIT 1");
if ($updateRes) {
    header('Location: orders.php?msg=completed');
} else {
    header('Location: orders.php?msg=complete_error');
}
exit();
?>
