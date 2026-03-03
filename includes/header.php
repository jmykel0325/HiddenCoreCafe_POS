<?php
require_once __DIR__ . '/../config/function.php';
?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Hidden Core Cafe</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <style>
      :root {
        --hc-primary: #000000;
        --hc-secondary: #1A1A1A;
        --hc-bg: #FFFFFF;
        --hc-card: #F5F5F5;
        --hc-border: #E0E0E0;
        --hc-subtext: #555555;
      }

      body {
        background-color: var(--hc-bg);
        color: var(--hc-primary);
      }

      h1, h2, h3, h4, h5, h6,
      .h1, .h2, .h3, .h4, .h5, .h6 {
        color: var(--hc-primary);
      }

      .text-muted {
        color: var(--hc-subtext) !important;
      }

      .card {
        background-color: var(--hc-card);
        border-color: var(--hc-border);
      }

      .border,
      .border-top,
      .border-bottom,
      .border-start,
      .border-end {
        border-color: var(--hc-border) !important;
      }

      .btn-primary,
      .btn.btn-primary {
        background-color: var(--hc-primary) !important;
        border-color: var(--hc-primary) !important;
        color: #FFFFFF !important;
      }

      .btn-primary:hover,
      .btn.btn-primary:hover {
        background-color: var(--hc-secondary) !important;
        border-color: var(--hc-secondary) !important;
        color: #FFFFFF !important;
      }

      .login-modal .modal-dialog {
        max-width: 960px;
      }

      .login-modal .modal-content {
        border-radius: 20px;
        overflow: hidden;
        border: 1px solid var(--hc-border);
        box-shadow: 0 32px 80px rgba(0, 0, 0, 0.35);
      }

      .login-panel-left {
        background: #000000;
        color: #ffffff;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 100%;
      }

      .login-panel-left .brand-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
      }

      .login-panel-right {
        padding: 3rem 3rem 3rem 3rem;
        background: #111111;
        position: relative;
        overflow: hidden;
      }

      .login-panel-right::before {
        content: "";
        position: absolute;
        inset: 0;
        background: url("/HiddenCoreCafe/admin/assets/img/background.jpg") center center / cover no-repeat;
        filter: blur(10px) brightness(0.45);
        transform: scale(1.05);
        z-index: 0;
      }

      .login-panel-right > .w-100 {
        position: relative;
        z-index: 1;
        color: #ffffff;
      }

      .login-panel-right h3 {
        font-weight: 700;
        letter-spacing: 0.03em;
        color: #ffffff;
      }

      .login-panel-right p {
        color: rgba(255, 255, 255, 0.75);
      }

      .login-modal-form label {
        font-weight: 600;
        color: #ffffff;
      }

      .login-modal-form .form-control {
        border-radius: 999px;
        padding: 0.9rem 1.2rem;
        border: 1px solid #e0e0e0;
        box-shadow: none;
      }

      .login-modal-form .form-control:focus {
        border-color: #000000;
        box-shadow: 0 0 0 2px rgba(0, 0, 0, 0.08);
      }

      @media (max-width: 767.98px) {
        .login-panel-left {
          display: none;
        }

        .login-panel-right {
          padding: 2.25rem 1.75rem;
        }
      }
    </style>
  
</head>
 <body>

    <?php include('navbar.php') ?>

    <!-- Login Modal -->
    <div class="modal fade login-modal" id="loginModal" tabindex="-1" aria-hidden="true" aria-labelledby="loginModalLabel">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
          <div class="modal-body p-0">
            <div class="row g-0">
              <div class="col-md-5 login-panel-left">
                <img
                  src="/HiddenCoreCafe/admin/assets/img/Login Hidden.jpg"
                  alt="Hidden Core Cafe"
                  class="brand-image"
                />
              </div>
              <div class="col-md-7 login-panel-right d-flex align-items-center">
                <div class="w-100">
                  <?php if(function_exists('alertMessage')) { alertMessage(); } ?>
                  <h3 class="mb-1" id="loginModalLabel">Account Login</h3>
                  <p class="text-muted mb-4">Good day! Please sign in to continue.</p>
                  <form action="login-code.php" method="POST" class="login-modal-form">
                    <div class="mb-3">
                      <label for="loginUsername" class="form-label">Username</label>
                      <input type="text" id="loginUsername" name="username" class="form-control" required />
                    </div>
                    <div class="mb-3">
                      <label for="loginPassword" class="form-label">Password</label>
                      <input type="password" id="loginPassword" name="password" class="form-control" required />
                    </div>
                    <div class="d-grid mt-4">
                      <button type="submit" name="loginBtn" class="btn btn-primary">
                        Log in
                      </button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>