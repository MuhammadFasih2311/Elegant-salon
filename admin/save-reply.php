<?php
include("connect.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);
    $reply = mysqli_real_escape_string($conn, $_POST['reply'] ?? '');

    if ($id > 0 && $reply) {
        $query = "UPDATE contacts SET reply='$reply' WHERE id=$id";
        echo mysqli_query($conn, $query) ? "done" : "error";
    } else {
        echo "missing";
    }
} else {
    echo "invalid";
}
?>
