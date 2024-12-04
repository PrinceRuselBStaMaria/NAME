<?php
// get_blocked_times.php
header('Content-Type: application/json');
require_once '../connection.php';

$sql = "SELECT * FROM blocked_times";
$result = $conn->query($sql);
$events = array();

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $events[] = array(
            'id' => 'block_' . $row['id'],
            'title' => 'BLOCKED',
            'start' => $row['start_date'] . 'T' . $row['start_time'],
            'end' => $row['end_date'] . 'T' . $row['end_time'],
            'color' => '#FF0000',
            'display' => 'background'
        );
    }
}
echo json_encode($events);
$conn->close();
?>