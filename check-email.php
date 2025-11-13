<?php
session_start();
include("connect.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = mysqli_real_escape_string($conn, $_POST['email']);

  $query = "SELECT id FROM users WHERE email='$email'";
  $result = mysqli_query($conn, $query);

  if (mysqli_num_rows($result) === 1) {
    $_SESSION['reset_email'] = $email;
    header("Location: reset-password.php");
    exit;
  } else {
    $_SESSION['error'] = "❌ Email not found!";
    header("Location: forgot-password.php");
    exit;
  }
} else {
  // Prevent direct access
  header("Location: forgot-password.php");
  exit;
}
?>