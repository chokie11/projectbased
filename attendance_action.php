<?php
// attendance_action.php
date_default_timezone_set('Asia/Manila');
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

session_start();
include 'db.php';

$user_id = $_SESSION['user_id'] ;
// $username = $_SESSION['username'] ?? 'Deejay Labatiao';

$image = $_POST['image'] ?? '';
$type = $_POST['type'] ?? '';
$latitude = $_POST['latitude'] ?? '';
$longitude = $_POST['longitude'] ?? '';
$manual_lat = $_POST['manual_latitude'] ?? null;
$manual_lng = $_POST['manual_longitude'] ?? null;

if (empty($image) || empty($type)) {
    die("Invalid Request");
}



$user = $conn->prepare("SELECT email, CONCAT(firstname, ' ', middlename, ' ', lastname) AS fullname FROM users WHERE id=?");
$user->bind_param("i", $user_id);
$user->execute();
$userResult = $user->get_result();
$userData = $userResult->fetch_assoc();

$userEmail = $userData['email'] ?? '';
$username = $userData['fullname'];
/* GET ADDRESS */
function getAddress($lat, $lon)
{

    $url = "https://nominatim.openstreetmap.org/reverse?format=json&lat=$lat&lon=$lon";

    $opts = [
        "http" => [
            "method" => "GET",
            "header" => "User-Agent: AttendanceSystem/1.0\r\n"
        ]
    ];

    $context = stream_context_create($opts);
    $response = file_get_contents($url, false, $context);

    if ($response) {
        $data = json_decode($response, true);
        return $data['display_name'] ?? 'Unknown Location';
    }

    return "Location Not Found";
}

$address = getAddress($latitude, $longitude);
$manual_address = null;

if (!empty($manual_lat) && !empty($manual_lng)) {
    $manual_address = getAddress($manual_lat, $manual_lng);
}
/* CHECK TODAY */
$check = $conn->prepare("
SELECT * FROM attendance
WHERE user_id=? AND DATE(created_at)=CURDATE()
LIMIT 1
");
$check->bind_param("i", $user_id);
$check->execute();
$res = $check->get_result();
$today = $res->fetch_assoc();

/* SAVE IMAGE */
$image = str_replace('data:image/png;base64,', '', $image);
$image = str_replace(' ', '+', $image);
$imageName = time() . '_' . $type . '.png';
file_put_contents('uploads/' . $imageName, base64_decode($image));





function sendAdminNotification($subject, $messageBody, $userEmail)
{
    $adminEmail = "2022308224@pampangastateu.edu.ph"; // change if needed

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'ithelpdesk.colorsteel@gmail.com';
        $mail->Password = 'ywdloatptrbpgctu'; // use App Password
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('ithelpdesk.colorsteel@gmail.com', 'Attendance System');

        $mail->addAddress($adminEmail);

       // USER
        if(!empty($userEmail)){
            $mail->addAddress($userEmail);
        }

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $messageBody;

        $mail->send();
    } catch (Exception $e) {
        // Optional: log error instead of stopping script
    }
}


if ($type == "in") {

    if ($today) {
        die("Already Timed In");
    }

    $stmt = $conn->prepare("
INSERT INTO attendance
(user_id, photo_in, time_in, latitude_in, longitude_in, manual_latitude_in, manual_longitude_in, address_in, manual_address_in)
VALUES (?, ?, NOW(), ?, ?, ?, ?, ?, ?)
");

$stmt->bind_param(
    "isddssss",
    $user_id,
    $imageName,
    $latitude,
    $longitude,
    $manual_lat,
    $manual_lng,
    $address,
    $manual_address
);

$stmt->execute();


//     $stmt = $conn->prepare("
// INSERT INTO attendance
// (user_id,photo_in,time_in,latitude_in,longitude_in, manual_latitude, manual_longitude, address_in)
// VALUES (?,?,NOW(),?,?,?, ?, ?)
// ");
//     $stmt->bind_param("issssss", $user_id, $imageName, $latitude, $longitude, $manual_lat, 
//     $manual_lng, $address);
//     $stmt->execute();

    // Send email to admin
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
        Employee Attendance
    </h2>

    <p style="margin:0 0 25px 0; font-size:14px; color:#777; text-align:center;">
        Time In Confirmation
    </p>

    <!-- Status Badge -->
    <div style="text-align:center; margin-bottom:25px;">
        <span style="
            display:inline-block;
            padding:8px 18px;
            background-color:#28a745;
            color:#ffffff;
            font-size:13px;
            border-radius:50px;
            font-weight:600;
            letter-spacing:0.5px;">
            TIME IN
        </span>
    </div>

    <!-- Info Card -->
    <table width="100%" cellpadding="0" cellspacing="0"
        style="border-collapse:collapse; font-size:14px;">

        <tr>
            <td style="padding:12px; border-bottom:1px solid #eee; color:#555;">
                <strong>Employee Name</strong>
            </td>
            <td style="padding:12px; border-bottom:1px solid #eee; text-align:right; color:#222;">
                '.$username.'
            </td>
        </tr>

        <tr>
            <td style="padding:12px; border-bottom:1px solid #eee; color:#555;">
                <strong>Employee ID</strong>
            </td>
            <td style="padding:12px; border-bottom:1px solid #eee; text-align:right; color:#222;">
                '.$user_id.'
            </td>
        </tr>

        <tr>
            <td style="padding:12px; border-bottom:1px solid #eee; color:#555;">
                <strong>Date & Time</strong>
            </td>
            <td style="padding:12px; border-bottom:1px solid #eee; text-align:right; color:#222;">
                '.date("F j, Y g:i a").'
            </td>
        </tr>

        <tr>
            <td style="padding:12px; color:#555;">
                <strong>Location</strong>
            </td>
            <td style="padding:12px; text-align:right; color:#222;">
                '.$address.'
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

    sendAdminNotification("Employee Time In", $emailBody, $userEmail);

    echo "Time In Successful";
    exit();
}

if ($type == "out") {

    if (!$today) {
        die("Time In first");
    }
    if (!empty($today['time_out'])) {
        die("Already Timed Out");
    }

    $stmt = $conn->prepare("
UPDATE attendance
SET photo_out=?,time_out=NOW(),
latitude_out=?,longitude_out=?, address_out=?,  manual_latitude_out = ?, manual_longitude_out = ?, manual_address_out = ?
WHERE id=?
");
    $stmt->bind_param("sssssssi", $imageName, $latitude, $longitude, $address,  $manual_lat,
    $manual_lng,
    $manual_address, $today['id']);
    $stmt->execute();

        // Send email to admin
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
        Employee Attendance
    </h2>

    <p style="margin:0 0 25px 0; font-size:14px; color:#777; text-align:center;">
        Time Out Confirmation
    </p>

    <!-- Status Badge -->
    <div style="text-align:center; margin-bottom:25px;">
        <span style="
            display:inline-block;
            padding:8px 18px;
            background-color:#dc3545;
            color:#ffffff;
            font-size:13px;
            border-radius:50px;
            font-weight:600;
            letter-spacing:0.5px;">
            TIME OUT
        </span>
    </div>

    <!-- Info Card -->
    <table width="100%" cellpadding="0" cellspacing="0"
        style="border-collapse:collapse; font-size:14px;">

        <tr>
            <td style="padding:12px; border-bottom:1px solid #eee; color:#555;">
                <strong>Employee Name</strong>
            </td>
            <td style="padding:12px; border-bottom:1px solid #eee; text-align:right; color:#222;">
                '.$username.'
            </td>
        </tr>

        <tr>
            <td style="padding:12px; border-bottom:1px solid #eee; color:#555;">
                <strong>Employee ID</strong>
            </td>
            <td style="padding:12px; border-bottom:1px solid #eee; text-align:right; color:#222;">
                '.$user_id.'
            </td>
        </tr>

        <tr>
            <td style="padding:12px; border-bottom:1px solid #eee; color:#555;">
                <strong>Date & Time</strong>
            </td>
            <td style="padding:12px; border-bottom:1px solid #eee; text-align:right; color:#222;">
                '.date("F j, Y g:i a").'
            </td>
        </tr>

        <tr>
            <td style="padding:12px; color:#555;">
                <strong>Location</strong>
            </td>
            <td style="padding:12px; text-align:right; color:#222;">
                '.$address.'
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

    sendAdminNotification("Employee Time Out", $emailBody, $userEmail);

    echo "Time Out Successful";
    exit();
}

echo "Invalid Action";
?>