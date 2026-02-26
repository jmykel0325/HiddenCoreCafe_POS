<?php include('includes/header.php'); ?>

<style>
   body {
    background: #FFFFFF url('admin/assets/img/cover.jpg') no-repeat center center fixed;
    background-size: cover;
    font-family: Arial, sans-serif;
}

.container {
        background:rgba(255, 255, 255, 0.92);
        border: 1px solid #E0E0E0;
        padding: 15px;
        border-radius: 15px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        text-align: center;
}

.brand-container {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 20px;
}

.brand-container img {
    width: 400px; 
    height: auto;
}

.btn-primary {
    background-color: #000000;
    border-color: #000000;
}

.btn-primary:hover {
    background-color: #1A1A1A;
    color: #FFFFFF;
    border-color: #1A1A1A;
}
</style>

<div class="py-5">
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-12">
                <div class="brand-container">
                    <img src="LOGO.jpg" alt="Hidden Core Logo">
                </div>
                <a href="login.php" class="btn btn-primary mt-0">Login</a>
            </div>
        </div>
    </div>
</div>

<?php include('includes/footer.php'); ?>