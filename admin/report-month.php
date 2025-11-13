<?php
include("auth-check.php"); 
include("connect.php");

function price_to_int($p){ return ($p && preg_replace('/[^\d]/','',$p)!=='') ? intval(preg_replace('/[^\d]/','',$p)) : 0; }
function fmt_currency($n){ return 'PKR '.number_format($n); }

$selected_month = $_GET['month'] ?? date('Y-m');

$bookings = [];
$dailyTotals = [];
$total_revenue = 0;
$total_count = 0;

$sql = "SELECT b.*, s.price AS sprice
        FROM bookings b
        LEFT JOIN serve s ON b.service=s.category AND b.sub_service=s.label
        WHERE DATE_FORMAT(b.date, '%Y-%m') = ? AND b.status='completed'
        ORDER BY b.date ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $selected_month);
$stmt->execute();
$res = $stmt->get_result();
while($row=$res->fetch_assoc()){
    $num = ($row['price'] && $row['price'] > 0) 
           ? intval($row['price']) 
           : price_to_int($row['sprice'] ?? '');
    $row['price_numeric']=$num;
    $d = date('Y-m-d', strtotime($row['date']));
    $dailyTotals[$d] = ($dailyTotals[$d] ?? 0) + $num;
    $total_revenue += $num;
    $total_count++;
    $bookings[]=$row;
}

// === MONTHLY SUMMARY ===
$summary = [
  'total_bookings' => 0,
  'total_revenue'  => 0,
  'home_bookings'  => 0,
  'home_revenue'   => 0,
  'salon_bookings' => 0,
  'salon_revenue'  => 0
];

if (!empty($bookings)) {
  foreach ($bookings as $b) {
    $price = floatval($b['price_numeric'] ?? 0);
    $summary['total_bookings']++;
    $summary['total_revenue'] += $price;

    $stype = strtolower(trim($b['service_type'] ?? ''));
    if ($stype === 'home') {
      $summary['home_bookings']++;
      $summary['home_revenue'] += $price;
    } elseif ($stype === 'salon') {
      $summary['salon_bookings']++;
      $summary['salon_revenue'] += $price;
    }
  }
}

$stmt->close();

$labels=array_keys($dailyTotals);
$values=array_values($dailyTotals);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Month Report - Elegant Salon</title>
<link rel="icon" href="images/logo.png" type="image/png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
  html, body {
  overflow-x: hidden;
}
body{font-family:'Segoe UI';background:linear-gradient(135deg,#f8f9fa,#e9ecef);display:flex;flex-direction:column;min-height:100vh}
.content-wrapper{flex:1;display:flex;justify-content:center;align-items:flex-start;padding:40px 16px}
.report-card{width:100%;max-width:950px;background:#fff;border-radius:14px;padding:26px;box-shadow:0 12px 30px rgba(0,0,0,.08)}
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
/* Dark Mode */
body.dark-mode{background:#0f1112;color:#eee}
body.dark-mode .report-card{background:#121212;color:#eee;border:1px solid rgba(255,255,255,.05)}
body.dark-mode .table{color:#fff}
body.dark-mode .table thead{background:#fff;color:#000}
body.dark-mode .table tbody{background:#1a1a1a}
body.dark-mode .text-muted{color:white!important}
body.dark-mode .bg-light{background:#1e1e1e!important;color:#fff}
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
/* Table fix - alignment + responsiveness */
.table th, .table td {
  text-align: center;
  vertical-align: middle;
  white-space: nowrap;
}

/* Column width control */
.table th:nth-child(1), .table td:nth-child(1) { width: 5%; }
.table th:nth-child(2), .table td:nth-child(2) { width: 20%; }
.table th:nth-child(3), .table td:nth-child(3) { width: 25%; }
.table th:nth-child(4), .table td:nth-child(4) { width: 15%; }
.table th:nth-child(5), .table td:nth-child(5) { width: 15%; }
.table th:nth-child(6), .table td:nth-child(6) { width: 10%; }
</style>
</head>
<body class="d-flex flex-column min-vh-100">
<?php include("navbar.php"); ?>

<!-- Dark Mode Toggle -->
<button class="btn btn-dark position-fixed end-0 bottom-0 m-3 rounded-circle shadow z-3" id="darkToggle" title="Toggle Dark Mode">
  <i class="bi bi-moon-stars-fill"></i>
</button>

<div class="content-wrapper">
  <div class="report-card" data-aos="fade-up">
    <h3 class="text-warning mb-3"><i class="bi bi-calendar-month"></i> Month Report</h3>
    <form method="get" class="mb-3 row g-2 align-items-end">
      <div class="col-sm-6">
        <label class="form-label">Select Month</label>
        <input type="month" name="month" class="form-control" value="<?=htmlspecialchars($selected_month)?>">
      </div>
      <div class="col-sm-3">
        <button class="btn btn-warning w-100">Generate</button>
      </div>
      <div class="col-sm-3 text-end">
        <div class="small text-muted">Bookings: <?=$total_count?></div>
        <div class="small text-muted">Revenue: <?=fmt_currency($total_revenue)?></div>
      </div>
    </form>

    <!-- Line chart -->
    <h5 class="mt-4 text-warning"><i class="bi bi-bar-chart-fill"></i> Daily Revenue (Bar Chart)</h5>
    <canvas id="chart" height="120"></canvas>

    <hr>
    <h5 class="mt-4 text-warning"><i class="bi bi-bar-chart-fill"></i> Customers Data</h5>

    <!-- Filters -->
    <div class="row mb-3">
      <div class="col-md-4"><input type="text" id="nameFilter" class="form-control" placeholder="Filter by Name"
       maxlength="30" pattern="[A-Za-z\s]{1,30}" title="Only alphabets allowed, max 30 letters"></div>
      <div class="col-md-4"><input type="text" id="serviceFilter" class="form-control" placeholder="Filter by Service"
       maxlength="30" pattern="[A-Za-z\s]{1,30}" title="Only alphabets allowed, max 30 letters"></div>
      <div class="col-md-2 d-grid"><button id="applyFilter" class="btn btn-warning">Apply</button></div>
      <div class="col-md-2 d-grid"><button id="resetFilter" class="btn btn-secondary">Reset</button></div>
    </div>

    <div class="table-responsive">
      <?php if($bookings): ?>
        <div class="table-responsive mt-3">
  <table class="table table-striped table-bordered align-middle" id="reportTable">
        <thead class="table-dark"><tr>
          <th>#</th><th>Name</th><th>Service</th><th>Date</th><th>Price</th><th>Status</th>
        </tr></thead>
        <tbody>
        <?php foreach($bookings as $i=>$b): ?>
          <tr>
            <td><?=$i+1?></td>
            <td><?=htmlspecialchars($b['name'])?></td>
            <td><?=htmlspecialchars($b['service'].' - '.$b['sub_service'])?></td>
            <td><?=$b['date']?></td>
            <td><?=fmt_currency($b['price_numeric'])?></td>
            <td><?=$b['status']?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
      <div id="pagination" class="mt-3 d-flex justify-content-center"></div>
      <?php else: ?><p>No records.</p><?php endif; ?>
    </div>

<!-- ✅ Summary Cards Section -->
    <hr class="my-4">
    <h5 class="mt-4 text-warning"><i class="bi bi-bar-chart-line-fill"></i> Monthly Summary</h5>
    <div class="row text-center g-3 mt-2">
      <div class="col-md-4" data-aos="zoom-in">
        <div class="summary-card">
          <h6>Total Bookings</h6>
          <h3><?=$summary['total_bookings']?></h3>
          <div class="amount"><?=fmt_currency($summary['total_revenue'])?></div>
        </div>
      </div>
      <div class="col-md-4" data-aos="zoom-in" data-aos-delay="100">
        <div class="summary-card">
          <h6>Home Bookings</h6>
          <h3><?=$summary['home_bookings']?></h3>
          <div class="amount"><?=fmt_currency($summary['home_revenue'])?></div>
        </div>
      </div>
      <div class="col-md-4" data-aos="zoom-in"  data-aos-delay="200">
        <div class="summary-card">
          <h6>Salon Bookings</h6>
          <h3><?=$summary['salon_bookings']?></h3>
          <div class="amount"><?=fmt_currency($summary['salon_revenue'])?></div>
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

const labels=<?=json_encode($labels)?>;
const values=<?=json_encode($values)?>;

const ctxLine=document.getElementById('chart').getContext('2d');

let lineChart=new Chart(ctxLine,{
  type:'line',
  data:{labels:labels,datasets:[{label:'Revenue (PKR)',data:values,borderColor:'rgba(255,193,7,1)',backgroundColor:'rgba(255,193,7,.2)',tension:.3}]},
  options:{responsive:true,scales:{x:{ticks:{color:'#000'},grid:{color:'rgba(0,0,0,.1)'}},y:{beginAtZero:true,ticks:{color:'#000'},grid:{color:'rgba(0,0,0,.1)'}}},plugins:{legend:{labels:{color:'#000'}}}}
});

// ✅ Update chart colors dynamically
function updateChartColors(isDark){
  const tickColor=isDark?'#fff':'#000';
  const gridColor=isDark?'rgba(255,255,255,.2)':'rgba(0,0,0,.1)';
  const legendColor=isDark?'#fff':'#000';

  [lineChart].forEach(c=>{
    c.options.scales.x.ticks.color=tickColor;
    c.options.scales.y.ticks.color=tickColor;
    c.options.scales.x.grid.color=gridColor;
    c.options.scales.y.grid.color=gridColor;
    c.options.plugins.legend.labels.color=legendColor;
    c.update();
  });
}

// Dark Mode toggle
const darkToggle=document.getElementById('darkToggle');
if(localStorage.getItem('darkMode')==='enabled'){document.body.classList.add('dark-mode');updateChartColors(true);}
darkToggle.addEventListener('click',()=>{
  document.body.classList.toggle('dark-mode');
  const isDark=document.body.classList.contains('dark-mode');
  updateChartColors(isDark);
  localStorage.setItem('darkMode',isDark?'enabled':'disabled');
});

// Filters + Pagination
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
    prev.className="btn btn-sm btn-outline-warning me-1";
    prev.innerText="Previous"; prev.disabled=(page===1);
    prev.onclick=()=>{page--;render();}; pag.appendChild(prev);

    for(let i=1;i<=pages;i++){
      let btn=document.createElement("button");
      btn.className="btn btn-sm "+(i==page?"btn-warning":"btn-outline-warning")+" me-1";
      btn.innerText=i; btn.onclick=()=>{page=i;render();};
      pag.appendChild(btn);
    }

    let next=document.createElement("button");
    next.className="btn btn-sm btn-outline-warning";
    next.innerText="Next"; next.disabled=(page===pages);
    next.onclick=()=>{page++;render();}; pag.appendChild(next);
  }
}

function apply(){
  const nf=nameFilter.value.toLowerCase(), sf=serviceFilter.value.toLowerCase();
  filteredRows=rows.filter(r=>
    r.cells[1].textContent.toLowerCase().includes(nf) &&
    r.cells[2].textContent.toLowerCase().includes(sf));
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
