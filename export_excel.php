<?php
include 'db.php';

/* CHANGE HEADERS TO CSV */
header("Content-Type: text/csv");
header("Content-Disposition: attachment; filename=attendance_export.csv");

/* OPEN OUTPUT STREAM */
$output = fopen("php://output", "w");

/* COLUMN HEADERS */
fputcsv($output, [
    "Employee",
    "Date",
    "Time In",
    "Break In",
    "Break Out",
    "Time Out",
    "Address In",
    "Address Out"
]);


$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';
$userFilter = $_GET['user'] ?? '';

$query = "
SELECT 
    CONCAT(u.firstname, ' ', u.middlename, ' ', u.lastname) AS fullname,
    a.id as attendance_id,
    a.created_at,
    a.time_in,
    a.time_out,
    a.address_in,
    a.address_out,
    b.break_in,
    b.break_out,
    r.status as report_status
FROM attendance a
JOIN users u ON a.user_id = u.id
LEFT JOIN breaks b ON b.attendance_id = a.id
LEFT JOIN attendance_reports r ON r.attendance_id = a.id
WHERE 1
";

/* SEARCH */
if ($search) {
    $search = $conn->real_escape_string($search);
    $query .= " AND CONCAT(u.firstname,' ',u.middlename,' ',u.lastname) LIKE '%$search%'";
}

/* DATE FILTER (FIXED) */
if ($start_date && $end_date) {
    $query .= " AND DATE(a.created_at) BETWEEN '$start_date' AND '$end_date'";
} elseif ($start_date) {
    $query .= " AND DATE(a.created_at) = '$start_date'";
} elseif ($end_date) {
    $query .= " AND DATE(a.created_at) = '$end_date'";
}  else {
    // ✅ DEFAULT: CURRENT MONTH
    $currentMonth = date('m');
    $currentYear = date('Y');

    $query .= " AND MONTH(a.created_at) = '$currentMonth' 
                AND YEAR(a.created_at) = '$currentYear'";
}

/* USER FILTER */
if ($userFilter) {
    $query .= " AND a.user_id = " . intval($userFilter);
}

/* STATUS FILTER */
if ($status) {
    if ($status === 'normal') {
        $query .= " AND (r.id IS NULL OR r.status IN ('approved','rejected'))";
    } elseif ($status === 'pending') {
        $query .= " AND r.status = 'pending'";
    }
}

$query .= " ORDER BY a.created_at DESC";

$result = $conn->query($query);

/* LOOP DATA */
// while ($row = $result->fetch_assoc()) {

//     $dateFormatted = date("F j, Y", strtotime($row['created_at']));

//     $timeIn = $row['time_in'] ? date("g:i A", strtotime($row['time_in'])) : '';
//     $timeOut = $row['time_out'] ? date("g:i A", strtotime($row['time_out'])) : '';
//     $breakIn = $row['break_in'] ? date("g:i A", strtotime($row['break_in'])) : '';
//     $breakOut = $row['break_out'] ? date("g:i A", strtotime($row['break_out'])) : '';

//     fputcsv($output, [
//         $row['fullname'],
//         $dateFormatted,
//         $timeIn,
//         $breakIn,
//         $breakOut,
//         $timeOut,
//         $row['address_in'],
//         $row['address_out']
//     ]);
// }

$grouped = [];

while ($row = $result->fetch_assoc()) {
    $id = $row['attendance_id'];

    if (!isset($grouped[$id])) {
        $grouped[$id] = [
            'fullname' => $row['fullname'],
            'created_at' => $row['created_at'],
            'time_in' => $row['time_in'],
            'time_out' => $row['time_out'],
            'address_in' => $row['address_in'],
            'address_out' => $row['address_out'],
            'breaks' => []
        ];
    }

    // collect breaks
    if ($row['break_in'] || $row['break_out']) {
        $grouped[$id]['breaks'][] = [
            'break_in' => $row['break_in'],
            'break_out' => $row['break_out']
        ];
    }
}
/* OUTPUT CSV */
foreach ($grouped as $data) {

    $dateFormatted = date("F j, Y", strtotime($data['created_at']));
    $timeIn = $data['time_in'] ? date("g:i A", strtotime($data['time_in'])) : '';
    $timeOut = $data['time_out'] ? date("g:i A", strtotime($data['time_out'])) : '';

    $addressIn = $data['address_in'] ?? '';
    $addressOut = $data['address_out'] ?? '';

    // NO BREAKS
    if (empty($data['breaks'])) {
        fputcsv($output, [
            $data['fullname'],
            $dateFormatted,
            $timeIn,
            '',
            '',
            $timeOut,
            $addressIn,
            $addressOut
        ]);
        continue;
    }

    foreach ($data['breaks'] as $index => $break) {

        $breakIn = $break['break_in'] ? date("g:i A", strtotime($break['break_in'])) : '';
        $breakOut = $break['break_out'] ? date("g:i A", strtotime($break['break_out'])) : '';

        if ($index === 0) {
            // FIRST ROW (full info)
            fputcsv($output, [
                $data['fullname'],
                $dateFormatted,
                $timeIn,
                $breakIn,
                $breakOut,
                $timeOut,
                $addressIn,
                $addressOut
            ]);
        } else {
            // NEXT ROWS (only breaks)
            fputcsv($output, [
                '',
                '',
                '',
                $breakIn,
                $breakOut,
                '',
                '', // address_in empty
                ''  // address_out empty
            ]);
        }
    }

}
?>