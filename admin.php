<?php
session_start();
include 'db.php';

/* FETCH ALL ATTENDANCE WITH USER */
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';
$userFilter = $_GET['user'] ?? '';
$query = "
SELECT 
    a.*, 
    CONCAT(u.firstname, ' ', u.middlename, ' ', u.lastname) AS fullname, 
    r.id as report_id,
    r.status as report_status,
    r.report_type,
    r.reason,
    r.proof_image
FROM attendance a
JOIN users u ON a.user_id = u.id
LEFT JOIN attendance_reports r ON r.attendance_id = a.id
WHERE 1
";
if ($search) {
    $search = $conn->real_escape_string($search);
    $query .= " AND CONCAT(u.firstname, ' ', u.middlename, ' ', u.lastname) LIKE '%$search%'";
}

// edited date filter logic to allow single date filtering as well as range
if ($start_date && $end_date) {
    // RANGE
    $query .= " AND DATE(a.created_at) BETWEEN '$start_date' AND '$end_date'";
} elseif ($start_date) {
    // EXACT DATE ONLY
    $query .= " AND DATE(a.created_at) = '$start_date'";
} elseif ($end_date) {
    // OPTIONAL: if only end date → exact match too
    $query .= " AND DATE(a.created_at) = '$end_date'";
} else {
    // ✅ DEFAULT: CURRENT MONTH
    $currentMonth = date('m');
    $currentYear = date('Y');

    $query .= " AND MONTH(a.created_at) = '$currentMonth' 
                AND YEAR(a.created_at) = '$currentYear'";
}

if ($userFilter) {
    $query .= " AND a.user_id = " . intval($userFilter);
}

if ($status) {
    if ($status === 'normal') {
        $query .= " AND (r.id IS NULL OR r.status IN ('approved','rejected'))";
    } elseif ($status === 'pending') {
        $query .= " AND r.status = 'pending'";
    }
}

$query .= " ORDER BY a.created_at DESC";

$result = $conn->query($query);


?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>

<style>
    body {
        background: #F1F1F1;
        font-family: 'Segoe UI';
        min-height: 100vh;
    }

    .card-glass {
        background: white;
        backdrop-filter: blur(15px);
        border-radius: 20px;
        padding: 30px;
        color: black;
        -webkit-box-shadow: 2px 4px 12px -1px rgba(60, 60, 60, 0.75);
        box-shadow: 2px 4px 12px -1px rgba(60, 60, 60, 0.75);
    }


    .expand-btn {
        padding: 6px 14px;
        border: none;
        background: #fbc531;
        color: #000;
        font-weight: bold;
        border-radius: 5px;
        cursor: pointer;
    }

    .expand-btn:hover {
        background: #e1b12c;
    }

    .badge {
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 12px;
        color: #fff;
    }

    .badge.active {
        background: #44bd32;
    }

    .badge.inactive {
        background: #e84118;
    }

    /* Expandable Row */
    .expandable-row {
        display: none;
        background: #f9f9f9;
    }

    .expand-content {
        padding: 15px;
        animation: fadeSlide 0.3s ease;
    }

    @keyframes fadeSlide {
        from {
            opacity: 0;
            transform: translateY(-5px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .admin-card {
        background: white;
        border-radius: 20px;
        padding: 25px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
    }

    .accordion-button {
        border-radius: 15px !important;
        font-weight: 600;
    }

    .attendance-img {
        width: 100%;
        max-width: 200px;
        border-radius: 12px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
    }

    .badge-modern {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
    }

    .section-title {
        font-weight: 600;
        margin-bottom: 10px;
    }

    .info-box {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 15px;
    }

    .expanded-wrapper {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 16px;
    }

    .info-box {
        background: white;
        padding: 16px;
        border-radius: 14px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.06);
        height: 100%;
    }

    .info-box.border-success {
        border-left: 5px solid #28a745;
    }

    .info-box.border-danger {
        border-left: 5px solid #dc3545;
    }

    .attendance-img {
        width: 100%;
        max-width: 180px;
        border-radius: 12px;
        margin-top: 10px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
    }

    .modal-content {
        border-radius: 20px;
    }

    .modal-header {
        border-bottom: none;
    }

    .btn-outline-primary,
    .btn-outline-success {
        border-radius: 20px;
    }


    .proof-card {
        background: white;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
        transition: 0.3s ease;
        cursor: pointer;
    }

    .proof-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
    }

    .proof-img {
        width: 100%;
        height: 180px;
        object-fit: cover;
        transition: 0.3s ease;
    }

    .proof-card:hover .proof-img {
        transform: scale(1.05);
    }

    .portal-activity-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
        transition: 0.3s ease;
    }

    .portal-activity-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
    }

    .status-dot {
        display: inline-block;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        margin-right: 6px;
    }

    .status-dot.online {
        background-color: #28a745;
        /* green */
    }

    .status-dot.offline {
        background-color: #6c757d;
        /* grey */
    }

    .breaks-header h5 {
        color: #1f2937;
        font-size: 1.05rem;
    }

    .break-card-modern {
        position: relative;
        background: linear-gradient(145deg, #ffffff, #f8fafc);
        border: 1px solid #eef2f7;
        border-radius: 22px;
        padding: 20px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
        transition: all 0.25s ease;
        overflow: hidden;
    }

    .break-card-modern:hover {
        transform: translateY(-4px);
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.12);
        border-color: #f6d365;
    }

    .break-card-modern::before {
        content: "";
        position: absolute;
        inset: 0 auto 0 0;
        width: 5px;
        background: linear-gradient(180deg, #facc15, #f59e0b);
        border-radius: 22px 0 0 22px;
    }

    .break-card-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        margin-bottom: 18px;
    }

    .break-badge {
        display: inline-flex;
        align-items: center;
        background: #fff7d6;
        color: #9a6700;
        font-size: 12px;
        font-weight: 700;
        padding: 8px 12px;
        border-radius: 999px;
        letter-spacing: 0.2px;
    }

    .break-status {
        font-size: 12px;
        font-weight: 700;
        padding: 7px 12px;
        border-radius: 999px;
        white-space: nowrap;
    }

    .status-live {
        background: #fee2e2;
        color: #dc2626;
    }

    .status-done {
        background: #dcfce7;
        color: #16a34a;
    }

    .break-icon {
        display: flex;
        justify-content: center;
        margin-bottom: 18px;
    }

    .break-icon-circle {
        width: 58px;
        height: 58px;
        border-radius: 50%;
        background: linear-gradient(135deg, #fff7d6, #ffe9a8);
        color: #a16207;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.6);
    }

    .break-time-grid {
        display: flex;
        align-items: stretch;
        gap: 14px;
        background: #ffffff;
        border: 1px solid #f1f5f9;
        border-radius: 18px;
        padding: 16px;
    }

    .break-time-box {
        flex: 1;
        min-width: 0;
    }

    .break-label {
        font-size: 12px;
        font-weight: 600;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        margin-bottom: 6px;
    }

    .break-value {
        font-size: 18px;
        font-weight: 700;
        color: #111827;
        line-height: 1.2;
    }

    .break-time-divider {
        width: 1px;
        background: linear-gradient(to bottom, transparent, #e5e7eb, transparent);
    }

    @media (max-width: 576px) {
        .break-card-modern {
            padding: 16px;
            border-radius: 18px;
        }

        .break-time-grid {
            padding: 14px;
            gap: 10px;
        }

        .break-value {
            font-size: 16px;
        }

        .break-card-top {
            align-items: flex-start;
            flex-direction: column;
        }
    }
</style>

<body>

    <div class="container py-5">

        <div>
            <h4>Admin</h4>
            <p id="currentDateTime"></p>
        </div>

        <script>
            function updateDateTime() {
                const now = new Date();

                const options = {
                    weekday: 'long',   // 👈 This shows Monday, Tuesday, etc.
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit'
                };

                document.getElementById("currentDateTime").innerText =
                    now.toLocaleString('en-PH', options);
            }

            // Run immediately
            updateDateTime();

            // Update every second
            setInterval(updateDateTime, 1000);
        </script>

        <!-- edited form -->
        <form method="GET" id="filterForm" class="mb-4">
            <div class="card-glass p-3">
                <div class="row g-3">

                    <div class="col-md-4">
                        <label class="small">Start Date</label>
                        <input type="date" name="start_date" id="startDate" class="form-control rounded-pill"
                            value="<?= $_GET['start_date'] ?? '' ?>">
                    </div>

                    <div class="col-md-4">
                        <label class="small">End Date</label>
                        <input type="date" name="end_date" id="endDate" class="form-control rounded-pill"
                            value="<?= $_GET['end_date'] ?? '' ?>">
                    </div>

                    <div class="col-md-2 d-flex align-items-end">
                        <a href="?" class="btn btn-outline-secondary rounded-pill w-100">
                            Reset
                        </a>

                    </div>

                    <div class="col-md-2 d-flex align-items-end">
                        <a href="export_excel.php?<?= http_build_query($_GET) ?>"
                            class="btn btn-outline-primary rounded-pill w-100"> 📊 Export Excel </a>

                    </div>


                </div>
            </div>
        </form>

        <div class="card-glass">
            <div class="table-responsive">
                <table class="table text-white text-center">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Date</th>
                            <th>Time In</th>

                            <th>Time Out</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        <!-- Main Row -->

                        <?php if ($result->num_rows === 0): ?>
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    No attendance records found 📭
                                </td>
                            </tr>
                        <?php endif; ?>


                        <?php while ($row = $result->fetch_assoc()):
                            //                         $attendanceId = $row['id'];
                        
                            //                         $reportQuery = $conn->query("
                            //     SELECT * FROM attendance_reports 
                            //     WHERE attendance_id = $attendanceId
                            //     LIMIT 1
                            // ");
                            //                         $report = $reportQuery->fetch_assoc();
                            ?>


                            <?php

                            $sessionStmt = $conn->prepare("
    SELECT login_time, last_activity, logout_time
    FROM user_portal_sessions
    WHERE user_id = ?
    AND DATE(login_time) = DATE(?)
    ORDER BY login_time DESC
");

                            $sessionStmt->bind_param("is", $row['user_id'], $row['created_at']);
                            $sessionStmt->execute();
                            $sessionsResult = $sessionStmt->get_result();
                            ?>



                            <tr>
                                <td class="fw-semibold">
                                    <a href="?user=<?= $row['user_id'] ?>" class="text-decoration-none text-dark">
                                        <?= htmlspecialchars($row['fullname']) ?>
                                    </a>
                                </td>
                                <td>
                                    <?= date('M d, Y', strtotime($row['created_at'])) ?>
                                </td>
                                <td>
                                    <?= $row['time_in'] ? date('h:i A', strtotime($row['time_in'])) : '-' ?>
                                </td>

                                -

                                <td>
                                    <?= $row['time_out'] ? date('h:i A', strtotime($row['time_out'])) : '-' ?>
                                </td>
                                <td>
                                    <?php if (!$row['report_id']): ?>
                                        <span class="badge bg-success">Normal</span>

                                    <?php elseif ($row['report_status'] === 'pending'): ?>
                                        <span class="badge bg-danger ">Reported</span>

                                    <?php elseif ($row['report_status'] === 'approved'): ?>
                                        <span class="badge bg-success">Normal</span>

                                    <?php elseif ($row['report_status'] === 'rejected'): ?>
                                        <span class="badge bg-success">Normal</span>

                                    <?php endif; ?>
                                </td>
                                <td>

                                    <?php if (!$row['report_id']): ?>
                                        <!-- No report -->
                                        <button class="btn btn-sm btn-success rounded-pill" onclick="toggleRow(this)">
                                            View
                                        </button>

                                    <?php elseif ($row['report_status'] === 'pending'): ?>
                                        <!-- Pending report -->
                                        <div class="d-flex gap-2 justify-content-center">
                                            <button class="btn btn-sm btn-success rounded-pill" onclick="toggleRow(this)">
                                                View
                                            </button>

                                            <button class="btn btn-outline-success btn-sm rounded-pill" onclick='openAcceptModal(
                <?= $row["report_id"] ?>,
                <?= json_encode($row["fullname"]) ?>,
                <?= json_encode($row["id"]) ?>,
                <?= json_encode(date("Y-m-d", strtotime($row["created_at"]))) ?>,
                <?= json_encode($row["time_in"] ? date("H:i", strtotime($row["time_in"])) : "") ?>,
                <?= json_encode($row["time_out"] ? date("H:i", strtotime($row["time_out"])) : "") ?>,
                <?= json_encode($row["address_in"] ?? "") ?>,
                <?= json_encode($row["address_out"] ?? "") ?>
            )'>
                                                ✔ Accept
                                            </button>

                                            <button class="btn btn-outline-danger btn-sm rounded-pill"
                                                onclick="openRejectModal(<?= $row['report_id'] ?>)">
                                                ✖ Reject
                                            </button>
                                        </div>

                                    <?php else: ?>
                                        <!-- Accepted or Rejected -->
                                        <button class="btn btn-sm btn-success rounded-pill" onclick="toggleRow(this)">
                                            View
                                        </button>
                                    <?php endif; ?>

                                </td>
                            </tr>

                            <!-- Expandable Row -->
                            <tr class="expandable-row">
                                <td colspan="8">
                                    <div class="expanded-wrapper">

                                        <div class="row g-4">
                                            <!-- TIME IN -->
                                            <div class="col-md-6">
                                                <div class="info-box border-success">
                                                    <h6 class="text-success fw-bold mb-2">🟢 Time In</h6>

                                                    <p class="mb-1" style="font-size: 25px; font-weight: bold;">
                                                        <?= $row['time_in'] ? date('h:i A', strtotime($row['time_in'])) : '-' ?>
                                                    </p>

                                                    <p class="mb-1">
                                                        <?= (htmlspecialchars($row['address_in'])) ?>
                                                    </p>


                                                    <?php if ($row['manual_address_in']): ?>
                                                        <p class="mb-1"
                                                            style="font-size: 13px;  background: #ffe7e7; padding: 8px 12px; border-radius: 8px;}">
                                                            📍 Selected Location:
                                                            <?= (htmlspecialchars($row['manual_address_in'])) ?>
                                                        </p>

                                                    <?php endif; ?>


                                                    <?php if ($row['photo_in']): ?>
                                                        <button class="btn btn-sm btn-outline-primary"
                                                            onclick="openPhotoModal('uploads/<?= htmlspecialchars($row['photo_in']) ?>')">
                                                            📸 View Photo
                                                        </button>
                                                    <?php endif; ?>

                                                    <?php if ($row['latitude_in'] && $row['longitude_in']): ?>
                                                        <button class="btn btn-sm btn-outline-success" onclick='openMapModal(
    <?= json_encode($row["latitude_in"]) ?>,
    <?= json_encode($row["longitude_in"]) ?>,
    <?= json_encode($row["manual_latitude_in"]) ?>,
    <?= json_encode($row["manual_longitude_in"]) ?>
)'>
                                                            🗺️ View Location
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </div>

                                            <!-- TIME OUT -->
                                            <div class="col-md-6">
                                                <div class="info-box border-danger">
                                                    <h6 class="text-danger fw-bold mb-2">🔴 Time Out</h6>

                                                    <p class="mb-1" style="font-size: 25px; font-weight: bold;">
                                                        <?= $row['time_out'] ? date('h:i A', strtotime($row['time_out'])) : '-' ?>
                                                    </p>

                                                    <p class="mb-1">
                                                        <?= nl2br(htmlspecialchars($row['address_out'])) ?>
                                                    </p>

                                                    <?php if ($row['manual_address_out']): ?>
                                                        <p class="mb-1"
                                                            style="font-size: 13px;  background: #ffe7e7; padding: 8px 12px; border-radius: 8px;}">
                                                            📍 Selected Location:
                                                            <?= (htmlspecialchars($row['manual_address_out'])) ?>
                                                        </p>

                                                    <?php endif; ?>



                                                    <?php if ($row['photo_out']): ?>
                                                        <button class="btn btn-sm btn-outline-primary"
                                                            onclick="openPhotoModal('uploads/<?= htmlspecialchars($row['photo_out']) ?>')">
                                                            📸 View Photo
                                                        </button>
                                                    <?php endif; ?>

                                                    <?php if ($row['latitude_out'] && $row['longitude_out']): ?>
                                                        <button class="btn btn-sm btn-outline-success" onclick='openMapModal(
    <?= json_encode($row["latitude_out"]) ?>,
    <?= json_encode($row["longitude_out"]) ?>,
    <?= json_encode($row["manual_latitude_out"]) ?>,
    <?= json_encode($row["manual_longitude_out"]) ?>
)'>
                                                            🗺️ View Location
                                                        </button>
                                                    <?php endif; ?>

                                                </div>
                                            </div>
                                        </div>

                                        <?php
                                        $breakStmt = $conn->prepare("
    SELECT break_in, break_out 
    FROM breaks 
    WHERE attendance_id = ?
    ORDER BY break_in ASC
");
                                        $breakStmt->bind_param("i", $row['id']);
                                        $breakStmt->execute();
                                        $breaksResult = $breakStmt->get_result();
                                        ?>


                                        <?php if ($breaksResult->num_rows > 0): ?>
                                            <hr class="my-4">

                                            <div class="breaks-header mb-3">
                                                <div>
                                                    <h5 class="fw-semibold mb-1">Break Sessions</h5>
                                                    <p class="text-muted small mb-0">Employee break activities for this
                                                        attendance record</p>
                                                </div>
                                            </div>

                                            <div class="row g-3">
                                                <?php while ($break = $breaksResult->fetch_assoc()): ?>
                                                    <?php
                                                    $isOngoing = empty($break['break_out']);
                                                    ?>
                                                    <div class="col-12 col-md-6 col-xl-4">
                                                        <div class="break-card-modern h-100">

                                                            <div class="break-card-top">
                                                                <div class="break-badge-wrap">
                                                                    <span class="break-badge">
                                                                        <i class="bi bi-cup-hot-fill me-1"></i> Break
                                                                    </span>
                                                                </div>

                                                                <div
                                                                    class="break-status <?= $isOngoing ? 'status-live' : 'status-done' ?>">
                                                                    <?= $isOngoing ? 'Ongoing' : 'Completed' ?>
                                                                </div>
                                                            </div>

                                                            <!-- <div class="break-icon">
                                                                <div class="break-icon-circle">
                                                                    <i class="bi bi-pause-fill"></i>
                                                                </div>
                                                            </div> -->

                                                            <div class="break-time-grid">
                                                                <div class="break-time-box">
                                                                    <div class="break-label">Break In</div>
                                                                    <div class="break-value">
                                                                        <?= !empty($break['break_in']) ? date('h:i A', strtotime($break['break_in'])) : '-' ?>
                                                                    </div>
                                                                </div>

                                                                <div class="break-time-divider"></div>

                                                                <div class="break-time-box">
                                                                    <div class="break-label">Break Out</div>
                                                                    <div class="break-value <?= $isOngoing ? 'text-danger' : '' ?>">
                                                                        <?= !$isOngoing ? date('h:i A', strtotime($break['break_out'])) : 'Ongoing' ?>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                        </div>
                                                    </div>
                                                <?php endwhile; ?>
                                            </div>
                                        <?php endif; ?>
                                        <!-- REPORT PROOF -->


                                        <!-- REPORT SECTION -->
                                        <?php if ($row['report_status'] === 'pending'): ?>
                                            <hr class="my-3">

                                            <div style="text-align: left;">
                                                <h4 class="fw-semibold mb-2">📎 Report Details</h6>
                                                    <p><strong>Type:</strong>
                                                        <?= ucfirst($row['report_type']) ?>
                                                    </p>
                                                    <p><strong>Reason:</strong>
                                                        <?= nl2br(htmlspecialchars($row['reason'])) ?>
                                                    </p>
                                            </div>

                                            <hr class="my-4">
                                            <!-- 
                                            <div class="text-start mb-3">
                                                <h5 class="fw-semibold">🔐 Portal Activity</h5>
                                                <p class="text-muted small">Login and last active time</p>
                                            </div> -->

                                            <div class="breaks-header mb-3">
                                                <div>
                                                    <h5 class="fw-semibold mb-1">🔐 Portal Activity</h5>
                                                    <p class="text-muted small mb-0">Login and last active time</p>
                                                </div>
                                            </div>

                                            <div class="row g-3">
                                                <?php while ($session = $sessionsResult->fetch_assoc()): ?>

                                                    <?php
                                                    date_default_timezone_set('Asia/Manila');

                                                    $lastActivityTime = strtotime($session['last_activity']);
                                                    $currentTime = time();

                                                    $isActive = false;

                                                    if (!empty($session['last_activity'])) {
                                                        $isActive = ($currentTime - $lastActivityTime) <= 120;
                                                    }
                                                    ?>

                                                    <div class="col-md-4">
                                                        <div class="portal-activity-card p-3">

                                                            <div class="fw-semibold mb-2">
                                                                <span
                                                                    class="status-dot <?= $isActive ? 'online' : 'offline' ?>"></span>
                                                                Login:
                                                                <?= date('h:i:s A', strtotime($session['login_time'])) ?>
                                                            </div>



                                                            <div class="text-muted small">
                                                                🕒 Last Active:
                                                                <?= date('h:i:s A', strtotime($session['last_activity'])) ?>
                                                            </div>

                                                            <div class="mt-2">
                                                                <span
                                                                    class="badge <?= $isActive ? 'bg-success' : 'bg-secondary' ?>">
                                                                    <?= $isActive ? 'Currently Online' : 'Offline' ?>
                                                                </span>
                                                            </div>

                                                        </div>
                                                    </div>

                                                <?php endwhile; ?>
                                            </div>
                                        <?php endif; ?>



                                        <?php
                                        $proofImages = [];

                                        if (!empty($row['proof_image'])) {
                                            $proofImages = json_decode($row['proof_image'], true);
                                        }
                                        ?>

                                        <?php if (!empty($proofImages)): ?>
                                            <hr class="my-4">

                                            <div class="text-start mb-3">
                                                <h5 class="fw-semibold">📷 Proof Attachments</h5>
                                                <p class="text-muted small mb-0">Click image to view full screen</p>
                                            </div>

                                            <div class="row g-3">
                                                <?php foreach ($proofImages as $img): ?>
                                                    <div class="col-6 col-md-4 col-lg-3">
                                                        <div class="proof-card">
                                                            <img src="<?= htmlspecialchars($img) ?>" class="proof-img"
                                                                onclick="openFullscreenImage('<?= htmlspecialchars($img) ?>')">
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>

                                    </div>
                                </td>
                            </tr>




                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

        </div>

    </div>


    <!-- TOAST CONTAINER -->
    <div class="toast-container position-fixed top-0 end-0 p-4" style="z-index: 9999">
        <div id="liveToast" class="toast align-items-center border-0 shadow-lg rounded-4" role="alert">

            <div class="toast-header bg-white text-dark">
                <strong class="me-auto">CHI</strong>
                <small id="toastTimer">Closing in 3s</small>
                <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
            </div>

            <div class="toast-body bg-white text-dark fw-semibold" id="toastMessage">
            </div>

        </div>
    </div>
    <!-- </div> -->


    <!-- MEDIA MODAL -->
    <div class="modal fade" id="mediaModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content rounded-4">

                <div class="modal-header">
                    <h5 class="modal-title" id="mediaModalTitle">Preview</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body text-center">

                    <!-- IMAGE -->
                    <img id="modalImage" class="img-fluid rounded-3 d-none" alt="Attendance Photo">

                    <!-- MAP -->
                    <div id="mapContainer" class="d-flex gap-2 h-200 flex-column flex-md-row w-100"
                        style="height: 400px;">
                        <iframe id="mapFrame" loading="lazy" class="w-100 h-100 rounded-3 border-0" allowfullscreen>
                        </iframe>

                        <iframe id="manual_mapFrame" loading="lazy" class="w-100 h-100 rounded-3 border-0"
                            allowfullscreen>
                        </iframe>
                    </div>

                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="acceptModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 shadow-lg border-0">

                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold">Approve Report</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <form id="acceptForm">
                    <div class="modal-body">

                        <input type="hidden" name="report_id" id="acceptReportId">
                        <input type="hidden" name="attendance_id" id="attendanceID">
                        <input type="hidden" name="date" id="acceptDate">


                        <div class="mb-3">
                            <label class="form-label small">Employee</label>
                            <input type="text" id="acceptEmployee" class="form-control rounded-3" disabled>
                        </div>

                        <div class="row">
                            <div class="col">
                                <label class="small">Time In</label>
                                <input type="time" name="time_in" id="acceptTimeIn" class="form-control rounded-3">
                            </div>
                            <div class="col">
                                <label class="small">Time Out</label>
                                <input type="time" name="time_out" id="acceptTimeOut" class="form-control rounded-3">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col">
                                <label class="small">Address In</label>
                                <textarea name="address_in" id="acceptAddressIn" class="form-control mt-2 rounded-3"
                                    placeholder="Enter addreess..."></textarea>
                            </div>
                            <div class="col">
                                <label class="small">Address Out</label>
                                <textarea name="address_out" id="acceptAddressOut" class="form-control mt-2 rounded-3"
                                    placeholder="Enter address out..."></textarea>
                            </div>
                        </div>


                    </div>

                    <div class="modal-footer border-0">
                        <button type="submit" id="acceptBtn"
                            class="btn btn-success w-100 rounded-pill py-2 fw-semibold">
                            <span id="acceptBtnText">Approve & Update</span>
                            <span id="acceptLoader" class="spinner-border spinner-border-sm ms-2 d-none"></span>
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>


    <div class="modal fade" id="rejectModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 shadow-lg border-0">

                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold text-danger">Reject Report</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <form id="rejectForm">
                    <div class="modal-body">

                        <input type="hidden" name="report_id" id="rejectReportId">

                        <label class="fw-semibold mb-2">Select Reason</label>

                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="reason" value="Invalid proof" required>
                            <label class="form-check-label">Invalid proof</label>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="reason" value="Wrong date">
                            <label class="form-check-label">Wrong date</label>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="reason" value="Blurry image">
                            <label class="form-check-label">Blurry image</label>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="reason" value="Location mismatch">
                            <label class="form-check-label">Location mismatch</label>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="reason" value="others" id="othersRadio">
                            <label class="form-check-label">Others</label>
                        </div>

                        <textarea name="custom_reason" id="customReason" class="form-control mt-2 rounded-3"
                            placeholder="Enter custom reason..." style="display:none;"></textarea>

                    </div>

                    <div class="modal-footer border-0">
                        <button type="submit" id="rejectBtn" class="btn btn-danger w-100 rounded-pill py-2 fw-semibold">
                            <span id="rejectBtnText">Reject Report</span>
                            <span id="rejectLoader" class="spinner-border spinner-border-sm ms-2 d-none"></span>
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>


    <script>
        function openAcceptModal(id, name, attendanceID, date, timeIn, timeOut, addressIn, addressOut) {
            const modal = new bootstrap.Modal(document.getElementById('acceptModal'));

            document.getElementById('attendanceID').value = attendanceID;
            document.getElementById('acceptReportId').value = id;
            document.getElementById('acceptEmployee').value = name;
            document.getElementById('acceptDate').value = date;
            document.getElementById('acceptTimeIn').value = timeIn;
            document.getElementById('acceptTimeOut').value = timeOut;


            document.getElementById('acceptAddressIn').value = addressIn;
            document.getElementById('acceptAddressOut').value = addressOut;

            modal.show();
        }

        function openRejectModal(id) {
            const modal = new bootstrap.Modal(document.getElementById('rejectModal'));
            document.getElementById('rejectReportId').value = id;
            modal.show();
        }

        document.getElementById('othersRadio').addEventListener('change', function () {
            document.getElementById('customReason').style.display =
                this.checked ? 'block' : 'none';
        });

    </script>
    <script>

        // new added js
        const startInput = document.getElementById("startDate");
        const endInput = document.getElementById("endDate");
        const form = document.getElementById("filterForm");

        function autoSubmit() {
            if (startInput.value || endInput.value) {
                form.submit();
            }
        }

        startInput.addEventListener("change", autoSubmit);
        endInput.addEventListener("change", autoSubmit);

        function openPhotoModal(imageSrc) {
            const modal = new bootstrap.Modal(document.getElementById('mediaModal'));

            document.getElementById('mediaModalTitle').textContent = 'Attendance Photo';

            document.getElementById('modalImage').src = imageSrc;
            document.getElementById('modalImage').classList.remove('d-none');

            document.getElementById('mapContainer').classList.add('d-none');



            modal.show();
        }

        function openMapModal(latitude, longitude, manual_latitude, manual_longitude) {
            const modal = new bootstrap.Modal(document.getElementById('mediaModal'));

            document.getElementById('mediaModalTitle').textContent = 'Attendance Location';

            const mapFrame = document.getElementById('mapFrame');
            const manual_mapFrame = document.getElementById('manual_mapFrame');

            // ✅ Convert to numbers safely
            latitude = parseFloat(latitude);
            longitude = parseFloat(longitude);
            manual_latitude = manual_latitude !== null ? parseFloat(manual_latitude) : null;
            manual_longitude = manual_longitude !== null ? parseFloat(manual_longitude) : null;

            // ❌ If invalid GPS → stop
            if (isNaN(latitude) || isNaN(longitude)) {
                alert("Invalid GPS location");
                return;
            }

            // ✅ GPS MAP
            mapFrame.src = `https://www.openstreetmap.org/export/embed.html?bbox=${longitude - 0.005}%2C${latitude - 0.005}%2C${longitude + 0.005}%2C${latitude + 0.005}&layer=mapnik&marker=${latitude}%2C${longitude}`;
            mapFrame.style.display = "block";

            // ✅ Manual map only if valid
            if (
                manual_latitude !== null &&
                manual_longitude !== null &&
                !isNaN(manual_latitude) &&
                !isNaN(manual_longitude)
            ) {
                manual_mapFrame.src = `https://www.openstreetmap.org/export/embed.html?bbox=${manual_longitude - 0.005}%2C${manual_latitude - 0.005}%2C${manual_longitude + 0.005}%2C${manual_latitude + 0.005}&layer=mapnik&marker=${manual_latitude}%2C${manual_longitude}`;
                manual_mapFrame.style.display = "block";
            } else {
                manual_mapFrame.style.display = "none";
            }

            document.getElementById('mapContainer').classList.remove('d-none');
            document.getElementById('modalImage').classList.add('d-none');

            modal.show();
        }
        document.getElementById("acceptForm").addEventListener("submit", function (e) {
            e.preventDefault();

            const timeIn = document.getElementById("acceptTimeIn").value;

            // ⚠️ Check if time is later than 7:00 AM
            if (timeIn) {
                const selectedTime = new Date(`1970-01-01T${timeIn}`);
                const limitTime = new Date(`1970-01-01T07:00`);

                if (selectedTime > limitTime) {
                    const confirmLate = confirm(
                        "⚠ Warning!\n\nThe Time In is later than 7:00 AM.\nThe employee will still be marked as LATE.\n\nAre you sure you want to approve and update this record?"
                    );

                    if (!confirmLate) {
                        return; // stop submit
                    }
                }
            }

            const formData = new FormData(this);

            const btn = document.getElementById("acceptBtn");
            const loader = document.getElementById("acceptLoader");
            const text = document.getElementById("acceptBtnText");

            btn.disabled = true;
            loader.classList.remove("d-none");
            text.textContent = "Processing...";

            fetch("accept_report.php", {
                method: "POST",
                body: formData
            })
                .then(res => res.text())
                .then(data => {

                    if (data.trim() === "success") {

                        showToast("Report Approved Successfully!", "success");
                        setTimeout(() => location.reload(), 1500);

                    } else {

                        btn.disabled = false;
                        loader.classList.add("d-none");
                        text.textContent = "Approve & Update";

                        showToast("Something went wrong.", "error");

                    }

                });
        });

        document.getElementById("rejectForm").addEventListener("submit", function (e) {
            e.preventDefault();

            const formData = new FormData(this);

            const btn = document.getElementById("rejectBtn");
            const loader = document.getElementById("rejectLoader");
            const text = document.getElementById("rejectBtnText");

            btn.disabled = true;
            loader.classList.remove("d-none");
            text.textContent = "Processing...";

            fetch("reject_report.php", {
                method: "POST",
                body: formData
            })
                .then(res => res.text())
                .then(data => {

                    if (data.trim() === "success") {

                        showToast("Report Rejected Successfully!", "success");

                        setTimeout(() => location.reload(), 1500);

                    } else {

                        btn.disabled = false;
                        loader.classList.add("d-none");
                        text.textContent = "Reject Report";

                        showToast("Something went wrong.", "error");

                    }

                });
        });


    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>




    <script>
        function toggleRow(button) {
            const currentRow = button.closest("tr");
            const nextRow = currentRow.nextElementSibling;

            if (nextRow.style.display === "table-row") {
                nextRow.style.display = "none";
                button.textContent = "View";
            } else {
                nextRow.style.display = "table-row";
                button.textContent = "Hide";
            }



        }

        function openFullscreenImage(imageSrc) {
            const modal = new bootstrap.Modal(document.getElementById('mediaModal'));

            document.getElementById('mediaModalTitle').textContent = 'Proof Preview';

            const modalImage = document.getElementById('modalImage');
            modalImage.src = imageSrc;
            modalImage.classList.remove('d-none');

            document.getElementById('mapContainer').classList.add('d-none');

            modal.show();
        }
    </script>


    <script>
        function showToast(message, type = "success") {

            const toastEl = document.getElementById("liveToast");
            const toastBody = document.getElementById("toastMessage");
            const toastTimer = document.getElementById("toastTimer");

            toastBody.textContent = message;

            // Remove previous color classes
            toastEl.classList.remove("bg-success", "bg-danger", "text-white");

            if (type === "success") {
                toastEl.classList.add("bg-success", "text-white");
            } else if (type === "error") {
                toastEl.classList.add("bg-danger", "text-white");
            }

            const toast = new bootstrap.Toast(toastEl, {
                autohide: true,
                delay: 3000
            });

            toast.show();

            // 🔥 Countdown logic
            let timeLeft = 3;
            toastTimer.textContent = `Closing in ${timeLeft}s`;

            const countdown = setInterval(() => {
                timeLeft--;
                if (timeLeft > 0) {
                    toastTimer.textContent = `Closing in ${timeLeft}s`;
                } else {
                    clearInterval(countdown);
                }
            }, 1000);

            // Clear interval if manually closed
            toastEl.addEventListener('hidden.bs.toast', () => {
                clearInterval(countdown);
            });
        }
    </script>
</body>

</html>