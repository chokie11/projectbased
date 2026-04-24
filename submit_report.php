<?php

date_default_timezone_set('Asia/Manila');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

session_start();
include 'db.php';

$user_id = $_SESSION['user_id'] ;

$attendance_id = $_POST['attendance_id'];
$type = $_POST['type'];
$reason = $_POST['reason'];


// Delete only if status is rejected (for security)
$stmt = $conn->prepare("
        DELETE FROM attendance_reports
        WHERE attendance_id = ?
        AND status = 'rejected'
    ");
$stmt->bind_param("i", $attendance_id);

$stmt->execute();



$uploadDir = 'uploads/proofs/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$proofPaths = [];

if (!empty($_FILES['proofs']['name'][0])) {

    foreach ($_FILES['proofs']['tmp_name'] as $index => $tmpName) {

        if ($_FILES['proofs']['error'][$index] !== UPLOAD_ERR_OK)
            continue;

        $fileName = time() . '_' . basename($_FILES['proofs']['name'][$index]);
        $targetPath = $uploadDir . $fileName;

        move_uploaded_file($tmpName, $targetPath);
        $proofPaths[] = $targetPath;
    }
}



/* Save as JSON */
$proofJson = json_encode($proofPaths);

$stmt = $conn->prepare("
    INSERT INTO attendance_reports
    (attendance_id, user_id, report_type, reason, proof_image)
    VALUES (?,?,?,?,?)
");

$stmt->bind_param(
    "iisss",
    $attendance_id,
    $user_id,
    $type,
    $reason,
    $proofJson
);

$stmt->execute();




$user = $conn->prepare("SELECT CONCAT(u.firstname, ' ', u.middlename, ' ', u.lastname) AS fullname, email FROM users WHERE id=?");
$user->bind_param("i", $user_id);
$user->execute();
$userResult = $user->get_result();
$userData = $userResult->fetch_assoc();

$userName = $userData['fullname'] ?? '';
$userEmail = $userData['email'] ?? '';



/* SEND EMAIL FUNCTION */
function sendAdminNotification($subject, $body)
{
    $mail = new PHPMailer(true);

    try {

        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'ithelpdesk.colorsteel@gmail.com';
        $mail->Password = 'ywdloatptrbpgctu';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('ithelpdesk.colorsteel@gmail.com', 'Attendance System');

        $adminEmail = "2022308224@pampangastateu.edu.ph"; // change if needed
        $mail->addAddress($adminEmail);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;

        $mail->send();

    } catch (Exception $e) {
        // optional logging
    }
}


/* EMAIL TEMPLATE */
 $emailBody = '
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Attendance Notification</title>
</head>
<body style="margin:0; padding:0; background-color:#f4f6f9; font-family:Arial, sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f6f9; padding:30px 0;">
<tr>
<td align="center">

<table width="500" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:12px; overflow:hidden; box-shadow:0 5px 15px rgba(0,0,0,0.05);">

<tr>
<td align="center" style="background:linear-gradient(135deg,#28a745,#3be062); padding:30px; color:#ffffff;">
    
    <img src="https://chiportal.colorsteelholdings.com/images/CHIs.png" width="70" style="margin-bottom:10px;">
    
    <h2 style="margin:0; font-weight:600;">Colorsteel Holdings Inc.</h2>
    <p style="margin:5px 0 0; font-size:14px; opacity:0.9;">
        CHI is right mix of complementing subsidiaries give the holdings group a competitive edge.
    </p>
</td>
</tr>

<tr>
<td style="padding:40px 30px; background-color:#ffffff;">

    <!-- Title -->
  <h2 style="margin:0 0 5px 0; font-size:20px; color:#222; text-align:center;">New Attendance Report Submitted</h2>

<p style="text-align:center; color:#555; font-size:14px;">
'.$userName.' has submitted a new attendance report.
</p>

<p style="text-align:center; color:#777; font-size:13px;">
Please review the report details below and take the appropriate action.
</p>

    <!-- Info Card -->
    <table width="100%" cellpadding="0" cellspacing="0"
        style="border-collapse:collapse; font-size:14px;">

        <tr>
            <td style="padding:12px; border-bottom:1px solid #eee; color:#555;">
                <strong>Reported Date</strong>
            </td>
            <td style="padding:12px; border-bottom:1px solid #eee; text-align:right; color:#222;">
              '.date("l, F j, Y").'
            </td>
        </tr>

          <tr>
            <td style="padding:12px; border-bottom:1px solid #eee; color:#555;">
                <strong>Report Type:</strong>
            </td>
            <td style="padding:12px; border-bottom:1px solid #eee; text-align:right; color:#222;">
                '.$type.'
            </td>
        </tr>

        <tr>
            <td style="padding:12px; border-bottom:1px solid #eee; color:#555;">
                <strong>Reason</strong>
            </td>
            <td style="padding:12px; border-bottom:1px solid #eee; text-align:right; color:#222;">
                '.$reason.'
            </td>
        </tr>


      



    </table>

</td>
</tr>

<tr>
<td align="center" style="padding:20px; font-size:12px; color:#999; background:#fafafa;">
    © '.date("Y").' Colorsteel Holdings Inc. All rights reserved.
</td>
</tr>

</table>

</td>
</tr>
</table>

</body>
</html>
';

sendAdminNotification("New Attendance Report Submitted", $emailBody);


echo "✅ Report submitted. HR will review it.";