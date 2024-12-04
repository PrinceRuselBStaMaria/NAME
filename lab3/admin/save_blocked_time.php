<?php
// save_blocked_time.php
header('Content-Type: application/json');
require_once '../connection.php';

try {
    // Get and validate input
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['start']) || !isset($data['end'])) {
        throw new Exception('Missing required data');
    }

    // Parse dates and times
    $startDateTime = new DateTime($data['start']);
    $endDateTime = new DateTime($data['end']);
    
    $sql = "INSERT INTO blocked_times (
        start_date, 
        start_time, 
        end_date, 
        end_time
    ) VALUES (?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $startDate = $startDateTime->format('Y-m-d');
    $startTime = $startDateTime->format('H:i:s');
    $endDate = $endDateTime->format('Y-m-d');
    $endTime = $endDateTime->format('H:i:s');

    $stmt->bind_param('ssss', 
        $startDate,
        $startTime,
        $endDate,
        $endTime
    );
    
    $success = $stmt->execute();
    
    echo json_encode([
        'success' => $success,
        'id' => $success ? $conn->insert_id : null
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'error' => $e->getMessage()
    ]);
}

$conn->close();