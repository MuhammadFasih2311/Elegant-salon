<?php include("auth.php"); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <meta name="description" content="Learn more about Elegant Salon's mission, team, and high-quality beauty services that make us unique." />
  <title>About Us - Elegant Salon</title>
  <link rel="icon" href="images/logo.png" type="image/png">
  <link rel="stylesheet" href="style.css"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- AOS Animation CSS -->
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
<style>
  #darkToggle {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 9999;
  }
  .dark-mode {
  background-color: #121212 !important;
  color: #ffffff !important;
}

.dark-mode .bg-light {
  background-color: #1e1e1e !important;
}

.dark-mode .text-dark {
  color: #ffffff !important;
}
.dark-mode .text {
  color: #ffffff !important;
}
.dark-mode .carousel-inner .border-dark {
  border-color: #ffc107 !important; 
}

</style>

</head>
<body>
  <?php
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit();
}
?>

<!-- Navbar -->
<?php
include("header.php");
?>

<button class="btn btn-dark position-fixed end-0 bottom-0 m-3 rounded-circle shadow" id="darkToggle">
<i class="bi bi-moon-stars-fill"></i>
</button>
  <br><br>
  <!-- About Section -->
  <section class="container my-5">
     <h1 class="text-center text-warning mb-3" data-aos="fade-down">ABOUT US</h1>
    <div class="row align-items-center" data-aos-duration="1200">
      <div class="col-md-6" data-aos="fade-right">
        <h1 class="text-warning">About Elegant Salon</h1>
        <p class="text">Elegant Salon has been serving the community since 2010. Our team of professional stylists and beauty experts are dedicated to making you look and feel your best. </p>
        <p class="text">We believe beauty should be accessible, relaxing, and personalized for every client.</p>
      </div>
      <div class="col-md-6 mt-5" data-aos="fade-left">
        <img src="images/about.jpg" alt="Our Salon" class="w-100 rounded-4 shadow" style="min-height: 350px; object-fit: cover;">
      </div>
    </div>
  </section>

   
  <!-- Our Team -->
<section class="py-5 text-white position-relative" style="background: url('images/background.jpg') center center/cover no-repeat;" data-aos="fade-up" data-aos-duration="1200">
  <!-- Overlay -->
  <div class="position-absolute top-0 start-0 w-100 h-100" style="background-color: rgba(0, 0, 0, 0.4); z-index: 0;"></div>

  <!-- Content -->
  <div class="container text-center position-relative" style="z-index: 1;">
    <h3 class="mb-4">Meet Our Stylists</h3>
    <div class="row">
      <div class="col-md-4" data-aos="zoom-in" data-aos-delay="200">
        <img src="images/worker1.jpg" class="rounded-circle mb-2" width="120">
        <h6>Fatima Ali</h6>
        <p class="small text-light">Hair Specialist</p>
      </div>
      <div class="col-md-4" data-aos="zoom-in" data-aos-delay="400">
        <img src="images/worker3.jpg" class="rounded-circle mb-2" width="120">
        <h6>Adnan Malik</h6>
        <p class="small text-light">Makeup Artist</p>
      </div>
      <div class="col-md-4" data-aos="zoom-in" data-aos-delay="600">
        <img src="images/worker2.jpg" class="rounded-circle mb-2" width="120">
        <h6>Ali Hussain</h6>
        <p class="small text-light">Nail Expert</p>
      </div>
    </div>
  </div>
</section>

<br>
<section class="container my-5" data-aos="fade-up" data-aos-duration="1200">
    <h2 class="text-center mb-4 text-warning">What Our Clients Say</h2>
    <div id="testimonialCarousel" class="carousel slide" data-bs-ride="carousel">
      <div class="carousel-inner">
        <div class="carousel-item active">
          <div class="p-5 bg-light border border-dark shadow-sm rounded text-center">
            <p class="fst-italic text-dark">"Best salon experience I've ever had!"</p>
            <footer class="blockquote-footer text-dark">Sara Malik</footer>
          </div>
        </div>
        <div class="carousel-item">
          <div class="p-5 bg-light border border-dark shadow-sm rounded text-center">
            <p class="fst-italic text-dark">"Amazing service and friendly staff."</p>
            <footer class="blockquote-footer text-dark">Adeel Khan</footer>
          </div>
        </div>
        <div class="carousel-item">
          <div class="p-5 bg-light border border-dark shadow-sm rounded text-center">
            <p class="fst-italic text-dark">"They understood exactly what I wanted — 10/10!"</p>
            <footer class="blockquote-footer text-dark">Hina Sohail</footer>
          </div>
        </div>
      </div>
      <button class="carousel-control-prev" type="button" data-bs-target="#testimonialCarousel" data-bs-slide="prev">
        <span class="carousel-control-prev-icon bg-dark rounded-circle" aria-hidden="true"></span>
        <span class="visually-hidden">Previous</span>
      </button>
      <button class="carousel-control-next" type="button" data-bs-target="#testimonialCarousel" data-bs-slide="next">
        <span class="carousel-control-next-icon bg-dark rounded-circle" aria-hidden="true"></span>
        <span class="visually-hidden">Next</span>
      </button>
    </div>
  </section>
<br>
<section class="bg-warning text-dark text-center py-4" data-aos="zoom-in" data-aos-duration="1000">
  <div class="container">
    <h4 class="mb-3 text-dark">Ready to look your best?</h4>
    <a href="gallery.php#booking" class="btn btn-dark text-lig">Book an Appointment</a>
  </div>
</section>

<br>
  <!-- Footer -->
  <?php
  include("footer.php");
  ?>
<!-- AOS Animation JS -->
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
   AOS.init({
    duration: 1000 
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
