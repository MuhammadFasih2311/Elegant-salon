<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include 'connect.php';

$user_id = $_SESSION['user_id'];
$sql = "SELECT id, name, email, phone, created_at FROM users WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- 👈 important for mobile -->
  <title>My Profile - Elegant Salon</title>
  <link rel="icon" href="images/logo.png" type="image/png">
  <link rel="stylesheet" href="style.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" rel="stylesheet">
  <style>
    body { background:#f0f2f5; }
    .profile-hero { 
      background:linear-gradient(135deg,#ff8a00,#e52e71); 
      min-height:200px; 
      color:#fff; 
      display:flex; 
      justify-content:center; 
      align-items:center; 
      text-align:center;
      padding: 20px;
    }
    .profile-card { 
      margin-top:-60px; 
      background:#fff; 
      border-radius:20px; 
      padding:30px 20px; 
      box-shadow:0 8px 30px rgba(0,0,0,0.1); 
      max-width:600px; 
      width:100%;
      margin-left:auto;
      margin-right:auto;
    }
    .profile-avatar { 
      width:90px; 
      height:90px; 
      border-radius:50%; 
      background:#ff8a00; 
      color:#fff; 
      font-size:35px; 
      display:flex; 
      align-items:center; 
      justify-content:center; 
      margin:-60px auto 15px auto; 
      box-shadow:0 5px 15px rgba(0,0,0,0.2); 
    }
    /* Responsive Fix */
    @media (max-width: 576px) {
      .profile-card { padding:20px 15px; }
      .profile-avatar { width:70px; height:70px; font-size:28px; margin-top:-50px; }
      .profile-hero h1 { font-size:1.5rem; }
      p { font-size:0.9rem; }
    }
    /* Dark Mode */
    .dark-mode .profile-card { background:#000; color:#fff; }
    .dark-mode p, .dark-mode h4 { color:#fff !important; }
  </style>
</head>
<body class="d-flex flex-column min-vh-100"> <!-- 👈 yaha lagao -->

  <?php include 'header.php'; ?>

  <main class="flex-grow-1"> <!-- 👈 main content wrapper -->

    <!-- Dark Mode Toggle -->
    <button class="btn btn-dark position-fixed end-0 bottom-0 m-3 rounded-circle shadow" id="darkToggle">
      <i class="bi bi-moon-stars-fill"></i>
    </button>

    <section class="profile-hero my-4" data-aos="fade-down">
      <h1><?= htmlspecialchars($user['name']); ?></h1>
    </section>

    <div class="container">
      <div class="profile-card text-center mb-3" data-aos="fade-up">
        <div class="profile-avatar"><i class="bi bi-person"></i></div> 
        
    <?php if(isset($_SESSION['success'])): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= $_SESSION['success']; unset($_SESSION['success']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>

    <?php if(isset($_SESSION['error'])): ?>
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= $_SESSION['error']; unset($_SESSION['error']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>

    <h4>👤 Personal Information</h4>
    <p><strong>Name:</strong> <?= htmlspecialchars($user['name']); ?></p>
    <p><strong>Email:</strong> <?= htmlspecialchars($user['email']); ?></p>
    <p><strong>Phone:</strong> <?= htmlspecialchars($user['phone']); ?></p>
    <p><strong>Joined:</strong> <?= date("M d, Y", strtotime($user['created_at'])); ?></p>

    <div class="mt-3 d-flex flex-wrap justify-content-center gap-2">
      <a href="edit-profile.php" class="btn btn-primary"><i class="bi bi-pencil-square"></i> Edit Profile</a>
      <a href="change-password.php" class="btn btn-outline-danger"><i class="bi bi-shield-lock"></i> Change Password</a>
    </div>
  </div>
    </div>
  </main>

  <!-- 👇 footer hamesha body ke andar aur <main> ke baad hona chahiye -->
  <?php include 'footer.php'; ?> 

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
<script>
  AOS.init({ duration: 1000 });

  // Dark mode load check
  if (localStorage.getItem("dark-mode") === "enabled") {
    document.body.classList.add("dark-mode");
    darkToggle.classList.add("btn-light");
    darkToggle.classList.remove("btn-dark");
  }

  document.getElementById('darkToggle').addEventListener('click', function () {
    document.body.classList.toggle("dark-mode");
    this.classList.toggle("btn-light");
    this.classList.toggle("btn-dark");

    if (document.body.classList.contains("dark-mode")) {
      localStorage.setItem("dark-mode", "enabled");
    } else {
      localStorage.setItem("dark-mode", "disabled");
    }
  });
  
  // Auto close alerts after 5 sec
  setTimeout(() => {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
      const bsAlert = new bootstrap.Alert(alert);
      bsAlert.close();
    });
  }, 5000);
</script>
</body>
</html>
