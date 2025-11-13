<?php
// manual_booking_multi.php
session_start();
include("auth-check.php"); 
include("connect.php");

$errors=[];

// ✅ Price fetch helper
function getPrice($conn,$service,$sub_service){
    $stmt=$conn->prepare("SELECT price FROM serve WHERE category=? AND label=? LIMIT 1");
    $stmt->bind_param("ss",$service,$sub_service);
    $stmt->execute();
    $res=$stmt->get_result()->fetch_assoc();
    return $res ? (int)filter_var($res['price'],FILTER_SANITIZE_NUMBER_INT):0;
}

if($_SERVER['REQUEST_METHOD']==='POST'){
    $name=trim($_POST['name']??'');
    $phone=trim($_POST['phone']??'');
    $service_type=$_POST['service_type']??'';
    $address=trim($_POST['address']??'');
    $date=$_POST['date']??'';
    $time_slot=$_POST['time_slot']??'';
    $multi_services=$_POST['multi_services']??[];

    // 🔹 Validations
    if($name===""||strlen($name)>30||!preg_match("/^[A-Za-z\s]+$/",$name)){
        $errors[]="Name required (alphabets only, max 30 chars).";
    }
    if($phone===""||!preg_match("/^[0-9]{11}$/",$phone)){
        $errors[]="Phone must be exactly 11 digits.";
    }
    if($service_type==="home"&&strlen($address)<5){
        $errors[]="Address required for Home service.";
    }
    if(!$date||$date<date('Y-m-d')){
        $errors[]="Invalid Date.";
    }
    if(!$time_slot){
        $errors[]="Time slot is required.";
    }
    if(empty($multi_services)){
        $errors[]="Please select services for persons.";
    }

    // 🔹 Slot restriction → ek slot per ek multi booking
    if(empty($errors)){
        $stmt=$conn->prepare("SELECT COUNT(*) as cnt FROM bookings WHERE date=? AND time_slot=? AND service_type=? AND persons>1");
        $stmt->bind_param("sss",$date,$time_slot,$service_type);
        $stmt->execute();
        $count=$stmt->get_result()->fetch_assoc()['cnt'];
        if($count>=1){
            $errors[]="⚠️ Slot already full! Only 1 multi booking allowed per slot.";
        }
    }

    if(empty($errors)){
        $email="Admin";
        $persons=count($multi_services);

        foreach($multi_services as $ms){
            $cat=$ms['service']??'';
            $sub=$ms['sub_service']??'';
            if(!$cat||!$sub) continue;

            $price=getPrice($conn,$cat,$sub);

            $stmt=$conn->prepare("INSERT INTO bookings
            (user_id,name,phone,email,address,service_type,service,sub_service,price,date,time_slot,created_at,status,persons)
            VALUES(0,?,?,?,?,?,?,?,?,?,?,NOW(),'pending',?)");

            $stmt->bind_param("sssssssissi",
                $name,        // s
                $phone,       // s
                $email,       // s
                $address,     // s
                $service_type,// s
                $cat,         // s
                $sub,         // s
                $price,       // i
                $date,        // s
                $time_slot,   // s
                $persons      // i
            );
            $stmt->execute();
        }

        $_SESSION['success']="✅ Multiple booking created successfully!";
        header("Location: manual_booking_multi.php");
        exit;
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Manual Booking (Multiple)</title>
  <link rel="icon" href="images/logo.png" type="image/png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<style>
  html, body {
  overflow-x: hidden;
}
body.dark-mode { background:#121212; color:#fff; }
.dark-mode .card { background:#1e1e1e; color:#fff; border:1px solid #333; }
.dark-mode .form-control,.dark-mode .form-select { background:#2a2a2a; color:#fff; border:1px solid #444; }
.dark-mode label { color:#ffc107; }
.dark-mode input[type="date"]::-webkit-calendar-picker-indicator {
    filter: invert(1);
}
/* Dark mode select dropdown arrow white */
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
  #multiServicesContainer .row {
    flex-direction: column;
  }
  #multiServicesContainer .col-md-6 {
    width: 100%;
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
  #multiServicesContainer .row {
    padding: 10px !important;
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
          <i class="bi bi-people-fill"></i> Manual Booking (Multiple)
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
          <!-- Name & Phone -->
          <div class="col-md-6" data-aos="fade-right">
            <label class="form-label">Name *</label>
            <input type="text" name="name" class="form-control"
                   value="<?= htmlspecialchars($_POST['name']??'') ?>"
                   maxlength="30" minlength="3"
                   oninput="this.value=this.value.replace(/[^A-Za-z\s]/g,'')" required>
          </div>
          <div class="col-md-6"  data-aos="fade-left">
            <label class="form-label">Phone *</label>
            <input type="text" name="phone" maxlength="11"
                   class="form-control"
                   value="<?= htmlspecialchars($_POST['phone']??'') ?>"
                   pattern="\d{11}" required>
            <small class="text-muted">Must be exactly 11 digits</small>
          </div>

          <!-- Service Type -->
          <div class="col-md-6" data-aos="fade-right">
            <label class="form-label">Service Type *</label>
            <select name="service_type" id="service_type" class="form-select" required>
              <option value="">Select Service Type</option>
              <option value="salon" <?= ($_POST['service_type']??'')==='salon'?'selected':'' ?>>Salon</option>
              <option value="home" <?= ($_POST['service_type']??'')==='home'?'selected':'' ?>>Home</option>
            </select>
          </div>

          <!-- Date & Time -->
          <div class="col-md-6"  data-aos="fade-left">
            <label class="form-label">Date *</label>
            <input type="date" name="date" class="form-control" min="<?=date('Y-m-d')?>"
                   value="<?= htmlspecialchars($_POST['date']??'') ?>" required>
          </div>
          <!-- Address -->
          <div class="col-md-12" id="address_row" style="<?= (($_POST['service_type']??'')==='home')?'':'display:none;' ?>" data-aos="fade-right">
            <label class="form-label">Address</label>
            <textarea name="address" class="form-control" rows="2" maxlength="200"><?= htmlspecialchars($_POST['address']??'') ?></textarea>
          </div>

          <div class="col-md-6" data-aos="fade-right">
            <label class="form-label">Time Slot *</label>
            <select name="time_slot" class="form-select" required>
              <option value="">Select Time Slot</option>
              <?php
              $start=strtotime("11:00"); $end=strtotime("20:45");
              for($t=$start;$t<=$end;$t+=15*60){
                $slotValue=date("H:i",$t);
                $slotLabel=date("h:i A",$t);
                $sel=(($_POST['time_slot']??'')===$slotValue)?'selected':'';
                echo "<option value='$slotValue' $sel>$slotLabel</option>";
              }
              ?>
            </select>
          </div>

          <!-- Persons -->
          <div class="col-md-6" data-aos="fade-left">
            <label class="form-label">Persons *</label>
            <select id="personCount" class="form-select" required>
              <option value="">Select</option>
              <?php for($i=2;$i<=6;$i++): ?>
                <option value="<?=$i?>" <?= (($_POST['multi_services']??false) && count($_POST['multi_services'])==$i)?'selected':'' ?>><?=$i?></option>
              <?php endfor; ?>
            </select>
          </div>

          <!-- Apply All -->
          <div class="col-md-12" data-aos="fade-right">
            <div class="form-check mt-3">
              <input class="form-check-input" type="checkbox" id="applyAll">
              <label class="form-check-label fw-semibold" for="applyAll">
                Apply first person's service & sub-service to all
              </label>
            </div>
          </div>

          <!-- Multi persons services -->
          <div class="col-12" id="multiServicesContainer"></div>

          <!-- Submit -->
          <div class="col-12 text-center mt-3" data-aos="zoom-in-up">
            <button type="submit" class="btn btn-warning px-4 fw-semibold mb-2" data-aos="fade-right">
              <i class="bi bi-check-circle me-1"></i> Confirm Multi Booking
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
const subServicesMap = <?php
$categories=[];
$res=$conn->query("SELECT * FROM serve ORDER BY category");
while($r=$res->fetch_assoc()){
  $categories[$r['category']][]=['label'=>$r['label'],'price'=>$r['price']];
}
echo json_encode($categories);
?>;

// 🔹 Address toggle
$('#service_type').on('change',()=>$('#address_row').toggle($('#service_type').val()==='home'));

// 🔹 Generate persons blocks
const container=document.getElementById('multiServicesContainer');
function generateMultiBlocks(count){
  container.innerHTML="";
  for(let i=0;i<count;i++){
    let block=document.createElement("div");
    block.className="row g-3 border rounded p-3 mb-3";
    block.setAttribute("data-aos","fade-up"); // 🔹 animation for block

    let serviceOptions='<option value="">Select Category</option>';
    for(const service in subServicesMap){
      serviceOptions+=`<option value="${service}">${service}</option>`;
    }
    block.innerHTML=`
      <div class="col-md-6" data-aos="fade-right">
        <label class="form-label">Person ${i+1} - Service</label>
        <select class="form-select service-select" name="multi_services[${i}][service]" required>${serviceOptions}</select>
      </div>
      <div class="col-md-6" data-aos="fade-left">
        <label class="form-label">Person ${i+1} - Sub Service</label>
        <select class="form-select sub-service-select" name="multi_services[${i}][sub_service]" required>
          <option value="">Select Sub-Service</option>
        </select>
      </div>
    `;
    container.appendChild(block);


    // update subservice
    block.querySelector(".service-select").addEventListener("change",function(){
      let service=this.value;
      let subSelect=block.querySelector(".sub-service-select");
      subSelect.innerHTML='<option value="">Select Sub-Service</option>';
      if(service && subServicesMap[service]){
        subServicesMap[service].forEach(item=>{
          let opt=document.createElement("option");
          opt.value=item.label;
          opt.text=`${item.label} (${item.price})`;
          subSelect.appendChild(opt);
        });
      }
    });
  }
  bindApplyAll();
}

document.getElementById('personCount').addEventListener('change',function(){
  let c=parseInt(this.value);
  if(c>0) generateMultiBlocks(c);
});

// 🔹 Apply All
function copyFirstToAll(){
  const firstService=document.querySelector("#multiServicesContainer .service-select")?.value;
  const firstSub=document.querySelector("#multiServicesContainer .sub-service-select")?.value;
  if(firstService){
    document.querySelectorAll("#multiServicesContainer .service-select").forEach((sel,idx)=>{
      if(idx>0){ sel.value=firstService; sel.dispatchEvent(new Event("change")); }
    });
  }
  if(firstSub){
    document.querySelectorAll("#multiServicesContainer .sub-service-select").forEach((sel,idx)=>{
      if(idx>0) sel.value=firstSub;
    });
  }
}
function bindApplyAll(){
  const firstService=document.querySelector("#multiServicesContainer .service-select");
  const firstSub=document.querySelector("#multiServicesContainer .sub-service-select");
  if(firstService) firstService.addEventListener("change",()=>{ if(applyAll.checked) copyFirstToAll(); });
  if(firstSub) firstSub.addEventListener("change",()=>{ if(applyAll.checked) copyFirstToAll(); });
}
const applyAll=document.getElementById("applyAll");
applyAll.addEventListener("change",()=>{ if(applyAll.checked) copyFirstToAll(); });

// 🔹 Phone validation
document.querySelector("input[name='phone']").addEventListener("input",function(){
  this.value=this.value.replace(/[^0-9]/g,'');
  if(this.value.length>11) this.value=this.value.slice(0,11);
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

// 🔹 Restore old persons + selections after validation error
<?php if(!empty($_POST['multi_services'])): ?>
  let oldServices = <?= json_encode($_POST['multi_services']); ?>;
  generateMultiBlocks(oldServices.length);
  oldServices.forEach((ms, idx)=>{
    let block = document.querySelectorAll("#multiServicesContainer .row")[idx];
    if(ms.service){
      let serviceSelect = block.querySelector(".service-select");
      serviceSelect.value = ms.service;
      serviceSelect.dispatchEvent(new Event("change")); // load sub services
      if(ms.sub_service){
        setTimeout(()=>{
          block.querySelector(".sub-service-select").value = ms.sub_service;
        },200);
      }
    }
  });
<?php endif; ?>
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
