<?php
// GCash details
$gcash_number = '09534921530';
$gcash_name = 'Nicole B. Bayani';

// Get the amount from the query string
$amount = isset($_GET['amount']) ? floatval($_GET['amount']) : 0;

// The data to be encoded in the QR code.
// This creates a string with the GCash number and amount.
$data = "Pay GCash to: {$gcash_number} ({$gcash_name})\nAmount: PHP {$amount}";

// URL-encode the data
$encoded_data = urlencode($data);

// Use a public QR code generator API
$qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={$encoded_data}";

// Return an image tag
echo "<img src='{$qrCodeUrl}' alt='GCash QR Code' class='img-fluid' />";
?>