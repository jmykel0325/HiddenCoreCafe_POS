<?php
include('includes/header.php'); 
?>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-8 offset-md-2 text-center">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h2 class="text-success">Payment Successful!</h2>
                    <p>Your order has been placed and is now being processed.</p>
                    <p>You will be redirected to the orders page shortly.</p>
                    <a href="orders.php" class="btn btn-primary">View Your Orders</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Redirect to orders page after a few seconds
    setTimeout(function() {
        window.location.href = 'orders.php';
    }, 5000); // 5 seconds
</script>

<?php include('includes/footer.php'); ?>
