<?php include('includes/header.php'); ?>

<style>
  body {
    background: linear-gradient(rgba(247, 241, 235, 0.9), rgba(247, 241, 235, 0.9)), url('login_bg.png') no-repeat center center fixed;
    background-size: cover;
    font-family: "Segoe UI", Arial, sans-serif;
  }

  .home-hero {
    min-height: calc(100vh - 74px);
    display: flex;
    align-items: center;
    padding: 2.5rem 1rem 3rem;
  }

  .home-shell {
    width: 100%;
    max-width: 980px;
    margin: 0 auto;
    display: block;
  }

  .home-main {
    border: 1px solid rgba(15, 23, 42, 0.1);
    border-radius: 30px;
    background: rgba(255, 255, 255, 0.82);
    box-shadow: 0 20px 40px rgba(15, 23, 42, 0.08);
    padding: 2rem;
    backdrop-filter: blur(10px);
  }

  .home-kicker {
    display: inline-block;
    padding: 0.45rem 0.85rem;
    border-radius: 999px;
    font-size: 0.78rem;
    font-weight: 800;
    letter-spacing: 0.09em;
    text-transform: uppercase;
    color: #e76300;
    background: #fff2e7;
    border: 1px solid rgba(255, 122, 26, 0.25);
  }

  .home-title {
    margin: 1rem 0 0;
    color: #0f172a;
    font-size: clamp(2rem, 5vw, 3.4rem);
    line-height: 1;
    font-weight: 900;
    letter-spacing: -0.03em;
  }

  .home-gallery {
    margin-top: 1.35rem;
    border-radius: 22px;
    overflow: hidden;
    box-shadow: 0 16px 32px rgba(15, 23, 42, 0.12);
    background: #f8fafc;
  }

  .home-gallery img {
    width: 100%;
    height: 420px;
    object-fit: contain;
    display: block;
    background: #ffffff;
    padding: 0.5rem;
  }

  .home-gallery .carousel-control-prev,
  .home-gallery .carousel-control-next {
    width: 54px;
    opacity: 1;
  }

  .home-gallery .carousel-control-prev-icon,
  .home-gallery .carousel-control-next-icon {
    width: 40px;
    height: 40px;
    border-radius: 999px;
    background-size: 18px 18px;
    background-color: rgba(15, 23, 42, 0.78);
    box-shadow: 0 8px 20px rgba(15, 23, 42, 0.28);
  }

  .home-gallery .carousel-control-prev:hover .carousel-control-prev-icon,
  .home-gallery .carousel-control-next:hover .carousel-control-next-icon {
    background-color: rgba(231, 99, 0, 0.92);
  }

  @media (max-width: 991.98px) {
    .home-main {
      border-radius: 24px;
    }

    .home-gallery img {
      height: 300px;
    }
  }
</style>

<section class="home-hero">
  <div class="home-shell">
    <div class="home-main">
      <span class="home-kicker">Hidden Core Cafe POS</span>
      <h1 class="home-title">Where taste is a hidden treasure.</h1>

      <div class="home-gallery">
        <div id="menuCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-touch="true">
          <div class="carousel-inner">
            <div class="carousel-item active">
              <img src="/HiddenCoreCafe_POS/admin/assets/img/menu1.jpg" alt="Hidden Core Cafe menu 1">
            </div>
            <div class="carousel-item">
              <img src="/HiddenCoreCafe_POS/admin/assets/img/menu2.jpg" alt="Hidden Core Cafe menu 2">
            </div>
            <div class="carousel-item">
              <img src="/HiddenCoreCafe_POS/admin/assets/img/menu3.jpg" alt="Hidden Core Cafe menu 3">
            </div>
            <div class="carousel-item">
              <img src="/HiddenCoreCafe_POS/admin/assets/img/menu4.jpg" alt="Hidden Core Cafe menu 4">
            </div>
            <div class="carousel-item">
              <img src="/HiddenCoreCafe_POS/admin/assets/img/menu5.jpg" alt="Hidden Core Cafe menu 5">
            </div>
            <div class="carousel-item">
              <img src="/HiddenCoreCafe_POS/admin/assets/img/menu6.jpg" alt="Hidden Core Cafe menu 6">
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
</section>

<?php include('includes/footer.php'); ?>
