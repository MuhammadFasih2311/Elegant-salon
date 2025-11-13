<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include("connect.php");

// --- Security Settings for cookies (optional but recommended) ---
$secure   = isset($_SERVER['HTTPS']); // true if site is using HTTPS
$httponly = true;
$samesite = "Strict"; // or "Lax" if you want cross-site allowed (Strict is safer)

// --- Step 1: Agar already session hai to directly return ---
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    return;
}

// --- Step 2: Agar session nahi hai to cookies check karo ---
if (isset($_COOKIE['admin_email']) && isset($_COOKIE['admin_token'])) {
    $email = $_COOKIE['admin_email'];
    $rawToken = $_COOKIE['admin_token'];

    // DB se admin data lo
    $stmt = $conn->prepare("SELECT * FROM admin WHERE email=? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $dbToken = $row['remember_token'];

        // Token verify karo
        if ($dbToken && password_verify($rawToken, $dbToken)) {
            // ✅ Auto login success
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $row['id'];
            $_SESSION['admin_email'] = $row['email'];

            // Optional: Refresh cookies (extend expiry)
            $expire = time() + (86400 * 30); // 30 days
            setcookie("admin_email", $email, [
                'expires' => $expire,
                'path' => '/',
                'secure' => $secure,
                'httponly' => $httponly,
                'samesite' => $samesite
            ]);
            setcookie("admin_token", $rawToken, [
                'expires' => $expire,
                'path' => '/',
                'secure' => $secure,
                'httponly' => $httponly,
                'samesite' => $samesite
            ]);

            return;
        }
    }
}

// --- Step 3: Agar session + cookie dono fail ho gaye to login page bhejo ---
header("Location: admin-login.php");
exit();
?>
