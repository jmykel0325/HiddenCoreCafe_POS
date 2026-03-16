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
        --hc-primary: #ff7a1a;
        --hc-primary-strong: #e76300;
        --hc-ink: #0f172a;
        --hc-ink-soft: #334155;
        --hc-bg: #f7f1eb;
        --hc-surface: rgba(255, 255, 255, 0.88);
        --hc-card: #ffffff;
        --hc-border: rgba(15, 23, 42, 0.12);
        --hc-subtext: #64748b;
        --hc-shadow: 0 18px 40px rgba(15, 23, 42, 0.08);
      }

      body {
        background-color: var(--hc-bg);
        color: var(--hc-ink);
      }

      h1, h2, h3, h4, h5, h6,
      .h1, .h2, .h3, .h4, .h5, .h6 {
        color: var(--hc-ink);
      }

      .text-muted {
        color: var(--hc-subtext) !important;
      }

      .card {
        background-color: var(--hc-card);
        border-color: var(--hc-border);
        box-shadow: var(--hc-shadow);
      }

      .border,
      .border-top,
      .border-bottom,
      .border-start,
      .border-end {
        border-color: var(--hc-border) !important;
      }

      .navbar {
        background: rgba(255, 255, 255, 0.84) !important;
        backdrop-filter: blur(14px);
        border-bottom: 1px solid rgba(15, 23, 42, 0.08);
      }

      .btn-primary,
      .btn.btn-primary {
        background-color: var(--hc-primary) !important;
        border-color: var(--hc-primary) !important;
        color: #FFFFFF !important;
        border-radius: 999px;
        font-weight: 700;
      }

      .btn-primary:hover,
      .btn.btn-primary:hover {
        background-color: var(--hc-primary-strong) !important;
        border-color: var(--hc-primary-strong) !important;
        color: #FFFFFF !important;
      }
    </style>
  
</head>
 <body>

    <?php include('navbar.php') ?>
