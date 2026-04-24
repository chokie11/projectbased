<?php
session_start();
include 'db.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];


?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Leave Request</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

<style>
body {
    background: #F1F1F1;
    font-family: 'Segoe UI';
}

/* GLASS CARD */
.card {
    background: white;
    border-radius: 20px;
    padding: 25px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.08);
}

/* BUTTON */
.btn {
    border-radius: 12px;
    font-weight: 600;
}

/* INPUT */
.form-control, .form-select {
    border-radius: 12px;
    padding: 10px;
}

/* STATUS BADGE */
.badge {
    padding: 6px 12px;
    border-radius: 20px;
}

/* RESPONSIVE SPACING */
@media (max-width:768px){
    .card-modern{
        padding: 18px;
    }
}
</style>
</head>

<body>

<div class="container py-4">

    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Leave Management</h4>

        <a href="attendance.php" class="btn btn-outline-secondary rounded-pill">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>

    <div class="row g-4">

        <!-- LEAVE FORM -->
        <div class="col-12 col-lg-6">
            <div class="card">

               
            </div>
        </div>

        <!-- File request documentaion -->
        <div class="col-12 col-lg-6">
            <div class="card">

            </div>
        </div>

    </div>

</div>

</body>
</html>