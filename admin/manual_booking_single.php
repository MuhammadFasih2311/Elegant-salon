<?php
// manual_booking_single.php
session_start();
include("auth-check.php"); 
include("connect.php");

$errors = [];
$success = "";

// ✅ Price fetch helper
function getPrice($conn, $service, $sub_service) {
    $stmt = $conn->prepare("SELECT price FROM serve WHERE category=? AND label=? LIMIT 1");
    $stmt->bind_param("ss", $service, $sub_service);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    return $res ? (int)filter_var($res['price'], FILTER_SANITIZE_NUMBER_INT) : 0;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name   = trim($_POST['name'] ?? '');
    $phone  = trim($_POST['phone'] ?? '');
    $service_type = $_POST['service_type'] ?? '';
    $address = trim($_POST['address'] ?? '');
    $category = $_POST['category'] ?? '';
    $sub_service = $_POST['sub_service'] ?? '';
    $date   = $_POST['date'] ?? '';
    $time_slot = $_POST['time_slot'] ?? '';

    // 🔹 Validations
    if ($name === "" || strlen($name) > 30 || !preg_match("/^[A-Za-z\s]+$/", $name)) {
        $errors[] = "Name required (alphabets only, max 30 chars).";
    }
    if ($phone === "" || !preg_match("/^[0-9]{11}$/", $phone)) {
        $errors[] = "Phone must be exactly 11 digits.";
    }
    if ($service_type === "home" && strlen($address) < 5) $errors[] = "Address required for Home service.";
    if (!$category) $errors[] = "Category is required.";
    if (!$sub_service) $errors[] = "Sub-service is required.";
    if (!$date || $date < date('Y-m-d')) $errors[] = "Invalid Date.";
    if (!$time_slot) $errors[] = "Time slot is required.";

    // 🔹 Slot Restriction
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM bookings WHERE date=? AND time_slot=? AND service_type=?");
        $stmt->bind_param("sss", $date,$time_slot,$service_type);
        $stmt->execute();
        $count = $stmt->get_result()->fetch_assoc()['cnt'];

        if ($service_type === "home" && $count >= 1) {
            $errors[] = "⚠️ Slot already full! Only 1 home booking allowed.";
        }
        if ($service_type === "salon" && $count >= 2) {
            $errors[] = "⚠️ Slot already full! Only 2 salon bookings allowed.";
        }
    }

    // ✅ Auto price fetch from serve
    $price = getPrice($conn, $category, $sub_service);

    if (empty($errors)) {
        $email = "Admin"; // ✅ fixed email for manual bookings

        $stmt = $conn->prepare("INSERT INTO bookings 
            (user_id,name,phone,email,address,service_type,service,sub_service,price,date,time_slot,created_at,status,persons) 
            VALUES (0,?,?,?,?,?,?,?,?,?,?,NOW(),'pending',1)");

        $stmt->bind_param("sssssssiss", 
    $name,        // s
    $phone,       // s
    $email,       // s
    $address,     // s
    $service_type,// s
    $category,    // s
    $sub_service, // s ✅ string hai
    $price,       // i ✅ integer
    $date,        // s
    $time_slot    // s
);

        if ($stmt->execute()) {
            $_SESSION['success'] = "✅ Booking created successfully!";
            header("Location: manual_booking_single.php"); // ✅ redirect to reset form
            exit;
        } else {
            $errors[] = "DB Error: ".$stmt->error;
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Manual Booking (Single)</title>
<link rel="icon" href="images/logo.png" type="image/png">
  <link rel="icon" href="images/logo.png" type="image/png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<style>
  html, body {
  overflow-x: hidden;
}
body {
  background: linear-gradient(135deg, #f8f9fa, #e9ecef);
}
body.dark-mode {
  background: #121212;
  color: #fff;
}

/* Card styling */
.card {
  background: #fff;
  border-radius: 16px;
  padding: 40px;
  max-width: 900px;
  margin: 0 auto;
  box-shadow: 0 4px 25px rgba(0,0,0,0.1);
  transition: all 0.3s ease-in-out;
  width: 100%;
}
body.dark-mode .card {
  background: #1e1e1e;
  color: #fff;
  border: 1px solid #333;
}

/* Typography & spacing */
h3 {
  font-size: 1.8rem;
}
.form-label {
  font-weight: 500;
}

/* Form alignment */
.form-control, .form-select, textarea {
  border-radius: 8px;
}
button.btn {
  border-radius: 10px;
}

/* Dark mode form controls */
.dark-mode .form-control,
.dark-mode .form-select {
  background: #2a2a2a;
  color: #fff;
  border: 1px solid #444;
}
.dark-mode label {
  color: #ffc107;
}
.dark-mode input[type="date"]::-webkit-calendar-picker-indicator {
  filter: invert(1);
}
.dark-mode select.form-select {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='white' class='bi bi-caret-down-fill' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592c.86 0 1.319 1.013.753 1.658l-4.796 5.482a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right .75rem center;
  background-size: 10px 10px;
}

/* ----------- Responsive Fixes ----------- */
@media (max-width: 1200px) {
  .card {
    max-width: 90%;
    padding: 35px;
  }
}

@media (max-width: 992px) {
  .card {
    max-width: 95%;
    padding: 30px;
  }
}

@media (max-width: 768px) {
  .container {
    padding: 0 15px !important;
  }
  .card {
    width: 100%;
    max-width: 100%;
    padding: 25px 20px !important;
    margin: 15px auto;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
  }
  h3 {
    font-size: 1.4rem;
  }
  .form-label {
    font-size: 0.9rem;
  }
  input, textarea, select, button {
    font-size: 0.9rem !important;
  }
  .alert {
    font-size: 0.9rem;
  }
}

@media (max-width: 576px) {
  .container {
    padding: 0 10px !important;
  }
  .card {
    padding: 18px 15px !important;
    border-radius: 10px;
    width: 100%;
    max-width: 100%;
  }
  h3 {
    font-size: 1.2rem !important;
  }
  .alert ul {
    padding-left: 15px;
  }
}

/* Dark mode toggle button */
#darkToggle {
  z-index: 9999;
  transition: background-color 0.3s ease, color 0.3s ease;
}
body.dark-mode #darkToggle {
  background: #fff !important;
  color: #000 !important;
  border: none;
}
@media(max-width:576px){
  #darkToggle { bottom: 15px; right: 15px; }
}
</style>
</head>
<body>
<?php include("navbar.php"); ?>

<!-- Dark Mode Toggle -->
<button class="btn btn-dark position-fixed end-0 bottom-0 m-3 rounded-circle shadow" id="darkToggle">
  <i class="bi bi-moon-stars-fill"></i>
</button>

<div class="container my-5">
  <div class="row justify-content-center">
    <div class="col-lg-8">
      <div class="card p-4 shadow-lg border-0 rounded-4">
        <h3 class="mb-3 text-center fw-bold text-warning" data-aos="fade-down">
          <i class="bi bi-calendar2-check"></i> Manual Booking (Single)
        </h3>

        <!-- Alerts -->
  <div data-aos="fade-down">
  <?php if ($errors): ?>
    <div class="alert alert-danger alert-dismissible fade show" data-aos="fade-up">
      <ul class="mb-0"><?php foreach($errors as $e) echo "<li>$e</li>"; ?></ul>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>
  <?php if (!empty($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show text-center" data-aos="fade-up">
      <?= $_SESSION['success']; unset($_SESSION['success']); ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>
  </div>

        <!-- Booking Form -->
        <form method="post" class="row g-3" data-aos="fade-up">
          <!-- Name -->
          <div class="col-md-6" data-aos="fade-right">
            <label class="form-label">Name *</label>
            <input type="text" name="name" class="form-control" maxlength="30" minlength="3"
                   value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                   oninput="this.value=this.value.replace(/[^A-Za-z\s]/g,'')" required>
          </div>

          <!-- Phone -->
          <div class="col-md-6" data-aos="fade-left">
            <label class="form-label">Phone *</label>
            <input type="text" name="phone" maxlength="11" class="form-control"
                   value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>"
                   pattern="\d{11}" required>
            <small class="text-muted">Must be exactly 11 digits</small>
          </div>

          <!-- Service Type -->
          <div class="col-md-6" data-aos="fade-right">
            <label class="form-label">Service Type *</label>
            <select name="service_type" id="service_type" class="form-select" required>
              <option value="">Select Service Type</option>
              <option value="salon" <?= ($_POST['service_type'] ?? '')==='salon' ? 'selected' : '' ?>>Salon</option>
              <option value="home"  <?= ($_POST['service_type'] ?? '')==='home'  ? 'selected' : '' ?>>Home</option>
            </select>
          </div>

          <!-- Persons -->
          <div class="col-md-6" data-aos="fade-left">
            <label class="form-label">Persons</label>
            <input type="text" value="1" class="form-control" disabled>
          </div>

          <!-- Address -->
          <div class="col-12" id="address_row" style="<?= (($_POST['service_type'] ?? '')==='home') ? '' : 'display:none;' ?>" data-aos="fade-right">
            <label class="form-label">Address</label>
            <textarea name="address" class="form-control" rows="2" maxlength="200"><?= htmlspecialchars($_POST['address'] ?? '') ?></textarea>
          </div>

          <!-- Category -->
          <div class="col-md-6" data-aos="fade-right">
            <label class="form-label">Category *</label>
            <select name="category" id="category" class="form-select" required>
              <option value="">Select Category</option>
              <?php
              $res=$conn->query("SELECT DISTINCT category FROM serve");
              while($r=$res->fetch_assoc()){
                  $sel = ($_POST['category'] ?? '') === $r['category'] ? 'selected' : '';
                  echo "<option value='{$r['category']}' $sel>{$r['category']}</option>";
              }
              ?>
            </select>
          </div>

          <!-- Sub-Service -->
          <div class="col-md-6" data-aos="fade-left">
            <label class="form-label">Sub-Service *</label>
            <select name="sub_service" id="sub_service" class="form-select" required>
              <option value="">Select Sub-Service</option>
              <?php if(!empty($_POST['category'])): 
                $cat = $conn->real_escape_string($_POST['category']);
                $res=$conn->query("SELECT label,price FROM serve WHERE category='$cat'");
                while($r=$res->fetch_assoc()){
                  $sel = ($_POST['sub_service'] ?? '') === $r['label'] ? 'selected' : '';
                  echo "<option value='{$r['label']}' $sel>{$r['label']} ({$r['price']})</option>";
                }
              endif; ?>
            </select>
          </div>

          <!-- Date -->
          <div class="col-md-6" data-aos="fade-right">
            <label class="form-label">Date *</label>
            <input type="date" name="date" class="form-control" min="<?=date('Y-m-d')?>"
                   value="<?= htmlspecialchars($_POST['date'] ?? '') ?>" required>
          </div>

          <!-- Time Slot -->
          <div class="col-md-6" data-aos="fade-left">
            <label class="form-label">Time Slot *</label>
            <select name="time_slot" class="form-select" required>
                <option value="">Select Time Slot</option>
                <?php
                $start = strtotime("11:00");
                $end   = strtotime("20:45");
                for($t=$start; $t<=$end; $t+=15*60){
                    $slotValue = date("H:i", $t);  
                    $slotLabel = date("h:i A", $t);
                    $sel = ($_POST['time_slot'] ?? '') === $slotValue ? 'selected' : '';
                    echo "<option value='$slotValue' $sel>$slotLabel</option>";
                }
                ?>
            </select>
          </div>

          <!-- Submit -->
          <div class="col-12 text-center mt-3" data-aos="zoom-in-up">
            <button type="submit" class="btn btn-warning px-4 fw-semibold mb-2" data-aos="fade-right">
              <i class="bi bi-check-circle me-1"></i> Confirm Booking
            </button>
            <a href="manual_booking.php" class="btn btn-secondary ms-2" data-aos="fade-left">
              <i class="bi bi-arrow-left"></i> Back
            </a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>


<?php include("foot.php"); ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
  AOS.init({ duration: 1000, });
// 🔹 Address toggle
$('#service_type').on('change',()=>$('#address_row').toggle($('#service_type').val()==='home'));

// 🔹 Load sub-services with price display
$('#category').on('change', function(){
  $.get('subservices_api.php',{category:$(this).val()}, data=>{
    $('#sub_service').html(data);
  });
});

// Dark mode apply on page load
if (localStorage.getItem("darkMode") === "enabled") {
  document.body.classList.add("dark-mode");
}

// Toggle button
$('#darkToggle').on('click', function(){
  document.body.classList.toggle("dark-mode");
  localStorage.setItem("darkMode", document.body.classList.contains("dark-mode") ? "enabled" : "disabled");
});

// 🔹 Phone validation (only digits, max 11)
function setupPhoneValidation(inputName) {
  const input = document.querySelector(`input[name='${inputName}']`);
  if (!input) return;
  input.addEventListener("keypress", function(e) {
    if (!/[0-9]/.test(e.key)) e.preventDefault();
  });
  input.addEventListener("input", function() {
    this.value = this.value.replace(/[^0-9]/g, ""); 
    if (this.value.length > 11) this.value = this.value.slice(0,11);
  });
}
setupPhoneValidation("phone");

// 🔹 Auto-hide alerts
setTimeout(()=>{
  document.querySelectorAll('.alert').forEach(el=>{
    let bsAlert = bootstrap.Alert.getOrCreateInstance(el);
    bsAlert.close();
  });
},8000);
</script>
</body>
</html>
