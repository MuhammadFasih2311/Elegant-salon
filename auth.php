<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include("connect.php");

// agar session already set hai to continue
if (!isset($_SESSION['user_id']) && isset($_COOKIE['user_email'], $_COOKIE['remember_token'])) {
    $email = $_COOKIE['user_email'];
    $rawToken = $_COOKIE['remember_token'];

    $stmt = $conn->prepare("SELECT id, name, email, phone, remember_token FROM users WHERE email=? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // hashed token verify
        if (!empty($user['remember_token']) && password_verify($rawToken, $user['remember_token'])) {
            // set session (same keys as login_process.php)
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name']    = $user['name'];
            $_SESSION['email']   = $user['email'];
            $_SESSION['phone']   = $user['phone'];

            // rotate token
            $newRaw = bin2hex(random_bytes(32));
            $newHash = password_hash($newRaw, PASSWORD_DEFAULT);

            $up = $conn->prepare("UPDATE users SET remember_token=? WHERE id=?");
            $up->bind_param("si", $newHash, $user['id']);
            $up->execute();

            // update cookies (30 days)
            $expire = time() + (86400 * 30);
            setcookie("remember_token", $newRaw, $expire, "/", "", isset($_SERVER['HTTPS']), true);
            setcookie("user_email", $user['email'], $expire, "/", "", isset($_SERVER['HTTPS']), true);
        } else {
            // agar token galat ho to cookies clear karo
            setcookie("remember_token", "", time() - 3600, "/");
            setcookie("user_email", "", time() - 3600, "/");
        }
    }
}

// agar abhi bhi session set nahi hua to login page bhejo
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
