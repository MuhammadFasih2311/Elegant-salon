<?php include("auth.php"); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Explore our wide range of beauty, hair, and skincare services tailored to your needs at Elegant Salon." />
  <title>Elegant Salon</title>
  <link rel="icon" href="images/logo.png" type="image/png">
  <link rel="stylesheet" href="style.css" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  
<!-- AOS CSS -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" rel="stylesheet">
<!-- Font Awesome for Icons (or use Bootstrap Icons if preferred) -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

</head>
<body>
  <?php
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit();
}
include("header.php");
?>
<br>
<button class="btn btn-dark position-fixed end-0 bottom-0 m-3 rounded-circle shadow" id="darkToggle">
  <i class="bi bi-moon-stars-fill"></i>
</button>
<br>
 <div class="container">
  <h1 class="text-center text-warning mb-4 mt-5" data-aos="fade-down">OUR SERVICES</h1>
  <div class="row g-4 px-3" id="services-container">
    <?php
    include("connect.php");
    $sql = "SELECT * FROM services";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0):
      $index = 0;
      while ($row = mysqli_fetch_assoc($result)):
        $delay = ($index % 3) * 100;
    ?>
      <div class="col-md-6 col-sm-12 col-lg-4" data-aos="fade-up" data-aos-delay="<?= $delay ?>" data-aos-duration="1000">
        <div class="card h-100 border-0 shadow-sm bg-dark text-warning">
          <img src="<?= htmlspecialchars($row['image']) ?>" class="card-img-top rounded-top img-fixed" alt="<?= htmlspecialchars($row['title']) ?>">
          <div class="card-body text-center">
            <h5 class="card-title"><i class="<?= htmlspecialchars($row['icon']) ?> me-2"></i><?= htmlspecialchars($row['title']) ?></h5>
            <p class="card-text text-white small"><?= htmlspecialchars($row['description']) ?></p>
            <p class="text-light mb-2"><strong>Starting from <?= htmlspecialchars($row['price']) ?></strong></p>
            <a href="gallery.php?category=<?= urlencode($row['title']) ?>#booking" 
            class="btn btn-warning btn-sm">Book Now</a>
          </div>
        </div>
      </div>
    <?php
        $index++;
      endwhile;
    else:
      echo "<p class='text-white'>No services found.</p>";
    endif;

    mysqli_close($conn);
    ?>
  </div> 
</div>



<br>
  <?php
  include("footer.php");
  ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
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
