<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$current_page = basename($_SERVER['PHP_SELF']); // ✅ detect current file
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top shadow-sm px-3" style="transition: all 0.4s ease;">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center" href="index.php" data-aos="fade-right">
      <img src="images/logo.png" alt="Logo" width="80" class="me-2">
      <span class="logo text-white fw-bold fs-4">Elegant <span class="text-warning">Salon</span></span>
    </a>

    <!-- Hamburger icon -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" data-aos="fade-down">
      <span class="animated-toggler">
        <span class="bar"></span>
        <span class="bar"></span>
        <span class="bar"></span>
      </span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item" data-aos="fade-down" data-aos-delay="50">
          <a class="nav-link <?= $current_page == 'index.php' ? 'active text-warning fw-bold' : '' ?>" href="index.php">Home</a>
        </li>
        <li class="nav-item" data-aos="fade-down" data-aos-delay="100">
          <a class="nav-link <?= $current_page == 'about.php' ? 'active text-warning fw-bold' : '' ?>" href="about.php">About</a>
        </li>
        <li class="nav-item" data-aos="fade-down" data-aos-delay="150">
          <a class="nav-link <?= $current_page == 'gallery.php' ? 'active text-warning fw-bold' : '' ?>" href="gallery.php">Gallery</a>
        </li>
        <li class="nav-item" data-aos="fade-down" data-aos-delay="200">
          <a class="nav-link <?= $current_page == 'services.php' ? 'active text-warning fw-bold' : '' ?>" href="services.php">Services</a>
        </li>
        <li class="nav-item" data-aos="fade-down" data-aos-delay="250">
          <a class="nav-link <?= $current_page == 'contact.php' ? 'active text-warning fw-bold' : '' ?>" href="contact.php">Contact</a>
        </li>

        <?php if (isset($_SESSION['user_id'])): ?>
        <li class="nav-item dropdown" data-aos="fade-down" data-aos-delay="300">
          <a class="nav-link dropdown-toggle text-warning" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            👋 Welcome, <?= htmlspecialchars($_SESSION['user_name'] ?? $_SESSION['name'] ?? 'User'); ?>
          </a>
          <ul class="dropdown-menu dropdown-menu-end animate__animated animate__fadeInDown bg-dark">
            <li>
              <a class="dropdown-item text-light <?= $current_page == 'profile.php' ? 'active text-warning fw-bold' : '' ?>" href="profile.php">
                Profile
              </a>
            </li>
            <li>
              <a class="dropdown-item text-light <?= $current_page == 'my-bookings.php' ? 'active text-warning fw-bold' : '' ?>" href="my-bookings.php">
                My Bookings
              </a>
            </li>
            <li>
              <a class="dropdown-item text-light <?= $current_page == 'my-messages.php' ? 'active text-warning fw-bold' : '' ?>" href="my-messages.php">
                My Messages
              </a>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-light" href="logout.php">Logout</a></li>
          </ul>
        </li>
        <?php else: ?>
        <li class="nav-item" data-aos="fade-down" data-aos-delay="350">
          <a class="nav-link <?= $current_page == 'login.php' ? 'active text-warning fw-bold' : '' ?>" href="login.php">Login</a>
        </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<style>
/* Toggler animation */
.animated-toggler {
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  width: 24px;
  height: 18px;
}
.animated-toggler .bar {
  height: 2px;
  width: 100%;
  background: #fff;
  transition: all 0.3s ease;
}
.navbar-toggler[aria-expanded="true"] .bar:nth-child(1) {
  transform: translateY(8px) rotate(45deg);
}
.navbar-toggler[aria-expanded="true"] .bar:nth-child(2) {
  opacity: 0;
}
.navbar-toggler[aria-expanded="true"] .bar:nth-child(3) {
  transform: translateY(-8px) rotate(-45deg);
}

/* Scroll effect */
nav.navbar.scrolled {
  background-color: rgba(0, 0, 0, 0.95) !important;
  box-shadow: 0 5px 20px rgba(0, 0, 0, 0.3);
}

/* Active link styling */
.nav-link.active,
.dropdown-item.active {
  color: #ffc107 !important;
  font-weight: 600;
  border-bottom: 2px solid #ffc107;
}

/* On hover */
.nav-link:hover,
.dropdown-item:hover {
  color: #ffc107 !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const navbar = document.querySelector('nav.navbar');
  const toggler = document.querySelector('.navbar-toggler');
  const collapse = document.getElementById('navbarNav');

  window.addEventListener('scroll', function () {
    if (window.scrollY > 50) {
      navbar.classList.add('scrolled');
    } else {
      navbar.classList.remove('scrolled');
    }
  });

  collapse.addEventListener('show.bs.collapse', function () {
    toggler.classList.add('open');
  });

  collapse.addEventListener('hide.bs.collapse', function () {
    toggler.classList.remove('open');
  });
});
</script>
