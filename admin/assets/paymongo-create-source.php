<?php
require '../../config/dbcon.php';
require_once '../../config/function.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('orders-create.php', 'Invalid request method.');
}

$customer_name = validate($_POST['customer_name']);
$products = $_POST['products'];
$payment_mode = validate($_POST['payment_mode']);

if (empty($customer_name) || empty($products)) {
    redirect('orders-create.php', 'Please provide customer name and select products.');
}

$total_amount = 0;
$line_items = [];
foreach ($products as $product) {
    $product_total = $product['price'] * $product['quantity'];
    $total_amount += $product_total;
    $line_items[] = [
        'currency' => 'PHP',
        'amount' => $product['price'] * 100, // Amount in cents
        'name' => $product['name'],
        'quantity' => (int)$product['quantity'],
    ];
}

// Store order details in session to be used after payment
$_SESSION['order_details'] = [
    'customer_name' => $customer_name,
    'products' => $products,
    'total' => $total_amount,
    'payment_mode' => $payment_mode,
];

$data = [
    'data' => [
        'attributes' => [
            'amount' => $total_amount * 100, // Amount in cents
            'description' => 'Payment for Kopikuys Order',
            'statement_descriptor' => 'Kopikuys',
            'currency' => 'PHP',
            'redirect' => [
                'success' => 'http://' . $_SERVER['HTTP_HOST'] . '/Kopikuys/admin/assets/paymongo-payment-success.php',
                'failed' => 'http://' . $_SERVER['HTTP_HOST'] . '/Kopikuys/admin/assets/orders-create.php?payment=failed',
            ],
            'type' => 'gcash', // You can change this to other types like 'paymaya', 'card'
        ],
    ],
];

$secret_key = PAYMONGO_SECRET_KEY;
$encoded_key = base64_encode($secret_key);

$ch = curl_init('https://api.paymongo.com/v1/sources');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Basic ' . $encoded_key,
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code >= 200 && $http_code < 300) {
    $result = json_decode($response, true);
    if (isset($result['data']['attributes']['redirect']['checkout_url'])) {
        $_SESSION['source_id'] = $result['data']['id'];
        header('Location: ' . $result['data']['attributes']['redirect']['checkout_url']);
        exit();
    } else {
        redirect('orders-create.php', 'Failed to get checkout URL from PayMongo.');
    }
} else {
    $error_response = json_decode($response, true);
    $error_message = 'PayMongo API Error: ';
    if (isset($error_response['errors'])) {
        foreach ($error_response['errors'] as $error) {
            $error_message .= $error['detail'] . ' ';
        }
    } else {
        $error_message .= 'An unknown error occurred.';
    }
    redirect('orders-create.php', $error_message);
}