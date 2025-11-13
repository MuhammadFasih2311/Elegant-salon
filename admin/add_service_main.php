<?php
include("connect.php");

session_start();
if (!isset($_SESSION['admin_logged_in'])) {
  header("Location: admin-login.php");
  exit();
}

if (isset($_POST['submit'])) {
  $title = $_POST['title'];
  $description = $_POST['description'];
  $price = $_POST['price'];
  $icon = $_POST['icon'];

  $image = $_FILES['image']['name'];
  $tmp = $_FILES['image']['tmp_name'];
  move_uploaded_file($tmp, "images/" . $image);

  $sql = "INSERT INTO services (title, description, price, icon, image) 
          VALUES ('$title', '$description', '$price', '$icon', 'images/$image')";

  if (mysqli_query($conn, $sql)) {
    header("Location: manage-services.php?msg=Service added successfully");
    exit();
  } else {
    echo "Error: " . mysqli_error($conn);
  }
}
include("auth-check.php"); 
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add Service</title>
  <meta name="viewport" content="width=device-width, initial-scale=1"> <!-- ✅ important -->
  <link rel="icon" href="images/logo.png" type="image/png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
  <style>
    body { 
      background: linear-gradient(135deg,#f8f9fa,#e9ecef); 
    }
    body.dark-mode { 
      background:#121212; 
      color:#fff; 
    }
    .form-card { 
      max-width:800px; 
      margin:auto; 
      background:#fff; 
      border-radius:14px; 
      padding:30px; 
      box-shadow:0 10px 25px rgba(0,0,0,0.1); 
      width:100%;
    }
    body.dark-mode .form-card { 
      background:#1a1a1a; 
      border:1px solid #333; 
    }
    /* ✅ Responsive Fix */
    @media (max-width: 768px) {
      .form-card {
        padding:20px;
        border-radius:10px;
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
    }

    #darkToggle {
    z-index: 9999;
  }
  /* Dark mode toggle button style */
body.dark-mode #darkToggle {
  background-color: #fff !important;
  color: #000 !important;
  border: none;
}
#darkToggle {
  transition: background-color 0.3s ease, color 0.3s ease;
}
/* Choti screen par bhi same position */
@media (max-width: 576px) {
  #darkToggle {
    bottom: 15px;
    right: 15px;
  }
}

  </style>
</head>
<body class="d-flex flex-column min-vh-100">

<?php include("navbar.php"); ?>

<!-- Dark Mode Toggle -->
<button class="btn btn-dark position-fixed end-0 bottom-0 m-3 rounded-circle shadow" id="darkToggle">
  <i class="bi bi-moon-stars-fill"></i>
</button>

<div class="container my-5 flex-grow-1">
  <div class="form-card">
    <h3 class="text-warning mb-4 text-center" data-aos="fade-down">➕ Add New Service</h3>
    <form method="POST" enctype="multipart/form-data" class="row g-3">
      <div class="col-md-6" data-aos="fade-right">
        <label class="form-label">Title</label>
        <input type="text" name="title" class="form-control" placeholder="Title" required maxlength="30" pattern="[A-Za-z0-9\s\-\&\,\.\'\(\)\+]+"
       title="Alphabets, numbers & limited symbols (-, &, ., ', (, ), +, ,) allowed">
      </div>
      <div class="col-md-6" data-aos="fade-left">
        <label class="form-label">Icon</label>
        <input type="text" name="icon" class="form-control" placeholder="Icon (e.g. fas fa-spa)" required maxlength="30">
      </div>
      <div class="col-12" data-aos="fade-left">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" placeholder="Description" rows="3" required maxlength="200"></textarea>
      </div>
      <div class="col-md-6" data-aos="fade-right">
        <label class="form-label">Price</label>
        <input type="text" name="price" class="form-control" placeholder="Price" required maxlength="10">
      </div>
      <div class="col-md-6" data-aos="fade-left">
        <label class="form-label">Image</label>
        <input type="file" name="image" class="form-control" required>
      </div>
      <div class="col-12 text-center" data-aos="zoom-in-up">
        <button type="submit" name="submit" class="btn btn-warning px-5" data-aos="fade-right">Add Service</button>
        <a href="manage-services.php" class="btn btn-secondary ms-2" data-aos="fade-left">
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
  AOS.init({ duration: 1000, once: true });
  const toggle = document.getElementById('darkToggle');
  if(localStorage.getItem('darkMode')==='enabled') document.body.classList.add('dark-mode');
  toggle.addEventListener('click',()=>{
    document.body.classList.toggle('dark-mode');
    localStorage.setItem('darkMode',document.body.classList.contains('dark-mode')?'enabled':'disabled');
  });

  // Title Validation (alphabets only)
  const catInput = document.querySelector("input[name='title']");
if (catInput) {
  catInput.addEventListener("input", function () {
    // Allow letters, numbers, spaces, and limited punctuation
    this.value = this.value.replace(/[^a-zA-Z0-9\s\-\&\,\.\'\(\)\+]/g, "");
  });
}
</script>
</body>
</html>
