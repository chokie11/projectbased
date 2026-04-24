<?php
session_start();
include "db.php";

date_default_timezone_set('Asia/Manila');

$user_id = $_SESSION['user_id'];
$type = $_POST['type'];

$today = date("Y-m-d");
$now = date("Y-m-d H:i:s");


// get today's attendance
$stmt = $conn->prepare("
SELECT id FROM attendance 
WHERE user_id=? AND DATE(created_at)=CURDATE()
LIMIT 1
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
$attendance = $res->fetch_assoc();

if (!$attendance) {
    die("Time In first");
}

$attendance_id = $attendance['id'];

if ($type == "break_in") {

    // check if may active break
    $check = $conn->prepare("
        SELECT * FROM breaks 
        WHERE attendance_id=? AND break_out IS NULL
    ");
    $check->bind_param("i", $attendance_id);
    $check->execute();
    $active = $check->get_result();

    if ($active->num_rows > 0) {
        die("Already on break");
    }

    $stmt = $conn->prepare("
        INSERT INTO breaks (attendance_id, break_in)
        VALUES (?, NOW())
    ");
    $stmt->bind_param("i", $attendance_id);
    $stmt->execute();

    echo "Break Started";
}

if ($type == "break_out") {

    $stmt = $conn->prepare("
        UPDATE breaks 
        SET break_out = NOW()
        WHERE attendance_id=? AND break_out IS NULL
        ORDER BY break_in DESC
        LIMIT 1
    ");
    $stmt->bind_param("i", $attendance_id);
    $stmt->execute();

    echo "Break Ended";
}
?>