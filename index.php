<?php include('includes/header.php'); ?>

<style>
  body {
    background: #FFFFFF url('login_bg.png') no-repeat center center fixed;
    background-size: cover;
    font-family: Arial, sans-serif;
  }

  .home-hero {
    min-height: calc(100vh - 80px);
    display: flex;
    align-items: flex-start;
    padding: 2.5rem 1.5rem;
  }

  .home-hero .container {
    max-width: 100%;
    padding-left: 0;
    padding-right: 0;
  }

  .home-hero .row {
    margin-left: 0;
  }

  .home-hero .col-lg-5,
  .home-hero .col-md-6 {
    padding-left: 0;
  }

  .menu-carousel-card {
    border-radius: 24px;
    overflow: hidden;
    box-shadow: 0 24px 60px rgba(0, 0, 0, 0.35);
    background-color: #000000;
    max-width: 420px;
    transition: transform 0.6s ease, box-shadow 0.6s ease;
    animation: floatCard 8s ease-in-out infinite;
  }

  .menu-carousel-card img {
    width: 100%;
    height: 380px;
    object-fit: cover;
    display: block;
  }

  #menuCarousel .carousel-item img {
    transition: transform 0.8s ease, opacity 0.8s ease;
  }

  #menuCarousel .carousel-item.active img {
    transform: scale(1.03);
    opacity: 1;
  }

  #menuCarousel .carousel-item:not(.active) img {
    opacity: 0.7;
  }

  .menu-carousel-card:hover {
    transform: translateY(-6px) scale(1.01);
    box-shadow: 0 32px 80px rgba(0, 0, 0, 0.45);
  }

  @keyframes floatCard {
    0% {
      transform: translateY(0);
    }
    50% {
      transform: translateY(-10px);
    }
    100% {
      transform: translateY(0);
    }
  }
</style>

<section class="home-hero">
  <div class="container">
    <div class="row">
      <div class="col-lg-5 col-md-6">
        <div class="menu-carousel-card">
          <div id="menuCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-touch="true">
            <div class="carousel-inner">
              <div class="carousel-item active">
                <img src="/HiddenCoreCafe/admin/assets/img/menu1.jpg" alt="Hidden Core Cafe menu 1">
              </div>
              <div class="carousel-item">
                <img src="/HiddenCoreCafe/admin/assets/img/menu2.jpg" alt="Hidden Core Cafe menu 2">
              </div>
              <div class="carousel-item">
                <img src="/HiddenCoreCafe/admin/assets/img/menu3.jpg" alt="Hidden Core Cafe menu 3">
              </div>
              <div class="carousel-item">
                <img src="/HiddenCoreCafe/admin/assets/img/menu4.jpg" alt="Hidden Core Cafe menu 4">
              </div>
              <div class="carousel-item">
                <img src="/HiddenCoreCafe/admin/assets/img/menu5.jpg" alt="Hidden Core Cafe menu 5">
              </div>
              <div class="carousel-item">
                <img src="/HiddenCoreCafe/admin/assets/img/menu6.jpg" alt="Hidden Core Cafe menu 6">
              </div>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#menuCarousel" data-bs-slide="prev">
              <span class="carousel-control-prev-icon" aria-hidden="true"></span>
              <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#menuCarousel" data-bs-slide="next">
              <span class="carousel-control-next-icon" aria-hidden="true"></span>
              <span class="visually-hidden">Next</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<?php include('includes/footer.php'); ?>