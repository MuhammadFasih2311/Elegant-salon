<?php
session_start();
$id = $_POST['id'] ?? null;

if ($id) {
    if (!isset($_SESSION['hidden_bookings'])) {
        $_SESSION['hidden_bookings'] = [];
    }
    if (!in_array($id, $_SESSION['hidden_bookings'])) {
        $_SESSION['hidden_bookings'][] = $id;
    }
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false]);
}
