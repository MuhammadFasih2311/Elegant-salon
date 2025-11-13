<?php
include("connect.php");
$cat = $_GET['category'] ?? '';
$cat = trim($cat);

if ($cat !== '') {
    $stmt = $conn->prepare("SELECT label FROM serve WHERE category=? ORDER BY label ASC");
    $stmt->bind_param("s", $cat);
    $stmt->execute();
    $res = $stmt->get_result();
    while($r = $res->fetch_assoc()){
        echo "<option value='".htmlspecialchars($r['label'])."'>".htmlspecialchars($r['label'])."</option>";
    }
}
?>
