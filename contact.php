<?php include("auth.php"); ?>
<?php
include("connect.php");
$user_id = $_SESSION['user_id'] ?? 0;
$user_name = "";
$user_email = "";

if ($user_id) {
  $query = mysqli_query($conn, "SELECT name, email FROM users WHERE id = $user_id");
  if ($query && mysqli_num_rows($query) > 0) {
    $u = mysqli_fetch_assoc($query);
    $user_name = htmlspecialchars($u['name']);
    $user_email = htmlspecialchars($u['email']);
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <meta name="description" content="Get in touch with Elegant Salon for bookings, questions, or feedback. We're here to assist you!" />
  <title>Contact Us - Elegant Salon</title>
  <link rel="icon" href="images/logo.png" type="image/png">
  <link rel="stylesheet" href="style.css" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
  <style>
    .contact-section {
      background:rgb(43, 42, 42);
      padding: 60px 20px;
      border-radius: 20px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
    }

    .form-control {
      border-radius: 12px;
    }

    .contact-form h2 {
      font-weight: 600;
    }

    .btn-warning {
      border-radius: 12px;
      font-weight: bold;
    }

    .info-icons i {
      font-size: 1.2rem;
      margin-right: 8px;
    }

    .map-container iframe {
      border-radius: 12px;
    }
    .dark-mode .contact-section
      {
       background:rgb(230, 230, 230);
      padding: 60px 20px;
      border-radius: 20px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
     
    }
    .dark-mode .text{
    color: #f1f1f1 
    }
.dark-mode .contact-form .form-label {
  color: #000 !important;
}

    
    @media (max-width: 768px) {
      .map-container {
        margin-top: 30px;
      }
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

<?php include("header.php"); ?>
<br><br>
<button class="btn btn-dark position-fixed end-0 bottom-0 m-3 rounded-circle shadow" id="darkToggle">
  <i class="bi bi-moon-stars-fill"></i>
</button>
<h1 class="text-center text-warning mb-3 my-5" data-aos="fade-down">Contact US</h1>
<!-- Contact Section -->
<section class="container contact-section my-5" data-aos="fade-up" data-aos-duration="1000"> 
  <div class="row gy-5 align-items-center" >
    <!-- Contact Form -->
    <div class="col-md-6 contact-form" data-aos="flip-right">
      <h2 class="mb-4 text-warning text-center">Get in Touch</h2>
      <form method="post" action="contact-message.php">
        <div class="mb-3">
          <label for="name" class="form-label text-light">Full Name</label>
          <input type="text" class="form-control" id="name" name="name"
       value="<?= $user_name ?>" placeholder="Your name"
       oninput="this.value=this.value.replace(/[^A-Za-z\s]/g,'')"
       required maxlength="30">
        </div>
        <div class="mb-3">
          <label for="email" class="form-label text-light">Email Address</label> 
      <input type="email" class="form-control" id="email" name="email"
          value="<?= $user_email ?>" readonly>
        </div>
        <div class="mb-3">
          <label for="message" class="form-label text-light">Your Message</label>
          <textarea class="form-control" id="message" name="message" rows="5" placeholder="Write your message here..." required maxlength="450" minlength="10"></textarea>
        </div>
        <button type="submit" name="save" class="btn btn-warning w-100 text-dark">Send Message</button>
      </form>
    </div>

    <!-- Google Map -->
    <div class="col-md-6 map-container" data-aos="flip-left">
      <div class="ratio ratio-4x3">
        <iframe 
          src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d1510.6897313428983!2d67.0747063289006!3d24.863880101002895!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3eb33ea3db108f41%3A0x42acc4507358b160!2sAptech%20Learning%2C%20Shahrah%20e%20Faisal%20Center!5e1!3m2!1sen!2s!4v1749849213079!5m2!1sen!2s" 
          allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade">
        </iframe>
      </div>
    </div>
  </div>
</section>

<!-- Contact Info -->
<section class="container my-5 text-center" data-aos="zoom-in" data-aos-duration="1000">
  <h2 class="mb-3 text-warning">Salon Location</h2>
  <p class="text"><i class="bi bi-geo-alt-fill text-warning info-icons text"></i> Main Street, Lahore, Pakistan</p>
 <p class="text"><i class="bi bi-telephone-fill text-warning info-icons text"></i> +92-XXX-XXXXXXX</p>
 <p class="text"><i class="bi bi-envelope-fill text-warning info-icons text"></i> info@elegantsalon.com</p>

  <h5 class="mt-4">Working Hours</h5>
  <ul class="list-unstyled">
    <li>Mon - Fri: 10am - 8pm</li>
    <li>Sat - Sun: 11am - 9pm</li>
  </ul>

  <h5 class="mt-4">Follow Us</h5>
  <a href="https://www.facebook.com" target="_blank" class="text-warning me-3 fs-5"><i class="bi bi-facebook"></i></a>
  <a href="https://www.instagram.com" target="_blank" class="text-warning me-3 fs-5"><i class="bi bi-instagram"></i></a>
  <a href="#" target="_blank" class="text-warning fs-5"><i class="bi bi-whatsapp"></i></a>
</section>

<?php include("footer.php"); ?>

<!-- AOS Animation JS -->
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
AOS.init(
  { duration: 1000 }
);
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
