<?php 

include('includes/header.php');

if(isset($_SESSION['loggedIn'])){
    ?>
    <script>window.location.href = 'dashboard.php';</script>
    <?php
}
?>

<style>
    body {
        background: url('login_bg.png') no-repeat center center;
        background-size: cover;
        background-attachment: scroll;
    }
    .login-container {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
    }
    .card {
        background-color: rgba(255, 255, 255, 0.94);
        border: 1px solid #E0E0E0;
        border-radius: 15px;
        padding: 30px;
        width: 400px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        text-align: center;
    }
    .btn-primary {
        background-color: #000000;
        border-color: #000000;
    }
    .btn-primary:hover {
        background-color:#1A1A1A;
        border-color: #1A1A1A;
        color: #FFFFFF;
    }
    label {
        font-weight: bold;
        color: #000000;
    }
    .form-control {
        border-radius: 5px;
    }
</style>

<div class="login-container">
    <div class="card">
        <?php alertMessage(); ?>

        <h4 class="text-dark mb-3">Login to Kopikuys</h4>
        <form action="login-code.php" method="POST">
            <div class="mb-3">
                <label>Enter Username</label>
                <input type="text" name="username" class="form-control" required />
            </div>
            <div class="mb-3">
                <label>Enter Password</label>
                <input type="password" name="password" class="form-control" required />
            </div>
            <div class="my-3">
                <button type="submit" name="loginBtn" class="btn btn-primary w-100 mt-2">
                    Login
                </button>
            </div>
        </form>
    </div>
</div>

<?php include('includes/footer.php');

?>