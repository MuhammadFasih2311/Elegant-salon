<?php
session_start();
include("connect.php");

// ✅ Get price from DB (fix applied)
function getPrice($conn, $service, $sub_service) {
    $stmt = $conn->prepare("SELECT price FROM serve WHERE category=? AND label=? LIMIT 1");
    $stmt->bind_param("ss", $service, $sub_service);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();

    if ($row) {
        return (int) filter_var($row['price'], FILTER_SANITIZE_NUMBER_INT);
    }
    return 0;
}

// ✅ Count bookings in given interval (user OR email OR phone)
function countBookings($conn, $user_id, $email, $phone, $minutes, $isMulti=false) {
    $condition = $isMulti ? "persons > 1" : "persons = 1";
    $stmt = $conn->prepare("
        SELECT COUNT(*) as total 
        FROM bookings 
        WHERE (
            (user_id > 0 AND user_id=?) 
            OR email=? 
            OR phone=?
        )
        AND $condition
        AND created_at >= (NOW() - INTERVAL ? MINUTE)
    ");
    $stmt->bind_param("issi", $user_id, $email, $phone, $minutes);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    return $res['total'] ?? 0;
}

// ✅ Check duplicate date+time booking
function hasDuplicateBooking($conn, $user_id, $email, $phone, $date, $time_slot) {
    $stmt = $conn->prepare("
        SELECT COUNT(*) as total 
        FROM bookings 
        WHERE (user_id=? OR email=? OR phone=?)
          AND date=? 
          AND time_slot=?
    ");
    $stmt->bind_param("issss", $user_id, $email, $phone, $date, $time_slot);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    return ($res['total'] ?? 0) > 0;
}

// ✅ Check slot capacity
function slotFull($conn, $date, $time_slot, $service_type, $isMulti=false) {
    if ($service_type === 'home') {
        if ($isMulti) {
            $stmt = $conn->prepare("
                SELECT COUNT(*) as total 
                FROM bookings 
                WHERE date=? 
                  AND time_slot=? 
                  AND service_type='home'
                  AND status IN ('pending','accepted')
            ");
            $stmt->bind_param("ss", $date, $time_slot);
            $stmt->execute();
            $cnt = $stmt->get_result()->fetch_assoc()['total'] ?? 0;
            return $cnt > 0; 
        } else {
            $stmt = $conn->prepare("
                SELECT COUNT(*) as total 
                FROM bookings 
                WHERE date=? 
                  AND time_slot=? 
                  AND service_type='home'
                  AND status IN ('pending','accepted')
            ");
            $stmt->bind_param("ss", $date, $time_slot);
            $stmt->execute();
            $cnt = $stmt->get_result()->fetch_assoc()['total'] ?? 0;
            return $cnt >= 1;
        }
    } elseif ($service_type === 'salon') {
        if ($isMulti) {
            $stmt = $conn->prepare("
                SELECT COUNT(*) as total 
                FROM bookings 
                WHERE date=? 
                  AND time_slot=? 
                  AND service_type='salon'
                  AND persons > 1
                  AND status IN ('pending','accepted')
            ");
            $stmt->bind_param("ss", $date, $time_slot);
            $stmt->execute();
            $cnt = $stmt->get_result()->fetch_assoc()['total'] ?? 0;
            return $cnt > 0;
        } else {
            $stmt = $conn->prepare("
                SELECT 
                  SUM(CASE WHEN persons > 1 THEN 1 ELSE 0 END) as multi_bookings,
                  SUM(CASE WHEN persons = 1 THEN 1 ELSE 0 END) as single_bookings
                FROM bookings 
                WHERE date=? 
                  AND time_slot=? 
                  AND service_type='salon'
                  AND status IN ('pending','accepted')
            ");
            $stmt->bind_param("ss", $date, $time_slot);
            $stmt->execute();
            $res = $stmt->get_result()->fetch_assoc();
            $multi = $res['multi_bookings'] ?? 0;
            $single = $res['single_bookings'] ?? 0;
            if ($multi > 0) return true;
            return $single >= 2;
        }
    }
    return false;
}

// ✅ Main booking logic
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['book'])) {
    $user_id   = $_SESSION['user_id'] ?? 0;
    $name      = trim($_POST['name']);
    $email     = trim($_POST['email']);
    $phone     = trim($_POST['phone'] ?? $_POST['phno']);
    $date      = trim($_POST['date']);
    $time_slot = trim($_POST['time_slot']);
    $address   = isset($_POST['address']) ? trim($_POST['address']) : "";

    // 🔹 Fix: service_type detection
    if (isset($_POST['service_type_single'])) {
        $service_type = $_POST['service_type_single'];
    } elseif (isset($_POST['service_type_multi'])) {
        $service_type = $_POST['service_type_multi'];
    } else {
        $service_type = "home"; // fallback only
    }

    $isMulti   = isset($_POST['multi_booking']);
    $success   = false;

  // 🔹 Address required (min 5 letters for home)
if ($service_type === "home") {
    if (empty($address) || strlen(trim($address)) < 5) {
        $_SESSION['msg'] = "⚠️ Address must be at least 5 characters for home service booking.";
        $_SESSION['msg_type'] = "warning";
        $_SESSION['activeTab'] = $isMulti ? "#multiple" : "#single"; // 🔥 preserve active tab
        header("Location: gallery.php#booking");
        exit;
    }
}

    // ❌ Duplicate slot
    if (hasDuplicateBooking($conn, $user_id, $email, $phone, $date, $time_slot)) {
        $_SESSION['msg'] = "⚠️ You already have a booking at this date & time.";
        $_SESSION['msg_type'] = "warning";
        $_SESSION['old'] = $_POST; // 🔥 form values save
        header("Location: gallery.php#booking");
        exit;
    }

    // ❌ Slot full
if (slotFull($conn, $date, $time_slot, $service_type, $isMulti)) {
    $_SESSION['msg'] = "⚠️ This $service_type slot is fully booked. Please choose another time.";
    $_SESSION['msg_type'] = "danger";
    $_SESSION['activeTab'] = $isMulti ? "#multiple" : "#single"; // 🔥 preserve active tab
    $_SESSION['old'] = $_POST; // 🔥 form values save
    header("Location: gallery.php#booking");
    exit;
}

    if ($isMulti) {
        if (countBookings($conn, $user_id, $email, $phone, 30, true) >= 1) {
            $_SESSION['msg'] = "⚠️ You can make only 1 multiple booking every 30 minutes.";
            $_SESSION['msg_type'] = "warning";
            header("Location: gallery.php#booking");
            exit;
        }

        if (!empty($_POST['multi_services'])) {
            $personsCount = count($_POST['multi_services']);
            foreach ($_POST['multi_services'] as $srv) {
                $service     = $srv['service'];
                $sub_service = !empty($srv['sub_service']) ? $srv['sub_service'] : '';
                $price       = getPrice($conn, $service, $sub_service);

                $sql = "INSERT INTO bookings 
                        (user_id, name, email, phone, address, service_type, service, sub_service, price, date, time_slot, created_at, status, persons) 
                        VALUES (?,?,?,?,?,?,?,?,?,?,?,NOW(),'pending',?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("issssssssssi",
                    $user_id, $name, $email, $phone, $address,
                    $service_type, $service, $sub_service, $price, $date, $time_slot, $personsCount
                );
                if ($stmt->execute()) {
                    $success = true;
                }
            }
        }
    } else {
        if (countBookings($conn, $user_id, $email, $phone, 60, false) >= 3) {
            $_SESSION['msg'] = "⚠️ You can make only 3 single bookings per hour.";
            $_SESSION['msg_type'] = "warning";
            header("Location: gallery.php#booking");
            exit;
        }

        $service     = $_POST['service'];
        $sub_service = $_POST['sub_service'];
        $persons     = 1;
        $price       = getPrice($conn, $service, $sub_service);

        $sql = "INSERT INTO bookings 
                (user_id, name, email, phone, address, service_type, service, sub_service, price, date, time_slot, created_at, status, persons) 
                VALUES (?,?,?,?,?,?,?,?,?,?,?,NOW(),'pending',?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issssssssssi",
            $user_id, $name, $email, $phone, $address, $service_type,
            $service, $sub_service, $price, $date, $time_slot, $persons
        );
        if ($stmt->execute()) {
            $success = true;
        }
    }

    if ($success) {
        $_SESSION['msg'] = "✅ Booking confirmed successfully!";
        $_SESSION['msg_type'] = "success";
    } else {
        $_SESSION['msg'] = "❌ Something went wrong. Please try again.";
        $_SESSION['msg_type'] = "danger";
    }

    header("Location: gallery.php#booking");
    exit;
}
?>
