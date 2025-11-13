<?php
session_start();
include("connect.php");

// Agar session khatam hai lekin remember cookies maujood hain
if (!isset($_SESSION['user_id']) && isset($_COOKIE['user_email'], $_COOKIE['remember_token'])) {
    $email = $_COOKIE['user_email'];
    $rawToken = $_COOKIE['remember_token'];

    $stmt = $conn->prepare("SELECT id, name, email, remember_token FROM users WHERE email=? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // verify raw cookie token with hashed token in DB
        if (!empty($user['remember_token']) && password_verify($rawToken, $user['remember_token'])) {
            // login success -> set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['email'] = $user['email'];

            // rotate token (better security)
            $newRaw = bin2hex(random_bytes(32));
            $newHash = password_hash($newRaw, PASSWORD_DEFAULT);

            $up = $conn->prepare("UPDATE users SET remember_token=? WHERE id=?");
            $up->bind_param("si", $newHash, $user['id']);
            $up->execute();

            // update cookies
            $expire = time() + (86400 * 30);
            setcookie("remember_token", $newRaw, $expire, "/", "", isset($_SERVER['HTTPS']), true);
            setcookie("user_email", $user['email'], $expire, "/", "", isset($_SERVER['HTTPS']), true);
        } else {
            // invalid token -> clear cookies
            setcookie("remember_token", "", time() - 3600, "/");
            setcookie("user_email", "", time() - 3600, "/");
        }
    }
}

// agar abhi bhi session nahi bana to redirect login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<?php include("auth.php"); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Elegant Salon offers top-notch hair, beauty, and skincare services in a luxurious setting. Book your appointment today!" />
  <title>Elegant Salon</title>
  <link rel="icon" href="images/logo.png" type="image/png">
  <link rel="stylesheet" href="style.css?v=<?= time(); ?>">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <!-- Swiper CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
<!-- Swiper JS -->
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

  <!-- AOS Animation CSS -->
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
<style>
  .swiper-pagination-bullet {
  width: 12px;
  height: 12px;
  background-color: #fff;
  opacity: 0.6;
  margin: 0 6px !important;
  transition: all 0.3s ease;
  border-radius: 50%;
}

.swiper-pagination-bullet-active {
  background-color: #ffc107; /* Bootstrap warning color */
  opacity: 1;
  transform: scale(1.2);
}
</style>
</head>
<body>
  <?php
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit();
}
include("header.php");
?>

<button class="btn btn-dark position-fixed end-0 bottom-0 m-3 rounded-circle shadow" id="darkToggle">
  <i class="bi bi-moon-stars-fill"></i>
</button>

  <!-- HERO SECTION  -->
<section class="position-relative hero-section" style="height: 100vh; overflow: hidden;" data-aos="fade-zoom-in" data-aos-easing="ease-in-back">

<!-- Swiper Pagination (built-in indicators) -->
<div class="swiper-pagination position-absolute bottom-0 start-50 translate-middle-x mb-3"></div>

  <!-- Swiper Container -->
  <div class="swiper hero-swiper w-100 h-100">
    <div class="swiper-wrapper">
      <div class="swiper-slide">
        <img src="images/slider2.jpg" alt="Slide 1">
      </div>
      <div class="swiper-slide">
        <img src="images\slider1 (1).jpg" alt="Slide 2">
      </div>
      <div class="swiper-slide">
        <img src="images/slider3.jpg" alt="Slide 3">
      </div>
    </div>
    <!-- Swiper Pagination -->
    <div class="swiper-pagination"></div>

    <!-- Overlay Text -->
    <div class="hero-overlay d-flex justify-content-center align-items-center text-white text-center">
      <div>
        <h1 class="display-4 fw-bold" style="text-shadow: 2px 2px 5px #000;">Welcome to Elegant Salon</h1>
        <p class="lead text-light" style="text-shadow: 1px 1px 3px #000;">Where beauty meets perfection</p>
        <a href="gallery.php#booking" class="btn btn-outline-warning btn-lg mt-3">Book Now</a>
      </div>
    </div>
  </div>
</section>

<!-- hero section -->
  <section class="container my-5">
  <div class="row align-items-center">
    <!-- IMAGE COLUMN -->
    <div class="col-lg-6 col-md-6 mb-4 mb-md-0" data-aos="fade-right">
      <img src="images/salon1.jpg" class="w-100 rounded-4 shadow" style="min-height: 350px; object-fit: cover;" alt="Salon" />
    </div>

    <!-- TEXT COLUMN -->
    <div class="col-lg-6 col-md-6 text-center" data-aos="fade-left">
      <h1 class="text-warning mb-4">Our Mission</h1>
      <p class="text">
        Welcome to Elegant Salon — where beauty meets perfection. We offer premium salon services in a serene, modern space designed to make you feel pampered, confident, and effortlessly elegant. Experience expert care, luxury treatments, and a touch of glamour — because you deserve nothing less.
      </p>
      <a href="gallery.php#booking" class="btn btn-warning text-dark mt-3 px-4 py-2 fw-semibold shadow-sm d-inline-flex align-items-center">
        <i class="bi bi-calendar-check me-2"></i> Book an Appointment
      </a>
    </div>
  </div>
</section>


 <section class=" text-dark py-5 why" data-aos="zoom-in" data-aos-duration="1000">
  <div class="container text-center">
    <h2 class="text-light mb-4 display-6 fw-bold">Why Choose Us?</h2>
    <div class="row g-4 mt-3">
      <div class="col-md-4" data-aos="fade-up">
        <div class="p-4 bg-white shadow-sm rounded-4 h-100">
          <i class="bi bi-people-fill fs-1 text-warning"></i>
          <h5 class="fw-bold mt-3">Experienced Team</h5>
          <p class="text-muted">Over 10 years in the beauty industry delivering top-tier services with passion and care.</p>
        </div>
      </div>
      <div class="col-md-4" data-aos="fade-up">
        <div class="p-4 bg-white shadow-sm rounded-4 h-100">
          <i class="bi bi-stars fs-1 text-warning"></i>
          <h5 class="fw-bold mt-3">Premium Products</h5>
          <p class="text-muted">Only the finest brands and natural ingredients for your skin and hair.</p>
        </div>
      </div>
      <div class="col-md-4" data-aos="fade-up">
        <div class="p-4 bg-white shadow-sm rounded-4 h-100">
          <i class="bi bi-house-heart-fill fs-1 text-warning"></i>
          <h5 class="fw-bold mt-3">Relaxing Ambience</h5>
          <p class="text-muted">Modern, peaceful environment designed to offer complete comfort and rejuvenation.</p>
        </div>
      </div>
    </div>
  </div>
</section>


  <section class="py-5 gradient-animated-bg text-dark" id="about-feedback">
  <div class="container">
    <h2 class="text-center fw-bold mb-5" data-aos="fade-up" style="color:rgb(255, 198, 11);">💖 What People Love About Elegant Salon</h2>
    
    <div class="row g-4">
      <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
        <div class="card shadow border-0 rounded-4 p-4 h-100 hover-white-text">
          <h5 class="fw-bold">✨ Friendly Team</h5>
          <p class="mb-0">“They treat you like royalty — warm, kind, and super professional!”</p>
        </div>
      </div>
      <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
        <div class="card shadow border-0 rounded-4 p-4 h-100 hover-white-text">
          <h5 class="fw-bold">🧖‍♀️ Relaxing Environment</h5>
          <p class="mb-0">“Loved the vibe, soft music, and cozy setup. Totally worth it.”</p>
        </div>
      </div>
      <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
        <div class="card shadow border-0 rounded-4 p-4 h-100 hover-white-text">
          <h5 class="fw-bold">💅 Quality Services</h5>
          <p class="mb-0">“Their facials and hair services are absolutely the best in town!”</p>
        </div>
      </div>
    </div>
  </div>
</section>


<br>

  <?php
  include("footer.php");
  ?>

<!-- AOS Animation JS -->
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
  AOS.init({ duration: 1000 });

  const swiper = new Swiper('.hero-swiper', {
    loop: true,
    autoplay: {
      delay: 4000,
      disableOnInteraction: false
    },
      grabCursor: true,
  pagination: {
    el: '.swiper-pagination',
    clickable: true
  }
  });

 // Check on page load
  if (localStorage.getItem("dark-mode") === "enabled") {
    document.body.classList.add("dark-mode");
    document.getElementById('darkToggle').classList.add("btn-light");
    document.getElementById('darkToggle').classList.remove("btn-dark");
  }

  // Toggle dark mode and save to localStorage
  document.getElementById('darkToggle').addEventListener('click', function () {
    document.body.classList.toggle('dark-mode');
    this.classList.toggle('btn-light');
    this.classList.toggle('btn-dark');

    // Save preference
    if (document.body.classList.contains("dark-mode")) {
      localStorage.setItem("dark-mode", "enabled");
    } else {
      localStorage.setItem("dark-mode", "disabled");
    }
  });


</script>

</body>
</html>
