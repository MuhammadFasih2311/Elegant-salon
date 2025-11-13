<?php
include("connect.php");
header('Content-Type: application/json');

$res = $conn->query("SELECT COUNT(*) as cnt FROM bookings WHERE status='pending'");
$count = 0;
if ($res) {
    $row = $res->fetch_assoc();
    $count = (int)$row['cnt'];
}
echo json_encode(['count' => $count]);
?>
