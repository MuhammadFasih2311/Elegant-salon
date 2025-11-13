<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$current_page = basename($_SERVER['PHP_SELF']);
$bookCount = 0;
if (isset($conn)) {
    $res = $conn->query("SELECT COUNT(*) as cnt FROM bookings WHERE status='pending'");
    if($res) {
        $row = $res->fetch_assoc();
        $bookCount = $row['cnt'];
    }
}
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow">
  <div class="container">
    <a class="navbar-brand d-flex" href="admin_dashboard.php" data-aos="fade-right">
      <img src="images/logo.png" alt="Logo" width="80" class="me-2">
      <span class="logo text-white fw-bold fs-4">Salon <span class="text-warning">Admin</span></span>
    </a>

    <!-- Toggler -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <div class="animated-toggler">
        <span class="bar"></span>
        <span class="bar"></span>
        <span class="bar"></span>
      </div>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item" data-aos="fade-down" data-aos-delay="50">
          <a class="nav-link <?= $current_page == 'manage-services.php' ? 'active text-warning fw-bold' : '' ?>" href="manage-services.php">🛠️ Services</a>
        </li>
        <li class="nav-item" data-aos="fade-down" data-aos-delay="100">
          <a class="nav-link <?= $current_page == 'admin-messages.php' ? 'active text-warning fw-bold' : '' ?>" href="admin-messages.php">📩 Messages</a>
        </li>
        <li class="nav-item" data-aos="fade-down" data-aos-delay="150">
          <a class="nav-link <?= $current_page == 'admin_dashboard.php' ? 'active text-warning fw-bold' : '' ?>" href="admin_dashboard.php">🧴 Products</a>
        </li>
        <li class="nav-item" data-aos="fade-down" data-aos-delay="200">
          <a class="nav-link d-inline-flex align-items-center gap-1 <?= $current_page == 'booking.php' ? 'active text-warning fw-bold' : '' ?>" href="booking.php">
            📅 Bookings
            <span class="badge rounded-pill bg-danger ms-1" style="<?= $bookCount > 0 ? '' : 'display:none;' ?>"><?= $bookCount ?></span>
          </a>
        </li>

        <?php if (isset($_SESSION['admin_logged_in'])): ?>
        <li class="nav-item dropdown" data-aos="fade-down" data-aos-delay="250">
          <a class="nav-link dropdown-toggle text-warning" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            👋 Hello, Admin
          </a>
          <ul class="dropdown-menu dropdown-menu-end animate__animated animate__fadeInDown bg-dark">
            <li>
              <a class="dropdown-item text-light <?= $current_page == 'reports.php' ? 'active text-warning fw-bold' : '' ?>" href="reports.php">
                📊 Reports
              </a>
            </li>
            <li>
              <a class="dropdown-item text-light <?= $current_page == 'manual_booking.php' ? 'active text-warning fw-bold' : '' ?>" href="manual_booking.php">
                <i class="bi bi-calendar-plus text-success me-1"></i> Add Booking
              </a>
            </li>
            <li><hr class="dropdown-divider bg-warning"></li>
            <li>
              <a class="dropdown-item text-light" href="admin-logout.php">
                <i class="bi bi-box-arrow-right me-2"></i> Logout
              </a>
            </li>
          </ul>
        </li>
        <?php else: ?>
        <li class="nav-item">
          <a class="btn btn-sm btn-warning ms-2" href="admin-login.php">
            <i class="bi bi-box-arrow-in-right me-1"></i> Login
          </a>
        </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<style>
/* Dropdown hover & active */
.dropdown-menu .dropdown-item {
  color: #f8f9fa;
  transition: all 0.3s ease;
  border-radius: 4px;
}
.dropdown-menu .dropdown-item:hover {
  background-color: #ffc107;
  color: #000 !important;
  transform: translateX(4px);
}
.dropdown-item.active {
  background-color: #ffc107 !important;
  color: #000 !important;
  font-weight: 600;
}

/* Navbar base */
.nav-link.active,
.dropdown-item.active {
  color: #f8f7f3ec !important;
  font-weight: 600;
  border-bottom: 2px solid #ffc107;
}
.nav-link:hover,
.dropdown-item:hover {
  color: #ffc107 !important;
}
.animated-toggler {
  display: flex; flex-direction: column; justify-content: space-between;
  width: 24px; height: 18px;
}
.animated-toggler .bar {
  height: 2px; width: 100%; background: #fff; transition: all 0.3s ease;
}
.navbar-toggler[aria-expanded="true"] .bar:nth-child(1) { transform: translateY(8px) rotate(45deg); }
.navbar-toggler[aria-expanded="true"] .bar:nth-child(2) { opacity: 0; }
.navbar-toggler[aria-expanded="true"] .bar:nth-child(3) { transform: translateY(-8px) rotate(-45deg); }
nav.navbar.scrolled { background-color: rgba(0,0,0,0.95)!important; box-shadow: 0 5px 20px rgba(0,0,0,0.3); }
</style>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
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
  // ✅ Active link highlighter
document.addEventListener("DOMContentLoaded", () => {
  const current = window.location.pathname.split("/").pop();
  document.querySelectorAll(".navbar-nav .nav-link").forEach(link => {
    const linkPage = link.getAttribute("href");
    if (linkPage === current) {
      link.classList.add("active", "text-warning");
    } else {
      link.classList.remove("active", "text-warning");
    }
  });
});
// ✅ Live pending booking counter update
document.addEventListener("DOMContentLoaded", () => {
  const badge = document.querySelector(".nav-link[href='booking.php'] .badge");

  async function updateBookingCount() {
    try {
      const res = await fetch("get-pending-count.php");
      const data = await res.json();
      if (!badge) return;

      if (data.count > 0) {
        badge.textContent = data.count;
        badge.style.display = "inline";
      } else {
        badge.style.display = "none";
      }
    } catch (err) {
      console.error("Error fetching booking count:", err);
    }
  }

  // 🔁 Har 10 sec me auto-update
  updateBookingCount();
  setInterval(updateBookingCount, 1000);
});

</script>
