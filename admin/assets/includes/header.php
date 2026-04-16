<?php

require '../../config/function.php';
require 'authentication.php';

?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Hidden Core Cafe</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Poppins:wght@500;600;700;800&display=swap" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />

        <link href="css/styles.css" rel="stylesheet" />

        <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>

        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.14.0/build/css/alertify.min.css"/>
        <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.14.0/build/css/themes/default.min.css"/>
        <link href="css/custom.css" rel="stylesheet" />
        <link href="css/hc-admin-theme.css" rel="stylesheet" />
        <link href="css/hc-premium-dark.css" rel="stylesheet" />
</head>
<body class="sb-nav-fixed hc-admin">
    <button class="btn hc-mobile-sidebar-toggle d-lg-none" id="sidebarToggle" type="button" aria-label="Toggle sidebar">
        <i class="fas fa-bars"></i>
    </button>

    <div id="layoutSidenav">

        <?php include('sidebar.php') ?>

          <div id="layoutSidenav_content">

            <main>                

