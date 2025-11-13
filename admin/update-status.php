<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit();
}
include("connect.php");

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['id'], $_POST['status'])) {
    $id = intval($_POST['id']);
    $status = $_POST['status'];

    $allowed = ['pending', 'accepted', 'rejected', 'completed'];
    if (in_array($status, $allowed)) {
        $stmt = $conn->prepare("UPDATE bookings SET status=? WHERE id=?");
        $stmt->bind_param("si", $status, $id);
        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Status updated successfully!"]);
            exit();
        }
    }
    echo json_encode(["success" => false, "message" => "Failed to update status"]);
} else {
    echo json_encode(["success" => false, "message" => "Invalid request"]);
}
?>
