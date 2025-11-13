<?php
session_start();
include("auth-check.php"); 
include("connect.php");

$errors = [];
function getPrice($conn, $service, $sub_service) {
    $stmt = $conn->prepare("SELECT price FROM serve WHERE category=? AND label=? LIMIT 1");
    $stmt->bind_param("ss", $service, $sub_service);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    return $res ? (int)filter_var($res['price'], FILTER_SANITIZE_NUMBER_INT) : 0;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name   = trim($_POST['name'] ?? '');
    $mode   = $_POST['mode'] ?? 'auto';
    $date   = date('Y-m-d'); // today fixed
    $service_type = "salon";
    $email = "WalkIn";
    $address = "";
    $phone = "WalkIn"; // save as WalkIn
    $persons = (int)($_POST['persons'] ?? 1);

    // Round current time to nearest 15 min slot
    $mins = date("i");
    $rounded = floor($mins / 15) * 15;
    $time_slot = date("H", time()) . ":" . str_pad($rounded, 2, "0", STR_PAD_LEFT);

    if ($name === "" || strlen($name) > 30 || !preg_match("/^[A-Za-z\s]+$/", $name)) {
        $errors[] = "Valid name required.";
    }

    // services expected as array of per-person data
    if (empty($errors)) {
        if (empty($_POST['services']) || !is_array($_POST['services'])) {
            $errors[] = "Please provide service details for each person.";
        }
    }

    if (empty($errors)) {
        // validate each service entry first
        $services = $_POST['services'];
        foreach ($services as $idx => $s) {
            if ($mode === "auto") {
                $category    = trim($s['category'] ?? '');
                $sub_service = trim($s['sub_service'] ?? '');
                if ($category === '' || $sub_service === '') {
                    $errors[] = "Select category & sub-service for person " . ($idx+1) . ".";
                }
            } else {
                $category    = trim($s['custom_service'] ?? '');
                $sub_service = trim($s['custom_sub_service'] ?? '');
                $price       = (int)($s['custom_price'] ?? 0);
                if ($category === '' || $sub_service === '' || $price <= 0) {
                    $errors[] = "Provide service, sub-service and valid price for person " . ($idx+1) . ".";
                }
            }
        }
    }

    // If still no errors, insert one row per person
    if (empty($errors)) {
        foreach ($services as $s) {
            if ($mode === "auto") {
                $category    = trim($s['category'] ?? '');
                $sub_service = trim($s['sub_service'] ?? '');
                $price = getPrice($conn, $category, $sub_service);
            } else {
                $category    = trim($s['custom_service'] ?? '');
                $sub_service = trim($s['custom_sub_service'] ?? '');
                $price       = (int)($s['custom_price'] ?? 0);
            }

            $stmt = $conn->prepare("INSERT INTO bookings 
                (user_id,name,phone,email,address,service_type,service,sub_service,price,date,time_slot,created_at,status,persons) 
                VALUES(0,?,?,?,?,?,?,?,?,?,?,NOW(),'completed',?)");

            // types: name s, phone s, email s, address s, service_type s, service s, sub_service s, price i, date s, time_slot s, persons i
            $stmt->bind_param("sssssssissi",
                $name, $phone, $email, $address, $service_type,
                $category, $sub_service, $price, $date, $time_slot, $persons
            );
            if (!$stmt->execute()) {
                $errors[] = "DB Error: " . $stmt->error;
            }
            $stmt->close();
        }
    }

    if (empty($errors)) {
        $_SESSION['success'] = "✅ Walk-in entries saved for {$persons} person(s)!";
        header("Location: manual_booking_walkin.php");
        exit;
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Walk-in Entry</title>
<link rel="icon" href="images/logo.png" type="image/png">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<style>
  html, body {
  overflow-x: hidden;
}
body.dark-mode { background:#121212; color:#fff; }
.dark-mode .card { background:#1e1e1e; color:#fff; border:1px solid #333; }
.dark-mode .form-control, .dark-mode .form-select { background:#2a2a2a; color:#fff; border:1px solid #444; }
.dark-mode label { color:#ffc107; }
/* Dark mode select dropdown arrow white */
.dark-mode select.form-select {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='white' class='bi bi-caret-down-fill' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592c.86 0 1.319 1.013.753 1.658l-4.796 5.482a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right .75rem center;
    background-size: 10px 10px;
}
/* Make generated person blocks visually distinct */
.person-row { border: 1px dashed rgba(0,0,0,0.06); padding: 12px; border-radius: 8px; }
.dark-mode .person-row { border-color: rgba(255,255,255,0.06); }
/* ✅ Fix shrink & better responsive alignment */
.card {
  max-width: 100%;
  transition: all 0.3s ease;
}
.container {
  max-width: 1000px;
}
.person-row {
  border:1px dashed rgba(0,0,0,0.06);
  padding:12px;
  border-radius:8px;
}
.dark-mode .person-row { border-color:rgba(255,255,255,0.1); }

/* ✅ Responsive tweaks */
@media (max-width:768px) {
  .card { padding:20px 15px !important; }
  .form-label { font-size:0.9rem; }
  .col-md-6, .col-md-4 { flex:0 0 100%; max-width:100%; }
  .person-row { padding:10px; margin-bottom:12px; }
  #darkToggle {
    width:48px; height:48px;
  }
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
    <div class="col-lg-9">
      <div class="card p-4 shadow-lg border-0 rounded-4">
        <h3 class="mb-3 text-center fw-bold text-warning" data-aos="fade-down">
          <i class="bi bi-person-check"></i> Walk-in Entry
        </h3>

        <!-- Alerts -->
         <div class="fade-down">
        <?php if ($errors): ?>
          <div class="alert alert-danger alert-dismissible fade show" data-aos="fade-up">
            <ul class="mb-0"><?php foreach($errors as $e) echo "<li>".htmlspecialchars($e)."</li>"; ?></ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        <?php endif; ?>
        <?php if (!empty($_SESSION['success'])): ?>
          <div class="alert alert-success alert-dismissible fade show text-center" data-aos="fade-up">
            <?= $_SESSION['success']; unset($_SESSION['success']); ?>
            <button type="button" class="btn btn-close" data-bs-dismiss="alert"></button>
          </div>
        <?php endif; ?>
      </div>

        <form method="post" class="row g-3" data-aos="fade-up">
          <!-- Name -->
          <div class="col-md-6" data-aos="fade-right">
            <label class="form-label">Customer Name *</label>
            <input type="text" name="name" class="form-control" maxlength="30" minlength="2"
                   value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                   oninput="this.value=this.value.replace(/[^A-Za-z\s]/g,'')" required>
          </div>

          <!-- Mode -->
          <div class="col-md-6" data-aos="fade-left">
            <label class="form-label">Entry Mode *</label>
            <select name="mode" id="mode" class="form-select" required>
              <option value="auto" <?= ($_POST['mode']??'')==='auto'?'selected':'' ?>>Auto (Select Service)</option>
              <option value="custom" <?= ($_POST['mode']??'')==='custom'?'selected':'' ?>>Custom (Manual)</option>
            </select>
          </div>

          <!-- Persons -->
          <div class="col-md-6" data-aos="fade-right">
            <label class="form-label">Persons</label>
            <select name="persons" id="persons" class="form-select" required>
              <?php for($i=1;$i<=6;$i++): ?>
                <option value="<?=$i?>" <?= (($_POST['persons']??1)==$i)?'selected':'' ?>><?=$i?></option>
              <?php endfor; ?>
            </select>
          </div>

          <!-- Today fixed date -->
          <div class="col-md-6" data-aos="fade-up">
            <label class="form-label">Date</label>
            <input type="text" class="form-control" value="<?=date('Y-m-d')?>" disabled>
          </div>

           <!-- Apply for all -->
          <div class="col-md-12 d-flex align-items-end mt-3" data-aos="fade-right">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="applyAll">
              <label class="form-check-label fw-semibold" for="applyAll">Apply first person's values to all</label>
            </div>
          </div>

          <!-- Dynamic container -->
          <div id="personsContainer" class="row g-3"></div>

          <div class="col-12 text-center mt-3" data-aos="zoom-in-up">
            <button type="submit" class="btn btn-warning px-4 fw-semibold mb-2">
              <i class="bi bi-check-circle me-1"></i> Save Walk-in
            </button>
            <a href="manual_booking.php" class="btn btn-secondary ms-2">
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
AOS.init({ duration: 900, once: true });

// subservices map is optional here; we'll use AJAX per-row to keep data fresh
// helper to fetch subservices HTML for a category and callback with html
function fetchSubservices(category, callback) {
  if (!category) { callback('<option value="">Select Sub-Service</option>'); return; }
  $.get('subservices_api.php', { category: category }, function(data) {
    callback(data);
  });
}

function generatePersonBlocks(count) {
  let mode = $('#mode').val();
  let container = $('#personsContainer');
  container.html('');

  for (let i = 0; i < count; i++) {
    // wrapper row for each person so .closest finds it easily
    let wrapper = $('<div class="col-12 person-row mb-2" data-aos="fade-up"></div>');
    let inner = $('<div class="row g-3 align-items-end"></div>');

    if (mode === 'auto') {
      // Category select
      let catCol = $(`
        <div class="col-md-6">
          <label class="form-label">Person ${i+1} - Category *</label>
          <select name="services[${i}][category]" class="form-select category-select" data-index="${i}" required>
            <option value="">Select Category</option>
            <?php
            $res = $conn->query("SELECT DISTINCT category FROM serve ORDER BY category");
            while($r = $res->fetch_assoc()){
              echo "<option value=\"" . htmlspecialchars($r['category']) . "\">" . htmlspecialchars($r['category']) . "</option>";
            }
            ?>
          </select>
        </div>
      `);
      // Sub-service select
      let subCol = $(`
        <div class="col-md-6">
          <label class="form-label">Person ${i+1} - Sub-Service *</label>
          <select name="services[${i}][sub_service]" class="form-select sub-service-select" required>
            <option value="">Select Sub-Service</option>
          </select>
        </div>
      `);

      inner.append(catCol).append(subCol);
    } else {
      // custom mode inputs
      let svcCol = $(`
        <div class="col-md-4">
          <label class="form-label">Person ${i+1} - Service *</label>
          <input type="text" name="services[${i}][custom_service]" class="form-control" required>
        </div>
      `);
      let subCol = $(`
        <div class="col-md-4">
          <label class="form-label">Person ${i+1} - Sub-Service *</label>
          <input type="text" name="services[${i}][custom_sub_service]" class="form-control" required>
        </div>
      `);
      let priceCol = $(`
        <div class="col-md-4">
          <label class="form-label">Person ${i+1} - Price *</label>
          <input type="number" name="services[${i}][custom_price]" class="form-control" min="0" required>
        </div>
      `);
      inner.append(svcCol).append(subCol).append(priceCol);
    }

    wrapper.append(inner);
    container.append(wrapper);
  }

  // If there is old data from server (after validation error), restore selections (see below)
  restoreOldServicesIfAny();
}

// when category select changes, load subservices into sibling sub-service-select
$(document).on('change', '.category-select', function() {
  let $row = $(this).closest('.person-row');
  let $sub = $row.find('.sub-service-select');
  let cat = $(this).val();
  fetchSubservices(cat, function(html) {
    $sub.html(html);
  });
});

// Apply first to all behavior
function copyFirstToAll() {
  const apply = $('#applyAll').prop('checked');
  if (!apply) return;

  const firstRow = $('#personsContainer .person-row').first();
  if (!firstRow.length) return;

  const mode = $('#mode').val();

  if (mode === 'auto') {
    const firstCat = firstRow.find('.category-select').val();
    const firstSub = firstRow.find('.sub-service-select').val();

    // For each other row: set category, trigger change, then set sub after AJAX populates
    $('#personsContainer .person-row').each(function(idx) {
      if (idx === 0) return; // skip first
      const row = $(this);
      const catSelect = row.find('.category-select');
      const subSelect = row.find('.sub-service-select');
      if (firstCat) {
        catSelect.val(firstCat).trigger('change');
        // after fetchSubservices callback, set sub value if exists
        // fetchSubservices is called by the change handler and will populate subSelect
        // we set a short observer to set the sub when options appear
        let tries = 0;
        const setSubInterval = setInterval(function() {
          tries++;
          if (subSelect.find('option').length > 1 || tries > 20) {
            if (firstSub) subSelect.val(firstSub);
            clearInterval(setSubInterval);
          }
        }, 80);
      }
    });
  } else {
    // custom mode: copy inputs
    const svc = firstRow.find('input[name^="services"]').filter('[name$="[custom_service]"]').val();
    const sub = firstRow.find('input[name^="services"]').filter('[name$="[custom_sub_service]"]').val();
    const price = firstRow.find('input[name^="services"]').filter('[name$="[custom_price]"]').val();

    $('#personsContainer .person-row').each(function(idx) {
      if (idx === 0) return;
      const row = $(this);
      row.find('input[name$="[custom_service]"]').val(svc);
      row.find('input[name$="[custom_sub_service]"]').val(sub);
      row.find('input[name$="[custom_price]"]').val(price);
    });
  }
}

// Bind applyAll change & also re-copy when first row changes if applyAll is checked
$(document).on('change', '#applyAll', function() {
  if ($(this).is(':checked')) copyFirstToAll();
});

// If first row changes, re-apply to others when applyAll is checked
$(document).on('change', '.person-row:first .category-select, .person-row:first .sub-service-select, .person-row:first input', function() {
  if ($('#applyAll').is(':checked')) copyFirstToAll();
});

// initialize blocks
$('#persons, #mode').on('change', function() {
  generatePersonBlocks(parseInt($('#persons').val() || 1));
});

// on page load, generate initial
$(function() {
  generatePersonBlocks(parseInt($('#persons').val() || 1));
});

// Restore old POSTed services if validation failed (server-side)
function restoreOldServicesIfAny() {
  <?php if (!empty($_POST['services']) && is_array($_POST['services'])): ?>
    const old = <?= json_encode($_POST['services']); ?>;
    // iterate over old and set values
    setTimeout(()=>{ // slight delay to ensure DOM is ready
      $('#personsContainer .person-row').each(function(i) {
        if (!old[i]) return;
        const row = $(this);
        const data = old[i];
        if ($('#mode').val() === 'auto') {
          if (data.category) {
            row.find('.category-select').val(data.category).trigger('change');
            // after AJAX, set sub_service
            let tries = 0;
            const subSet = setInterval(function() {
              tries++;
              if (row.find('.sub-service-select option').length > 1 || tries > 30) {
                if (data.sub_service) row.find('.sub-service-select').val(data.sub_service);
                clearInterval(subSet);
              }
            }, 80);
          }
        } else {
          if (data.custom_service) row.find('input[name$="[custom_service]"]').val(data.custom_service);
          if (data.custom_sub_service) row.find('input[name$="[custom_sub_service]"]').val(data.custom_sub_service);
          if (data.custom_price) row.find('input[name$="[custom_price]"]').val(data.custom_price);
        }
      });
      // if applyAll was checked in POST, restore it
      <?php if (!empty($_POST['applyAll'])): ?>
        $('#applyAll').prop('checked', true);
        copyFirstToAll();
      <?php endif; ?>
    }, 150);
  <?php endif; ?>
}

// Dark mode persistence
if (localStorage.getItem("darkMode") === "enabled") document.body.classList.add("dark-mode");
$('#darkToggle').on('click', function(){
  document.body.classList.toggle("dark-mode");
  localStorage.setItem("darkMode", document.body.classList.contains("dark-mode") ? "enabled" : "disabled");
});

// Auto-hide alerts
setTimeout(()=>{
  document.querySelectorAll('.alert').forEach(el=>{
    let bsAlert = bootstrap.Alert.getOrCreateInstance(el);
    bsAlert.close();
  });
},8000);
</script>
</body>
</html>
