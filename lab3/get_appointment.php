<?php
header('Content-Type: application/json');
require_once 'connection.php';

$sql = "SELECT id, doctor, service, appointment_date, appointment_time FROM appointments";
$result = $conn->query($sql);

$events = array();

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $start = $row['appointment_date'] . 'T' . $row['appointment_time'];
        $end = date('Y-m-d\TH:i:s', strtotime($start . '+1 hour'));
        
        // Updated title to show doctor and service
        $events[] = array(
            'id' => $row['id'],
            'title' => $row['doctor'] . ' - ' . $row['service'],
            'start' => $start,
            'end' => $end,
            'color' => '#378006'
        );
    }
}

echo json_encode($events);
$conn->close();
?>