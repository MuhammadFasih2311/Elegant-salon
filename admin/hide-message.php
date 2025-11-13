<?php
session_start();

if (!isset($_SESSION['admin_logged_in'])) {
  exit("unauthorized");
}

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;

if ($id > 0) {
  if (!isset($_SESSION['hidden_messages'])) {
    $_SESSION['hidden_messages'] = [];
  }

  // Agar pehle se hide nahi hai to push karo
  if (!in_array($id, $_SESSION['hidden_messages'])) {
    $_SESSION['hidden_messages'][] = $id;
  }
}

echo "done";
?>
