<?php


include('includes/header.php'); 
include('../../config/dbcon.php');

if (isset($_POST['place_order'])) {
    $customer_name = mysqli_real_escape_string($conn, $_POST['customer_name']);
    $payment_mode = mysqli_real_escape_string($conn, $_POST['payment_mode']);
    $products = $_POST['products'];

    $total = 0;
    foreach ($products as $p) {
        $total += $p['quantity'] * $p['price']; // Calculate total based on quantity and price
    }

    if ($payment_mode === 'Cash') {
        $cash_received = isset($_POST['cash_received']) ? floatval($_POST['cash_received']) : 0;
        $change_due = $cash_received - $total;
        $order_query = "INSERT INTO orders (customer_name, payment_mode, total, cash_received, change_due) 
                        VALUES ('$customer_name', '$payment_mode', '$total', '$cash_received', '$change_due')";
    } else { // For GCash or other non-cash methods
        $order_query = "INSERT INTO orders (customer_name, payment_mode, total) 
                        VALUES ('$customer_name', '$payment_mode', '$total')";
    }

    if (mysqli_query($conn, $order_query)) {
        $order_id = mysqli_insert_id($conn); // Get the last inserted order ID

        foreach ($products as $p) {
            $prod_id = intval($p['id']);
            $qty = intval($p['quantity']);
            $price = floatval($p['price']);
            $product_name = mysqli_real_escape_string($conn, $p['name']);
            $category = isset($p['category']) ? mysqli_real_escape_string($conn, $p['category']) : '';

            // Insert each product into the order_items table
            $item_query = "INSERT INTO order_items (order_id, product_name, category, quantity, price)
                        VALUES ('$order_id', '$product_name', '$category', '$qty', '$price')";
            mysqli_query($conn, $item_query);

            // Update the product quantity in the products table
            $update_query = "UPDATE products SET quantity = quantity - $qty WHERE id = $prod_id";
            mysqli_query($conn, $update_query);
        }

        // Show a centered toast notification and redirect after a short delay
        echo "
        <style>
        .notif-toast {
            position: fixed;
            top: 30px;
            left: 50%;
            transform: translateX(-50%);
            background: #000000;
            color: #fff;
            padding: 16px 28px;
            border-radius: 8px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.18);
            font-size: 1.1rem;
            z-index: 9999;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s;
        }
        .notif-toast.show {
            opacity: 1;
            pointer-events: auto;
        }
        </style>
        <div id='notifToast' class='notif-toast'></div>
        <script>
        function showNotif(message, color = '#000000') {
            const toast = document.getElementById('notifToast');
            toast.textContent = message;
            toast.style.background = color;
            toast.classList.add('show');
            setTimeout(() => {
                toast.classList.remove('show');
                window.location.href='orders.php';
            }, 1800);
        }
        showNotif('Order placed successfully!');
        </script>
        ";
    } else {
        echo "
        <style>
        .notif-toast {
            position: fixed;
            top: 30px;
            left: 50%;
            transform: translateX(-50%);
            background: #d9534f;
            color: #fff;
            padding: 16px 28px;
            border-radius: 8px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.18);
            font-size: 1.1rem;
            z-index: 9999;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s;
        }
        .notif-toast.show {
            opacity: 1;
            pointer-events: auto;
        }
        </style>
        <div id='notifToast' class='notif-toast'></div>
        <script>
        function showNotif(message, color = '#d9534f') {
            const toast = document.getElementById('notifToast');
            toast.textContent = message;
            toast.style.background = color;
            toast.classList.add('show');
            setTimeout(() => {
                toast.classList.remove('show');
            }, 2200);
        }
        showNotif('Failed to place order.');
        </script>
        ";
    }
}
?>

<?php include('includes/footer.php'); ?>