<?php
include("connect.php");
session_start();

if(isset($_POST['save'])) {
    $name = mysqli_real_escape_string($conn, $_POST["name"]);
    $email = mysqli_real_escape_string($conn, $_POST["email"]);
    $message = mysqli_real_escape_string($conn, $_POST["message"]);

    // 🕐 Check how many messages user sent in last 1 hour
    $check_query = "SELECT COUNT(*) AS cnt FROM contacts 
                    WHERE email='$email' AND created_at >= NOW() - INTERVAL 1 HOUR";
    $check_res = mysqli_query($conn, $check_query);
    $row = mysqli_fetch_assoc($check_res);
    $count = $row['cnt'];

    if ($count >= 3) {
        echo "<script>alert('❌ You can only send 3 messages per hour to prevent spam. Please try again later.'); 
              window.location.href='contact.php';</script>";
        exit();
    }

    // ✅ Insert new message
    $insert_query = "INSERT INTO contacts (name, email, message, created_at) 
                     VALUES('$name', '$email', '$message', NOW())";

    if (mysqli_query($conn, $insert_query)) {
        echo "<script>alert('✅ Your message has been submitted successfully!'); 
              window.location.href='contact.php';</script>";
    } else {
        echo "<script>alert('❌ Error submitting message.');</script>";
    }

    mysqli_close($conn);
}
?>
