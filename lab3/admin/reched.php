<?php

require_once './connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["find_appointment"])) {
    $patient_name = $_POST["patient_name"];
    $appointment_date = $_POST["appointment_date"];
    
    // Fetch appointments based on patient name and appointment date
    $sql = "SELECT id FROM appointments WHERE patient_name = '$patient_name' AND appointment_date = '$appointment_date'";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $appointment = $result->fetch_assoc();
        $appointment_id = $appointment['id'];
        // Redirect to update_appointment.php with the selected appointment ID
        header("Location: update_appointment.php?appointment_id=$appointment_id");
        exit();
    } else {
        echo "No appointments found for the given patient name and date.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reschedule Appointment</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
        }
        input {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
        }
        button {
            background-color: #ff4444;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #cc0000;
        }
    </style>
</head>
<body>
    <h2>Find The Patinet Schedule</h2>
    <form action="reched.php" method="post">
        <div class="form-group">
            <label for="patient_name">Patient Name:</label>
            <input type="text" id="patient_name" name="patient_name" required>
        </div>
        <div class="form-group">
            <label for="appointment_date">Appointment Date:</label>
            <input type="date" id="appointment_date" name="appointment_date" required>
        </div>
        <button type="submit" name="find_appointment">Find Appointment</button>
    </form>
</body>
</html>