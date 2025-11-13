<?php
// report-day.php
include("auth-check.php"); 
include("connect.php");

// helper to convert price
function price_to_int($price_str) {
    if (!$price_str) return 0;
    $digits = preg_replace('/[^\d]/', '', $price_str);
    return $digits === '' ? 0 : intval($digits);
}

// default date
$selected_date = $_GET['date'] ?? date('Y-m-d');

// fetch bookings
$bookings = [];
$total_revenue = 0;
$total_count = 0;

$sql = "SELECT b.id, b.name, b.phone, b.service, b.sub_service, b.service_type, 
               b.price, b.date, b.created_at, b.status, b.persons, b.time_slot,
               s.price AS sprice
        FROM bookings b
        LEFT JOIN serve s 
          ON b.service = s.category AND b.sub_service = s.label
        WHERE DATE(b.date) = ? AND b.status='completed'
        ORDER BY b.created_at ASC";


if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("s", $selected_date);
    $stmt->execute();
    $res = $stmt->get_result();
   while ($row = $res->fetch_assoc()) {
    $num = ($row['price'] && $row['price'] > 0) 
           ? intval($row['price']) 
           : price_to_int($row['sprice'] ?? '');
    $row['price_numeric'] = $num;
    $total_revenue += $num;
    $total_count++;
    $bookings[] = $row;
}
    $stmt->close();
}

$sql_today = "SELECT b.id, b.name, b.phone, b.service, b.sub_service, b.service_type, 
                     b.price, b.date, b.created_at, b.status, b.persons, b.time_slot,
                     s.price AS sprice
              FROM bookings b
              LEFT JOIN serve s 
                ON b.service = s.category AND b.sub_service = s.label
              WHERE DATE(b.date) = ? AND b.status = 'completed'";
$stmt_today = $conn->prepare($sql_today);

// === SUMMARY FOR SELECTED DATE === (Accurate Cards Data)
$today_summary = [
    'total_bookings' => 0,
    'total_revenue'  => 0,
    'home_bookings'  => 0,
    'home_revenue'   => 0,
    'salon_bookings' => 0,
    'salon_revenue'  => 0
];

if (!empty($bookings)) {
    foreach ($bookings as $b) {
        $num = floatval($b['price_numeric'] ?? 0);
        $today_summary['total_bookings'] += 1;
        $today_summary['total_revenue']  += $num;

        $stype = strtolower(trim($b['service_type'] ?? ''));
        if ($stype === 'home') {
            $today_summary['home_bookings'] += 1;
            $today_summary['home_revenue']  += $num;
        } elseif ($stype === 'salon') {
            $today_summary['salon_bookings'] += 1;
            $today_summary['salon_revenue']  += $num;
        }
    }
}

// hourly totals (based on time_slot, not created_at)
$hourly = array_fill(0, 24, 0);
foreach ($bookings as $b) {
    // agar time_slot hai to use karo, warna created_at fallback
    $timeSource = $b['time_slot'] ?? $b['created_at'];
    $hour = intval(date('G', strtotime($timeSource)));
    $hourly[$hour] += $b['price_numeric'];
}

// format helper
function fmt_currency($n) { return 'PKR ' . number_format($n); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Day Report - Elegant Salon</title>
  <link rel="icon" href="images/logo.png" type="image/png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <style>
    html, body {
  overflow-x: hidden;
}
    body { font-family: 'Segoe UI', sans-serif; background: linear-gradient(135deg,#f8f9fa,#e9ecef); color:#222; min-height:100vh; display:flex; flex-direction:column; }
    .content-wrapper { flex:1; display:flex; justify-content:center; align-items:flex-start; padding:40px 16px; }
    .report-card { width:100%; max-width:950px; background:#fff; border-radius:14px; padding:26px; box-shadow:0 12px 30px rgba(0,0,0,0.08); }
    /* Dropdown hover fix */
.dropdown-menu {
  background-color: #222; /* dark background for dropdown */
  border: none;
}
/* Dropdown menu item hover */
.dropdown-menu .dropdown-item {
  color: #333;
  transition: all 0.3s ease;
  border-radius: 4px;
}

.dropdown-menu .dropdown-item:hover {
  background-color: #ffc107;
  color: #000 !important;
  transform: translateX(4px);
}

/* Mobile fix - make dropdown full width on small screens */
@media (max-width: 768px) {
  .dropdown-menu {
    min-width: 100%;
    border: none;
    box-shadow: none;
  }

  .dropdown-menu .dropdown-item {
    padding: 0.75rem 1.25rem;
    font-size: 1rem;
  }
}
    /* dark mode */
    body.dark-mode { background:#0f1112; color:#eee; }
    body.dark-mode .report-card { background:#121212; color:#eee; box-shadow:none; border:1px solid rgba(255,255,255,0.03); }
    body.dark-mode .text-muted { color:#fff !important; }
    body.dark-mode .table { color:#fff; }
    body.dark-mode .table thead { background:#fff; color:#000; }
    body.dark-mode .table tbody { background:#1a1a1a; }
    body.dark-mode .table-striped tbody tr:nth-of-type(odd) { background-color:#222; }
    body.dark-mode .table-bordered th, body.dark-mode .table-bordered td { border-color:#555; }
    body.dark-mode .summary-card{background:#1a1a1a;color:#fff;border:1px solid rgba(255,255,255,.05);box-shadow:none;}
body.dark-mode .summary-card h6{color:rgba(255,255,255,0.85);}
body.dark-mode .summary-card .amount{color:rgba(255,255,255,0.95);}
body.dark-mode .text-muted{color:#fff!important}
/* Summary Cards */
.summary-card {
  background: #f8f9fa;
  border-radius: 10px;
  padding: 18px;
  box-shadow: 0 6px 16px rgba(0,0,0,0.08);
  transition: all 0.3s ease;
}
.summary-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 20px rgba(0,0,0,0.12);
}
.summary-card h6 {
  font-weight: 600;
  color: #6c757d;
  margin-bottom: 6px;
}
.summary-card h3 {
  color: #ffc107;
  font-size: 1.8rem;
  font-weight: 700;
  margin-bottom: 4px;
}
.summary-card .amount {
  font-size: 0.95rem;
  color: #6c757d;
  font-weight: 600;
}
/* Table alignment fix */
.table th, .table td {
  text-align: center;
  vertical-align: middle;
  white-space: nowrap; /* har column ek line me रहे */
}

/* Column width control */
.table th:nth-child(1), .table td:nth-child(1) { width: 5%; }
.table th:nth-child(2), .table td:nth-child(2) { width: 15%; }
.table th:nth-child(3), .table td:nth-child(3) { width: 15%; }
.table th:nth-child(4), .table td:nth-child(4) { width: 15%; }
.table th:nth-child(5), .table td:nth-child(5) { width: 15%; }
.table th:nth-child(6), .table td:nth-child(6) { width: 10%; }
.table th:nth-child(7), .table td:nth-child(7) { width: 15%; }
.table th:nth-child(8), .table td:nth-child(8) { width: 10%; }
.table th:nth-child(9), .table td:nth-child(9) { width: 10%; }


  </style>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="d-flex flex-column min-vh-100">

<?php include("navbar.php"); ?>

<!-- Dark Mode Toggle -->
<button class="btn btn-dark position-fixed end-0 bottom-0 m-3 rounded-circle shadow z-3 no-print" id="darkToggle" title="Toggle Dark Mode">
  <i class="bi bi-moon-stars-fill"></i>
</button>

<div class="content-wrapper">
  <div class="report-card" data-aos="fade-up">
    <div class="d-flex align-items-center mb-3 gap-3">
      <img src="../images/logo.png" width="56" alt="logo">
      <div>
        <h3 class="text-warning mb-0"><i class="bi bi-calendar-day me-1"></i> Day Report</h3>
        <div class="text-muted small">Hourly earnings & bookings for selected date</div>
      </div>
    </div>

    <!-- date form -->
    <form method="get" class="report-form mb-3">
      <div class="row g-2 align-items-end">
        <div class="col-sm-8 col-md-6">
          <label class="form-label">Select Date</label>
          <input type="date" name="date" class="form-control" required value="<?= htmlspecialchars($selected_date) ?>">
        </div>
        <div class="col-sm-4 col-md-2">
          <button type="submit" class="btn btn-warning w-100">Generate</button>
        </div>
        <div class="col-12 col-md-4 text-md-end mt-2 mt-md-0">
          <div class="small text-muted">Total bookings</div>
          <div style="font-weight:700; font-size:1.05rem;"><?= $total_count ?></div>
          <div class="small text-muted mt-1">Total revenue</div>
          <div style="font-weight:700; font-size:1.05rem;"><?= fmt_currency($total_revenue) ?></div>
        </div>
      </div>
    </form>

    <!-- chart -->
    <div>
      <canvas id="hourChart" height="120"></canvas>
    </div>

    <hr>
    
    <h5 class="mt-4 text-warning my-3"><i class="bi bi-bar-chart-fill"></i> Customers Data:</h5>

    <!-- Filters -->
    <div class="row mb-3">
      <div class="col-md-4"><input type="text" id="nameFilter" class="form-control" placeholder="Filter by Name"
       maxlength="30" pattern="[A-Za-z\s]{1,30}" title="Only alphabets allowed, max 30 letters"></div>
      <div class="col-md-4"><input type="text" id="serviceFilter" class="form-control" placeholder="Filter by Service"
       maxlength="30" pattern="[A-Za-z\s]{1,30}" title="Only alphabets allowed, max 30 letters"></div>
      <div class="col-md-2 d-grid"><button id="applyFilter" class="btn btn-warning">Apply</button></div>
      <div class="col-md-2 d-grid"><button id="resetFilter" class="btn btn-secondary">Reset</button></div>
    </div>

    <!-- table -->
    <div class="table-responsive mt-3">
      <?php if (!empty($bookings)): ?>
      <div class="table-responsive mt-3">
  <table class="table table-striped table-bordered align-middle" id="reportTable">
        <thead class="table-dark">
          <tr>
            <th>#</th><th>Name</th><th>Phone</th><th>Service</th>
            <th>Sub-service</th><th>Date</th><th>Booked At</th><th>Price</th><th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($bookings as $i => $b): ?>
          <tr>
            <td><?= $i+1 ?></td>
            <td><?= htmlspecialchars($b['name']) ?></td>
            <td><?= htmlspecialchars($b['phone']) ?></td>
            <td><?= htmlspecialchars($b['service']) ?></td>
            <td><?= htmlspecialchars($b['sub_service']) ?></td>
            <td><?= htmlspecialchars($b['date']) ?></td>
            <td><?= htmlspecialchars($b['created_at']) ?></td>
            <td><?= fmt_currency($b['price_numeric']) ?></td>
            <td>
              <span class="badge bg-<?= ($b['status']=='pending'?'warning':($b['status']=='accepted'?'success':'primary')) ?> badge-status">
                <?= htmlspecialchars($b['status']) ?>
              </span>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
          </div>
      <div id="pagination" class="mt-3 d-flex justify-content-center"></div>
      <?php else: ?><p class="text-muted">No bookings found for this date.</p><?php endif; ?>
    </div>

    <hr class="my-4">

<!-- Daily Summary Cards -->

<h5 class="mt-4 text-warning"><i class="bi bi-card-checklist"></i> Daily Summary</h5>
<div class="row text-center g-3 mt-2">
  <div class="col-md-4" data-aos="zoom-in">
    <div class="summary-card">
      <h6>Total Bookings</h6>
      <h3><?= $today_summary['total_bookings'] ?? 0 ?></h3>
      <div class="amount"><?= fmt_currency($today_summary['total_revenue'] ?? 0) ?></div>
    </div>
  </div>
  <div class="col-md-4" data-aos="zoom-in" data-aos-delay="200">
    <div class="summary-card">
      <h6>Home Bookings</h6>
      <h3><?= $today_summary['home_bookings'] ?? 0 ?></h3>
      <div class="amount"><?= fmt_currency($today_summary['home_revenue'] ?? 0) ?></div>
    </div>
  </div>
  <div class="col-md-4" data-aos="zoom-in" data-aos-delay="200">
    <div class="summary-card">
      <h6>Salon Bookings</h6>
      <h3><?= $today_summary['salon_bookings'] ?? 0 ?></h3>
      <div class="amount"><?= fmt_currency($today_summary['salon_revenue'] ?? 0) ?></div>
    </div>
  </div>
</div>

</div>
      </div>
<?php include("foot.php"); ?>

<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
  AOS.init({ duration: 1000, once: true });

  function restrictInput(input) {
  input.addEventListener("keypress", function(e) {
    if (!/[a-zA-Z\s]/.test(e.key)) {
      e.preventDefault(); // numbers / special chars stop
    }
  });

  input.addEventListener("input", function() {
    this.value = this.value.replace(/[^a-zA-Z\s]/g, ""); 
    if (this.value.length > 30) {
      this.value = this.value.slice(0, 30);
    }
  });
}

restrictInput(document.getElementById("nameFilter"));
restrictInput(document.getElementById("serviceFilter"));

  // chart data
  const hourlyData = <?= json_encode(array_values($hourly), JSON_NUMERIC_CHECK) ?>;
  const labels = Array.from({length:24}, (_,i)=> i + ":00");

  const ctxHour = document.getElementById('hourChart').getContext('2d');
  let hourChart = new Chart(ctxHour, {
    type: 'bar',
    data: {
      labels: labels,
      datasets: [{ label:'Earnings (PKR)', data:hourlyData, backgroundColor:'rgba(255,193,7,0.9)' }]
    },
    options: {
      responsive:true, maintainAspectRatio:false,
      scales:{
        x:{ title:{display:true,text:'Hour of day'}, ticks:{color:'#000'}, grid:{color:'rgba(0,0,0,.1)'} },
        y:{ beginAtZero:true, title:{display:true,text:'PKR'}, ticks:{color:'#000'}, grid:{color:'rgba(0,0,0,.1)'} }
      },
      plugins:{ legend:{display:false, labels:{color:'#000'} },
        tooltip:{callbacks:{label:ctx=>'PKR '+(ctx.parsed.y||0).toLocaleString()}} }
    }
  });

  function updateChartColors(isDark){
    const tickColor=isDark?'#fff':'#000';
    const gridColor=isDark?'rgba(255,255,255,.2)':'rgba(0,0,0,.1)';
    const legendColor=isDark?'#fff':'#000';
    const titleColor = isDark ? '#fff' : '#000';  
   
    hourChart.options.scales.x.ticks.color=tickColor;
    hourChart.options.scales.y.ticks.color=tickColor;
    hourChart.options.scales.x.grid.color=gridColor;
    hourChart.options.scales.y.grid.color=gridColor;
    hourChart.options.plugins.legend.labels.color=legendColor;
    hourChart.options.scales.x.title.color = titleColor;
    hourChart.options.scales.y.title.color = titleColor;

  hourChart.update();
}

  // dark mode toggle
  const darkToggle=document.getElementById('darkToggle');
  if(localStorage.getItem('darkMode')==='enabled'){
    document.body.classList.add('dark-mode');
    updateChartColors(true);
  }
  darkToggle.addEventListener('click',()=>{
    document.body.classList.toggle('dark-mode');
    const isDark=document.body.classList.contains('dark-mode');
    updateChartColors(isDark);
    localStorage.setItem('darkMode',isDark?'enabled':'disabled');
  });

  // filters + pagination
  const rows=[...document.querySelectorAll("#reportTable tbody tr")];
  const nameFilter=document.getElementById("nameFilter");
  const serviceFilter=document.getElementById("serviceFilter");
  const applyFilter=document.getElementById("applyFilter");
  const resetFilter=document.getElementById("resetFilter");
  const perPage=8; let page=1, filteredRows=[...rows];

  function render(){
    rows.forEach(r=>r.style.display="none");
    let start=(page-1)*perPage, end=page*perPage;
    filteredRows.slice(start,end).forEach(r=>r.style.display="");

    let pages=Math.ceil(filteredRows.length/perPage);
    let pag=document.getElementById("pagination"); pag.innerHTML="";
    if(pages>1){
      let prev=document.createElement("button");
      prev.className="btn btn-sm btn-outline-warning me-1"; prev.innerText="Previous"; prev.disabled=(page===1);
      prev.onclick=()=>{page--;render();}; pag.appendChild(prev);

      for(let i=1;i<=pages;i++){
        let btn=document.createElement("button");
        btn.className="btn btn-sm "+(i==page?"btn-warning":"btn-outline-warning")+" me-1";
        btn.innerText=i; btn.onclick=()=>{page=i;render();};
        pag.appendChild(btn);
      }

      let next=document.createElement("button");
      next.className="btn btn-sm btn-outline-warning"; next.innerText="Next"; next.disabled=(page===pages);
      next.onclick=()=>{page++;render();}; pag.appendChild(next);
    }
  }

  function apply(){
    const nf=nameFilter.value.toLowerCase(), sf=serviceFilter.value.toLowerCase();
    filteredRows=rows.filter(r=>
      r.cells[1].textContent.toLowerCase().includes(nf) &&
      r.cells[3].textContent.toLowerCase().includes(sf));
    page=1; render();
  }

  applyFilter.addEventListener("click",e=>{e.preventDefault();apply();});
  resetFilter.addEventListener("click",e=>{
    e.preventDefault();
    nameFilter.value=""; serviceFilter.value="";
    filteredRows=[...rows]; page=1; render();
  });

  render();
</script>
</body>
</html>
