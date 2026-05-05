<?php


include('includes/header.php'); 
include('../../config/dbcon.php');

if (!function_exists('ensureOrderItemsSizeColumn')) {
    function ensureOrderItemsSizeColumn($conn) {
        static $checked = false;
        static $available = false;

        if ($checked) {
            return $available;
        }

        $checked = true;
        $checkResult = mysqli_query($conn, "SHOW COLUMNS FROM order_items LIKE 'size'");
        if ($checkResult && mysqli_num_rows($checkResult) > 0) {
            $available = true;
            return true;
        }

        $alterResult = mysqli_query($conn, "ALTER TABLE order_items ADD COLUMN size VARCHAR(20) NOT NULL DEFAULT '12oz' AFTER category");
        if ($alterResult) {
            $available = true;
        }

        return $available;
    }
}

if (!function_exists('ensureOrdersDiscountColumns')) {
    function ensureOrdersDiscountColumns($conn) {
        static $checked = false;
        static $available = false;

        if ($checked) {
            return $available;
        }

        $checked = true;
        
        // Check for discount_type column
        $checkType = mysqli_query($conn, "SHOW COLUMNS FROM orders LIKE 'discount_type'");
        if (!$checkType || mysqli_num_rows($checkType) === 0) {
            mysqli_query($conn, "ALTER TABLE orders ADD COLUMN discount_type VARCHAR(20) NULL AFTER payment_mode");
        }
        
        // Check for discount_rate column
        $checkRate = mysqli_query($conn, "SHOW COLUMNS FROM orders LIKE 'discount_rate'");
        if (!$checkRate || mysqli_num_rows($checkRate) === 0) {
            mysqli_query($conn, "ALTER TABLE orders ADD COLUMN discount_rate DECIMAL(5,2) DEFAULT 0 AFTER discount_type");
        }
        
        // Check for discount_amount column
        $checkAmount = mysqli_query($conn, "SHOW COLUMNS FROM orders LIKE 'discount_amount'");
        if (!$checkAmount || mysqli_num_rows($checkAmount) === 0) {
            mysqli_query($conn, "ALTER TABLE orders ADD COLUMN discount_amount DECIMAL(10,2) DEFAULT 0 AFTER discount_rate");
        }
        
        // Check for final_total column (total after discount)
        $checkFinal = mysqli_query($conn, "SHOW COLUMNS FROM orders LIKE 'final_total'");
        if (!$checkFinal || mysqli_num_rows($checkFinal) === 0) {
            mysqli_query($conn, "ALTER TABLE orders ADD COLUMN final_total DECIMAL(10,2) DEFAULT 0 AFTER discount_amount");
        }

        $available = true;
        return true;
    }
}
if (!function_exists('ensureOrdersGcashReferenceColumn')) {
    function ensureOrdersGcashReferenceColumn($conn) {
        static $checked = false;
        static $available = false;

        if ($checked) {
            return $available;
        }

        $checked = true;
        $checkResult = mysqli_query($conn, "SHOW COLUMNS FROM orders LIKE 'gcash_reference'");
        if ($checkResult && mysqli_num_rows($checkResult) > 0) {
            $available = true;
            return true;
        }

        $alterResult = mysqli_query($conn, "ALTER TABLE orders ADD COLUMN gcash_reference VARCHAR(100) NULL AFTER payment_mode");
        if ($alterResult) {
            $available = true;
        }

        return $available;
    }
}

if (isset($_POST['place_order'])) {
    $customer_name = mysqli_real_escape_string($conn, $_POST['customer_name']);
    $payment_mode = mysqli_real_escape_string($conn, $_POST['payment_mode']);
    $gcash_reference = isset($_POST['gcash_reference']) ? trim((string)$_POST['gcash_reference']) : '';
    $gcash_reference_escaped = mysqli_real_escape_string($conn, $gcash_reference);
    $products = $_POST['products'];

    $total = 0;
    $normalizedProducts = [];
    foreach ($products as $p) {
        $prod_id = intval($p['id']);
        $qty = max(1, intval($p['quantity']));
        $size = isset($p['size']) && $p['size'] === '16oz' ? '16oz' : '12oz';

        $productResult = mysqli_query($conn, "SELECT name, price, price_12oz, price_16oz FROM products WHERE id = $prod_id LIMIT 1");
        if (!$productResult || mysqli_num_rows($productResult) === 0) {
            continue;
        }

        $productRow = mysqli_fetch_assoc($productResult);
        $price12 = isset($productRow['price_12oz']) ? (float)$productRow['price_12oz'] : (float)$productRow['price'];
        $price16 = isset($productRow['price_16oz']) ? (float)$productRow['price_16oz'] : (float)$productRow['price'];
        $unitPrice = $size === '16oz' ? $price16 : $price12;

        $normalizedProducts[] = [
            'id' => $prod_id,
            'quantity' => $qty,
            'size' => $size,
            'price' => $unitPrice,
            'name' => $productRow['name'] ?? ($p['name'] ?? '')
        ];
        $total += $qty * $unitPrice;
    }

    $discount_type = isset($_POST['discount_type']) ? mysqli_real_escape_string($conn, $_POST['discount_type']) : '';
    $discount_rate = isset($_POST['discount_rate']) ? floatval($_POST['discount_rate']) : 0;
    $discount_amount = isset($_POST['discount_amount']) ? floatval($_POST['discount_amount']) : 0;

    if (empty($normalizedProducts)) {
        echo "<script>alert('No valid products found in order.'); window.location.href='orders-create.php';</script>";
        include('includes/footer.php');
        exit;
    }

    // Calculate final total after discount
    $subtotal = $total;
    if ($discount_type === 'PWD' || $discount_type === 'Senior') {
        $discount_rate = 0.20;
        $discount_amount = $subtotal * $discount_rate;
        $total = $subtotal - $discount_amount;
    }
    
    $final_total = $total;
    
    $hasDiscountColumns = ensureOrdersDiscountColumns($conn);
    $hasGcashReferenceColumn = ensureOrdersGcashReferenceColumn($conn);

    if ($payment_mode === 'Cash') {
        $cash_received = isset($_POST['cash_received']) ? floatval($_POST['cash_received']) : 0;
        $change_due = $cash_received - $total;
        
        $columns = "customer_name, payment_mode, discount_type, discount_rate, discount_amount, final_total, total, cash_received, change_due";
        $values = "'$customer_name', '$payment_mode', " . ($discount_type ? "'$discount_type'" : "NULL") . ", $discount_rate, $discount_amount, $final_total, $subtotal, $cash_received, $change_due";
        
        if ($hasGcashReferenceColumn) {
            $columns .= ", gcash_reference";
            $values .= ", NULL";
        }
        
        $order_query = "INSERT INTO orders ($columns) VALUES ($values)";
    } else { // For GCash
        if ($gcash_reference === '') {
            echo "<script>alert('GCash reference number is required.'); window.location.href='orders-create.php';</script>";
            include('includes/footer.php');
            exit;
        }

        $columns = "customer_name, payment_mode, gcash_reference, discount_type, discount_rate, discount_amount, final_total, total";
        $values = "'$customer_name', '$payment_mode', '$gcash_reference_escaped', " . ($discount_type ? "'$discount_type'" : "NULL") . ", $discount_rate, $discount_amount, $final_total, $subtotal";
        
        $order_query = "INSERT INTO orders ($columns) VALUES ($values)";
    }

    if (mysqli_query($conn, $order_query)) {
        $order_id = mysqli_insert_id($conn); // Get the last inserted order ID

        $hasOrderItemSize = ensureOrderItemsSizeColumn($conn);
        foreach ($normalizedProducts as $p) {
            $prod_id = (int)$p['id'];
            $qty = (int)$p['quantity'];
            $price = (float)$p['price'];
            $size = mysqli_real_escape_string($conn, (string)$p['size']);
            $product_name = mysqli_real_escape_string($conn, (string)$p['name']);
            $category = isset($p['category']) ? mysqli_real_escape_string($conn, $p['category']) : '';

            // Insert each product into the order_items table
            if ($hasOrderItemSize) {
                $item_query = "INSERT INTO order_items (order_id, product_name, category, size, quantity, price)
                            VALUES ('$order_id', '$product_name', '$category', '$size', '$qty', '$price')";
            } else {
                $item_query = "INSERT INTO order_items (order_id, product_name, category, quantity, price)
                            VALUES ('$order_id', '$product_name', '$category', '$qty', '$price')";
            }
            mysqli_query($conn, $item_query);

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
