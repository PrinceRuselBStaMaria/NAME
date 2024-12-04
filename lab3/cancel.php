<?php
require_once 'connection.php';

// Handle both GET (from admin) and POST (from user form) requests
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['id'])) {
    // Admin cancellation by ID
    $appointmentId = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
    
    try {
        $deleteSql = "DELETE FROM appointments WHERE id = ?";
        $deleteStmt = $conn->prepare($deleteSql);
        $deleteStmt->bind_param("i", $appointmentId);
        
        if ($deleteStmt->execute()) {
            echo "<script>
                alert('Appointment cancelled successfully!');
                window.location.href = 'admin/admin_dashboard.php';
            </script>";
        } else {
            throw new Exception("Error cancelling appointment");
        }
        $deleteStmt->close();
        
    } catch (Exception $e) {
        echo "<script>
            alert('Error: " . $e->getMessage() . "');
            window.history.back();
        </script>";
    }
    
} elseif ($_SERVER["REQUEST_METHOD"] == "POST") {
    // User cancellation by name and date
    $patientName = filter_input(INPUT_POST, 'patientName', FILTER_SANITIZE_STRING);
    $appointmentDate = filter_input(INPUT_POST, 'appointmentDate', FILTER_SANITIZE_STRING);
    
    if (!$patientName || !$appointmentDate) {
        echo "<script>alert('Please fill all required fields!'); window.history.back();</script>";
        exit;
    }

    try {
        // Check if appointment exists
        $checkSql = "SELECT id FROM appointments WHERE patient_name = ? AND appointment_date = ?";
        $stmt = $conn->prepare($checkSql);
        $stmt->bind_param("ss", $patientName, $appointmentDate);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $appointmentId = $row['id'];
            
            $deleteSql = "DELETE FROM appointments WHERE id = ?";
            $deleteStmt = $conn->prepare($deleteSql);
            $deleteStmt->bind_param("i", $appointmentId);
            
            if ($deleteStmt->execute()) {
                echo "<script>
                    alert('Appointment cancelled successfully!');
                    window.location.href = 'cancel.html';
                </script>";
            } else {
                throw new Exception("Error cancelling appointment");
            }
            $deleteStmt->close();
        } else {
            echo "<script>
                alert('No appointment found with these details!');
                window.history.back();
            </script>";
        }
        
        $stmt->close();
    } catch (Exception $e) {
        echo "<script>
            alert('Error: " . $e->getMessage() . "');
            window.history.back();
        </script>";
    }
}

$conn->close();
?>