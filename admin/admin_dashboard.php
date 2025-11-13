<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
  header("Location: admin-login.php");
  exit();
}

include("auth-check.php"); 
include("connect.php");

// Pagination logic
$limit = 12;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// Filters
$filter_category = $_GET['category'] ?? '';
$filter_label    = $_GET['label'] ?? '';

$where = " WHERE 1 ";
if ($filter_category) {
  $safe_cat = mysqli_real_escape_string($conn, $filter_category);
  $where .= " AND category LIKE '%$safe_cat%' ";
}
if ($filter_label) {
  $safe_label = mysqli_real_escape_string($conn, $filter_label);
  $where .= " AND label LIKE '%$safe_label%' ";
}

// Count total rows
$total_result = mysqli_query($conn, "SELECT COUNT(*) as total FROM serve $where");
$total_services = mysqli_fetch_assoc($total_result)['total'];
$total_pages = ceil($total_services / $limit);

// Fetch services for current page
$sql = "SELECT * FROM serve $where ORDER BY category LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $sql);

?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="description" content="Elegant Salon admin dashboard. Monitor and manage services, bookings, and customer data." />
  <title>Admin Dashboard - Elegant Salon</title>
  <link rel="icon" href="images/logo.png" type="image/png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- AOS CSS -->
  <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
  <style>
   td img {
  border-radius: 8px;
}

@media print {
  .no-print { display: none !important; }
}

.dropdown-menu .dropdown-item:hover {
  background-color: #ffc107;
  color: #000 !important;
}

/* Dark Mode Styles */
/* Dark Mode Styles */
body.dark-mode {
  background-color: #121212;
  color: #fff;
}

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

/* Text force white in cells */
body.dark-mode td,
body.dark-mode th {
  color: #070505ff;
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

/* Dark mode toggle button */
body.dark-mode #darkToggle {
  background-color: #fff !important;
  color: #000 !important;
  border: none;
}
#darkToggle {
  transition: background-color 0.3s ease, color 0.3s ease;
}
/* Table alignment */
.table th, .table td {
  text-align: center;
  vertical-align: middle;
  white-space: nowrap;
}

/* Specific width for columns */
.table th:nth-child(1), .table td:nth-child(1) { width: 5%; }   /* ID */
.table th:nth-child(2), .table td:nth-child(2) { width: 20%; }  /* Category */
.table th:nth-child(3), .table td:nth-child(3) { width: 25%; }  /* Label */
.table th:nth-child(4), .table td:nth-child(4) { width: 10%; }  /* Price */
.table th:nth-child(5), .table td:nth-child(5) { width: 15%; }  /* Image */
.table th:nth-child(6), .table td:nth-child(6) { width: 25%; }  /* Actions */

/* Image style */
.table td img {
  border-radius: 8px;
  object-fit: cover;
  width: 80px;
  height: 60px;
  box-shadow: 0 2px 6px rgba(0,0,0,0.3);
}

/* Buttons */
.table .btn {
  padding: 4px 10px;
  font-size: 13px;
}

  </style>
</head>
<body class="d-flex flex-column min-vh-100">
<?php include('navbar.php'); ?>

<button class="btn btn-dark position-fixed end-0 bottom-0 m-3 rounded-circle shadow z-3" id="darkToggle" title="Toggle Dark Mode">
  <i class="bi bi-moon-stars-fill"></i>
</button>

<div class="container my-5 flex-grow-1">
  <h2 class="mb-4 text-center text-warning" data-aos="fade-down">Admin Dashboard - Product</h2>

  <?php if (isset($_GET['msg'])): ?>
  <div class="alert alert-success alert-dismissible fade show" role="alert" id="flashAlert" data-aos="zoom-in">
    <?= htmlspecialchars($_GET['msg']) ?>
    <button type="button" class="btn-close" aria-label="Close" onclick="document.getElementById('flashAlert').remove();"></button>
  </div>
  <script>
  setTimeout(() => {
    const al = document.getElementById('flashAlert');
    if (al) bootstrap.Alert.getOrCreateInstance(al).close();
  }, 4000);
</script>

  <?php endif; ?>

  <div class="mb-3 text-end" data-aos="fade-left">
    <a href="add_service.php" class="btn btn-success">+ Add New Service</a>
  </div>

<!-- Filter form -->
<form method="get" class="row g-3 align-items-end mb-3">
  <div class="col-md-4">
    <label class="form-label" data-aos="fade-right" data-aos-delay="100">Filter by Category</label>
    <input type="text" name="category" class="form-control" 
           value="<?= htmlspecialchars($filter_category) ?>"
           maxlength="30" pattern="[A-Za-z\s]{1,30}"
           title="Only alphabets allowed, max 30 letters" data-aos="fade-right" >
  </div>
  <div class="col-md-4">
    <label class="form-label" data-aos="fade-left" data-aos-delay="100">Filter by Label</label>
    <input type="text" name="label" class="form-control" 
           value="<?= htmlspecialchars($filter_label) ?>"
           maxlength="30" pattern="[A-Za-z\s]{1,30}"
           title="Only alphabets allowed, max 30 letters" data-aos="fade-left">
  </div>
  <div class="col-md-4">
    <button type="submit" class="btn btn-primary" data-aos="fade-left" data-aos-delay="100">Apply Filters</button>
    <a href="admin_dashboard.php" class="btn btn-secondary" data-aos="fade-left" data-aos-delay="200">Reset</a>
  </div>
</form>

  <div class="table-responsive" data-aos="fade-up">
  <table class="table table-bordered table-striped table-hover align-middle text-center">
    <thead class="table-dark">
      <tr>
        <th>ID</th>
        <th>Category</th>
        <th>Label</th>
        <th>Price</th>
        <th>Image</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php $delay = 0; while ($row = mysqli_fetch_assoc($result)): $delay += 100; ?>
        <tr data-aos="fade-right" data-aos-delay="<?= $delay ?>">
          <td><?= $row['id'] ?></td>
          <td><?= htmlspecialchars($row['category']) ?></td>
          <td><?= htmlspecialchars($row['label']) ?></td>
          <td><?= htmlspecialchars($row['price']) ?></td>
          <td><img src="gallery images/<?= htmlspecialchars($row['image']) ?>" alt="Service Image"></td>
          <td>
            <a href="edit_service.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
            <a href="delete_service.php?id=<?= $row['id'] ?>" 
               onclick="return confirm('Are you sure you want to delete this service?')" 
               class="btn btn-sm btn-danger">Delete</a>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

  <!-- Pagination -->
  <nav aria-label="Page navigation" class="mt-4" data-aos="fade-up">
  <ul class="pagination justify-content-center">
    <?php if ($page > 1): ?>
      <li class="page-item">
        <a class="page-link" href="?category=<?= urlencode($filter_category) ?>&label=<?= urlencode($filter_label) ?>&page=<?= $page - 1 ?>">Previous</a>
      </li>
    <?php endif; ?>

    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
      <li class="page-item <?= $i == $page ? 'active' : '' ?>">
        <a class="page-link" href="?category=<?= urlencode($filter_category) ?>&label=<?= urlencode($filter_label) ?>&page=<?= $i ?>"><?= $i ?></a>
      </li>
    <?php endfor; ?>

    <?php if ($page < $total_pages): ?>
      <li class="page-item">
        <a class="page-link" href="?category=<?= urlencode($filter_category) ?>&label=<?= urlencode($filter_label) ?>&page=<?= $page + 1 ?>">Next</a>
      </li>
    <?php endif; ?>
  </ul>
</nav>
</div>

<?php include("foot.php"); ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- AOS JS -->
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
  AOS.init({
    duration: 1000,
    once: true
  });

  const darkToggle = document.getElementById('darkToggle');
  const currentMode = localStorage.getItem('darkMode');

  if (currentMode === 'enabled') {
    document.body.classList.add('dark-mode');
  }
  darkToggle.addEventListener('click', () => {
    document.body.classList.toggle('dark-mode');
    const mode = document.body.classList.contains('dark-mode') ? 'enabled' : 'disabled';
    localStorage.setItem('darkMode', mode);
  });
  // Category input validation
const catInput = document.querySelector("input[name='category']");
if(catInput){
  catInput.addEventListener("input", function(){
    this.value = this.value.replace(/[^a-zA-Z\s]/g, "");
    if(this.value.length > 30) this.value = this.value.slice(0, 30);
  });
}

// Label input validation
const lblInput = document.querySelector("input[name='label']");
if(lblInput){
  lblInput.addEventListener("input", function(){
    this.value = this.value.replace(/[^a-zA-Z\s]/g, "");
    if(this.value.length > 30) this.value = this.value.slice(0, 30);
  });
}

</script>

</body>
</html>
