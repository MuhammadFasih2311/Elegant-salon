<?php
session_start();
include("connect.php");

if (!isset($_SESSION['reset_email'])) {
  header("Location: forgot-password.php");
  exit;
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $new_password = $_POST['new_password'];
  $confirm_password = $_POST['confirm_password'];

  if ($new_password !== $confirm_password) {
    $error = "⚠️ Passwords do not match!";
  } elseif (strlen($new_password) < 8) {
    $error = "⚠️ Password must be at least 8 characters!";
  } else {
    $hashed = password_hash($new_password, PASSWORD_DEFAULT);
    $email = $_SESSION['reset_email'];

    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
    $stmt->bind_param("ss", $hashed, $email);

    if ($stmt->execute()) {
      unset($_SESSION['reset_email']);
      echo "<script>alert('Password updated successfully!'); window.location.href = 'login.php';</script>";
      exit;
    } else {
      $error = "❌ Error updating password.";
    }
    $stmt->close();
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Reset Password</title>
  <link rel="icon" href="images/logo.png" type="image/png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
  <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet" />

  <style>
    body {
      background: linear-gradient(135deg, #f8f9fa, #e9ecef);
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      padding: 1rem;
      font-family: 'Segoe UI', sans-serif;
    }
    .reset-card {
      background-color: #fff;
      border-radius: 16px;
      padding: 30px 25px;
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
      width: 100%;
      max-width: 400px;
      text-align: center;
    }
    .eye-btn {
      position: absolute;
      right: 10px;
      top: 50%;
      transform: translateY(-50%);
      background: none;
      border: none;
      color: #6c757d;
    }
    .position-relative { position: relative; }
    .tiny-hint { font-size: 0.85rem; text-align: left; }
  </style>
</head>
<body>

  <div class="reset-card" data-aos="zoom-in">
    <h4 class="mb-3 text-warning">🔑 Reset Your Password</h4>

    <?php if (!empty($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

    <form method="post" id="resetForm">
      <div class="mb-3 text-start">
        <label class="form-label">New Password</label>
        <div class="position-relative">
          <input type="password" class="form-control pe-5" id="new_password" name="new_password" required minlength="8">
          <button type="button" class="eye-btn" onclick="togglePassword('new_password','eye1')">
            <i class="bi bi-eye-slash" id="eye1"></i>
          </button>
        </div>
      </div>

      <div class="mb-3 text-start">
        <label class="form-label">Confirm Password</label>
        <div class="position-relative">
          <input type="password" class="form-control pe-5" id="confirm_password" name="confirm_password" required minlength="8">
          <button type="button" class="eye-btn" onclick="togglePassword('confirm_password','eye2')">
            <i class="bi bi-eye-slash" id="eye2"></i>
          </button>
        </div>
      </div>

      <!-- Password hint -->
      <div class="mb-3 text-start">
        <small id="passwordHint" class="tiny-hint"></small>
      </div>

      <button type="submit" id="updateBtn" class="btn btn-warning w-100" disabled>Update Password</button>
    </form>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
  <script>
    AOS.init({ duration: 1000 });

    function togglePassword(inputId, iconId) {
      const input = document.getElementById(inputId);
      const icon = document.getElementById(iconId);
      if(input.type==="password"){
        input.type="text";
        icon.classList.replace("bi-eye-slash","bi-eye");
      }else{
        input.type="password";
        icon.classList.replace("bi-eye","bi-eye-slash");
      }
    }

    const password = document.getElementById("new_password");
    const confirm = document.getElementById("confirm_password");
    const hint = document.getElementById("passwordHint");
    const btn = document.getElementById("updateBtn");

    function validatePasswords() {
      let okLen = password.value.length >= 8;
      let match = password.value === confirm.value && okLen;

      if (!okLen) {
        hint.textContent = "❌ Password must be at least 8 characters.";
        hint.className = "tiny-hint text-danger";
      } else if (!match) {
        hint.textContent = "❌ Passwords do not match.";
        hint.className = "tiny-hint text-danger";
      } else {
        hint.textContent = "✅ Passwords match.";
        hint.className = "tiny-hint text-success";
      }

      btn.disabled = !(okLen && match);
    }

    password.addEventListener("input", validatePasswords);
    confirm.addEventListener("input", validatePasswords);
  </script>
</body>
</html>
