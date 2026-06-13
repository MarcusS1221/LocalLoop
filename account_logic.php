<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config.php';

//redirects user to login if they arent logged in
if (!isset($_SESSION['email'])) {
    header("Location: register.php");
    exit();
}

$email   = $_SESSION['email'];
$message = "";
$type    = "";

// south african ID verification method (LUHN)
function validateSAID($id) {
    if (!preg_match('/^\d{13}$/', $id)) return false;
    $year  = substr($id, 0, 2);
    $month = substr($id, 2, 2);
    $day   = substr($id, 4, 2);
    $year  = ($year <= date('y')) ? "20$year" : "19$year";
    if (!checkdate($month, $day, $year)) return false;
    $digits = str_split($id);
    $sum = 0;
    for ($i = 0; $i < 12; $i++) {
        if ($i % 2 === 0) {
            $sum += $digits[$i];
        } else {
            $doubled = $digits[$i] * 2;
            $sum += ($doubled > 9) ? $doubled - 9 : $doubled;
        }
    }
    $checkDigit = (10 - ($sum % 10)) % 10;
    return $checkDigit == $digits[12];
}

// saves account details
if (isset($_POST['save_details'])) {
    $name = $_POST['name'];
    $stmt = $conn->prepare("UPDATE users SET name=? WHERE email=?");
    $stmt->bind_param("ss", $name, $email);
    if ($stmt->execute()) {
        $_SESSION['name'] = $name;
        $message = "Details saved!";
        $type    = "success";
    } else {
        $message = "Something went wrong.";
        $type    = "error";
    }
    $stmt->close();
}

// saves and validates user
if (isset($_POST['save_verify'])) {
    $area  = $_POST['area'];
    $idNum = $_POST['id_number'];
    $phone = $_POST['phone'];

    if (!validateSAID($idNum)) {
        $message = "❌ Invalid South African ID number. Please check and try again.";
        $type    = "error";
    } else {
        $verified = 1;
        $check = $conn->prepare("SELECT id FROM user_verification WHERE user_email=?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $stmt = $conn->prepare("UPDATE user_verification SET area=?, id_number=?, phone=?, verified=? WHERE user_email=?");
            $stmt->bind_param("ssssi", $area, $idNum, $phone, $verified, $email);
        } else {
            $stmt = $conn->prepare("INSERT INTO user_verification (user_email, area, id_number, phone, verified) VALUES (?,?,?,?,?)");
            $stmt->bind_param("ssssi", $email, $area, $idNum, $phone, $verified);
        }
        $check->close();

        if ($stmt->execute()) {
            $message = "✅ ID verified! Your account is now verified.";
            $type    = "success";
        } else {
            $message = "Something went wrong saving your info.";
            $type    = "error";
        }
        $stmt->close();
    }
}

// loads user data
$result = $conn->query("SELECT name, email, role FROM users WHERE email = '$email'");
$user   = $result->fetch_assoc();
$name   = $user['name'];
$role   = $user['role'];

$_SESSION['role'] = $role;

//loads verification data
$area = $idNum = $phone = "";
$isVerified = 0;
$stmt = $conn->prepare("SELECT area, id_number, phone, verified FROM user_verification WHERE user_email=?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->bind_result($area, $idNum, $phone, $isVerified);
$stmt->fetch();
$stmt->close();
?>
