<?php
date_default_timezone_set('Asia/Manila');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';


include 'db.php';

$report_id = $_POST['report_id'];
$attendance_id = $_POST['attendance_id'];
$date = $_POST['date'];
$time_in = $_POST['time_in'] ?? '';
$time_out = $_POST['time_out'] ?? '';

$address_in = $_POST['address_in'] ?? '';
$address_out = $_POST['address_out'] ?? '';


/* Force exact MySQL DATETIME format */
$datetime_in  = $date . ' ' . $time_in . ':00';
$datetime_out = $date . ' ' . $time_out . ':00';

$status = 'approved';

/* Update report status */
$stmt1 = $conn->prepare("UPDATE attendance_reports SET status=? WHERE id=?");
$stmt1->bind_param("si", $status, $report_id);
$stmt1->execute();

/* Build datetime values only if provided */
$datetime_in = null;
$datetime_out = null;

if (!empty($time_in)) {
    $datetime_in = $date . ' ' . $time_in . ':00';
}

if (!empty($time_out)) {
    $datetime_out = $date . ' ' . $time_out . ':00';
}

/* Update attendance conditionally */
$stmt2 = $conn->prepare("
UPDATE attendance 
SET 
    time_in = COALESCE(?, time_in),
    time_out = COALESCE(?, time_out),
    address_in = COALESCE(?, address_in),
    address_out = COALESCE(?, address_out)
WHERE id = ?
");

$stmt2->bind_param("ssssi", $datetime_in, $datetime_out, $address_in, $address_out, $attendance_id);
$stmt2->execute();


/* GET USER EMAIL */
$query = $conn->prepare("
SELECT u.email, CONCAT(u.firstname, ' ', u.middlename, ' ', u.lastname) AS fullname
FROM attendance a
JOIN users u ON a.user_id = u.id
WHERE a.id = ?
");
$query->bind_param("i", $attendance_id);
$query->execute();
$result = $query->get_result();
$user = $result->fetch_assoc();

$email = $user['email'];
$name = $user['fullname'];



/* SEND EMAIL FUNCTION */
function sendUserNotification($email, $subject, $body)
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
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;

        $mail->send();

    } catch (Exception $e) {
        // optional logging
    }
}


$timeInRow = '';
$timeOutRow = '';

if (!empty($time_in)) {
    $timeInRow = '
    <tr>
        <td style="padding:12px; border-bottom:1px solid #eee; color:#555;">
            <strong>Time In</strong>
        </td>
        <td style="padding:12px; border-bottom:1px solid #eee; text-align:right; color:#222;">
            '.date("g:i A", strtotime($time_in)).'
        </td>
    </tr>';
}

if (!empty($time_out)) {
    $timeOutRow = '
    <tr>
        <td style="padding:12px; border-bottom:1px solid #eee; color:#555;">
            <strong>Time Out</strong>
        </td>
        <td style="padding:12px; border-bottom:1px solid #eee; text-align:right; color:#222;">
            '.date("g:i A", strtotime($time_out)).'
        </td>
    </tr>';
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
    <h2 style="margin:0 0 5px 0; font-size:20px; color:#222; text-align:center;">
       Report Approved
    </h2>

 <p>Hello <b>'.$name.'</b>,</p>

<p>Your attendance correction report has been <b style="color:green;">APPROVED</b> by the admin.</p>


    <!-- Info Card -->
    <table width="100%" cellpadding="0" cellspacing="0"
        style="border-collapse:collapse; font-size:14px;">

        <tr>
            <td style="padding:12px; border-bottom:1px solid #eee; color:#555;">
                <strong>Date</strong>
            </td>
            <td style="padding:12px; border-bottom:1px solid #eee; text-align:right; color:#222;">
              '.date("l, F j, Y", strtotime($date)).'
            </td>
        </tr>

      '.$timeInRow.'
'.$timeOutRow.'




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


sendUserNotification($email, "Attendance Report Approved", $emailBody);


echo "success";