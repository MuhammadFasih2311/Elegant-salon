<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
  header("Location: admin-login.php");
  exit();
}
include("auth-check.php"); 
include("connect.php");
$booking_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$sql = "SELECT * FROM bookings WHERE id=? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$result = $stmt->get_result();
$booking = $result->fetch_assoc();

if (!$booking) {
  echo "<script>alert('Booking not found!'); window.location.href='booking.php';</script>";
  exit();
}

$status = $booking['status'];
$badgeClass = "secondary";
if ($status == "pending") $badgeClass = "warning";
if ($status == "accepted") $badgeClass = "success";
if ($status == "rejected") $badgeClass = "danger";
if ($status == "completed") $badgeClass = "primary";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <meta name="description" content="Booking detail page for Elegant Salon admin." />
  <title>Booking Detail (Admin) - Elegant Salon</title>
  <link rel="icon" href="images/logo.png" type="image/png">
  <link rel="icon" href="images/logo.png" type="image/png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
  <style>
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
    .detail-card { 
      background:#fff; 
      border-radius:20px; 
      padding:40px 30px; 
      box-shadow:0 8px 20px rgba(0,0,0,0.1); 
      width:100%; 
      max-width:500px;
    }
    .btn-warning{border-radius:15px; font-weight:500}
    .list-group-item{border-radius:8px;margin-bottom:6px;}
    @media (max-width:576px){ 
      .detail-card{padding:30px 20px} 
    }
    /* Dropdown hover fix */
.dropdown-menu {
  background-color: #222; /* dark background for dropdown */
  border: none;
}
.dropdown-menu .dropdown-item {
  color: #fff; /* white text */
}
.dropdown-menu .dropdown-item:hover {
  background-color: #ffc107; 
  color: #000 !important;
}

    /* Dark mode */
    body.dark-mode { background:#121212; color:#fff; }
    .dark-mode .detail-card { background:#1c1c1c; color:#fff; }
    .dark-mode .list-group-item { background:#2a2a2a; color:#ddd; }
  </style>
</head>
<body class="d-flex flex-column min-vh-100">

<?php include("navbar.php"); ?>

<div class="content-wrapper">
  <div class="detail-card" data-aos="zoom-in">
    <div class="mb-4 text-center">
      <img src="images/logo.png" alt="Elegant Salon" width="70">
      <h3 class="text-warning mt-2">📌 Booking Detail</h3>
      <p class="text-muted small dark-text">Here is the complete booking info</p>
    </div>

    <ul class="list-group mb-3 text-start">
      <li class="list-group-item"><strong>Name:</strong> <?= htmlspecialchars($booking['name']) ?></li>
      <li class="list-group-item"><strong>Email:</strong> <?= htmlspecialchars($booking['email']) ?></li>
      <li class="list-group-item"><strong>Phone:</strong> <?= htmlspecialchars($booking['phone']) ?></li>
      <li class="list-group-item"><strong>Service Type:</strong> <?= ucfirst(htmlspecialchars($booking['service_type'])) ?></li>
        <?php if ($booking['service_type'] === 'home'): ?>
    <li class="list-group-item"><strong>Address:</strong> <?= htmlspecialchars($booking['address']) ?></li>
  <?php endif; ?>
      <li class="list-group-item"><strong>Person:</strong> <?= htmlspecialchars($booking['persons']) ?></li>
      <li class="list-group-item"><strong>Service:</strong> <?= htmlspecialchars($booking['service']) ?></li>
      <li class="list-group-item"><strong>Sub-Service:</strong> <?= htmlspecialchars($booking['sub_service']) ?></li>
      <li class="list-group-item"><strong>Price:</strong> <?= htmlspecialchars($booking['price']) ?></li>

      <li class="list-group-item"><strong>Time Slot:</strong> <?= htmlspecialchars($booking['time_slot']) ?></li>
      <li class="list-group-item"><strong>Date:</strong> <?= htmlspecialchars($booking['date']) ?></li>
      <li class="list-group-item"><strong>Booked At:</strong> <?= date("d M Y, h:i A", strtotime($booking['created_at'])) ?></li>
      <li class="list-group-item"><strong>Status:</strong> 
        <span class="badge bg-<?= $badgeClass ?>"><?= ucfirst($status) ?></span>
      </li>
    </ul>

    <div class="d-flex justify-content-between">
      <a href="booking.php" class="btn btn-outline-warning"><i class="bi bi-arrow-left"></i> Back</a>
      <button class="btn btn-warning" onclick="window.print()"><i class="bi bi-printer"></i> Print</button>
    </div>
  </div>
</div>

<?php include("foot.php"); ?>

<!-- Dark Mode Toggle -->
<button class="btn btn-dark position-fixed end-0 bottom-0 m-3 rounded-circle shadow z-3 no-print" 
        id="darkToggle" title="Toggle Dark Mode">
  <i class="bi bi-moon-stars-fill"></i>
</button>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
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
