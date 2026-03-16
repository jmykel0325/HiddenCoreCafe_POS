<?php
include('../../config/function.php');
include('../../config/dbcon.php');

$loggedInRole = $_SESSION['loggedInUser']['role'] ?? '';
if ($loggedInRole === 'staff') {
    header("Location: orders.php?error=forbidden");
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: orders.php?error=invalid_id");
    exit();
}

$order_id = $_GET['id'];

// Delete items first due to foreign key constraint
$delete_items_query = "DELETE FROM order_items WHERE order_id = $order_id";
mysqli_query($conn, $delete_items_query);

// Then delete the order
$delete_order_query = "DELETE FROM orders WHERE id = $order_id";
$delete_result = mysqli_query($conn, $delete_order_query);

if ($delete_result) {
    header("Location: orders.php?deleted=success");
} else {
    header("Location: orders.php?deleted=failed");
}
exit();
?>
