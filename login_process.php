<?php
session_start();
include("connect.php"); // $conn = new mysqli(...)

// Helper: set secure cookie with options (PHP 7.3+ style)
function set_secure_cookie($name, $value, $days = 30) {
    $expire = time() + (86400 * $days);
    $secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
    // If PHP supports array options (7.3+)
    if (PHP_VERSION_ID >= 70300) {
        setcookie($name, $value, [
            'expires' => $expire,
            'path' => '/',
            'domain' => $_SERVER['HTTP_HOST'],
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    } else {
        // fallback (older PHP) - sameSite not fully supported in older versions
        setcookie($name, $value, $expire, "/", $_SERVER['HTTP_HOST'], $secure, true);
    }
}

// Basic CSRF check (optional but recommended)
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
        // invalid CSRF
        echo "<script>alert('Invalid form submission'); window.location.href='login.php';</script>";
        exit;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $remember = isset($_POST["remember"]);

    // Prepared statement to fetch user
   $stmt = $conn->prepare("SELECT id, name, email, phone, password FROM users WHERE email = ? LIMIT 1");
$stmt->bind_param("s", $email);
$stmt->execute();
$res = $stmt->get_result();

if ($res && $res->num_rows === 1) {
    $user = $res->fetch_assoc();

    if (password_verify($password, $user['password'])) {
        // good login
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name']    = $user['name'];
        $_SESSION['email']   = $user['email'];
        $_SESSION['phone']   = $user['phone']; 

            if ($remember) {
                // generate raw token for cookie
                $rawToken = bin2hex(random_bytes(32)); // 64 chars strong token
                // hash token for DB storage
                $hashed = password_hash($rawToken, PASSWORD_DEFAULT);

                // store hashed token in DB (remember_token column)
                $up = $conn->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
                $up->bind_param("si", $hashed, $user['id']);
                $up->execute();

                // set cookie with raw token (cookie only)
                set_secure_cookie("remember_token", $rawToken, 30);
                set_secure_cookie("user_email", $user['email'], 30);
            } else {
                // clear cookies if exist
                if (PHP_VERSION_ID >= 70300) {
                    setcookie("remember_token", "", ['expires' => time() - 3600, 'path' => '/', 'domain' => $_SERVER['HTTP_HOST'], 'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']==='on', 'httponly' => true, 'samesite' => 'Lax']);
                    setcookie("user_email", "", ['expires' => time() - 3600, 'path' => '/', 'domain' => $_SERVER['HTTP_HOST'], 'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']==='on', 'httponly' => true, 'samesite' => 'Lax']);
                } else {
                    setcookie("remember_token", "", time() - 3600, "/", $_SERVER['HTTP_HOST'], isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']==='on', true);
                    setcookie("user_email", "", time() - 3600, "/", $_SERVER['HTTP_HOST'], isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']==='on', true);
                }
                // also remove token from DB for this user (optional)
                $up = $conn->prepare("UPDATE users SET remember_token = NULL WHERE id = ?");
                $up->bind_param("i", $user['id']);
                $up->execute();
            }

            echo "<script>window.location.href = 'index.php';</script>";
            exit;
        } else {
            echo "<script>alert('Invalid password'); window.location.href = 'login.php';</script>";
            exit;
        }
    } else {
        echo "<script>alert('Email not found'); window.location.href = 'login.php';</script>";
        exit;
    }
}
?>
