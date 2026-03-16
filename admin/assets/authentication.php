<?php

if(isset($_SESSION['loggedIn'])){

    $username = validate($_SESSION['loggedInUser']['username']);

    $query = "SELECT * FROM cashier_staff WHERE username ='$username' LIMIT 1";
    $result = mysqli_query($conn, $query);

    if(mysqli_num_rows($result) == 0){

        logoutSession();
        redirect('../../login.php', 'Ooops! Access Denied :(');
    }

    $user = mysqli_fetch_assoc($result);

    $position = strtolower(trim((string)($user['position'] ?? '')));
    $sessionRole = $_SESSION['loggedInUser']['role'] ?? '';
    $resolvedRole = ($position === 'cashier' || $position === 'staff') ? 'staff' : 'admin';

    if ($sessionRole !== $resolvedRole) {
        $_SESSION['loggedInUser']['role'] = $resolvedRole;
    }
    $_SESSION['loggedInUser']['position'] = $user['position'] ?? '';

    $currentPage = strtolower(basename($_SERVER['PHP_SELF'] ?? ''));

    if ($resolvedRole === 'staff') {
        $staffAllowedPages = [
            'orders-create.php',
            'orders.php',
            'orders-view.php',
            'place_order.php',
            'paymongo-create-source.php',
            'paymongo-payment-success.php',
            'payment_success.php',
        ];

        if (!in_array($currentPage, $staffAllowedPages, true)) {
            redirect('orders-create.php', 'Access restricted to cashiering only.');
        }
    }
}else{

    redirect('../../login.php', 'Please, Login to continue...');
}

?>
