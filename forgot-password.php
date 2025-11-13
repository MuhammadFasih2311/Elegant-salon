<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Forgot Password - Elegant Salon</title>
  <link rel="icon" href="images/logo.png" type="image/png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet" />

  <style>
    body {
      background: linear-gradient(135deg, #f8f9fa, #e9ecef);
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      font-family: 'Segoe UI', sans-serif;
      padding: 1rem;
    }

    .forgot-card {
      background-color: #fff;
      border-radius: 16px;
      padding: 30px 25px;
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
      width: 100%;
      max-width: 400px;
    }

    a {
      text-decoration: none;
    }

    .btn-warning {
      font-weight: 500;
    }
  </style>
</head>
<body>

  <div class="forgot-card text-center" data-aos="fade-down">
    <h4 class="mb-3 text-warning">🔐 Forgot Password</h4>

    <?php if (!empty($_SESSION['error'])): ?>
      <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <form method="post" action="check-email.php">
      <div class="mb-3 text-start">
        <label class="form-label">Enter your registered email</label>
        <input type="email" name="email" class="form-control" placeholder="you@example.com" required>
      </div>
      <button type="submit" class="btn btn-warning w-100">Next</button>
    </form>

    <p class="mt-3 small text-muted"><a href="login.php">← Back to Login</a></p>
  </div>

  <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
  <script>AOS.init({ duration: 1000 });</script>

</body>
</html>
