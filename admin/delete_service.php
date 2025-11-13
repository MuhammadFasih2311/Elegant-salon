<?php
include("connect.php");

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Optional: delete image from folder
    $get = mysqli_query($conn, "SELECT image FROM serve WHERE id = $id");
    $row = mysqli_fetch_assoc($get);
    $image_path = "gallery images/" . $row['image'];
    if (file_exists($image_path)) {
        unlink($image_path);
    }

    $delete = "DELETE FROM serve WHERE id = $id";
    if (mysqli_query($conn, $delete)) {
        header("Location: admin_dashboard.php?delete_msg=" . urlencode("Service deleted successfully!"));
        exit();
    } else {
        echo "Error deleting service: " . mysqli_error($conn);
    }
}
?>
