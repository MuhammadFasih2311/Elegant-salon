<?php
include("connect.php");
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
  header("Location: admin-login.php");
  exit();
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
  echo "<h3 style='color:red;text-align:center;'>Error: No service ID provided.</h3>";
  exit();
}

$id = intval($_GET['id']);
$sql = "SELECT * FROM serve WHERE id = $id";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) === 0) {
  echo "<h3 style='color:red;text-align:center;'>Error: No service found with this ID.</h3>";
  exit();
}
$data = mysqli_fetch_assoc($result);

if (isset($_POST['update'])) {
  $category = $_POST['category'];
  $label = $_POST['label'];
  $price = $_POST['price'];

  if (!empty($_FILES['image']['name'])) {
    $image_name = $_FILES['image']['name'];
    $image_tmp = $_FILES['image']['tmp_name'];
    move_uploaded_file($image_tmp, "gallery images/" . $image_name);
  } else {
    $image_name = $data['image'];
  }

  $update = "UPDATE serve SET 
              category='$category', 
              label='$label', 
              price='$price', 
              image='$image_name' 
            WHERE id=$id";

  if (mysqli_query($conn, $update)) {
    header("Location: admin_dashboard.php?msg=" . urlencode("Service updated successfully!"));
    exit();
  } else {
    echo "Error updating: " . mysqli_error($conn);
  }
}
?>
<?php include("auth-check.php");  ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Product</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" href="images/logo.png" type="image/png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
  <style>
    body { background: linear-gradient(135deg,#f8f9fa,#e9ecef); }
    body.dark-mode { background:#121212; color:#fff; }

    .form-card { 
      max-width:800px; margin:auto; background:#fff; border-radius:14px;
      padding:30px; box-shadow:0 10px 25px rgba(0,0,0,0.1); width:100%;
    }
    body.dark-mode .form-card { background:#1a1a1a; border:1px solid #333; }

    /* Responsive fix */
    @media (max-width:768px){
      .form-card{ padding:20px; border-radius:10px; }
      h3{ font-size:1.4rem; }
      .form-label{ font-size:0.9rem; }
      input,textarea,select,button{ font-size:0.9rem !important; }
    }

    /* Dark mode toggle */
    #darkToggle{ 
      z-index:9999;
      transition:background-color 0.3s ease,color 0.3s ease;
    }
    body.dark-mode #darkToggle{ background:#fff !important; color:#000 !important; border:none; }
    @media(max-width:576px){ #darkToggle{ bottom:15px; right:15px; } }
  </style>
</head>
<body class="d-flex flex-column min-vh-100">

<?php include("navbar.php"); ?>

<button class="btn btn-dark position-fixed end-0 bottom-0 m-3 rounded-circle shadow" id="darkToggle">
  <i class="bi bi-moon-stars-fill"></i>
</button>

<div class="container my-5 flex-grow-1">
  <div class="form-card">
    <h3 class="text-warning mb-4 text-center" data-aos="fade-down">✏️ Edit Product</h3>
    <form method="POST" enctype="multipart/form-data" class="row g-3">
      <div class="col-md-6" data-aos="fade-right">
        <label class="form-label">Category</label>
        <input type="text" name="category" class="form-control" 
       value="<?= htmlspecialchars($data['category']) ?>" 
       required maxlength="50"
       pattern="[A-Za-z0-9\s\-\&\,\.\'\(\)\+]+"
       title="Alphabets, numbers & limited symbols (-, &, ., ', (, ), +, ,) allowed">
      </div>
      <div class="col-md-6" data-aos="fade-left">
        <label class="form-label">Label</label>
        <input type="text" name="label" class="form-control" value="<?= htmlspecialchars($data['label']) ?>" required maxlength="30">
      </div>
      <div class="col-md-6" data-aos="fade-right">
        <label class="form-label">Price</label>
        <input type="text" name="price" class="form-control" value="<?= htmlspecialchars($data['price']) ?>" required maxlength="10">
      </div>
      <div class="col-md-6" data-aos="fade-left">
  <label class="form-label fw-semibold">Change Image (optional)</label>
  <input type="file" name="image" class="form-control" accept="image/*">
  
  <small class="text-muted d-block mt-2">Current: <?= htmlspecialchars($data['image']) ?></small>
  
  <?php if (!empty($data['image']) && file_exists("gallery images/" . $data['image'])): ?>
    <div class="mt-2">
      <img src="gallery images/<?= htmlspecialchars($data['image']) ?>" 
           alt="Current Image" 
           width="120" height="120" 
           class="rounded shadow-sm border">
    </div>
  <?php else: ?>
    <small class="text-danger">No image available.</small>
  <?php endif; ?>
</div>

      <div class="col-12 text-center" data-aos="zoom-in-up">
        <button type="submit" name="update" class="btn btn-warning px-5" data-aos="fade-right">Update</button>
        <a href="admin_dashboard.php" class="btn btn-secondary ms-2" data-aos="fade-left">
              <i class="bi bi-arrow-left"></i> Back
            </a>
      </div>
    </form>
  </div>
</div>

<?php include("foot.php"); ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
  AOS.init({ duration:1000, once:true });
  const toggle=document.getElementById('darkToggle');
  if(localStorage.getItem('darkMode')==='enabled') document.body.classList.add('dark-mode');
  toggle.addEventListener('click',()=>{
    document.body.classList.toggle('dark-mode');
    localStorage.setItem('darkMode',document.body.classList.contains('dark-mode')?'enabled':'disabled');
  });

  // Category Validation
const catInput = document.querySelector("input[name='category']");
if (catInput) {
  catInput.addEventListener("input", function () {
    // Allow letters, numbers, spaces, and limited punctuation
    this.value = this.value.replace(/[^a-zA-Z0-9\s\-\&\,\.\'\(\)\+]/g, "");
  });
}

</script>
</body>
</html>
