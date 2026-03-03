<nav class="navbar navbar-expand-lg bg-light shadow">
  <div class="container">
    
    <a class="navbar-brand" href="/HiddenCoreCafe/admin/assets/dashboard.php" style="font-weight: bold;">
      <img src="/HiddenCoreCafe/LOGO.jpg" alt="Hidden Core Logo" style="height: 40px; width: auto;" />
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link active" href="index.php">Home</a>
        </li>
        
        <?php if(isset($_SESSION['loggedIn'])) : ?>
        <li class="nav-item">
          <a class="nav-link" href="#"><?= $_SESSION['loggedInUser']['username'];?></a>
        </li>
        <li class="nav-item">
          <a class="btn btn-primary" href="logout.php">
            Logout
          </a>
        </li>
        <?php else: ?>
        <li class="nav-item">
          <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#loginModal">Login</a>
        </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>