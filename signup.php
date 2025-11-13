<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="description" content="Create your Elegant Salon account and start experiencing luxury beauty services today!" />
  <title>Sign Up - Elegant Salon</title>
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
    .signup-card {
      background-color: #ffffff;
      border-radius: 16px;
      padding: 30px 25px;
      box-shadow: 0 6px 18px rgba(0, 0, 0, 0.1);
      width: 100%;
      max-width: 400px;
    }
    .form-control { border-radius: 10px; }
    .btn-warning { border-radius: 12px; font-weight: 500; }
    .logo-text { font-size: 1.6rem; font-weight: bold; color: #003366; }
    .form-label { font-size: 0.9rem; }
    .form-control::placeholder { font-size: 0.85rem; }
    .position-relative { position: relative; }
    .eye-btn {
      position: absolute; top: 50%; right: 10px;
      transform: translateY(-50%);
      background: none; border: none; color: #6c757d;
    }
    .tiny-hint { font-size: 0.85rem; text-align: left; }
  </style>
</head>
<body>

<div class="signup-card text-center" data-aos="fade-up">
  <div class="mb-3">
    <img src="images/logo.png" alt="Elegant Salon" width="70">
    <div class="logo-text">Elegant Salon</div>
    <p class="text-muted small">Create your account</p>
  </div>

  <form method="post" action="signup_process.php">
    <div class="mb-2 text-start">
      <label for="fullname" class="form-label">Full Name</label>
      <input type="text" class="form-control" id="fullname" name="fullname" placeholder="Your full name" required pattern="^[A-Za-z\s]+$"/>
    </div>

    <div class="mb-2 text-start">
      <label for="email" class="form-label">Email Address</label>
      <input type="email" class="form-control" id="email" name="email" placeholder="you@example.com" required />
    </div>
    <div class="mb-2 text-start">
  <label for="phone" class="form-label">Phone Number</label>
  <input type="text" class="form-control" id="phone" name="phone" 
         placeholder="03XXXXXXXXX" required pattern="[0-9]{11}" maxlength="11"/>
  <small class="text-muted">Phone must be exactly 11 digits</small>
  </div>
    <div class="mb-2 text-start">
      <label for="password" class="form-label">Password</label>
      <div class="position-relative">
        <input type="password" class="form-control pe-5" id="password" name="password" required minlength="8" />
        <button type="button" class="eye-btn" onclick="togglePassword('password', 'eyeIcon1')">
          <i class="bi bi-eye-slash" id="eyeIcon1"></i>
        </button>
      </div>
    </div>

    <div class="mb-3 text-start">
      <label for="confirm_password" class="form-label">Confirm Password</label>
      <div class="position-relative">
        <input type="password" class="form-control pe-5" id="confirm_password" name="confirm_password" required minlength="8"/>
        <button type="button" class="eye-btn" onclick="togglePassword('confirm_password', 'eyeIcon2')">
          <i class="bi bi-eye-slash" id="eyeIcon2"></i>
        </button>
      </div>
    </div>

    <!-- Password hint -->
    <div class="mb-3 text-start">
      <small id="passwordHint" class="tiny-hint"></small>
    </div>

    <div class="d-grid mt-3">
      <button type="submit" name="signup" id="signupBtn" class="btn btn-warning text-dark" disabled>Sign Up</button>
    </div>
  </form>

  <p class="mt-3 small text-muted">Already have an account? <a href="login.php">Login</a></p>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
  AOS.init({ duration: 1000 });

  function togglePassword(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(iconId);
    if (input.type === "password") {
      input.type = "text";
      icon.classList.remove("bi-eye-slash");
      icon.classList.add("bi-eye");
    } else {
      input.type = "password";
      icon.classList.remove("bi-eye");
      icon.classList.add("bi-eye-slash");
    }
  }

  // === Live Password Validation ===
  const password = document.getElementById("password");
  const confirm = document.getElementById("confirm_password");
  const hint = document.getElementById("passwordHint");
  const btn = document.getElementById("signupBtn");

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

  // Allow only alphabets + space for fullname
  const nameInput = document.getElementById("fullname");
  nameInput.addEventListener("keypress", function(e) {
    if (!/[a-zA-Z\s]/.test(e.key)) e.preventDefault();
  });
  nameInput.addEventListener("input", function() {
    this.value = this.value.replace(/[^a-zA-Z\s]/g, "");
  });
  
  const phoneInput = document.getElementById("phone");
phoneInput.addEventListener("input", function() {
  this.value = this.value.replace(/[^0-9]/g, '').slice(0, 11);
});

</script>
</body>
</html>
