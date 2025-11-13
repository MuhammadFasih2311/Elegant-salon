<?php
include("connect.php");

if (!isset($_GET['id'])) {
    echo "Invalid ID";
    exit();
}

$id = $_GET['id'];
mysqli_query($conn, "DELETE FROM services WHERE id=$id");

header("Location: manage-services.php?msg=Service deleted successfully");
exit();
?>
