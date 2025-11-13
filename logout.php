<?php
session_start();
include("connect.php");

// Agar user login hai to uska DB token null karo
if (isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];
    $stmt = $conn->prepare("UPDATE users SET remember_token=NULL WHERE id=?");
    $stmt->bind_param("i", $uid);
    $stmt->execute();
}

// Session destroy
session_unset();
session_destroy();

// Cookies delete
setcookie("user_email", "", time() - 3600, "/");
setcookie("remember_token", "", time() - 3600, "/");

header("Location: login.php");
exit();
?>
