<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit();
}
include("connect.php");

// Get logged-in user info
$user_id = $_SESSION['user_id'];
$user_email = "";
$user_name = "";

$user_query = $conn->prepare("SELECT name, email FROM users WHERE id = ?");
$user_query->bind_param("i", $user_id);
$user_query->execute();
$user_result = $user_query->get_result();
if ($user_result->num_rows > 0) {
  $u = $user_result->fetch_assoc();
  $user_email = $u['email'];
  $user_name = $u['name'];
}

// Pagination setup
$limit = 6;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// Search filter
$search = $_GET['search'] ?? "";
$searchTerm = "%$search%";

// Count total messages (with search)
if ($search) {
  $count_sql = "SELECT COUNT(*) as total FROM contacts WHERE email = ? AND (message LIKE ? OR reply LIKE ?)";
  $count_stmt = $conn->prepare($count_sql);
  $count_stmt->bind_param("sss", $user_email, $searchTerm, $searchTerm);
} else {
  $count_sql = "SELECT COUNT(*) as total FROM contacts WHERE email = ?";
  $count_stmt = $conn->prepare($count_sql);
  $count_stmt->bind_param("s", $user_email);
}
$count_stmt->execute();
$total_rows = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);

// Fetch messages (with search)
if ($search) {
$sql = "SELECT * FROM contacts 
        WHERE email = ? 
        AND (message LIKE ? OR reply LIKE ?) 
        ORDER BY created_at DESC 
        LIMIT $limit OFFSET $offset";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $user_email, $searchTerm, $searchTerm);
} else {
  $sql = "SELECT * FROM contacts WHERE email = ? ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user_email);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<?php include("auth.php"); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="View your sent messages and admin replies on Elegant Salon." />
  <title>My Messages - Elegant Salon</title>
  <link rel="icon" href="images/logo.png" type="image/png">
  <link rel="stylesheet" href="style.css" >
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
  <style>
    .card:hover p { color:white; }

    /* Dark mode */
    .dark-mode h2,
    .dark-mode p.text-muted,
    .dark-mode .text { color: #fff !important; }

    .dark-mode .card {
      background-color: #000 !important;
      color: #fff !important;
    }
    .dark-mode .card:hover {
      transform: scale(1.02);
      transition: all 0.3s ease;
    }
    .dark-mode .card p { color: #fff !important; }
    .dark-mode .card-title { color: #ffc107 !important; }
  </style>
</head>
<body>
<?php include("header.php");?>

<!-- Dark Mode Toggle -->
<button class="btn btn-dark position-fixed end-0 bottom-0 m-3 rounded-circle shadow" id="darkToggle">
  <i class="bi bi-moon-stars-fill"></i>
</button>

<br><br>

<!-- Hero Section -->
<div class="container text-center mt-5" data-aos="zoom-in">
  <h1 class="display-4 fw-bold text-warning">📨 My Messages</h1>
  <p class="lead">See your contact messages and admin replies</p>
</div>

<!-- Messages List -->
<div class="container py-5">
  <div class="text-center mb-5">
    <h2 class="fw-bold text-secondary text" data-aos="fade-up">Your Message History</h2>
    <p class="text-muted text" data-aos="fade-up" data-aos-delay="100">
      View all messages you’ve sent to the salon team and their replies.
    </p>
  </div>

  <!-- Search -->
  <form method="GET" class="row justify-content-center mb-4" data-aos="fade-up">
    <div class="col-md-5">
      <input type="text" name="search" class="form-control"
             value="<?= htmlspecialchars($search) ?>"
             placeholder="Search your messages or replies...">
    </div>
    <div class="col-md-2 d-flex gap-2">
      <button class="btn btn-primary w-100" type="submit">Search</button>
      <a href="my-messages.php" class="btn btn-secondary w-100">Reset</a>
    </div>
  </form>

  <div class="row g-4">
    <?php if ($result->num_rows > 0): ?>
      <?php while($row = $result->fetch_assoc()): 
        $status = !empty($row['reply']) ? "Replied" : "Pending";
        $badgeClass = !empty($row['reply']) ? "success" : "warning";
      ?>
      <div class="col-md-6 col-lg-4" data-aos="zoom-in">
        <div class="card shadow h-100 border-0 rounded-3">
          <div class="card-body">
            <h5 class="card-title text-warning mb-3">
              <i class="bi bi-chat-dots me-2"></i> Message #<?= $row['id'] ?>
            </h5>

            <p><strong>Name:</strong> <?= htmlspecialchars($row['name']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($row['email']) ?></p>

            <p><strong>Message:</strong><br> <?= nl2br(htmlspecialchars($row['message'])) ?></p>
            <p><strong>Sent At:</strong> <?= date("d M Y, h:i A", strtotime($row['created_at'])) ?></p>

            <p>
              <span class="badge bg-<?= $badgeClass ?> px-3 py-2"><?= $status ?></span>
            </p>

            <?php if (!empty($row['reply'])): ?>
              <div class="alert alert-success mt-3 text-start">
                <strong>Admin Reply:</strong><br>
                <?= nl2br(htmlspecialchars($row['reply'])) ?>
              </div>
            <?php else: ?>
              <div class="alert alert-secondary mt-3 text-start">
                ⏳ Awaiting admin reply...
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <?php endwhile; ?>
    <?php else: ?>
      <div class="col-12 text-center">
        <p class="text-muted">No messages found for your search.</p>
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

<!-- JS -->
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
AOS.init({ duration: 1000 });

// Dark Mode setup
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
