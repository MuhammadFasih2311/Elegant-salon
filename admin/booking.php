<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
  header("Location: admin-login.php");
  exit();
}
include("connect.php");

// Handle search and filter
$search = $_GET['search'] ?? '';
$filter_date = $_GET['date'] ?? '';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Count total rows
// Count total rows
$count_query = "SELECT COUNT(*) as total FROM bookings WHERE 1";
if ($search) {
  $s = mysqli_real_escape_string($conn, $search);
  $count_query .= " AND (name LIKE '%$s%' 
                     OR email LIKE '%$s%' 
                     OR phone LIKE '%$s%' 
                     OR address LIKE '%$s%'
                     OR service LIKE '%$s%'
                     OR sub_service LIKE '%$s%'
                     OR service_type LIKE '%$s%')";
}
if ($filter_date) {
  $safe_date = mysqli_real_escape_string($conn, $filter_date);
  $count_query .= " AND DATE(date) = '$safe_date'";   // 👈 booking ki date
}

$total_result = mysqli_query($conn, $count_query);
$total_rows = mysqli_fetch_assoc($total_result)['total'];
$total_pages = ceil($total_rows / $limit);

// Main data fetch
// Main data fetch
$query = "SELECT * FROM bookings WHERE 1";
if ($search) {
  $s = mysqli_real_escape_string($conn, $search);
  $query .= " AND (name LIKE '%$s%' 
                OR email LIKE '%$s%' 
                OR phone LIKE '%$s%' 
                OR address LIKE '%$s%'
                OR service LIKE '%$s%'
                OR sub_service LIKE '%$s%'
                OR service_type LIKE '%$s%')";
}
if ($filter_date) {
  $safe_date = mysqli_real_escape_string($conn, $filter_date);
  $query .= " AND DATE(date) = '$safe_date'";   // 👈 booking ki date
}

// agar hidden bookings hain to unko exclude karo
if (!empty($_SESSION['hidden_bookings'])) {
    $hidden = implode(",", array_map("intval", $_SESSION['hidden_bookings']));
    $query .= " AND id NOT IN ($hidden)";
}

$query .= " ORDER BY id DESC LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $query);

$status_counts = [
  'pending' => 0,
  'accepted' => 0,
  'completed' => 0,
  'rejected' => 0
];

$status_query = "SELECT status, COUNT(*) as cnt FROM bookings GROUP BY status";
$res_status = mysqli_query($conn, $status_query);
while ($row = mysqli_fetch_assoc($res_status)) {
  $status_counts[$row['status']] = $row['cnt'];
}

?>
<?php include("auth-check.php"); ?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="View and manage all customer orders and booking status in the Elegant Salon admin area." />
  <title>Admin Bookings - Elegant Salon</title>
  <link rel="icon" href="images/logo.png" type="image/png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <style>
    @media print { .no-print { display: none !important; } }
    .dropdown-menu .dropdown-item:hover { background-color: #ffc107; color: #000 !important; }
    /* Dark Mode Styles */
   body.dark-mode {
  background-color: #121212;
  color: #fff;
}

/* Table full dark */
body.dark-mode .table {
  color: #fff;
  background-color: #1a1a1a;
  border-radius: 8px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.7);
}

/* Table header */
body.dark-mode .table thead {
  background-color: #222 !important;
  color: #ffc107 !important;
}
body.dark-mode .table thead th {
  background-color: #222 !important;
  color: #ffc107 !important;
  border-bottom: 2px solid #444;
}

/* Table body */
body.dark-mode .table tbody {
  background-color: #1a1a1a;
  color: #fff;
}
body.dark-mode .table-striped tbody tr:nth-of-type(odd) {
  background-color: #2a2a2a;
}
body.dark-mode .table-hover tbody tr:hover {
  background-color: #333;
}

/* Table borders */
body.dark-mode .table-bordered th,
body.dark-mode .table-bordered td {
  border-color: #444;
}

/* Text force white */
body.dark-mode td,
body.dark-mode th {
  color: #050303ff;
}

/* Pagination */
body.dark-mode .page-link {
  background-color: #222;
  color: #fff;
}
body.dark-mode .page-item.active .page-link {
  background-color: #ffc107;
  color: #000;
  border-color: #ffc107;
}

/* Buttons */
body.dark-mode .btn-outline-warning {
  color: #ffc107;
  border-color: #ffc107;
}
body.dark-mode .btn-outline-warning:hover {
  background-color: #ffc107;
  color: #000;
}
    #darkToggle { transition: background-color 0.3s ease, color 0.3s ease; }
    .card-cta {
  border-radius: 22px;
  padding: 1.5rem;
  background: #fff;
  transition: all .25s;
}
.card-cta:hover {
  transform: translateY(-5px);
  box-shadow: 0 0 20px rgba(255,193,7,0.3);
}
.summary-card {
  border-radius: 22px;
  padding: 1.2rem 1.5rem;
  margin-top: 1rem;
}
body.dark-mode .summary-card.active .h5{
  color:black!important;
}
/* Table responsiveness fix */
.table-responsive {
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
  border-radius: 10px;
}

/* Table alignment */
.table th, .table td {
  vertical-align: middle !important;
  text-align: center;
  white-space: nowrap; /* prevent breaking in weird places */
}

/* Table header styling */
.table thead th {
  font-size: 14px;
  font-weight: 600;
  text-transform: uppercase;
}

/* Table body text sizing */
.table tbody td {
  font-size: 14px;
}

/* Buttons alignment fix */
.table .btn {
  padding: 3px 8px;
  font-size: 13px;
}

/* Mobile view fix: smaller font + wrap */
@media (max-width: 768px) {
  .table thead {
    font-size: 13px;
  }
  .table tbody td {
    font-size: 12px;
    white-space: normal; /* allow text wrapping */
  }
}
  </style>
</head>
<body class="d-flex flex-column min-vh-100">

<?php include("navbar.php"); ?>

<button class="btn btn-dark position-fixed end-0 bottom-0 m-3 rounded-circle shadow z-3" id="darkToggle" title="Toggle Dark Mode">
  <i class="bi bi-moon-stars-fill"></i>
</button>

<div class="container my-5 flex-grow-1">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="text-warning" data-aos="fade-down">📅 All Bookings</h2>
    <button class="btn btn-warning no-print" onclick="window.print()" data-aos="fade-left">
      <i class="bi bi-printer"></i> Print
    </button>
  </div>

  <!-- Filter form -->
  <form method="GET" class="row g-3 align-items-end no-print mb-4" data-aos="fade-right">
    <div class="col-md-4">
      <label class="form-label">Search by Customer Name</label>
      <input type="text" name="search" class="form-control" 
       value="<?= htmlspecialchars($search) ?>" 
       placeholder="e.g. Fasih"
       maxlength="30" pattern="[A-Za-z\s]{1,30}" 
       title="Only alphabets allowed, max 30 letters">
    </div>
    <div class="col-md-4">
      <label class="form-label">Filter by Date</label>
      <input type="date" name="date" class="form-control" value="<?= htmlspecialchars($filter_date) ?>">
    </div>
    <div class="col-md-4">
      <button class="btn btn-primary" type="submit">Apply Filters</button>
      <button type="button" class="btn btn-secondary" onclick="window.location.href='booking.php'">Reset</button>
    </div>
  </form>

  <!-- Table -->
  <div class="table-responsive" data-aos="fade-up">
  <table class="table table-bordered table-hover table-striped align-middle text-center">
    <thead class="table-dark">
      <tr>
        <th>#</th>
        <th>Name</th>
        <th>Email</th>
        <th>Service</th>
        <th>Sub-service</th>
        <th><i class="bi bi-cash-coin"></i> Price</th>
        <th>Status</th>
        <th class="no-print">Actions</th>
        <th class="no-print">View</th>
        <th class="no-print">Delete</th>
      </tr>
    </thead>
    <tbody>
        <?php 
$count = $offset + 1; 
$delay = 0;
while ($row = mysqli_fetch_assoc($result)): 
  $price = "N/A";

  if (!empty($row['price']) && $row['price'] > 0) {
      // ✅ Agar manual booking mein price save hai to use show karo
      $price = $row['price'];
  } else {
      // ✅ Warna serve table se fetch karo (old auto bookings ke liye)
      $service = mysqli_real_escape_string($conn, $row['service']);
      $sub_service = mysqli_real_escape_string($conn, $row['sub_service']);
      $price_result = mysqli_query($conn, "SELECT price FROM serve WHERE category='$service' AND label='$sub_service'");
      if ($p = mysqli_fetch_assoc($price_result)) {
          $price = $p['price'];
      }
  }

  $delay += 50;
?>
<tr data-aos="fade-right" data-aos-delay="<?= $delay ?>">
  <td><?= $count++ ?></td>
  <td><?= htmlspecialchars($row['name']) ?></td>
  <td><?= htmlspecialchars($row['email']) ?></td>
  <td><?= htmlspecialchars($row['service']) ?></td>
  <td><?= htmlspecialchars($row['sub_service']) ?></td>
  <td><?= $price ?></td>
          <td class="status-cell">
            <?php if ($row['status'] == 'pending'): ?>
              <span class="badge bg-warning text-dark">Pending</span>
            <?php elseif ($row['status'] == 'accepted'): ?>
              <span class="badge bg-success">Accepted</span>
            <?php elseif ($row['status'] == 'rejected'): ?>
              <span class="badge bg-danger">Rejected</span>
            <?php else: ?>
              <span class="badge bg-primary">Completed</span>
            <?php endif; ?>
          </td>
          <td class="no-print">
            <form class="update-form d-flex align-items-center" data-id="<?= $row['id'] ?>">
              <select name="status" class="form-select form-select-sm me-2">
                <option value="pending"   <?= $row['status']=='pending'?'selected':'' ?>>Pending</option>
                <option value="accepted"  <?= $row['status']=='accepted'?'selected':'' ?>>Accepted</option>
                <option value="rejected"  <?= $row['status']=='rejected'?'selected':'' ?>>Rejected</option>
                <option value="completed" <?= $row['status']=='completed'?'selected':'' ?>>Completed</option>
              </select>
              <button type="submit" class="btn btn-sm btn-success">Update</button>
            </form>
          </td>
          <td class="no-print">
            <a href="booking-detail-admin.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-primary">
              <i class="bi bi-eye"></i> View
            </a>
          </td>
          <td class="no-print">
            <button type="button" class="btn btn-sm btn-danger delete-row">
              <i class="bi bi-trash"></i> Delete
            </button>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999">
  <div id="statusToast" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body" id="toastMessage">
        Status updated successfully!
      </div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
  </div>
</div>

  <!-- Pagination -->
  <nav class="mt-4" data-aos="fade-up" id="paginationNav">
    <ul class="pagination justify-content-center">
      <?php if ($page > 1): ?>
        <li class="page-item"><a class="page-link" href="?search=<?= $search ?>&date=<?= $filter_date ?>&page=<?= $page - 1 ?>">Previous</a></li>
      <?php endif; ?>
      <?php for ($i = 1; $i <= $total_pages; $i++): ?>
        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
          <a class="page-link" href="?search=<?= $search ?>&date=<?= $filter_date ?>&page=<?= $i ?>"><?= $i ?></a>
        </li>
      <?php endfor; ?>
      <?php if ($page < $total_pages): ?>
        <li class="page-item"><a class="page-link" href="?search=<?= $search ?>&date=<?= $filter_date ?>&page=<?= $page + 1 ?>">Next</a></li>
      <?php endif; ?>
    </ul>
  </nav>

  <!-- ✅ Summary yahan le aao -->
  <div class="summary-card mt-4" data-aos="fade-up" id="statusSummary">
    <h6 class="mb-3">📊 Booking Status Summary</h6>
    <div class="row g-3 text-center">
      <div class="col-md-3 col-6">
        <div class="card-cta h-100">
          <i class="bi bi-hourglass-split text-warning"></i>
          <h5 class="mt-2 text-dark">Pending</h5> <!-- ✅ dark mode fix -->
          <p class="fw-bold fs-5 text-warning"><?= $status_counts['pending'] ?></p>
        </div>
      </div>
      <div class="col-md-3 col-6">
        <div class="card-cta h-100">
          <i class="bi bi-check-circle-fill text-success"></i>
          <h5 class="mt-2 text-dark">Accepted</h5>
          <p class="fw-bold fs-5 text-success"><?= $status_counts['accepted'] ?></p>
        </div>
      </div>
      <div class="col-md-3 col-6">
        <div class="card-cta h-100">
          <i class="bi bi-clipboard-check-fill text-primary"></i>
          <h5 class="mt-2 text-dark">Completed</h5>
          <p class="fw-bold fs-5 text-primary"><?= $status_counts['completed'] ?></p>
        </div>
      </div>
      <div class="col-md-3 col-6">
        <div class="card-cta h-100">
          <i class="bi bi-x-circle-fill text-danger"></i>
          <h5 class="mt-2 text-dark">Rejected</h5>
          <p class="fw-bold fs-5 text-danger"><?= $status_counts['rejected'] ?></p>
        </div>
      </div>
    </div>
  </div>
      </div>

<?php include("foot.php"); ?>

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
<script>
document.querySelectorAll(".update-form").forEach(form => {
  form.addEventListener("submit", function(e) {
    e.preventDefault();

    const id = this.getAttribute("data-id");
    const status = this.querySelector("select").value;

    fetch("update-status.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: `id=${id}&status=${status}`
    })
    .then(res => res.json())
    .then(data => {
      const toastEl = document.getElementById("statusToast");
      const toastBody = document.getElementById("toastMessage");
      toastBody.textContent = data.message;

      if (data.success) {
        toastEl.classList.remove("text-bg-danger");
        toastEl.classList.add("text-bg-success");

        const row = form.closest("tr");
        const badgeCell = row.querySelector(".status-cell");
        if (badgeCell) {
          let badgeHtml = "";
          if (status === "pending") {
            badgeHtml = '<span class="badge bg-warning text-dark">Pending</span>';
          } else if (status === "accepted") {
            badgeHtml = '<span class="badge bg-success">Accepted</span>';
          } else if (status === "rejected") {
            badgeHtml = '<span class="badge bg-danger">Rejected</span>';
          } else if (status === "completed") {
            badgeHtml = '<span class="badge bg-primary">Completed</span>';
          }
          badgeCell.innerHTML = badgeHtml;
        }
      } else {
        toastEl.classList.remove("text-bg-success");
        toastEl.classList.add("text-bg-danger");
      }

      const toast = new bootstrap.Toast(toastEl, { delay: 5000 });
      toast.show();
    });
  });
});

document.querySelectorAll(".delete-row").forEach(btn => {
  btn.addEventListener("click", function() {
    const row = this.closest("tr");
    const bookingId = row.querySelector("form.update-form").getAttribute("data-id");

    if (confirm("Are you Sure you want to remove this booking from your table?")) {
      row.remove();

      fetch("hide-booking.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `id=${bookingId}`
      });
    }
  });
});

//  Search name validation (alphabets only, max 30)
const searchInput = document.querySelector("input[name='search']");
if (searchInput) {
  searchInput.addEventListener("keypress", function(e) {
    if (!/[a-zA-Z\s]/.test(e.key)) {
      e.preventDefault(); // stop number/special char
    }
  });
  searchInput.addEventListener("input", function() {
    this.value = this.value.replace(/[^a-zA-Z\s]/g, ""); 
    if (this.value.length > 30) {
      this.value = this.value.slice(0, 30); // max 30
    }
  });
}

//pagination without reload
function attachPaginationListeners() {
  document.querySelectorAll(".pagination a").forEach(link => {
    link.addEventListener("click", function(e) {
      e.preventDefault();
      const url = this.getAttribute("href");

      fetch(url, { headers: { "X-Requested-With": "XMLHttpRequest" } })
        .then(res => res.text())
        .then(data => {
          const parser = new DOMParser();
          const doc = parser.parseFromString(data, "text/html");

          // table replace
          const newTable = doc.querySelector("table tbody").innerHTML;
          document.querySelector("table tbody").innerHTML = newTable;

          // pagination replace
          const newPagination = doc.querySelector("#paginationNav").innerHTML;
          document.querySelector("#paginationNav").innerHTML = newPagination;

          // summary replace
          const newSummary = doc.querySelector("#statusSummary").innerHTML;
          document.querySelector("#statusSummary").innerHTML = newSummary;

          // AOS re-init
          AOS.refresh();

          // ✅ naya pagination load hua, ab listeners firse attach karo
          attachPaginationListeners();
        });
    });
  });
}

// ✅ first time load pe run karo
document.addEventListener("DOMContentLoaded", attachPaginationListeners);


</script>
</body>
</html>
