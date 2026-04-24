<?php
session_start();
include 'db.php';

if(isset($_SESSION['portal_session_id'])) {

    $session_id = $_SESSION['portal_session_id'];

    $stmt = $conn->prepare("
        UPDATE user_portal_sessions
        SET last_activity = NOW()
        WHERE id = ?
        AND logout_time IS NULL
    ");

    $stmt->bind_param("i", $session_id);
    $stmt->execute();

    echo "updated";

} else {
    echo "no session id";
}