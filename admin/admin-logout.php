<?php
session_start();
include("connect.php");

if (isset($_SESSION['admin_id'])) {
    $id = $_SESSION['admin_id'];
    $stmt = $conn->prepare("UPDATE admin SET remember_token=NULL WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

session_unset();
session_destroy();

// clear cookies
setcookie("admin_email", "", time() - 3600, "/");
setcookie("admin_token", "", time() - 3600, "/");

header("Location: admin-login.php");
exit();
?>
