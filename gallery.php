<?php include("auth.php"); 
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Browse our Elegant Salon gallery showcasing stunning hair styles, skincare, and beauty transformations." />
  <title>Elegant Salon</title>
  <link rel="icon" href="images/logo.png" type="image/png">
  <link rel="stylesheet" href="style.css" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
  <style>
    html { scroll-behavior: smooth; }
    .gallery-img {
      width: 90%; height: 400px; object-fit: cover;
      border-radius: 10px;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      cursor: pointer;
    }
    .gallery-img:hover {
      transform: scale(1.03);
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
    }
    .category-title {
      color: #ffc107; text-align: center;
      font-weight: bold; margin-bottom: 20px;
    }
    .gallery-nav a {
      border-radius: 50px; padding: 10px 20px;
      font-weight: 500; transition: all 0.3s ease;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .gallery-nav a:hover, .gallery-nav a:focus {
      background-color: #ffc107 !important;
      color: #212529 !important;
      box-shadow: 0 6px 20px rgba(0,0,0,0.2);
      transform: translateY(-2px);
       transform: scale(1.05);
  transition: all 0.3s ease-in-out;
    }
    .modal-body-custom {
      display: flex; gap: 20px; flex-wrap: wrap; justify-content: center; align-items: center;
    }
    .modal-body-custom img {
      width: 100%; max-width: 350px; border-radius: 10px;
    }
    .modal-details {
      flex: 1;
      text-align: center;
    }
    /* Custom placeholder for input[type="date"] */
input[type="date"]::before {
  content: attr(placeholder);
  position: absolute;
  left: 50%;
  transform: translateX(-50%);
  color: #6c757d; /* Bootstrap placeholder color */
}

/* Jab user select kare ya focus ho toh placeholder gayab */
input[type="date"]:focus::before,
input[type="date"]:valid::before {
  content: "";
}
/* Dark mode for booking form */
.dark-mode .dark-mode-bg {
  background: #222 !important;
  color: #fff;
}
.dark-mode .dark-mode-bg label {
  color: #ffc107 !important;
}
.dark-mode small.text-muted {
  color: #bbb !important;
}
  </style>
</head>
<body>
<?php
include("header.php");
?>
<br>
<button class="btn btn-dark position-fixed end-0 bottom-0 m-3 rounded-circle shadow" id="darkToggle">
  <i class="bi bi-moon-stars-fill"></i>
</button>
<?php
include("connect.php");
$categories = [];
$sql = "SELECT * FROM serve ORDER BY category";
$result = mysqli_query($conn, $sql);
while ($row = mysqli_fetch_assoc($result)) {
  $cat = $row['category'];
  if (!isset($categories[$cat])) $categories[$cat] = [];
  $categories[$cat][] = [
    'label' => $row['label'],
    'price' => $row['price'],
    'img' => $row['image']
  ];
}
$ids = ['Hair Services' => 'hair','Makeup' => 'makeup','Facial Treatments' => 'facial','Nail Care' => 'nailcare','Hair Coloring' => 'coloring','Bridal Packages' => 'bridal'];
?>
<br>
<div class="container my-5">
  <h1 class="text-center text-warning mb-5 mt-3" data-aos="fade-down">SALON GALLERY</h1>
  <div class="text-center my-5 gallery-nav">
    <?php foreach ($ids as $name => $id): ?>
      <a href="#<?= $id ?>" class="btn btn-outline-warning mx-2" data-aos="zoom-in"><?= $name ?></a>
    <?php endforeach; ?>
  </div>
  <?php foreach ($categories as $category => $services): ?>
    <div id="<?= $ids[$category] ?? strtolower(str_replace(' ', '', $category)) ?>" class="mb-5" data-aos="fade-up">
      <h2 class="category-title"><?= $category ?></h2>
      <div class="row">
  <?php 
  $i = 0; 
  foreach ($services as $service): 
      $delay = $i * 200; // har card ka delay 0ms, 200ms, 400ms
  ?>
    <div class="col-md-4 col-sm-12 col-lg-4 mb-3" data-aos="fade-up" data-aos-delay="<?= $delay ?>">
      <img src="gallery images/<?= $service['img'] ?>" class="gallery-img" 
           alt="<?= $category ?>" 
           onclick="openModal('<?= $category ?>', 'gallery images/<?= $service['img'] ?>', 'Style: <?= $service['label'] ?>', 'Price: <?= $service['price'] ?>')">
    </div>
  <?php 
  $i++;
  endforeach; 
  ?>
</div>
    </div>
  <?php endforeach; ?>
</div>

<!-- Modal -->
<div class="modal fade" id="serviceModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content bg-dark text-light">
      <div class="modal-header border-0">
        <h5 class="modal-title" id="serviceModalLabel"></h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body modal-body-custom">
        <img id="modalImage" src="" alt="Service Image">
        <div class="modal-details">
          <p id="modalDescription"></p>
          <p id="modalPrice" class="fw-bold text-warning"></p>
          <a href="#booking" class="btn btn-warning mt-2" onclick="setServiceAndScroll()">Book Now</a>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Booking Section -->
<section class="container my-5" id="booking">
  <div class="card shadow-lg border-0 rounded-4 p-4 bg-light dark-mode-bg">
    <h2 class="text-center text-warning mb-4" data-aos="zoom-in">
      <i class="bi bi-calendar2-check"></i> Book Your Appointment
    </h2>

      <?php if(isset($_SESSION['msg'])): ?>
  <div class="alert alert-<?= $_SESSION['msg_type'] ?? 'info' ?> alert-dismissible fade show text-center" role="alert" id="flashMsg">
    <?= $_SESSION['msg']; unset($_SESSION['msg'], $_SESSION['msg_type']); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
<?php endif; ?>


    <!-- Toggle Buttons -->
    <ul class="nav nav-pills justify-content-center mb-4" id="bookingTabs" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" id="single-tab" data-bs-toggle="pill" data-bs-target="#single" type="button" role="tab" data-aos="fade-right">Single Booking</button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="multiple-tab" data-bs-toggle="pill" data-bs-target="#multiple" type="button" role="tab" data-aos="fade-left">Multiple Booking</button>
      </li>
    </ul>

    <div class="alert alert-info text-center mb-4" data-aos="zoom-in">
  <i class="bi bi-info-circle"></i> 
  Note: You can book <b>maximum 3 appointments in 1 hour</b> for single booking, 
  and <b>only 1 multiple booking in 30 minutes</b>.
</div>

    <!-- Tab Content -->
    <div class="tab-content" id="bookingTabsContent">

      <!-- Single Booking Form -->
      <div class="tab-pane fade show active" id="single" role="tabpanel">
        <form method="post" action="booking_process.php" class="row g-3 booking-form">
          
        <!-- Service Type (Home / Salon) -->
  <div class="d-flex justify-content-center mb-4">
    <div class="form-check form-check-inline" data-aos="fade-right">
      <input class="form-check-input" type="radio" name="service_type_single" value="home" 
  <?= (($_SESSION['old']['service_type_single'] ?? 'home') === 'home') ? 'checked' : '' ?>>
<label class="form-check-label fw-semibold" for="homeService_single">For Home Service</label>
    </div>
    <div class="form-check form-check-inline ms-3" data-aos="fade-left">    
<input class="form-check-input" type="radio" name="service_type_single" value="salon"
  <?= (($_SESSION['old']['service_type_single'] ?? '') === 'salon') ? 'checked' : '' ?>>
<label class="form-check-label fw-semibold" for="salonService_single">For Salon Booking</label>
    </div>
  </div>
        
        <!-- Name -->
          <div class="col-md-6" data-aos="fade-right">
            <label class="form-label fw-semibold">Full Name</label>
            <input type="text" name="name" class="form-control form-control-lg rounded-3"
                   value="<?= htmlspecialchars($_SESSION['old']['name'] ?? $_SESSION['name'] ?? '') ?>" 
                   oninput="this.value=this.value.replace(/[^A-Za-z\s]/g,'')" required maxlength="30">
          </div>
          <!-- Email -->
          <div class="col-md-6" data-aos="fade-left">
            <label class="form-label fw-semibold">Email</label>
            <input type="email" name="email" class="form-control form-control-lg rounded-3"
                   value="<?= htmlspecialchars($_SESSION['email'] ?? '') ?>" readonly>
          </div>
          <!-- Phone -->
          <div class="col-md-6" data-aos="fade-right">
            <label class="form-label fw-semibold">Phone</label>
            <input type="text" name="phone" maxlength="11"
                   class="form-control form-control-lg rounded-3"
                   value="<?= htmlspecialchars($_SESSION['old']['phone'] ?? $_SESSION['phone'] ?? '') ?>"  required pattern="\d{11}">  
            <small class="text-muted">Must be exactly 11 digits</small>
          </div>
          <!-- Date -->
          <div class="col-md-6" data-aos="fade-left">
            <label class="form-label fw-semibold">Select Date</label>
            <input type="date" name="date" class="form-control form-control-lg rounded-3"
       id="dateInput1" required
       value="<?= htmlspecialchars($_SESSION['old']['date'] ?? '') ?>">
          </div>
          <!-- Time & Persons -->
          <div class="col-md-12 d-flex gap-3" data-aos="fade-right">
            <div class="flex-grow-1">
              <label class="form-label fw-semibold">Select Time</label>
              <select name="time_slot" class="form-select form-select-lg rounded-3" required>
            <?php 
            $start = strtotime("11:00");
            $end   = strtotime("20:45");
            for($t=$start; $t<=$end; $t+=15*60){
              $slot = date("H:i", $t);
              $selected = ($_SESSION['old']['time_slot'] ?? '') === $slot ? 'selected' : '';
              echo "<option value='$slot' $selected>$slot</option>";
            }
            ?>
          </select>
            </div>
            <div style="width:120px">
              <label class="form-label fw-semibold">Persons</label>
              <input type="number" class="form-control form-control-lg rounded-3" value="1" disabled>
            </div>
          </div>
          <!-- Address -->
          <div class="col-md-12 address-field-single" data-aos="fade-right">
            <label class="form-label fw-semibold">Address</label>
            <textarea name="address" class="form-control form-control-lg rounded-3" rows="3" maxlength="200" minlength="5"><?= htmlspecialchars($_SESSION['old']['address'] ?? '') ?></textarea>
          </div>
          <!-- Service -->
          <div class="col-md-6" data-aos="fade-right">
            <label class="form-label fw-semibold">Service Category</label>
            <select class="form-select form-select-lg rounded-3" name="service" id="service1" required onchange="updateSubServices1()">
            <option value="">-- Choose Category --</option>
            <?php foreach ($categories as $service => $_): 
              $sel = ($_SESSION['old']['service'] ?? '') === $service ? 'selected' : '';
              echo "<option value=\"$service\" $sel>$service</option>";
            endforeach; ?>
          </select>
          </div>
          <!-- Sub Service -->
          <div class="col-md-6" data-aos="fade-left">
            <label class="form-label fw-semibold">Sub Service</label>
            <select class="form-select form-select-lg rounded-3" name="sub_service" id="sub_service1" required>
            <option value="">-- Choose Sub-Service --</option>
            <?php if (!empty($_SESSION['old']['service']) && isset($categories[$_SESSION['old']['service']])) {
                foreach ($categories[$_SESSION['old']['service']] as $item) {
                    $sel = ($_SESSION['old']['sub_service'] ?? '') === $item['label'] ? "selected" : "";
                    echo "<option value=\"{$item['label']}\" $sel>{$item['label']} ({$item['price']})</option>";
                }
            }
            ?>
          </select>
          </div>
          <!-- Hidden Persons -->
          <input type="hidden" name="persons" value="1">
          <!-- Submit -->
          <div class="col-12 text-center mt-4" data-aos="fade-up">
            <button type="submit" name="book" class="btn btn-warning px-5 py-2 fw-semibold shadow-sm rounded-3">
              <i class="bi bi-check2-circle"></i> Book Now
            </button>
          </div>
        </form>
      </div>

      <!-- Multiple Booking -->
      <div class="tab-pane fade" id="multiple">
        <form method="post" action="booking_process.php" class="row g-3 booking-form">
          <input type="hidden" name="multi_booking" value="1">
          <!-- Service Type -->
          <div class="d-flex justify-content-center mb-4">
            <div class="form-check form-check-inline" data-aos="fade-right">
              <input class="form-check-input" type="radio" name="service_type_multi" value="home" 
          <?= (($_SESSION['old']['service_type_multi'] ?? 'home') === 'home') ? 'checked' : '' ?>>
        <label class="form-check-label fw-semibold">For Home Service</label>
                    </div>
            <div class="form-check form-check-inline ms-3" data-aos="fade-left">
              <input class="form-check-input" type="radio" name="service_type_multi" value="salon"
        <?= (($_SESSION['old']['service_type_multi'] ?? '') === 'salon') ? 'checked' : '' ?>>
      <label class="form-check-label fw-semibold">For Salon Booking</label>
          </div>
  </div>
        
    <!-- Common Info -->
    <div class="col-md-6" data-aos="fade-right">
      <label class="form-label fw-semibold">Full Name</label>
      <input type="text" name="name" class="form-control form-control-lg rounded-3"
             value="<?= htmlspecialchars($_SESSION['old']['name'] ?? $_SESSION['name'] ?? '') ?>" 
             oninput="this.value=this.value.replace(/[^A-Za-z\s]/g,'')" 
             required maxlength="30">
    </div>
    <div class="col-md-6" data-aos="fade-left">
      <label class="form-label fw-semibold">Email</label>
      <input type="email" name="email" class="form-control form-control-lg rounded-3"
             value="<?= htmlspecialchars($_SESSION['email'] ?? '') ?>" readonly>
    </div>
    <div class="col-md-6" data-aos="fade-right">
      <label class="form-label fw-semibold">Phone</label>
      <input type="text" name="phno" maxlength="11"
             value="<?= htmlspecialchars($_SESSION['old']['phno'] ?? $_SESSION['phone'] ?? '') ?>" 
             class="form-control form-control-lg rounded-3"
             pattern="\d{11}" required>
      <small class="text-muted">Must be exactly 11 digits</small>
    </div>
    <div class="col-md-6" data-aos="fade-left">
      <label class="form-label fw-semibold">Select Date</label>
      <input type="date" name="date" class="form-control form-control-lg rounded-3" id="dateInput2" value="<?= htmlspecialchars($_SESSION['old']['date'] ?? '') ?>" required>
    </div>
    <div class="col-md-6" data-aos="fade-right">
      <label class="form-label fw-semibold">Select Time</label>
      <select name="time_slot" class="form-select form-select-lg rounded-3" required>
        <?php 
        $start = strtotime("11:00");
        $end   = strtotime("20:45");
        for($t=$start; $t<=$end; $t+=15*60){
          $slot = date("H:i", $t);
          $selected = (($_SESSION['old']['time_slot'] ?? '') === $slot) ? "selected" : "";
          echo "<option value='$slot' $selected>$slot</option>";
        }
        ?>
      </select>
    </div>
    <div class="col-md-6" data-aos="fade-left">
      <label class="form-label fw-semibold">Number of Persons</label>
      <select id="personCount" class="form-select form-select-lg rounded-3" required>
    <option value="">-- Select --</option>
    <?php for($i=2;$i<=6;$i++): 
      $sel = (($_SESSION['old']['multi_services'] ?? false) && count($_SESSION['old']['multi_services']) == $i) ? "selected" : "";
    ?>
      <option value="<?= $i ?>" <?= $sel ?>><?= $i ?></option>
    <?php endfor; ?>
  </select>
    </div>

    <!-- Address (toggle via Home/Salon) -->
    <div class="col-md-12 address-field-multi mb-3" data-aos="fade-right">
      <label class="form-label fw-semibold">Address</label>
      <textarea name="address" class="form-control form-control-lg rounded-3" rows="3" maxlength="200" minlength="5"><?= htmlspecialchars($_SESSION['old']['address'] ?? '') ?></textarea>
    </div>

    <div class="col-md-12" data-aos="fade-right">
  <div class="alert alert-warning py-2">
    <i class="bi bi-info-circle"></i>
    Tick below if all persons want the same service.
  </div>
  <div class="form-check my-3" data-aos="fade-right">
    <input class="form-check-input" type="checkbox" id="applyAll">
    <label class="form-check-label fw-semibold" for="applyAll">
      Apply same service & sub-service to all persons
    </label>
  </div>
</div>

    <!-- Dynamic Persons Services -->
    <div id="multiServicesContainer" class="col-12"></div>

    <div class="col-12 text-center mt-4" data-aos="zoom-in">
      <button type="submit" name="book" class="btn btn-warning px-5 py-2 fw-semibold shadow-sm rounded-3">
        <i class="bi bi-check2-circle"></i> Book Now
      </button>
    </div>
  </form>
</div>

    </div>
  </div>
</section>
<?php unset($_SESSION['old']); ?>

<?php include("footer.php"); ?>

<script>
  const oldMultiServices = <?php echo json_encode($_SESSION['old']['multi_services'] ?? []); ?>;
</script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>

  // ===============================
  // 🔹 Init AOS animations
  // ===============================
  AOS.init({
  duration: 1000,
  startEvent: 'load' // ✅ page load hote hi trigger
});


  // ===============================
  // 🔹 Modal Logic
  // ===============================
  let selectedService = '';
  let selectedSubService = '';

  function openModal(service, imagePath, description, price) {
    selectedService = service;
    selectedSubService = description.replace("Style: ", "").trim();

    document.getElementById("serviceModalLabel").innerText = service;
    document.getElementById("modalImage").src = imagePath;
    document.getElementById("modalDescription").innerText = description;
    document.getElementById("modalPrice").innerText = price;

    new bootstrap.Modal(document.getElementById('serviceModal')).show();
  }

  function setServiceAndScroll() {
    const serviceSelect = document.getElementById("service1");
    if (serviceSelect) {
      serviceSelect.value = selectedService;
      updateSubServices1();

      // Modal ke baad sub-service select karte waqt
      setTimeout(() => {
        const subSelect = document.getElementById("sub_service1");
        if (subSelect) {
          for (let opt of subSelect.options) {
            if (opt.value === selectedSubService) {
              subSelect.value = selectedSubService;
              break;
            }
          }
        }
      }, 300);
    }

    // Hamesha Single tab open kare
    document.querySelector("#single-tab").classList.add("active");
    document.querySelector("#multiple-tab").classList.remove("active");
    document.querySelector("#single").classList.add("show", "active");
    document.querySelector("#multiple").classList.remove("show", "active");

    document.getElementById("booking").scrollIntoView({ behavior: "smooth" });
    bootstrap.Modal.getInstance(document.getElementById('serviceModal')).hide();
  }

  // ===============================
  // 🔹 Dark Mode Toggle
  // ===============================
  if (localStorage.getItem("dark-mode") === "enabled") {
    document.body.classList.add("dark-mode");
    document.getElementById('darkToggle').classList.add("btn-light");
    document.getElementById('darkToggle').classList.remove("btn-dark");
  }

  document.getElementById('darkToggle').addEventListener('click', function () {
    document.body.classList.toggle('dark-mode');
    this.classList.toggle('btn-light');
    this.classList.toggle('btn-dark');
    localStorage.setItem("dark-mode", document.body.classList.contains("dark-mode") ? "enabled" : "disabled");
  });

  // ===============================
  // 🔹 Phone Validation
  // ===============================
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
  setupPhoneValidation("phno");

  // ===============================
  // 🔹 Date Min (today onwards)
  // ===============================
  const today = new Date();
  const yyyy = today.getFullYear();
  const mm = String(today.getMonth() + 1).padStart(2, '0');
  const dd = String(today.getDate()).padStart(2, '0');
  const formattedToday = `${yyyy}-${mm}-${dd}`;
  if (document.getElementById("dateInput1")) {
    document.getElementById("dateInput1").setAttribute("min", formattedToday);
  }
  if (document.getElementById("dateInput2")) {
    document.getElementById("dateInput2").setAttribute("min", formattedToday);
  }

  // ===============================
  // 🔹 Subservices Map (from PHP)
  // ===============================
  const subServicesMap = <?php echo json_encode($categories); ?>;
  function updateSubServices1() {
    const service = document.getElementById('service1').value;
    const subSelect = document.getElementById('sub_service1');
    subSelect.innerHTML = '<option value="">-- Choose Sub-Service --</option>';
    if (service && subServicesMap[service]) {
      subServicesMap[service].forEach((item, idx) => {
        const option = document.createElement('option');
        option.value = item.label;
        option.text  = `${item.label} (${item.price})`;
        if (idx === 0) option.selected = true; // ✅ auto select first
        subSelect.appendChild(option);
      });
    }
  }

  // ===============================
  // 🔹 URL Param (auto select category)
  // ===============================
  const urlParams = new URLSearchParams(window.location.search);
  const selectedCategory = urlParams.get('category');
  if (selectedCategory) {
    const serviceSelect = document.getElementById("service1");
    if (serviceSelect) {
      serviceSelect.value = selectedCategory;
      updateSubServices1();
    }
  }

  // ===============================
  // 🔹 Address field toggle (Home/Salon)
  // ===============================
  function toggleAddress(radioName, addressSelector) {
    const radios = document.querySelectorAll(`input[name='${radioName}']`);
    const addressField = document.querySelector(addressSelector);
    if (!addressField) return;
    const textarea = addressField.querySelector("textarea");

    function applyToggle() {
      radios.forEach(radio => {
        if (radio.checked) {
          if (radio.value === "salon") {
            addressField.style.display = "none";
            textarea.disabled = true;
            textarea.removeAttribute("required");
          } else {
            addressField.style.display = "block";
            textarea.disabled = false;
            textarea.setAttribute("required", "required");
          }
        }
      });
    }

    // Listen to changes
    radios.forEach(radio => radio.addEventListener("change", applyToggle));

    // Run once on page load
    applyToggle();
  }

  // ✅ Single booking radio
  toggleAddress("service_type_single", ".address-field-single");
  // ✅ Multiple booking radio
  toggleAddress("service_type_multi", ".address-field-multi");

  // ===============================
  // 🔹 Dynamic Multiple Booking (Persons)
  // ===============================
  const personCountEl = document.getElementById('personCount');
  const container = document.getElementById('multiServicesContainer');

  function generateMultiBlocks(count, restore = true) {
    container.innerHTML = "";

    for (let i = 0; i < count; i++) {
      const block = document.createElement('div');
      block.className = "row g-3 border rounded p-3 mb-3 bg-light";
      block.setAttribute("data-aos", "fade-up");

      // Service options
      let serviceOptions = '<option value="">-- Choose Category --</option>';
      for (const service in subServicesMap) {
        const selected = (restore && oldMultiServices[i]?.service === service) ? "selected" : "";
        serviceOptions += `<option value="${service}" ${selected}>${service}</option>`;
      }

      // Subservice options
      let subOptions = '<option value="">-- Choose Sub-Service --</option>';
      if (restore && oldMultiServices[i]?.service && subServicesMap[oldMultiServices[i].service]) {
        subServicesMap[oldMultiServices[i].service].forEach(item => {
          const sel = oldMultiServices[i]?.sub_service === item.label ? "selected" : "";
          subOptions += `<option value="${item.label}" ${sel}>${item.label} (${item.price})</option>`;
        });
      }

      block.innerHTML = `
        <div class="col-md-6" data-aos="fade-right">
          <label class="form-label fw-semibold">Person ${i+1} - Service Category</label>
          <select class="form-select form-select-lg rounded-3 service-select" 
                  name="multi_services[${i}][service]" required>
            ${serviceOptions}
          </select>
        </div>
        <div class="col-md-6" data-aos="fade-left">
          <label class="form-label fw-semibold">Person ${i+1} - Sub Service</label>
          <select class="form-select form-select-lg rounded-3 sub-service-select" 
                  name="multi_services[${i}][sub_service]" required>
            ${subOptions}
          </select>
        </div>
      `;

      container.appendChild(block);

      // Service change → update subservices
      block.querySelector(".service-select").addEventListener("change", function() {
        const service = this.value;
        const subSelect = block.querySelector(".sub-service-select");
        subSelect.innerHTML = '<option value="">-- Choose Sub-Service --</option>';
        if (service && subServicesMap[service]) {
          subServicesMap[service].forEach(item => {
            const opt = document.createElement("option");
            opt.value = item.label;
            opt.text  = `${item.label} (${item.price})`;
            subSelect.appendChild(opt);
          });
        }
      });
    }

    // ✅ Only refresh new dynamic blocks (not whole form)
    setTimeout(() => {
      container.querySelectorAll("[data-aos]").forEach(el => {
        el.classList.remove("aos-animate");
      });
      AOS.refreshHard(); // sirf naye blocks trigger karega
    }, 50);

    bindApplyAll();
  }

  // Person dropdown change
  if (personCountEl) {
    personCountEl.addEventListener('change', function() {
      const count = parseInt(this.value);
      if (count > 0) {
        generateMultiBlocks(count, true);
      } else {
        container.innerHTML = "";
      }
    });

    // ✅ Restore from session if available
    if (oldMultiServices.length > 0) {
      personCountEl.value = oldMultiServices.length;
      generateMultiBlocks(oldMultiServices.length, true);
    }
  }

  // ===============================
  // 🔹 Apply to All Logic
  // ===============================
  const applyAllChk = document.getElementById("applyAll");

  function copyFirstToAll() {
    const firstService = document.querySelector("#multiServicesContainer .service-select")?.value;
    const firstSub     = document.querySelector("#multiServicesContainer .sub-service-select")?.value;

    if (firstService) {
      document.querySelectorAll("#multiServicesContainer .service-select").forEach((sel, idx) => {
        if (idx > 0) {
          sel.value = firstService;
          sel.dispatchEvent(new Event("change"));
        }
      });
    }

    if (firstSub) {
      document.querySelectorAll("#multiServicesContainer .sub-service-select").forEach((sel, idx) => {
        if (idx > 0) {
          sel.value = firstSub;
        }
      });
    }
  }

  function bindApplyAll() {
    if (!applyAllChk) return;
    const firstService = document.querySelector("#multiServicesContainer .service-select");
    const firstSub     = document.querySelector("#multiServicesContainer .sub-service-select");

    if (firstService) {
      firstService.addEventListener("change", function () {
        if (applyAllChk.checked) copyFirstToAll();
      });
    }
    if (firstSub) {
      firstSub.addEventListener("change", function () {
        if (applyAllChk.checked) copyFirstToAll();
      });
    }

    if (applyAllChk.checked) {
      copyFirstToAll();
    }
  }

  // ✅ Also run when user checks later
  if (applyAllChk) {
    applyAllChk.addEventListener("change", function() {
      if (this.checked) {
        copyFirstToAll();
      }
    });
  }

  // ===============================
  // 🔹 Flash message auto hide + fix footer overlap
  // ===============================
  document.addEventListener("DOMContentLoaded", () => {
  // ✅ Tab restore after reload
  let activeTab = "<?php echo $_SESSION['activeTab'] ?? ''; unset($_SESSION['activeTab']); ?>";
  if (!activeTab) {
    activeTab = sessionStorage.getItem("activeTab");
  }
  if (activeTab) {
    const trigger = document.querySelector(`[data-bs-target="${activeTab}"]`);
    if (trigger) new bootstrap.Tab(trigger).show();
  }

  // ✅ Save tab click
  document.querySelectorAll('#bookingTabs button').forEach(btn => {
    btn.addEventListener('shown.bs.tab', e => {
      sessionStorage.setItem("activeTab", e.target.getAttribute("data-bs-target"));
    });
  });

  // ✅ Scroll to booking if alert
  const flashMsg = document.getElementById("flashMsg");
  if (flashMsg) {
    document.getElementById("booking").scrollIntoView({ behavior: "smooth" });
    setTimeout(() => {
      let alert = bootstrap.Alert.getOrCreateInstance(flashMsg);
      alert.close();
    }, 8000);
  }

  // ✅ Force animation for top inputs on page load
  triggerInitialAOS();
});

function triggerInitialAOS() {
  document.querySelectorAll("[data-aos]").forEach(el => {
    el.classList.add("aos-init"); // AOS ka initial state
    setTimeout(() => {
      el.classList.add("aos-animate"); // Animate force karna
    }, 200);
  });
}
// Page load ke baad nudge
window.addEventListener("load", () => {
  setTimeout(() => {
    AOS.refreshHard();
  }, 300);
});

// ✅ Jab bhi Single <-> Multiple tab switch hoga, dobara animation trigger
document.querySelectorAll('#bookingTabs button').forEach(btn => {
  btn.addEventListener('shown.bs.tab', () => {
    setTimeout(() => {
      AOS.refreshHard();
    }, 200);
  });
});

</script>
</body>
</html>
