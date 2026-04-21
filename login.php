<?php 

$hideNavbar = true;
include('includes/header.php');

if(isset($_SESSION['loggedIn'])){
    ?>
    <script>window.location.href = 'admin/assets/dashboard.php';</script>
    <?php
}
?>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap');
    :root {
        --login-bg: #f7f2ec;
        --login-surface: #ffffff;
        --login-primary: #ff7a1a;
        --login-primary-hover: #e96a0c;
        --login-primary-light: #fff1e6;
        --login-border: #e9e3dc;
        --login-text-dark: #1f2a44;
        --login-text-muted: #7a726b;
    }
    * { font-family: 'Poppins', sans-serif; }
    body {
        min-height: 100vh;
        background: radial-gradient(circle at 14% 14%, #fff7ef 0%, #f7f2ec 42%, #f2ece4 100%);
        color: var(--login-text-dark);
    }
    .hc-login-shell {
        min-height: 100vh;
        padding: 2rem 1.25rem;
        display: grid;
        place-items: center;
    }
    .hc-login-wrap {
        width: min(1200px, 100%);
        min-height: min(760px, calc(100vh - 4rem));
        border-radius: 30px;
        overflow: hidden;
        border: 1px solid var(--login-border);
        box-shadow: 0 28px 64px rgba(37, 23, 15, 0.16);
        background: var(--login-surface);
        display: grid;
        grid-template-columns: 1.1fr 0.9fr;
    }
    .hc-login-left {
        position: relative;
        padding: 2.6rem 2.3rem;
        background:
            radial-gradient(circle at 82% 16%, rgba(255, 122, 26, 0.26) 0%, rgba(255, 122, 26, 0) 34%),
            radial-gradient(circle at 14% 88%, rgba(255, 174, 92, 0.2) 0%, rgba(255, 174, 92, 0) 33%),
            linear-gradient(145deg, #fff7ef 0%, #f6ede4 100%);
        border-right: 1px solid var(--login-border);
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .hc-login-brand-showcase {
        text-align: center;
        max-width: 360px;
        width: 100%;
    }
    .hc-login-brand-icon {
        width: 110px;
        height: 110px;
        margin: 0 auto 1.1rem;
        border-radius: 50%;
        border: 2px solid #ffd2b3;
        background: #fff7ef;
        overflow: hidden;
        display: grid;
        place-items: center;
        box-shadow: 0 14px 26px rgba(255, 122, 26, 0.12);
    }
    .hc-login-brand-icon img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .hc-login-brand-title {
        margin: 0;
        color: var(--login-text-dark);
        font-size: clamp(1.9rem, 3.4vw, 2.7rem);
        font-weight: 800;
        letter-spacing: -0.02em;
    }
    .hc-login-brand-subtitle {
        margin: 0.35rem 0 0;
        color: var(--login-text-muted);
        font-size: 0.82rem;
        letter-spacing: 0.16em;
        text-transform: uppercase;
        font-weight: 600;
    }

    .hc-login-right {
        background: #fffdfa;
        padding: 2.35rem 2.1rem;
        display: flex;
        align-items: center;
    }
    .hc-login-card {
        width: 100%;
        border-radius: 24px;
        border: 1px solid var(--login-border);
        background: var(--login-surface);
        box-shadow: 0 18px 36px rgba(30, 20, 14, 0.08);
        padding: 1.9rem;
    }
    .hc-login-card h2 {
        margin: 0;
        font-weight: 800;
        color: var(--login-text-dark);
        font-size: 2rem;
        letter-spacing: -0.03em;
    }
    .hc-login-subtitle {
        margin-top: 0.35rem;
        color: var(--login-text-muted);
        font-size: 0.92rem;
    }
    .hc-login-card .alert {
        border-radius: 12px;
        border: 1px solid var(--login-border);
        margin-bottom: 1rem;
    }
    .hc-login-form { margin-top: 1.15rem; }
    .hc-login-form label {
        margin-bottom: 0.42rem;
        font-size: 0.88rem;
        color: #433a31;
        font-weight: 600;
    }
    .hc-login-form .form-control {
        min-height: 50px;
        border-radius: 13px;
        border: 1px solid var(--login-border);
        background: #fffdf9;
        color: var(--login-text-dark);
        padding: 0.8rem 0.92rem;
        font-weight: 500;
    }
    .hc-login-form .form-control:focus {
        border-color: var(--login-primary);
        box-shadow: 0 0 0 0.18rem rgba(255, 122, 26, 0.17);
        background: #fff;
    }
    .hc-login-submit {
        margin-top: 0.25rem;
        width: 100%;
        min-height: 50px;
        border-radius: 999px;
        border: 0;
        background: var(--login-primary) !important;
        color: #fff !important;
        font-weight: 700;
        box-shadow: 0 14px 24px rgba(255, 122, 26, 0.28);
    }
    .hc-login-submit:hover { background: var(--login-primary-hover) !important; }

    @media (max-width: 1024px) {
        .hc-login-wrap { grid-template-columns: 1fr; }
        .hc-login-left { border-right: 0; border-bottom: 1px solid var(--login-border); }
    }
    @media (max-width: 768px) {
        .hc-login-shell { padding: 1rem 0.75rem; }
        .hc-login-wrap {
            min-height: auto;
            border-radius: 20px;
        }
        .hc-login-left, .hc-login-right { padding: 1.35rem; }
        .hc-login-card { padding: 1.2rem; border-radius: 18px; }
    }
</style>
<section class="hc-login-shell">
    <div class="hc-login-wrap">
        <div class="hc-login-left">
            <div class="hc-login-brand-showcase">
                <span class="hc-login-brand-icon">
                    <img src="/HiddenCoreCafe_POS/LOGO.jpg" alt="Hidden Core Cafe Logo">
                </span>
                <h1 class="hc-login-brand-title">Hidden Core Cafe</h1>
                <p class="hc-login-brand-subtitle">Cafe Management System</p>
                </div>
        </div>
        <div class="hc-login-right">
            <div class="hc-login-card">
                <?php alertMessage(); ?>
                <h2>Staff Login</h2>
                <p class="hc-login-subtitle">Sign in to access Hidden Core Cafe POS.</p>
                <form action="login-code.php" method="POST" class="hc-login-form">
                    <div class="mb-3">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" class="form-control" required />
                    </div>
                    <div class="mb-3">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" class="form-control" required />
                    </div>
                    <button type="submit" name="loginBtn" class="btn hc-login-submit">
                        Log in
                    </button>
                </form>
            </div>
        </div>
                </div>
</section>

<?php include('includes/footer.php');
