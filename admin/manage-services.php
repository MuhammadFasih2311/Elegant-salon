<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
  header("Location: admin-login.php");
  exit();
}
include("connect.php");

// Pagination setup
$limit = 10;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// Title filter
$filter_title = $_GET['title'] ?? '';

$where = " WHERE 1 ";
if ($filter_title) {
  $safe_title = mysqli_real_escape_string($conn, $filter_title);
  $where .= " AND title LIKE '%$safe_title%' ";
}

// Count total services
$total_result = mysqli_query($conn, "SELECT COUNT(*) as total FROM services $where");
$total_services = mysqli_fetch_assoc($total_result)['total'];
$total_pages = ceil($total_services / $limit);

// Fetch services with filter
$result = mysqli_query($conn, "SELECT * FROM services $where ORDER BY id DESC LIMIT $limit OFFSET $offset");
?>
<?php include("auth-check.php");  ?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="description" content="Add, update, or delete Elegant Salon services from the admin dashboard." />
  <title>Manage Services - Admin Panel</title>
  <link rel="icon" href="images/logo.png" type="image/png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" />
  <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
  <style>
  td img {
    border-radius: 6px;
  }
  .icon-preview {
    font-size: 1.5rem;
  }
  .dropdown-menu .dropdown-item:hover {
    background-color: #ffc107;
    color: #000 !important;
  }

  /* Dark Mode Styles */
body.dark-mode {
  background-color: #121212;
  color: #fff;
}

body.dark-mode .table {
  color: #fff;
  background-color: #1a1a1a;
  border-radius: 8px;
  box-shadow: 0 0 15px rgba(255, 255, 255, 0.1);
}

body.dark-mode .table thead {
  background-color: #222 !important;
  color: #ffc107 !important;
}

body.dark-mode .table thead th {
  background-color: #222 !important;
  color: #ffc107 !important;
  border-bottom: 2px solid #444;
}

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

body.dark-mode .table-bordered th,
body.dark-mode .table-bordered td {
  border-color: #444;
}

body.dark-mode td, 
body.dark-mode th {
  color: #070505ff;
}

body.dark-mode .btn-outline-warning {
  color: #ffc107;
  border-color: #ffc107;
}

body.dark-mode .btn-outline-warning:hover {
  background-color: #ffc107;
  color: #000;
}

body.dark-mode .page-link {
  background-color: #222;
  color: #fff;
}

body.dark-mode .page-item.active .page-link {
  background-color: #ffc107;
  color: #000;
  border-color: #ffc107;
}

/* Table shadow for stylish look */
body.dark-mode .table {
  box-shadow: 0 4px 20px rgba(0,0,0,0.7);
}
  #darkToggle {
    z-index: 9999;
  }
  /* Dark mode toggle button style */
body.dark-mode #darkToggle {
  background-color: #fff !important;
  color: #000 !important;
  border: none;
}
#darkToggle {
  transition: background-color 0.3s ease, color 0.3s ease;
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
  white-space: nowrap;
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

/* Image fix */
.table img {
  width: 80px;
  height: 60px;
  object-fit: cover;
  border-radius: 6px;
  box-shadow: 0 2px 6px rgba(0,0,0,0.2);
}

/* Buttons alignment fix */
.table .btn {
  padding: 3px 8px;
  font-size: 13px;
}

/* Mobile view fix */
@media (max-width: 768px) {
  .table thead {
    font-size: 13px;
  }
  .table tbody td {
    font-size: 12px;
    white-space: normal;
  }
}
/* ✅ Table Responsive Enhancement */
.table-responsive {
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
  border-radius: 10px;
  background: #fff;
  box-shadow: 0 0 10px rgba(0,0,0,0.05);
}

/* ✅ Table alignment and text wrap */
.table th, .table td {
  vertical-align: middle !important;
  text-align: center;
  white-space: nowrap;
}

/* ✅ Allow description text to wrap cleanly */
.table td:nth-child(4) {
  white-space: normal;
  text-align: left;
  max-width: 300px;
  word-wrap: break-word;
}

/* ✅ Buttons side by side fix */
.table .btn {
  padding: 5px 10px;
  font-size: 13px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 4px;
  white-space: nowrap;
}

/* ✅ Make buttons stay inline, not stacked */
.table td:last-child {
  min-width: 150px;
}

/* ✅ Image sizing consistent */
.table img {
  width: 80px;
  height: 60px;
  object-fit: cover;
  border-radius: 6px;
  box-shadow: 0 2px 6px rgba(0,0,0,0.2);
}

/* ✅ Header visual consistency */
.table thead th {
  font-size: 14px;
  font-weight: 600;
  text-transform: uppercase;
}

/* ✅ Body text */
.table tbody td {
  font-size: 14px;
}

/* ✅ Mobile optimization */
@media (max-width: 768px) {
  .table thead {
    font-size: 13px;
  }

  .table tbody td {
    font-size: 12px;
    white-space: normal;
  }

  /* Reduce image size on small screens */
  .table img {
    width: 60px;
    height: 45px;
  }

  /* Make buttons smaller but still inline */
  .table .btn {
    font-size: 12px;
    padding: 4px 8px;
  }

  /* Add a min-width to table so scroll works naturally */
  .table {
    min-width: 800px;
  }
}

/* ✅ Dark Mode Integration */
body.dark-mode .table {
  background-color: #1a1a1a;
  color: #fff;
  border-radius: 8px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.7);
}

body.dark-mode .table thead {
  background-color: #222 !important;
  color: #ffc107 !important;
}

body.dark-mode .table-striped tbody tr:nth-of-type(odd) {
  background-color: #2a2a2a;
}

body.dark-mode .table-hover tbody tr:hover {
  background-color: #333;
}

body.dark-mode .table-bordered th,
body.dark-mode .table-bordered td {
  border-color: #444;
}

</style>

</head>
<body class="d-flex flex-column min-vh-100">

<?php include("navbar.php"); ?>
<button class="btn btn-dark position-fixed end-0 bottom-0 m-3 rounded-circle shadow z-3" id="darkToggle" title="Toggle Dark Mode">
  <i class="bi bi-moon-stars-fill"></i>
</button>

<?php if (isset($_GET['msg'])): ?>
  <div class="alert alert-success alert-dismissible fade show mt-3 mx-3" role="alert" id="flashAlert">
    <?= htmlspecialchars($_GET['msg']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>

  <script>
    setTimeout(() => {
      const al = document.getElementById('flashAlert');
      if (al) bootstrap.Alert.getOrCreateInstance(al).close();
    }, 4000);
  </script>
<?php endif; ?>


<div class="container my-5 flex-grow-1">
  <h2 class="text-center text-warning mb-4" data-aos="fade-down">Manage Website Services</h2>

  <div class="text-end mb-3" data-aos="fade-left">
    <a href="add_service_main.php" class="btn btn-success">+ Add New Service</a>
  </div>

<!-- Filter Form -->
<form method="get" class="row g-3 align-items-end mb-3">
  <div class="col-md-6">
    <label class="form-label" data-aos="fade-right" data-aos-delay="100">Filter by Title</label>
    <input type="text" name="title" class="form-control"
           value="<?= htmlspecialchars($filter_title) ?>"
           maxlength="30" pattern="[A-Za-z\s]{1,30}"
           title="Only alphabets allowed, max 30 letters" data-aos="fade-right">
  </div>
  <div class="col-md-6">
    <button type="submit" class="btn btn-primary" data-aos="fade-left" data-aos-delay="100">Apply Filter</button>
    <a href="manage-services.php" class="btn btn-secondary" data-aos="fade-left" data-aos-delay="200">Reset</a>
  </div>
</form>

  <div class="table-responsive" data-aos="fade-up">
  <table class="table table-bordered table-hover table-striped align-middle text-center">
    <thead class="table-dark">
      <tr>
        <th>ID</th>
        <th>Icon</th>
        <th>Title</th>
        <th>Description</th>
        <th>Price</th>
        <th>Image</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = mysqli_fetch_assoc($result)): ?>
        <tr data-aos="fade-right">
          <td><?= $row['id'] ?></td>
          <td><i class="icon-preview <?= htmlspecialchars($row['icon']) ?> text-warning"></i></td>
          <td><?= htmlspecialchars($row['title']) ?></td>
          <td style="max-width: 300px; white-space: normal; text-align: left;">
            <?= htmlspecialchars($row['description']) ?>
          </td>
          <td><?= htmlspecialchars($row['price']) ?></td>
          <td>
            <img src="<?= htmlspecialchars($row['image']) ?>" alt="Service Image">
          </td>
          <td>
  <div class="d-flex justify-content-center gap-2 flex-wrap">
    <a href="edit_service_main.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
    <a href="delete_service_main.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger"
       onclick="return confirm('Delete this service?')">Delete</a>
  </div>
</td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

  <!-- Pagination -->
  <nav class="mt-4" data-aos="fade-up">
  <ul class="pagination justify-content-center">
    <?php if ($page > 1): ?>
      <li class="page-item">
        <a class="page-link" href="?title=<?= urlencode($filter_title) ?>&page=<?= $page - 1 ?>">Previous</a>
      </li>
    <?php endif; ?>

    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
      <li class="page-item <?= $i == $page ? 'active' : '' ?>">
        <a class="page-link" href="?title=<?= urlencode($filter_title) ?>&page=<?= $i ?>"><?= $i ?></a>
      </li>
    <?php endfor; ?>

    <?php if ($page < $total_pages): ?>
      <li class="page-item">
        <a class="page-link" href="?title=<?= urlencode($filter_title) ?>&page=<?= $page + 1 ?>">Next</a>
      </li>
    <?php endif; ?>
  </ul>
</nav>
</div>

<?php include("foot.php"); ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
  AOS.init({ duration: 1000, once: true });

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

  const titleInput = document.querySelector("input[name='title']");
if(titleInput){
  titleInput.addEventListener("input", function(){
    this.value = this.value.replace(/[^a-zA-Z\s]/g, "");
    if(this.value.length > 30) this.value = this.value.slice(0, 30);
  });
}

</script>
</body>
</html>
