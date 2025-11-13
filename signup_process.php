<?php
include 'connect.php';
session_start();

if (isset($_POST['signup'])) {
  $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
  $email = mysqli_real_escape_string($conn, $_POST['email']);
  $phone = mysqli_real_escape_string($conn, $_POST['phone']);
  $password = $_POST['password'];
  $confirm_password = $_POST['confirm_password'];

  if ($password !== $confirm_password) {
    echo "<script>alert('Passwords do not match'); window.history.back();</script>";
    exit();
  }

  if (!preg_match('/^[0-9]{11}$/', $phone)) {
    echo "<script>alert('Phone must be exactly 11 digits (e.g. 03XXXXXXXXX)'); window.history.back();</script>";
    exit();
  }

  // Check email
  $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $stmt->store_result();
  if ($stmt->num_rows > 0) {
    echo "<script>alert('Email already registered. Please login or use password reset.'); window.history.back();</script>";
    exit();
  }
  $stmt->close();

  // Check phone
  $stmt = $conn->prepare("SELECT id FROM users WHERE phone = ? LIMIT 1");
  $stmt->bind_param("s", $phone);
  $stmt->execute();
  $stmt->store_result();
  if ($stmt->num_rows > 0) {
    echo "<script>alert('Phone number already registered. If this is your number use login or password reset.'); window.history.back();</script>";
    exit();
  }
  $stmt->close();

  $hashed_password = password_hash($password, PASSWORD_DEFAULT);

  $stmt = $conn->prepare("INSERT INTO users (name, email, phone, password) VALUES (?, ?, ?, ?)");
  $stmt->bind_param("ssss", $fullname, $email, $phone, $hashed_password);

  if ($stmt->execute()) {
    echo "<script>alert('Signup successful! Please log in.'); window.location.href = 'login.php';</script>";
    exit();
  } else {
    // Friendly fallback — if UNIQUE constraint unexpectedly triggered
    if ($conn->errno == 1062) {
      echo "<script>alert('Duplicate entry detected. Please try logging in or contact support.'); window.history.back();</script>";
    } else {
      echo "Error: " . $conn->error;
    }
    exit();
  }
} else {
  echo "Invalid access.";
}
?>
