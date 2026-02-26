<?php
require_once __DIR__ . '/../config/function.php';
?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kopikuys</title>

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
    </style>
  
</head>
 <body>

    <?php include('navbar.php') ?>