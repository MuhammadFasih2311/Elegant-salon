<?php include("auth-check.php");  ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <meta name="description" content="Reports page for Elegant Salon admin." />
  <title>Reports - Elegant Salon</title>
  <link rel="icon" href="images/logo.png" type="image/png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <style>
    html, body {
  overflow-x: hidden;
}
    body { 
      background: linear-gradient(135deg, #f8f9fa, #e9ecef); 
      font-family:'Segoe UI',sans-serif; 
      min-height:100vh; 
      display:flex;
      flex-direction:column;
    }
    .content-wrapper {
      flex:1; 
      display:flex; 
      justify-content:center; 
      align-items:center; 
      padding:2rem 1rem;
    }
    .reports-card { 
      background:#fff; 
      border-radius:20px; 
      padding:40px 30px; 
      box-shadow:0 8px 20px rgba(0,0,0,0.1); 
      width:100%; 
      max-width:500px;
      text-align:center;
    }
    .report-link {
      display:block;
      background:#f8f9fa;
      border-radius:12px;
      padding:15px;
      margin-bottom:15px;
      text-decoration:none;
      color:#000;
      font-weight:500;
      transition:0.3s;
    }
    .report-link:hover {
      background:#ffc107;
      color:#000;
    }
    .btn-warning { border-radius:15px; font-weight:500 }

    @media (max-width:576px){ 
      .reports-card { padding:30px 20px } 
    }
    /* Dropdown hover fix */
.dropdown-menu {
  background-color: #222; /* dark background for dropdown */
  border: none;
}
/* Dropdown menu item hover */
.dropdown-menu .dropdown-item {
  color: #333;
  transition: all 0.3s ease;
  border-radius: 4px;
}

.dropdown-menu .dropdown-item:hover {
  background-color: #ffc107;
  color: #000 !important;
  transform: translateX(4px);
}

/* Mobile fix - make dropdown full width on small screens */
@media (max-width: 768px) {
  .dropdown-menu {
    min-width: 100%;
    border: none;
    box-shadow: none;
  }

  .dropdown-menu .dropdown-item {
    padding: 0.75rem 1.25rem;
    font-size: 1rem;
  }
}
 /* Dark mode */
    body.dark-mode { background:#121212; color:#fff; }
    .dark-mode .reports-card { background:#1c1c1c; color:#fff; }
    .dark-mode .report-link { background:#2a2a2a; color:#fff; }
    .dark-mode .report-link:hover { background:#ffc107; color:#000; }
      .dark-mode .text-muted{ color:white!important; }
  </style>
</head>
<body class="d-flex flex-column min-vh-100">

<?php include("navbar.php"); ?>

<div class="content-wrapper">
  <div class="reports-card">
    <div class="mb-4" data-aos="fade-down">
      <img src="../images/logo.png" alt="Elegant Salon" width="70">
      <h3 class="text-warning mt-2">📊 Reports</h3>
      <p class="text-muted small">View daily, monthly, yearly reports or add manual booking</p>
    </div>

    <a href="report-day.php" class="report-link" data-aos="zoom-in"><i class="bi bi-calendar-day me-2"></i> Day Report</a>
    <a href="report-month.php" class="report-link my-3" data-aos="zoom-in" data-aos-delay="100"><i class="bi bi-calendar-month me-2"></i> Month Report</a>
    <a href="report-year.php" class="report-link" data-aos="zoom-in" data-aos-delay="200"><i class="bi bi-calendar3 me-2"></i> Year Report</a>

    <div class="mt-3" data-aos="fade-up">
      <a href="manual_booking.php" class="btn btn-warning mt-3">
        <i class="bi bi-plus-circle"></i> Add Manual Booking
      </a>
    </div>
  </div>
</div>

<?php include("foot.php"); ?>

<!-- Dark Mode Toggle -->
<button class="btn btn-dark position-fixed end-0 bottom-0 m-3 rounded-circle shadow z-3" 
        id="darkToggle" title="Toggle Dark Mode">
  <i class="bi bi-moon-stars-fill"></i>
</button>

<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
  AOS.init({ duration: 1000, once: true });
  const darkToggle = document.getElementById('darkToggle');
  if (localStorage.getItem('darkMode') === 'enabled') document.body.classList.add('dark-mode');
  darkToggle.addEventListener('click', () => {
    document.body.classList.toggle('dark-mode');
    localStorage.setItem('darkMode', document.body.classList.contains('dark-mode') ? 'enabled' : 'disabled');
  });
</script>
</body>
</html>
