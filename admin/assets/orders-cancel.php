<?php
include('../../config/dbcon.php');

if (!isset($_GET['id'])) {
    header('Location: orders.php');
    exit;
}

$order_id = intval($_GET['id']);

// First, restore the product quantities
$items_query = "SELECT * FROM order_items WHERE order_id = $order_id";
$items_result = mysqli_query($conn, $items_query);

while ($item = mysqli_fetch_assoc($items_result)) {
    $product_name = mysqli_real_escape_string($conn, $item['product_name']);
    $quantity = intval($item['quantity']);
    
    // Restore quantity back to products
    mysqli_query($conn, "UPDATE products SET quantity = quantity + $quantity WHERE name = '$product_name'");
}

// Delete order items
mysqli_query($conn, "DELETE FROM order_items WHERE order_id = $order_id");

// Delete the order
$delete_query = "DELETE FROM orders WHERE id = $order_id";
if (mysqli_query($conn, $delete_query)) {
    header('Location: orders.php?msg=cancelled');
} else {
    header('Location: orders.php?msg=error');
}
exit;
?>
