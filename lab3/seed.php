<?php
include 'connection.php';

// Sample data arrays
$patientNames = [
    'John Smith', 'Maria Garcia', 'David Chen', 
    'Sarah Johnson', 'Michael Brown', 'Emma Davis',
    'James Wilson', 'Sofia Rodriguez', 'William Lee',
    'Olivia Taylor'
];

$services = [
    'Dental Cleaning', 'Root Canal', 'Tooth Extraction',
    'Dental Filling', 'Teeth Whitening'
];

$doctors = [
    'Dr. Anderson', 'Dr. Martinez', 'Dr. Thompson'
];

$emails = [
    'john@email.com', 'maria@email.com', 'david@email.com',
    'sarah@email.com', 'michael@email.com', 'emma@email.com',
    'james@email.com', 'sofia@email.com', 'william@email.com',
    'olivia@email.com'
];

// Get this month's weekdays
$month = date('Y-m');
$start = $month . '-01';
$end = date('Y-m-t', strtotime($start));
$appointments = [];

// Generate appointments
$sql = "INSERT INTO appointments (patient_name, email, service, doctor, appointment_date, appointment_time, notes) 
        VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

// Clear existing appointments for this month
$conn->query("DELETE FROM appointments WHERE appointment_date BETWEEN '$start' AND '$end'");

// Generate 10 appointments
for ($i = 0; $i < 10; $i++) {
    do {
        // Get random weekday
        $date = date('Y-m-d', strtotime("+$i weekday", strtotime($start)));
        $hour = rand(9, 16); // 9 AM to 4 PM
        $time = sprintf("%02d:00:00", $hour);
        
        // Check if slot is available
        $key = $date . $time;
    } while (isset($appointments[$key]) && $date <= $end);
    
    $appointments[$key] = true;
    
    $patient_name = $patientNames[$i];
    $email = $emails[$i];
    $service = $services[array_rand($services)];
    $doctor = $doctors[array_rand($doctors)];
    $notes = "Regular appointment for " . $service;
    
    $stmt->bind_param("sssssss", 
        $patient_name,
        $email,
        $service,
        $doctor,
        $date,
        $time,
        $notes
    );
    
    $stmt->execute();
    
    echo "Added appointment: $patient_name on $date at $time\n";
}

echo "Seeding completed successfully!\n";
$conn->close();
?>