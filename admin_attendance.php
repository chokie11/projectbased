<?php
session_start();
include 'db.php';

/* FETCH ALL ATTENDANCE WITH USER */
$query = "
SELECT a.*, CONCAT(u.firstname, ' ', u.middlename, ' ', u.lastname) AS fullname
FROM attendance a
JOIN users u ON a.user_id = u.id
ORDER BY a.created_at DESC
";

$result = $conn->query($query);
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Expandable Table</title>

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
</style>

<body>

    <div class="container py-5">
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

                        <?php while ($row = $result->fetch_assoc()):
                            $attendanceId = $row['id'];

                            $reportQuery = $conn->query("
        SELECT * FROM attendance_reports 
        WHERE attendance_id = $attendanceId
        LIMIT 1
    ");
                            $report = $reportQuery->fetch_assoc();
                            ?>


                            <tr>
                                <td class="fw-semibold">
                                    <?= htmlspecialchars($row['fullname']) ?>
                                </td>
                                <td>
                                    <?= date('M d, Y', strtotime($row['created_at'])) ?>
                                </td>
                                <td>
                                    <?= $row['time_in'] ? date('h:i A', strtotime($row['time_in'])) : '-' ?>
                                </td>
                                <td>
                                    <?= $row['time_out'] ? date('h:i A', strtotime($row['time_out'])) : '-' ?>
                                </td>
                                <td>
                                    <?php if ($report): ?>
                                        <span class="badge bg-warning ">Reported</span>
                                    <?php else: ?>
                                        <span class="badge bg-success" style="color: white;">Normal</span>
                                    <?php endif; ?>
                                </td>
                                <td>

                                    <?php if ($report): ?>


                                        <?php if ($report['status'] === 'pending'): ?>
                                            <div class="d-flex-center gap-2">

                                                <button class="btn btn-sm btn-success " onclick="toggleRow(this)">
                                                    View
                                                </button>


                                                <button class="btn btn-outline-success btn-sm rounded-pill px-3" onclick='openAcceptModal(
<?= $report["id"] ?>,
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

                                                <button class="btn btn-outline-danger btn-sm rounded-pill px-3"
                                                    onclick="openRejectModal(<?= $report['id'] ?>)">
                                                    ✖ Reject
                                                </button>

                                      

                                               
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php else: ?>
                                             <button class="btn btn-sm btn-success " onclick="toggleRow(this)">
                                                    View
                                                </button>


                                    <?php endif; ?>



                                </td>
                            </tr>

                            <!-- Expandable Row -->
                            <tr class="expandable-row">
                                <td colspan="6">
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

                                                    <?php if ($row['photo_in']): ?>
                                                        <button class="btn btn-sm btn-outline-primary"
                                                            onclick="openPhotoModal('uploads/<?= htmlspecialchars($row['photo_in']) ?>')">
                                                            📸 View Photo
                                                        </button>
                                                    <?php endif; ?>

                                                    <?php if ($row['latitude_in'] && $row['longitude_in']): ?>
                                                        <button class="btn btn-sm btn-outline-success"
                                                            onclick="openMapModal(<?= $row['latitude_in'] ?>, <?= $row['longitude_in'] ?>)">
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


                                                    <?php if ($row['photo_out']): ?>
                                                        <button class="btn btn-sm btn-outline-primary"
                                                            onclick="openPhotoModal('uploads/<?= htmlspecialchars($row['photo_out']) ?>')">
                                                            📸 View Photo
                                                        </button>
                                                    <?php endif; ?>

                                                    <?php if ($row['latitude_out'] && $row['longitude_out']): ?>
                                                        <button class="btn btn-sm btn-outline-success"
                                                            onclick="openMapModal(<?= $row['latitude_out'] ?>, <?= $row['longitude_out'] ?>)">
                                                            🗺️ View Location
                                                        </button>
                                                    <?php endif; ?>

                                                </div>
                                            </div>
                                        </div>

                                        <!-- REPORT PROOF -->


                                        <!-- REPORT SECTION -->
                                        <?php if ($report): ?>
                                            <hr class="my-3">

                                            <div style="text-align: left;">
                                                <h4 class="fw-semibold mb-2">📎 Report Details</h6>
                                                    <p><strong>Type:</strong>
                                                        <?= ucfirst($report['report_type']) ?>
                                                    </p>
                                                    <p><strong>Reason:</strong>
                                                        <?= nl2br(htmlspecialchars($report['reason'])) ?>
                                                    </p>
                                            </div>
                                        <?php endif; ?>



                                        <?php
                                        $proofImages = [];

                                        if (!empty($report['proof_image'])) {
                                            $proofImages = json_decode($report['proof_image'], true);
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
                    <div id="mapContainer" class="d-none w-100" style="height: 400px;">
                        <iframe id="mapFrame" loading="lazy" class="w-100 h-100 rounded-3 border-0" allowfullscreen>
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
                                <input type="time" name="time_in" id="acceptTimeIn" class="form-control rounded-3"
                                    required>
                            </div>
                            <div class="col">
                                <label class="small">Time Out</label>
                                <input type="time" name="time_out" id="acceptTimeOut" class="form-control rounded-3"
                                    required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col">
                                <label class="small">Address In</label>
                                <textarea name="address_in" id="acceptAddressIn" class="form-control mt-2 rounded-3"
                            placeholder="Enter addreess..." 
                                    required></textarea>
                            </div>
                            <div class="col">
                                <label class="small">Address Out</label>
                                <textarea name="address_out" id="acceptAddressOut" class="form-control mt-2 rounded-3"
                            placeholder="Enter address out..." 
                                    required></textarea>
                            </div>
                        </div>


                    </div>

                    <div class="modal-footer border-0">
                        <button type="submit" class="btn btn-success w-100 rounded-pill py-2 fw-semibold">
                            Approve & Update
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
                        <button type="submit" class="btn btn-danger w-100 rounded-pill py-2 fw-semibold">
                            Reject Report
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
        function openPhotoModal(imageSrc) {
            const modal = new bootstrap.Modal(document.getElementById('mediaModal'));

            document.getElementById('mediaModalTitle').textContent = 'Attendance Photo';

            document.getElementById('modalImage').src = imageSrc;
            document.getElementById('modalImage').classList.remove('d-none');

            document.getElementById('mapContainer').classList.add('d-none');

            modal.show();
        }

        function openMapModal(latitude, longitude) {
            const modal = new bootstrap.Modal(document.getElementById('mediaModal'));

            document.getElementById('mediaModalTitle').textContent = 'Attendance Location';

            const mapFrame = document.getElementById('mapFrame');
            mapFrame.src = `https://www.openstreetmap.org/export/embed.html?bbox=${longitude - 0.005}%2C${latitude - 0.005}%2C${longitude + 0.005}%2C${latitude + 0.005}&layer=mapnik&marker=${latitude}%2C${longitude}`;

            document.getElementById('mapContainer').classList.remove('d-none');
            document.getElementById('modalImage').classList.add('d-none');

            modal.show();
        }

        document.getElementById("acceptForm").addEventListener("submit", function (e) {
            e.preventDefault();

            const formData = new FormData(this);

            fetch("accept_report.php", {
                method: "POST",
                body: formData
            })
                .then(res => res.text())
                .then(data => {
                    if (data.trim() === "success") {
                        alert("Report Approved!");
                        location.reload(); // refresh table
                    } else {
                        alert("Error occurred.");
                    }
                });
        });

        document.getElementById("rejectForm").addEventListener("submit", function (e) {
            e.preventDefault();

            const formData = new FormData(this);

            fetch("reject_report.php", {
                method: "POST",
                body: formData
            })
                .then(res => res.text())
                .then(data => {
                    if (data.trim() === "success") {
                        alert("Report Rejected!");
                        location.reload();
                    } else {
                        alert("Error occurred.");
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
</body>

</html>