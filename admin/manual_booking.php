<?php
// manual_booking.php
session_start();
include("auth-check.php"); 
include("connect.php");
$today = date('Y-m-d');

// Summary counts
$cnts = ['total'=>0,'home'=>0,'salon'=>0];
$q = $conn->prepare("SELECT service_type, COUNT(*) as c FROM bookings WHERE date=? GROUP BY service_type");
if ($q) {
  $q->bind_param("s", $today);
  $q->execute();
  $res = $q->get_result();
  while($r = $res->fetch_assoc()){
    $cnts['total'] += intval($r['c']);
    if(isset($cnts[$r['service_type']])) $cnts[$r['service_type']] = intval($r['c']);
  }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Admin — Manual Booking</title>
    <link rel="icon" href="images/logo.png" type="image/png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <style>
    :root {
      --accent:#ffc107;
      --bg:#f7f7fb;
    }
    body {
      background: linear-gradient(180deg,#f6f8fb,var(--bg));
      font-family: "Segoe UI", system-ui, -apple-system, "Helvetica Neue", Arial;
      transition: background .3s,color .3s;
    }
    /* Modern Booking Cards */
.card-cta {
  border-radius: 22px;
  padding: 2rem 1.5rem;
  background: rgba(255,255,255,0.08);
  backdrop-filter: blur(12px);
  border: 1px solid rgba(255,255,255,0.1);
  transition: all .25s ease;
  text-align: center;
  height: 100%;
}

.card-cta i {
  font-size: 48px;
  color: var(--accent);
  margin-bottom: 14px;
}

.card-cta h5 {
  font-weight: 600;
  margin-bottom: 10px;
}

.card-cta p {
  font-size: 0.92rem;
  color: #bbb;
  margin-bottom: 1.2rem;
}

/* Hover Effect */
.card-cta:hover {
  transform: translateY(-6px) scale(1.02);
  box-shadow: 0 0 25px rgba(255,193,7,0.3);
  border-color: rgba(255,193,7,0.4);
}

/* Buttons */
.card-cta .btn {
  border-radius: 14px;
  font-weight: 600;
  padding: 0.65rem 1rem;
  width: 100%;
  box-shadow: 0 3px 12px rgba(0,0,0,0.15);
  transition: all 0.25s;
}
.card-cta .btn:hover {
  box-shadow: 0 5px 18px rgba(255,193,7,0.45);
  transform: translateY(-2px);
}
.card-cta .btn i.bi-arrow-right-circle {
  font-size: 16px;   /* chhoti height */
  vertical-align: middle;
  color: #000;       /* black color */
}

/* Light mode look */
body:not(.dark-mode) .card-cta {
  background: #fff;
  border: 1px solid #eee;
  box-shadow: 0 6px 20px rgba(0,0,0,0.08);
}
body:not(.dark-mode) .card-cta p { color:#555; }

/* Dark mode look */
body.dark-mode .card-cta {
  background: #1d1d1d;
  border: 1px solid #333;
  box-shadow: 0 4px 15px rgba(0,0,0,0.6);
}
body.dark-mode .card-cta p { color:#aaa; }

    .small-muted { color:#6c757d; font-size:.95rem }
    .logo{height:45px;width:auto;}
    /* Summary card */
    /* Summary card same style as booking cards */
.summary-card {
  border-radius: 22px;
  padding: 1.2rem 1.5rem;
  background: rgba(255,255,255,0.08);
  backdrop-filter: blur(12px);
  border: 1px solid rgba(255,255,255,0.1);
  transition: all .3s ease;
  margin-top: 1rem;
}

/* Light mode */
body:not(.dark-mode) .summary-card {
  background: #fff;
  border: 1px solid #eee;
  box-shadow: 0 6px 20px rgba(0,0,0,0.08);
}

/* Dark mode */
body.dark-mode .summary-card {
  background: #1d1d1d;
  border: 1px solid #333;
  box-shadow: 0 4px 15px rgba(0,0,0,0.6);
}


    /* Dark mode */
    body.dark-mode { background:#121212; color:#fff; }
    body.dark-mode .small-muted { color:#aaa; }
  </style>
</head>
<body>
<?php include("navbar.php"); ?>

<div class="container my-5">
  <div class="row justify-content-center">
    <div class="col-lg-9">
      <div class="d-flex align-items-center mb-4" data-aos="fade-down">
        <img src="images/logo.png" alt="logo" class="logo me-3" onerror="this.style.display='none'">
        <div class="mb-3">
          <h3 class="mb-0">Manual Booking — <span class="accent">Admin</span></h3>
          <div class="small-muted">Add bookings quickly (single, multiple, or walk-in)</div>
        </div>
      </div>

      <!-- Alerts -->
      <?php if(!empty($_SESSION['msg'])): ?>
        <div class="alert alert-<?=htmlspecialchars($_SESSION['msg_type'] ?? 'info')?> alert-dismissible fade show">
          <?= htmlspecialchars($_SESSION['msg']) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['msg'], $_SESSION['msg_type']); ?>
      <?php endif; ?>

      <div class="row g-4">
        <!-- Single -->
        <div class="col-md-12 col-sm-12 col-lg-4" data-aos="zoom-in">
          <div class="card-cta">
            <i class="bi bi-person-plus-fill"></i>
            <h5>Quick Single</h5>
            <p class="small-muted">Add a single-person booking (home or salon).</p>
            <a href="manual_booking_single.php" class="btn btn-warning fw-semibold mt-2">
            <i class="bi bi-arrow-right-circle me-1"></i> Open Single
          </a>
          </div>
        </div>
        <!-- Multiple -->
        <div class="col-md-12 col-sm-12 col-lg-4" data-aos="zoom-in" data-aos-delay="100">
          <div class="card-cta">
            <i class="bi bi-people-fill"></i>
            <h5>Manual Multiple</h5>
            <p class="small-muted">Create booking for 2–6 persons with per person service.</p>
            <a href="manual_booking_multi.php" class="btn btn-warning fw-semibold w-100 mt-2">
              <i class="bi bi-arrow-right-circle me-1"></i> Open Multiple
            </a>
          </div>
        </div>
        <!-- Walk-in -->
        <div class="col-md-12 col-sm-12 col-lg-4" data-aos="zoom-in" data-aos-delay="200">
          <div class="card-cta">
            <i class="bi bi-hand-thumbs-up-fill"></i>
            <h5>Walk-in / Post Service</h5>
            <p class="small-muted">Record customers who already had service.</p>
            <a href="manual_booking_walkin.php" class="btn btn-warning fw-semibold w-100 mt-2">
              <i class="bi bi-arrow-right-circle me-1"></i> Open Walk-in
            </a>
          </div>
        </div>

        <!-- Summary -->
        <div class="col-12" data-aos="fade-up">
          <div class="summary-card mt-2">
            <h6 class="mb-2">Today's Summary (<?= $today ?>)</h6>
            <div class="d-flex flex-wrap gap-4 align-items-center small-muted">
              <div><strong><?= intval($cnts['total']) ?></strong> bookings</div>
              <div><strong><?= intval($cnts['home']) ?></strong> home</div>
              <div><strong><?= intval($cnts['salon']) ?></strong> salon</div>
              <div><a href="report-day.php" class="text-warning text-decoration-none fw-semibold">View Report →</a></div>
            </div>
          </div>
        </div>

      </div> <!-- row -->
    </div>
  </div>
</div>

<?php include("foot.php"); ?>

<!-- Dark mode toggle button -->
<button class="btn btn-dark position-fixed end-0 bottom-0 m-3 rounded-circle shadow" 
        id="darkToggle" title="Toggle Dark Mode">
  <i class="bi bi-moon-stars-fill"></i>
</button>

<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
  AOS.init({ duration: 1000});
  // Dark mode toggle
  const darkToggle=document.getElementById('darkToggle');
  if(localStorage.getItem('darkMode')==='enabled') document.body.classList.add('dark-mode');
  darkToggle.addEventListener('click',()=>{
    document.body.classList.toggle('dark-mode');
    localStorage.setItem('darkMode',
      document.body.classList.contains('dark-mode') ? 'enabled':'disabled');
  });
</script>
</body>
</html>
