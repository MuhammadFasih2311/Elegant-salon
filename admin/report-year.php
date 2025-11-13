<?php
include("auth-check.php"); 
include("connect.php");

function price_to_int($p){ return ($p && preg_replace('/[^\d]/','',$p)!=='') ? intval(preg_replace('/[^\d]/','',$p)) : 0; }
function fmt_currency($n){ return 'PKR '.number_format($n); }

$selected_year=$_GET['year']??date('Y');
$monthlyTotals=array_fill(1,12,0);
$total_count=0;$total_revenue=0;

$summary=[
  'total_bookings'=>0,'total_revenue'=>0,
  'home_bookings'=>0,'home_revenue'=>0,
  'salon_bookings'=>0,'salon_revenue'=>0
];

$sql="SELECT b.*,s.price AS sprice 
      FROM bookings b 
      LEFT JOIN serve s ON b.service=s.category AND b.sub_service=s.label 
      WHERE YEAR(b.date)=? AND b.status='completed'
      ORDER BY b.date";

$stmt=$conn->prepare($sql);
$stmt->bind_param("i",$selected_year);
$stmt->execute();$res=$stmt->get_result();
while($r=$res->fetch_assoc()){
  $num = ($r['price'] && $r['price'] > 0) 
         ? intval($r['price']) 
         : price_to_int($r['sprice'] ?? '');
  $m=intval(date('n',strtotime($r['date'])));
  $monthlyTotals[$m]+=$num;
  $total_revenue+=$num;
  $total_count++;

  // Summary for cards
  $summary['total_bookings']++;
  $summary['total_revenue'] += $num;

  $stype=strtolower(trim($r['service_type'] ?? ''));
  if($stype==='home'){
    $summary['home_bookings']++;
    $summary['home_revenue'] += $num;
  } elseif($stype==='salon'){
    $summary['salon_bookings']++;
    $summary['salon_revenue'] += $num;
  }
}
$stmt->close();
$labels=["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"];
$values=array_values($monthlyTotals);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Year Report - Elegant Salon</title>
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
.report-card{max-width:950px;width:100%;background:#fff;border-radius:14px;padding:26px;box-shadow:0 12px 30px rgba(0,0,0,.08)}
/* Dropdown hover fix */
.dropdown-menu {background-color:#222;border:none;}
.dropdown-menu .dropdown-item {color:#333;transition:all .3s ease;border-radius:4px;}
.dropdown-menu .dropdown-item:hover {background-color:#ffc107;color:#000!important;transform:translateX(4px);}
@media (max-width:768px){.dropdown-menu{min-width:100%;border:none;box-shadow:none;}.dropdown-menu .dropdown-item{padding:.75rem 1.25rem;font-size:1rem;}}

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

/* Dark Mode */
body.dark-mode{background:#0f1112;color:#eee}
body.dark-mode .report-card{background:#121212;color:#eee;border:1px solid rgba(255,255,255,.05)}
body.dark-mode .summary-card{background:#1a1a1a;color:#fff;border:1px solid rgba(255,255,255,.05);box-shadow:none;}
body.dark-mode .summary-card h6{color:rgba(255,255,255,0.85);}
body.dark-mode .summary-card .amount{color:rgba(255,255,255,0.95);}
body.dark-mode .text-muted{color:#fff!important}
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
    <h3 class="text-warning"><i class="bi bi-calendar3"></i> Year Report</h3>
    <form method="get" class="row g-2 mb-3 align-items-end">
      <div class="col-sm-6">
        <label>Select Year</label>
        <input type="number" name="year" id="yearInput" class="form-control" 
       value="<?=$selected_year?>" 
       min="2000" max="2999" step="1" 
       maxlength="4"
       oninput="validateYear(this)">
      </div>
      <div class="col-sm-3"><button class="btn btn-warning w-100">Generate</button></div>
      <div class="col-sm-3 text-end">
        <div class="small text-muted">Bookings: <?=$total_count?></div>
        <div class="small text-muted">Revenue: <?=fmt_currency($total_revenue)?></div>
      </div>
    </form>

    <canvas id="chart" height="130"></canvas>

    <!-- ✅ Summary Cards Section -->
    <hr class="my-4">
    <h5 class="mt-4 text-warning"><i class="bi bi-bar-chart-line-fill"></i> Yearly Summary</h5>
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

const labels=<?=json_encode($labels)?>;
const values=<?=json_encode($values)?>;

const ctx=document.getElementById('chart').getContext('2d');
let chart=new Chart(ctx,{
  type:'bar',
  data:{
    labels:labels,
    datasets:[{label:'Revenue (PKR)',data:values,backgroundColor:'rgba(255,193,7,.9)'}]
  },
  options:{
    responsive:true,
    scales:{
      x:{grid:{color:'rgba(0,0,0,.1)'},ticks:{color:'#000'}},
      y:{beginAtZero:true,grid:{color:'rgba(0,0,0,.1)'},ticks:{color:'#000'}}
    },
    plugins:{legend:{labels:{color:'#000'}}}
  }
});

// ✅ Dark Mode toggle
const darkToggle=document.getElementById('darkToggle');
if(localStorage.getItem('darkMode')==='enabled'){document.body.classList.add('dark-mode');updateChartColors(true);}
darkToggle.addEventListener('click',()=>{
  document.body.classList.toggle('dark-mode');
  const isDark=document.body.classList.contains('dark-mode');
  updateChartColors(isDark);
  localStorage.setItem('darkMode',isDark?'enabled':'disabled');
});

// ✅ Update chart text colors
function updateChartColors(isDark){
  chart.options.scales.x.ticks.color=isDark?'#fff':'#000';
  chart.options.scales.y.ticks.color=isDark?'#fff':'#000';
  chart.options.scales.x.grid.color=isDark?'rgba(255,255,255,.2)':'rgba(0,0,0,.1)';
  chart.options.scales.y.grid.color=isDark?'rgba(255,255,255,.2)':'rgba(0,0,0,.1)';
  chart.options.plugins.legend.labels.color=isDark?'#fff':'#000';
  chart.update();
}

function validateYear(input){
  input.value=input.value.replace(/\D/g,'');
  if(input.value.length>4)input.value=input.value.slice(0,4);
  if(input.value==='' )return;
  if(input.value.length===4){
    let val=parseInt(input.value,10);
    if(val<2000)input.value=2000;
    if(val>2999)input.value=2999;
  }
}
</script>
</body>
</html>
