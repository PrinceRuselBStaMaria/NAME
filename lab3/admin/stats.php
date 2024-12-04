<?php
// stats.php
include '../connection.php';

// Get today's appointments
$today = date('Y-m-d');
$todayQuery = "SELECT COUNT(*) as today_count FROM appointments WHERE appointment_date = ?";
$stmt = $conn->prepare($todayQuery);
$stmt->bind_param("s", $today);
$stmt->execute();
$todayResult = $stmt->get_result();
$todayCount = $todayResult->fetch_assoc()['today_count'];

// Get pending appointments
$pendingQuery = "SELECT COUNT(*) as pending_count FROM appointments WHERE appointment_date >= ?";
$stmt = $conn->prepare($pendingQuery);
$stmt->bind_param("s", $today);
$stmt->execute();
$pendingResult = $stmt->get_result();
$pendingCount = $pendingResult->fetch_assoc()['pending_count'];

// Get completed appointments
$completedQuery = "SELECT COUNT(*) as completed_count FROM appointments WHERE appointment_date < ?";
$stmt = $conn->prepare($completedQuery);
$stmt->bind_param("s", $today);
$stmt->execute();
$completedResult = $stmt->get_result();
$completedCount = $completedResult->fetch_assoc()['completed_count'];

// Get cancelled appointments
$cancelledQuery = "SELECT COUNT(*) as cancelled_count FROM appointments WHERE status = 'cancelled'";
$result = $conn->query($cancelledQuery);
$cancelledCount = $result->fetch_assoc()['cancelled_count'];

$conn->close();
?>