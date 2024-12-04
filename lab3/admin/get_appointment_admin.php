<?php
header('Content-Type: application/json');
require_once '../connection.php';

// Get regular appointments
$appointments_sql = "SELECT id, patient_name, service, appointment_date, appointment_time FROM appointments";
$appointments_result = $conn->query($appointments_sql);

// Get blocked times
$blocked_sql = "SELECT id, start_date, start_time, end_date, end_time FROM blocked_times";
$blocked_result = $conn->query($blocked_sql);

$events = array();

// Add regular appointments
if ($appointments_result->num_rows > 0) {
    while($row = $appointments_result->fetch_assoc()) {
        $start = $row['appointment_date'] . 'T' . $row['appointment_time'];
        $end = date('Y-m-d\TH:i:s', strtotime($start . '+1 hour'));
        
        $events[] = array(
            'id' => 'apt_' . $row['id'],
            'title' => $row['patient_name'] . ' - ' . $row['service'],
            'start' => $start,
            'end' => $end,
            'color' => '#378006'
        );
    }
}

// Add blocked times
if ($blocked_result->num_rows > 0) {
    while($row = $blocked_result->fetch_assoc()) {
        $events[] = array(
            'id' => 'block_' . $row['id'],
            'title' => 'BLOCKED',
            'start' => $row['start_date'] . 'T' . $row['start_time'],
            'end' => $row['end_date'] . 'T' . $row['end_time'],
            'color' => '#FF0000'
        );
    }
}

echo json_encode($events);
$conn->close();
?>