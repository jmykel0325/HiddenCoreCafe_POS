<?php 

include('includes/header.php');

if(isset($_SESSION['loggedIn'])){
    ?>
    <script>window.location.href = 'admin/assets/dashboard.php';</script>
    <?php
}
?>

<style>
    body {
        min-height: 100vh;
        background: #f1ebe2;
    }

    .login-shell {
        position: relative;
        min-height: calc(100vh - 73px);
        padding: 3rem 1.25rem 4rem;
        display: flex;
        align-items: center;
    }

    .login-grid {
        position: relative;
        z-index: 1;
        width: 100%;
        max-width: 1180px;
        margin: 0 auto;
        display: grid;
        grid-template-columns: minmax(0, 1.05fr) minmax(360px, 460px);
        gap: 2rem;
        align-items: center;
    }

    .login-hero {
        padding: 2rem 1rem 2rem 0;
    }

    .login-kicker {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.45rem 0.85rem;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.72);
        border: 1px solid rgba(15, 23, 42, 0.08);
        color: #e76300;
        font-size: 0.78rem;
        font-weight: 800;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
    }

    .login-title {
        margin: 1.2rem 0 0;
        color: #102341;
        font-size: clamp(2.5rem, 6vw, 4.3rem);
        font-weight: 900;
        letter-spacing: -0.05em;
        line-height: 0.95;
    }

    .login-copy {
        max-width: 560px;
        margin: 1.1rem 0 0;
        color: #5b6474;
        font-size: 1.02rem;
    }

    .login-points {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
        max-width: 640px;
        margin-top: 1.8rem;
    }

    .login-point {
        padding: 1rem 1.1rem;
        border-radius: 22px;
        background: rgba(255, 255, 255, 0.72);
        border: 1px solid rgba(15, 23, 42, 0.08);
        box-shadow: 0 14px 30px rgba(15, 23, 42, 0.06);
    }

    .login-point strong {
        display: block;
        color: #0f172a;
        font-size: 1rem;
        font-weight: 800;
    }

    .login-point span {
        display: block;
        margin-top: 0.35rem;
        color: #64748b;
        font-size: 0.92rem;
    }

    .login-card {
        position: relative;
        z-index: 1;
        padding: 2rem;
        border-radius: 30px;
        background: rgba(255, 255, 255, 0.9);
        border: 1px solid rgba(15, 23, 42, 0.1);
        box-shadow: 0 28px 60px rgba(15, 23, 42, 0.14);
        backdrop-filter: blur(18px);
    }

    .login-card-header {
        margin-bottom: 1.5rem;
    }

    .login-card-title {
        margin: 0;
        color: #0f172a;
        font-size: 2rem;
        font-weight: 900;
        letter-spacing: -0.03em;
    }

    .login-card-subtitle {
        margin: 0.4rem 0 0;
        color: #64748b;
        font-size: 0.96rem;
    }

    .login-form label {
        margin-bottom: 0.45rem;
        color: #334155;
        font-weight: 800;
    }

    .login-form .form-control {
        min-height: 54px;
        border-radius: 18px;
        border: 1px solid rgba(15, 23, 42, 0.12);
        background: #ffffff;
        color: #0f172a;
        padding: 0.9rem 1rem;
        box-shadow: none;
    }

    .login-form .form-control:focus {
        border-color: rgba(255, 122, 26, 0.65);
        box-shadow: 0 0 0 0.22rem rgba(255, 122, 26, 0.14);
    }

    .login-submit {
        min-height: 54px;
        border: 0;
        background: linear-gradient(90deg, #ff7a1a 0%, #ff9f5f 100%) !important;
        box-shadow: 0 18px 28px rgba(255, 122, 26, 0.22);
    }

    .login-submit:hover {
        filter: brightness(0.98);
    }

    .login-card .alert {
        margin-bottom: 1.25rem;
        border: 0;
        border-radius: 18px;
    }

    @media (max-width: 991.98px) {
        .login-grid {
            grid-template-columns: 1fr;
        }

        .login-hero {
            padding: 0;
        }
    }

    @media (max-width: 767.98px) {
        .login-shell {
            padding-top: 2rem;
        }

        .login-card {
            padding: 1.5rem;
            border-radius: 24px;
        }

        .login-points {
            grid-template-columns: 1fr;
        }

        .login-title {
            font-size: 2.4rem;
        }
    }
</style>

<section class="login-shell">
    <div class="login-grid">
        <div class="login-hero">
            <div class="login-kicker">Hidden Core Cafe POS</div>
            <h1 class="login-title">One login flow. One visual system.</h1>
            <p class="login-copy">
                The sign-in page now follows the dashboard style with warm accents, soft surfaces, and a clearer entry point for staff access.
            </p>

            <div class="login-points">
                <div class="login-point">
                    <strong>Dashboard-aligned UI</strong>
                    <span>Uses the same orange accent, rounded cards, and lighter admin-inspired layout.</span>
                </div>
                <div class="login-point">
                    <strong>Single access point</strong>
                    <span>The navbar login now goes directly to this page instead of opening a second login design.</span>
                </div>
            </div>
        </div>

        <div class="login-card">
            <?php alertMessage(); ?>

            <div class="login-card-header">
                <h2 class="login-card-title">Staff Login</h2>
                <p class="login-card-subtitle">Sign in to continue to the POS dashboard.</p>
            </div>

            <form action="login-code.php" method="POST" class="login-form">
                <div class="mb-3">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" class="form-control" required />
                </div>
                <div class="mb-3">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" required />
                </div>
                <div class="mt-4">
                    <button type="submit" name="loginBtn" class="btn btn-primary login-submit w-100">
                        Log in
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>

<?php include('includes/footer.php');

?>
