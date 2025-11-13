<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit();
}
include("connect.php");

$user_id = $_SESSION['user_id'];
$booking_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$sql = "SELECT * FROM bookings WHERE id=? AND user_id=? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $booking_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$booking = $result->fetch_assoc();

if (!$booking) {
  echo "<script>alert('Booking not found or unauthorized!'); window.location.href='my-bookings.php';</script>";
  exit();
}

$status = $booking['status'];
$badgeClass = "secondary";
if ($status == "pending") $badgeClass = "warning";
if ($status == "accepted") $badgeClass = "success";
if ($status == "rejected") $badgeClass = "danger";
if ($status == "completed") $badgeClass = "primary";
?>
<?php include("auth.php"); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Booking details for Elegant Salon customer." />
  <title>Booking Detail - Elegant Salon</title>
  <link rel="icon" href="images/logo.png" type="image/png">
  <link rel="stylesheet" href="style.css?v=<?= time(); ?>">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
  <style>
    /* Card styling */
    .booking-card {
      border: none;
      border-radius: 15px;
      overflow: hidden;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .booking-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 20px rgba(0,0,0,0.2);
    }
    .booking-card .list-group-item {
      border: none;
      padding: 12px 15px;
      font-size: 15px;
    }
    .dark-mode .booking-card {
      background-color: #1c1c1c !important;
      color: #fff !important;
      box-shadow: 0 6px 15px rgba(255,255,255,0.1);
    }
    .dark-mode .list-group-item {
      background-color: transparent !important;
      color: #ddd !important;
    }
    .dark-mode .btn-outline-primary {
      border-color: #ffc107;
      color: #ffc107;
    }
    .dark-mode .btn-outline-primary:hover {
      background-color: #ffc107;
      color: #000;
    }
    .dark-mode h2,
.dark-mode p.text-muted,
.dark-mode .text {
  color: #fff !important;
}
  </style>
</head>
<body>
<?php include("header.php");?>

<!-- Dark Mode Toggle -->
<button class="btn btn-dark position-fixed end-0 bottom-0 m-3 rounded-circle shadow" id="darkToggle">
  <i class="bi bi-moon-stars-fill"></i>
</button>
<br><br>
<!-- Hero -->
<div class="container text-center mt-5" data-aos="zoom-in">
  <h1 class="display-4 fw-bold text-warning">📌 Booking Detail</h1>
  <p class="lead text-muted text" >Here are the details of your appointment</p>
</div>

<!-- Detail Card -->
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-lg-7" data-aos="fade-up">
      <div class="card booking-card shadow">
        <div class="card-body p-4">
          <h3 class="text-warning mb-4">
            <i class="bi bi-scissors me-2"></i> <?= htmlspecialchars($booking['service']) ?>
          </h3>
          <ul class="list-group list-group-flush mb-4">
            <li class="list-group-item">
          <i class="bi bi-person me-2 text-secondary"></i>
          <strong>Name:</strong> <?= htmlspecialchars($booking['name']) ?>
        </li>
        <li class="list-group-item">
          <i class="bi bi-envelope me-2 text-danger"></i>
          <strong>Email:</strong> <?= htmlspecialchars($booking['email']) ?>
        </li>
        <li class="list-group-item">
          <i class="bi bi-telephone me-2 text-success"></i>
          <strong>Phone:</strong> <?= htmlspecialchars($booking['phone']) ?>
        </li>
            <li class="list-group-item"><i class="bi bi-stars me-2 text-primary"></i><strong>Sub-Service:</strong> <?= htmlspecialchars($booking['sub_service']) ?></li>
            <li class="list-group-item">
              <i class="bi bi-house-door me-2 text-warning"></i>
              <strong>Service Type:</strong> <?= ucfirst($booking['service_type']) ?>
            </li>
            <li class="list-group-item">
              <i class="bi bi-clock me-2 text-info"></i>
              <strong>Time Slot:</strong> <?= htmlspecialchars($booking['time_slot']) ?>
            </li>
            <li class="list-group-item">
              <i class="bi bi-cash-coin me-2 text-success"></i>
              <strong>Price:</strong> Rs. <?= number_format($booking['price']) ?>
            </li>
            <li class="list-group-item">
              <i class="bi bi-people me-2 text-primary"></i>
              <strong>Persons:</strong> <?= htmlspecialchars($booking['persons']) ?>
            </li>
            <?php if (strtolower($booking['service_type']) === "home"): ?>
            <li class="list-group-item">
              <i class="bi bi-geo-alt me-2 text-warning"></i>
              <strong>Address:</strong> <?= htmlspecialchars($booking['address']) ?>
            </li>
            <?php endif; ?>
            <li class="list-group-item"><i class="bi bi-calendar-check me-2 text-success"></i><strong>Date:</strong> <?= htmlspecialchars($booking['date']) ?></li>
            <li class="list-group-item"><i class="bi bi-clock-history me-2 text-info"></i><strong>Booked At:</strong> <?= date("d M Y, h:i A", strtotime($booking['created_at'])) ?></li>
            <li class="list-group-item">
              <i class="bi bi-info-circle me-2 text-danger"></i><strong>Status:</strong> 
              <span class="badge bg-<?= $badgeClass ?> px-3 py-2"><?= ucfirst($status) ?></span>
            </li>
          </ul>
          <div class="text-center">
            <a href="my-bookings.php" class="btn btn-outline-warning px-4">
              <i class="bi bi-arrow-left"></i> Back to My Bookings
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include("footer.php"); ?>

<!-- AOS + Dark Mode -->
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
  AOS.init({ duration: 1000 });

  if (localStorage.getItem("dark-mode") === "enabled") {
    document.body.classList.add("dark-mode");
    document.getElementById('darkToggle').classList.add("btn-light");
    document.getElementById('darkToggle').classList.remove("btn-dark");
  }

  document.getElementById('darkToggle').addEventListener('click', function () {
    document.body.classList.toggle('dark-mode');
    this.classList.toggle('btn-light');
    this.classList.toggle('btn-dark');
    if (document.body.classList.contains("dark-mode")) {
      localStorage.setItem("dark-mode", "enabled");
    } else {
      localStorage.setItem("dark-mode", "disabled");
    }
  });
</script>
</body>
</html>
