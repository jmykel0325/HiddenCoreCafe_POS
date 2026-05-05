<?php

require '../../config/function.php';
require 'authentication.php';

$role = strtolower(trim((string)($_SESSION['loggedInUser']['role'] ?? '')));
$position = strtolower(trim((string)($_SESSION['loggedInUser']['position'] ?? '')));
$isStaff = ($role === 'staff' || $role === 'cashier' || $position === 'cashier' || $position === 'staff');

if ($isStaff) {
    redirect('orders-create.php', 'Access restricted to admin users.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('dashboard.php', 'Invalid quota update.');
}

$targetRaw = trim((string)($_POST['quota_target'] ?? ''));
if ($targetRaw === '' || !ctype_digit($targetRaw)) {
    redirect('dashboard.php', 'Please enter a valid quota target.');
}

$targetCups = (int)$targetRaw;
if ($targetCups < 1) {
    redirect('dashboard.php', 'Quota target must be at least 1 cup.');
}

$currentUserId = isset($_SESSION['loggedInUser']['user_id'])
    ? (int)$_SESSION['loggedInUser']['user_id']
    : null;

if (saveTodayQuotaTarget($targetCups, $currentUserId)) {
    redirect('dashboard.php', 'Today\'s quota updated successfully.');
}

redirect('dashboard.php', 'Unable to update today\'s quota.');

?>
