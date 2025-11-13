<?php
include("connect.php");
$sub = $_GET['sub_service'] ?? '';
$sub = trim($sub);

if ($sub !== '') {
    $stmt = $conn->prepare("SELECT price FROM serve WHERE label=? LIMIT 1");
    $stmt->bind_param("s", $sub);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    echo $res['price'] ?? '';
}
?>
