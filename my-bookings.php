<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit();
}
include("connect.php");


$user_id = $_SESSION['user_id'];

// Pagination
$limit = 6;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// Search filter
$search = $_GET['search'] ?? '';

// Count total
$count_sql = "SELECT COUNT(*) as total FROM bookings WHERE user_id=?";
$params = [$user_id];
$types = "i";

if ($search) {
    $count_sql .= " AND (service LIKE ? OR sub_service LIKE ? OR service_type LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= "sss";
}

$stmt = $conn->prepare($count_sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$total_rows = $stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);

// Fetch bookings
$sql = "SELECT * FROM bookings WHERE user_id=?";
$params = [$user_id];
$types = "i";

if ($search) {
    $sql .= " AND (service LIKE ? OR sub_service LIKE ? OR service_type LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= "sss";
}


$sql .= " ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
?>
<?php include("auth.php"); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Elegant Salon - Track and manage your booking history easily." />
  <title>My Bookings - Elegant Salon</title>
  <link rel="icon" href="images/logo.png" type="image/png">
  <link rel="stylesheet" href="style.css" >
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
</head>
<style>
     .card:hover p{
        color:white;
    }
/* Dark mode fix for headings and paragraphs */
.dark-mode h2,
.dark-mode p.text-muted,
.dark-mode .text {
  color: #fff !important;
}

/* Card dark mode */
.dark-mode .card {
  background-color: #000 !important;
  color: #fff !important;
}

/* Card hover - stay black */
.dark-mode .card:hover {
  background-color: #000 !important;
  transform: scale(1.02); /* thoda zoom effect */
  transition: all 0.3s ease;
}

/* Card body text */
.dark-mode .card p {
  color: #fff !important;
}

/* Card title (already yellow, keep it) */
.dark-mode .card-title {
  color: #ffc107 !important;
}

</style>
<body>
<?php include("header.php");?>
<!-- Dark Mode Toggle -->
<button class="btn btn-dark position-fixed end-0 bottom-0 m-3 rounded-circle shadow" id="darkToggle">
  <i class="bi bi-moon-stars-fill"></i>
</button>

<br><br>
<!-- Hero Section -->
  <div class="container text-center mt-5" data-aos="zoom-in">
    <h1 class="display-4 fw-bold text-warning">📅 My Bookings</h1>
    <p class="lead">Track the status of your appointments</p>
  </div>


<!-- Content -->
<div class="container py-5">
  <div class="text-center mb-5">
    <h2 class="fw-bold text-secondary text" data-aos="fade-up">Your Appointment History</h2>
    <p class="text-muted text" data-aos="fade-up" data-aos-delay="100">
      Check whether your booking is pending, accepted, rejected, or completed.
    </p>
  </div>

  <!-- Search -->
  <form method="GET" class="row justify-content-center mb-4" data-aos="fade-up">
  <div class="col-md-5">
    <input type="text" name="search" class="form-control" 
           value="<?= htmlspecialchars($search) ?>" placeholder="Search by service, sub-service, or type...">
  </div>
  <div class="col-md-2 d-flex gap-2">
    <button class="btn btn-primary w-100" type="submit">Search</button>
    <a href="my-bookings.php" class="btn btn-secondary w-100">Reset</a>
  </div>
</form>


  <!-- Bookings Grid -->
  <div class="row g-4">
    <?php if ($result->num_rows > 0): ?>
      <?php while($row = $result->fetch_assoc()): 
        $status = $row['status'];
        $badgeClass = "secondary";
        if ($status == "pending") $badgeClass = "warning";
        if ($status == "accepted") $badgeClass = "success";
        if ($status == "rejected") $badgeClass = "danger";
        if ($status == "completed") $badgeClass = "primary";
      ?>
      <div class="col-md-6 col-lg-4" data-aos="zoom-in">
        <div class="card shadow h-100 border-0 rounded-3">
          <div class="card-body">
            <h5 class="card-title text-warning mb-3">
              <i class="bi bi-scissors me-2"></i><?= htmlspecialchars($row['service']) ?>
            </h5>
            <p><strong>Sub-Service:</strong> <?= htmlspecialchars($row['sub_service']) ?></p>
            <p><strong>Service Type:</strong> <?= ucfirst(htmlspecialchars($row['service_type'])) ?></p>
            <p><strong>Date:</strong> <?= htmlspecialchars($row['date']) ?></p>
            <p><strong>Booked At:</strong> <?= date("d M Y, h:i A", strtotime($row['created_at'])) ?></p>
            <p>
              <span class="badge bg-<?= $badgeClass ?> px-3 py-2"><?= ucfirst($status) ?></span>
            </p>
            <a href="booking-detail-user.php?id=<?= $row['id'] ?>" 
               class="btn btn-warning text-dark mt-3 px-4 py-2 fw-semibold shadow-sm d-flex justify-content-center w-100">
              <i class="bi bi-eye"></i> View Detail
            </a>
          </div>
        </div>
      </div>
      <?php endwhile; ?>
    <?php else: ?>
      <div class="col-12 text-center">
        <p class="text-muted">No bookings found.</p>
      </div>
    <?php endif; ?>
  </div>

  <!-- Pagination -->
  <?php if ($total_pages > 1): ?>
    <nav class="mt-5" data-aos="fade-up">
      <ul class="pagination justify-content-center">
        <?php if ($page > 1): ?>
          <li class="page-item">
            <a class="page-link" href="?search=<?= $search ?>&page=<?= $page-1 ?>">Previous</a>
          </li>
        <?php endif; ?>
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
          <li class="page-item <?= $i == $page ? 'active' : '' ?>">
            <a class="page-link" href="?search=<?= $search ?>&page=<?= $i ?>"><?= $i ?></a>
          </li>
        <?php endfor; ?>
        <?php if ($page < $total_pages): ?>
          <li class="page-item">
            <a class="page-link" href="?search=<?= $search ?>&page=<?= $page+1 ?>">Next</a>
          </li>
        <?php endif; ?>
      </ul>
    </nav>
  <?php endif; ?>
</div>

<?php include("footer.php"); ?>

<!-- AOS Animation JS -->
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
  AOS.init({ duration: 1000 });

  // Dark mode check on load
  if (localStorage.getItem("dark-mode") === "enabled") {
    document.body.classList.add("dark-mode");
    document.getElementById('darkToggle').classList.add("btn-light");
    document.getElementById('darkToggle').classList.remove("btn-dark");
  }

  // Toggle dark mode
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
