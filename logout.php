<?php

session_start();
include 'db.php';

if(isset($_SESSION['portal_session_id'])) {

    $session_id = $_SESSION['portal_session_id'];

    // Get login time
    $stmt = $conn->prepare("
        SELECT login_time 
        FROM user_portal_sessions 
        WHERE id = ?
    ");
    $stmt->bind_param("i", $session_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $session = $result->fetch_assoc();

    if($session){

        $login_time = strtotime($session['login_time']);
        $logout_time = time();
        $duration = $logout_time - $login_time; // seconds

        $update = $conn->prepare("
            UPDATE user_portal_sessions
            SET logout_time = NOW(),
                session_duration = ?
            WHERE id = ?
        ");
        $update->bind_param("ii", $duration, $session_id);
        $update->execute();
    }
}

session_destroy();
header("Location: login.php");
exit();

