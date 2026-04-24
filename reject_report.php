<?php
date_default_timezone_set('Asia/Manila');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

include 'db.php';

$report_id = $_POST['report_id'];
$reason = $_POST['reason'];

if ($reason === 'others') {
    $reason = $_POST['custom_reason'];
}

$conn->query("UPDATE attendance_reports
              SET status='rejected',
                  reject_reason='$reason'
              WHERE id=$report_id");




/* GET USER EMAIL */
$query = $conn->prepare("
SELECT u.email, CONCAT(u.firstname, ' ', u.middlename, ' ', u.lastname) AS fullname, a.created_at
FROM attendance_reports a
JOIN users u ON a.user_id = u.id
WHERE a.id = ?
");
$query->bind_param("i", $report_id);
$query->execute();
$result = $query->get_result();
$user = $result->fetch_assoc();

$email = $user['email'];
$name = $user['fullname'];

$date = $user['created_at'];


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
       Report Rejected
    </h2>

 <p>Hello <b>'.$name.'</b>,</p>

<p>Your attendance correction report has been <b style="color:red;">REJECTED</b> by the admin.</p>


    <!-- Info Card -->
    <table width="100%" cellpadding="0" cellspacing="0"
        style="border-collapse:collapse; font-size:14px;">

        <tr>
            <td style="padding:12px; border-bottom:1px solid #eee; color:#555;">
                <strong>Reported Date</strong>
            </td>
            <td style="padding:12px; border-bottom:1px solid #eee; text-align:right; color:#222;">
              '.date("l, F j, Y", strtotime($date)).'
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


sendUserNotification($email, "Attendance Report Rejected", $emailBody);


echo "success";