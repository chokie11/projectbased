<?php
session_start();
include 'db.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';


$email = 'darkmasterlabatiao@gmail.com';

$otp = rand(100000, 999999);



$mail = new PHPMailer(true);

$mail->isSMTP();
$mail->Host = 'smtp.gmail.com';
$mail->SMTPAuth = true;
$mail->Username = 'ithelpdesk.colorsteel@gmail.com';
$mail->Password = 'ywdloatptrbpgctu'; // use App Password
$mail->SMTPSecure = 'tls';
$mail->Port = 587;

$mail->setFrom('ithelpdesk.colorsteel@gmail.com', 'Voting Portal');
$mail->addAddress($email, 'User');

$mail->isHTML(true);
$mail->Subject = 'Your new OTP';
$mail->Body = '
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>PhysicsHub OTP</title>
</head>
<body style="margin:0; padding:0; background-color:#f4f6f9; font-family:Arial, sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f6f9; padding:30px 0;">
<tr>
<td align="center">

<table width="500" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:12px; overflow:hidden; box-shadow:0 5px 15px rgba(0,0,0,0.05);">

<!-- HEADER -->
<tr>
<td align="center" style="background:linear-gradient(135deg,#28a745,#3be062); padding:30px; color:#ffffff;">
    
    <!-- LOGO -->
    <img src="https://chiportal.colorsteelholdings.com/images/CHIs.png" width="70" style="margin-bottom:10px;">
    
    <h2 style="margin:0; font-weight:600;">Colorsteel Holdings Inc.</h2>
    <p style="margin:5px 0 0; font-size:14px; opacity:0.9;">
        CHI is right mix of complementing subsidiaries give the holdings group a competitive edge.
    </p>
</td>
</tr>

<!-- CONTENT -->
<tr>
<td style="padding:40px 30px; text-align:center;">

    <h3 style="margin:0; color:#333;">Your Verification Code</h3>
    <p style="color:#666; font-size:14px;">
        Use the code below to continue. This code expires in 2 minutes.
    </p>

    <!-- OTP BOX -->
    <div style="
        margin:25px auto;
        padding:20px;
        width:200px;
        background:#f1f5ff;
        border-radius:10px;
        font-size:32px;
        letter-spacing:6px;
        font-weight:bold;
        color:#1e3c72;
    ">
        ' . $otp . '
    </div>

    <p style="font-size:13px; color:#999;">
        If you did not request this code, please ignore this email.
    </p>

</td>
</tr>

<!-- FOOTER -->
<tr>
<td align="center" style="padding:20px; font-size:12px; color:#999; background:#fafafa;">
    © ' . date("Y") . ' Colorsteel Holdings Inc.. All rights reserved.
</td>
</tr>

</table>

</td>
</tr>
</table>

</body>
</html>
';

$mail->send();

echo "resent";
?>