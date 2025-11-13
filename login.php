<?php
session_start();
// optional: generate CSRF token if you want to enable CSRF checks
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <meta name="description" content="Login to your Elegant Salon account to manage bookings, profile, and preferences." />
  <title>Login - Elegant Salon</title>
  <link rel="icon" href="images/logo.png" type="image/png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
  <style>
    /* your existing styles (kept same) */
    body { background: linear-gradient(135deg, #f8f9fa, #e9ecef); display:flex; justify-content:center; align-items:center; min-height:100vh; font-family:'Segoe UI',sans-serif; padding:1rem; }
    .login-card { background:#fff; border-radius:20px; padding:40px 30px; box-shadow:0 8px 20px rgba(0,0,0,0.1); width:100%; max-width:400px; }
    .form-control{border-radius:10px}
    .btn-warning{border-radius:15px; font-weight:500}
    .logo-text{font-size:1.8rem; font-weight:bold; color:#003366}
    .eye-btn{position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;z-index:10}
    @media (max-width:576px){ .login-card{padding:30px 20px} }
  </style>
</head>
<body>

  <div class="login-card text-center" data-aos="zoom-in">
    <div class="mb-4">
      <img src="images/logo.png" alt="Elegant Salon" width="80">
      <div class="logo-text">Elegant Salon</div>
      <p class="text-muted small mt-2">Welcome back! Please login.</p>
    </div>

   <form method="post" action="login_process.php">
     <!-- CSRF token -->
     <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

     <div class="mb-3 text-start">
       <label for="email" class="form-label">Email address</label>
       <input type="email" class="form-control" id="email" name="email"
              value="<?= htmlspecialchars($_COOKIE['user_email'] ?? '') ?>"
              placeholder="you@example.com" required>
     </div>

     <div class="mb-3 text-start">
       <label for="password" class="form-label">Password</label>
       <div class="position-relative">
         <!-- NOTE: No password prefill from cookie -->
         <input type="password" class="form-control pe-5" id="password" name="password"
                placeholder="Enter password" required>
         <button type="button" class="eye-btn" onclick="togglePassword('password','eyeIcon')">
           <i class="bi bi-eye-slash" id="eyeIcon"></i>
         </button>
       </div>
     </div>

     <div class="form-check mb-3 text-start">
       <!-- default unchecked -->
       <input class="form-check-input" type="checkbox" name="remember" id="remember">
       <label class="form-check-label" for="remember">Remember me</label>
     </div>

     <div class="d-grid mt-2">
       <button type="submit" class="btn btn-warning text-dark">Login</button>
     </div>
   </form>

    <p class="mt-3 small text-muted">Don't have an account? <a href="signup.php">Sign up</a></p>
    <div class="mb-2 text-center">
      <a href="forgot-password.php" class="text-decoration-none small">Forgot Password?</a>
    </div>
  </div>

  <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
  <script>
    AOS.init({ duration: 1000 });
    function togglePassword(inputId, iconId) {
      const input = document.getElementById(inputId);
      const icon = document.getElementById(iconId);
      if (input.type === "password") { input.type = "text"; icon.classList.remove("bi-eye-slash"); icon.classList.add("bi-eye"); }
      else { input.type = "password"; icon.classList.remove("bi-eye"); icon.classList.add("bi-eye-slash"); }
    }
  </script>
</body>
</html>
