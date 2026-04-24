<?php

if (!isset($_GET['lat']) || !isset($_GET['lon'])) {
    echo json_encode(['error' => 'Missing coordinates']);
    exit;
}

$lat = $_GET['lat'];
$lon = $_GET['lon'];

$url = "https://nominatim.openstreetmap.org/reverse?format=json&lat=$lat&lon=$lon";

$options = [
    "http" => [
        "header" => "User-Agent: AttendanceSystem/1.0\r\n"
    ]
];

$context = stream_context_create($options);
$response = file_get_contents($url, false, $context);

echo $response;