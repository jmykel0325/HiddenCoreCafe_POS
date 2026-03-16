<nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
    <?php
        $isStaff = (($_SESSION['loggedInUser']['role'] ?? '') === 'staff');
        $homeLink = $isStaff ? 'orders-create.php' : 'dashboard.php';
    ?>
    <!-- Navbar Brand with bold text and logo -->
    <a class="navbar-brand ps-3 d-flex align-items-center" href="<?= $homeLink ?>">
        <img src="/HiddenCoreCafe_POS/LOGO.jpg" alt="Hidden Core Logo" width="30" height="30" class="d-inline-block align-text-top me-2">
        <span class="fw-bold">HIDDEN CORE</span>
    </a>
    <!-- Spacer to push user dropdown to the right -->
    <div class="ms-auto"></div>
    <!-- Navbar-->
    <ul class="navbar-nav ms-auto ms-md-0 me-3 me-lg-4">
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle d-flex align-items-center" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-user-circle fa-lg me-2"></i>
                <?= $_SESSION['loggedInUser']['username']; ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                <li><a class="dropdown-item" href="../../logout.php">Logout</a></li>
            </ul>
        </li>
    </ul>
</nav>
