<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
include 'connect.php';

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current = $_POST['current_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    $res = $conn->query("SELECT password FROM users WHERE id=$user_id");
    $row = $res->fetch_assoc();

    if (!password_verify($current, $row['password'])) {
        $error = "❌ Current password is incorrect!";
    } elseif (strlen($new) < 8) {
        $error = "⚠️ New password must be at least 8 characters.";
    } elseif ($new !== $confirm) {
        $error = "⚠️ Passwords do not match!";
    } else {
        $hash = password_hash($new, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password=? WHERE id=?");
        $stmt->bind_param("si", $hash, $user_id);
        if ($stmt->execute()) {
            $_SESSION['success'] = "🔑 Password changed successfully!";
            header("Location: profile.php");
            exit();
        } else {
            $error = "❌ Failed to update password!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- 👈 important for mobile-->
  <title>Change Password - Elegant Salon</title>
  <link rel="icon" href="images/logo.png" type="image/png">
  <link rel="stylesheet" href="style.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

  <style>
    body { background:#f0f2f5; }
    .hero { 
      background:linear-gradient(135deg,#ff8a00,#e52e71); 
      min-height:200px; 
      color:#fff; 
      display:flex; 
      justify-content:center; 
      align-items:center; 
      text-align:center;
      padding:20px;
    }
    .form-card { 
      margin-top:-70px; 
      background:#fff; 
      border-radius:20px; 
      padding:30px; 
      box-shadow:0 10px 30px rgba(0,0,0,0.15); 
    }
    /* Dark Mode */
    .dark-mode .form-card { background:#000; color:#fff; }
    .dark-mode label { color:#ffc107 !important; }
    .dark-mode input { background:#222; color:#fff; border:1px solid #444; }

    /* Password toggle icon */
    .toggle-password {
      position: absolute;
      top: 50%;
      right: 12px;
      transform: translateY(-50%);
      cursor: pointer;
      font-size: 1.2rem;
      color: #6c757d;
      transition: color 0.2s;
    }
    .toggle-password:hover { color: #ffc107; }
    .dark-mode .toggle-password { color: #ccc; }

    /* Responsive Fix */
    @media (max-width: 576px) {
      .form-card { padding:15px; }
      .hero h1 { font-size:1.5rem; }
    }
  </style>
</head>
<body class="d-flex flex-column min-vh-100"> <!-- 👈 same as profile/edit -->

  <?php include 'header.php'; ?>

  <!-- Dark Mode Toggle -->
  <button class="btn btn-dark position-fixed end-0 bottom-0 m-3 rounded-circle shadow" id="darkToggle">
    <i class="bi bi-moon-stars-fill"></i>
  </button>

  <main class="flex-grow-1"> <!-- 👈 wrapper -->
    <section class="hero my-3" data-aos="fade-down">
      <h1><i class="bi bi-shield-lock"></i> Change Password</h1>
    </section>

    <div class="container">
      <div class="row justify-content-center">
        <div class="col-12 col-sm-10 col-md-8 col-lg-6">
          <div class="form-card" data-aos="fade-up">

            <?php if(!empty($error)): ?>
              <div class="alert alert-danger"><?= $error; ?></div>
            <?php endif; ?>
            <?php if(!empty($_SESSION['success'])): ?>
              <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <?php endif; ?>

            <form method="POST">
              <div class="mb-3 position-relative">
                <label class="form-label">Current Password</label>
                <input type="password" name="current_password" id="current_password" 
                       class="form-control" required maxlength="30" minlength="8">
                <i class="bi bi-eye-slash toggle-password" data-target="current_password"></i>
              </div>

              <div class="mb-3 position-relative">
                <label class="form-label">New Password</label>
                <input type="password" id="new_password" name="new_password" 
                       class="form-control" minlength="8" required maxlength="30">
                <i class="bi bi-eye-slash toggle-password" data-target="new_password"></i>
                <small id="newPassHint" class="text-danger"></small>
              </div>

              <div class="mb-3 position-relative">
                <label class="form-label">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" 
                       class="form-control" minlength="8" required maxlength="30">
                <i class="bi bi-eye-slash toggle-password" data-target="confirm_password"></i>
                <small id="confirmPassHint" class="text-danger"></small>
              </div>

              <div class="d-flex justify-content-between">
                <a href="profile.php" class="btn btn-secondary">Cancel</a>
                <button type="submit" id="updateBtn" class="btn btn-warning">🔒 Update Password</button>
              </div>
            </form>

          </div>
        </div>
      </div>
    </div>
  </main>

  <?php include 'footer.php'; ?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
  <script>
    AOS.init({ duration: 1000 });

    const newPass=document.getElementById('new_password');
    const confirmPass=document.getElementById('confirm_password');
    const newHint=document.getElementById('newPassHint');
    const confirmHint=document.getElementById('confirmPassHint');
    const btn=document.getElementById('updateBtn');

    function validatePasswords(){
      let validLen=newPass.value.length>=8;
      let match=newPass.value===confirmPass.value;
      newHint.textContent=validLen?"✅ Strong enough":"❌ Must be at least 8 characters";
      confirmHint.textContent=match?"✅ Passwords match":"❌ Passwords do not match";
      btn.disabled=!(validLen && match);
    }
    newPass.addEventListener('input',validatePasswords);
    confirmPass.addEventListener('input',validatePasswords);

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
      localStorage.setItem("dark-mode", 
        document.body.classList.contains("dark-mode") ? "enabled" : "disabled"
      );
    });

    // Toggle password visibility
    document.querySelectorAll(".toggle-password").forEach(icon => {
      icon.addEventListener("click", function() {
        const targetId = this.getAttribute("data-target");
        const input = document.getElementById(targetId);
        if (input.type === "password") {
          input.type = "text";
          this.classList.remove("bi-eye-slash");
          this.classList.add("bi-eye");
        } else {
          input.type = "password";
          this.classList.remove("bi-eye");
          this.classList.add("bi-eye-slash");
        }
      });
    });
  </script>
</body>
</html>
