<nav class="sb-topnav navbar navbar-expand navbar-light">

    <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle">
        <i class="fas fa-bars"></i>
    </button>

    <a class="navbar-brand ps-3 d-flex align-items-center" href="dashboard.php" style="font-weight: bold;">
        <img src="/HiddenCoreCafe/LOGO.jpg" alt="Hidden Core Logo" style="width: 60px; height: 60px; margin-right: 0px;" />
    </a>

    <ul class="navbar-nav ms-auto">
          <a href="/HiddenCoreCafe/API/api-tester.html" class="btn btn-link ms-3" title="API Tester">
            <i class="fas fa-plug" style="font-size: 1.6rem;"></i>
            </a>
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-user fa-fw"></i>
                <?= $_SESSION['loggedInUser']['username'];?>
            </a>
           
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                <li><a class="dropdown-item" href="../../logout.php">Logout</a></li>
            </ul>
        </li>
    </ul>
</nav>