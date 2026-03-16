<nav class="navbar navbar-expand-lg shadow-sm">
  <div class="container">
    
    <a class="navbar-brand d-flex align-items-center gap-3" href="/HiddenCoreCafe_POS/index.php" style="font-weight: 700; color: #0f172a;">
      <img src="/HiddenCoreCafe_POS/LOGO.jpg" alt="Hidden Core Logo" style="height: 40px; width: auto; border-radius: 12px;" />
      <span>Hidden Core Cafe</span>
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
          <a class="nav-link" href="login.php">Login</a>
        </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
