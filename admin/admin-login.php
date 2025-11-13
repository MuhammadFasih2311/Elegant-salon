<?php
session_start();
include("connect.php");

$email_cookie = $_COOKIE['admin_email'] ?? '';
$error = '';

if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM admin WHERE email=? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row['password'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $row['id'];
            $_SESSION['admin_email'] = $email;

            if (isset($_POST['remember'])) {
                // generate token
                $rawToken = bin2hex(random_bytes(32));
                $hashedToken = password_hash($rawToken, PASSWORD_DEFAULT);

                // save hash in DB
                $up = $conn->prepare("UPDATE admin SET remember_token=? WHERE id=?");
                $up->bind_param("si", $hashedToken, $row['id']);
                $up->execute();

                // set cookies (30 days)
                $expire = time() + (86400 * 30);
                setcookie("admin_email", $email, $expire, "/", "", isset($_SERVER['HTTPS']), true);
                setcookie("admin_token", $rawToken, $expire, "/", "", isset($_SERVER['HTTPS']), true);
            } else {
                // clear cookies
                setcookie("admin_email", "", time() - 3600, "/");
                setcookie("admin_token", "", time() - 3600, "/");
            }

            header("Location: manage-services.php");
            exit;
        } else {
            $error = "Incorrect password.";
        }
    } else {
        $error = "Admin not found.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="description" content="Admin login for Elegant Salon dashboard. Manage services, orders, and customer interactions securely." />
  <title>Admin Login</title>
  <link rel="icon" href="images/logo.png" type="image/png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(135deg, #f8f9fa, #e9ecef);
      height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: 'Segoe UI', sans-serif;
    }
    .login-card {
      background: white;
      border-radius: 16px;
      padding: 30px 25px;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
      max-width: 400px;
      width: 100%;
    }
    .eye-btn {
      position: absolute;
      top: 50%;
      right: 10px;
      transform: translateY(-50%);
      background: none;
      border: none;
    }
    .position-relative {
      position: relative;
    }
  </style>
</head>
<body>
  <div class="login-card">
    <h3 class="text-center text-primary mb-4">🔐 Admin Login</h3>

    <?php if ($error): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post">
      <div class="mb-3">
        <label>Email</label>
        <input type="email" name="email" class="form-control" 
               value="<?= htmlspecialchars($email_cookie) ?>" required>
      </div>

      <div class="mb-3 position-relative">
        <label>Password</label>
        <!-- no value prefilled for password -->
        <input type="password" name="password" id="password" class="form-control" required>
        <button type="button" class="eye-btn" onclick="togglePassword()">
          <i class="bi bi-eye-slash" id="eyeIcon"></i>
        </button>
      </div>

      <div class="form-check mb-3">
        <input class="form-check-input" type="checkbox" name="remember" id="remember" 
               <?= $email_cookie ? 'checked' : '' ?>>
        <label class="form-check-label" for="remember">Remember me</label>
      </div>

      <button type="submit" name="login" class="btn btn-primary w-100">Login</button>
    </form>
  </div>

  <script>
    function togglePassword() {
      const input = document.getElementById('password');
      const icon = document.getElementById('eyeIcon');
      if (input.type === "password") {
        input.type = "text";
        icon.classList.replace("bi-eye-slash", "bi-eye");
      } else {
        input.type = "password";
        icon.classList.replace("bi-eye", "bi-eye-slash");
      }
    }
  </script>
</body>
</html>
