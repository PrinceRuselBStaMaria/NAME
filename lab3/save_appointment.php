<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// Define PHPMailer path and verify
$phpmailer_path = __DIR__ . '/../PHPMailer/src/';

// Check if files exist
if (!file_exists($phpmailer_path . 'Exception.php') ||
    !file_exists($phpmailer_path . 'PHPMailer.php') ||
    !file_exists($phpmailer_path . 'SMTP.php')) {
    die('PHPMailer files not found in: ' . $phpmailer_path);
}

// Include required files
require $phpmailer_path . 'Exception.php';
require $phpmailer_path . 'PHPMailer.php';
require $phpmailer_path . 'SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once 'connection.php';

try {
    $servername = "localhost";
    $username = "root";
    $password = ""; 
    $dbname = "dental";
    // Database connection
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Date format conversion
    $originalDate = $_POST['date'];
    $dateObject = DateTime::createFromFormat('m/d/Y', $originalDate);
    
    if ($dateObject === false) {
        throw new Exception("Invalid date format");
    }
    
    $formattedDate = $dateObject->format('Y-m-d');

    // Time format conversion
    $originalTime = $_POST['time'];
    $timeObject = DateTime::createFromFormat('g:i:s A', $originalTime);
    
    if ($timeObject === false) {
        throw new Exception("Invalid time format");
    }
    
    $formattedTime = $timeObject->format('H:i:s');

    // Database insertion
    $stmt = $conn->prepare("INSERT INTO appointments (patient_name, email, service, doctor, appointment_date, appointment_time, notes) 
                           VALUES (:patientName, :email, :service, :doctor, :date, :time, :notes)");

    $stmt->bindParam(':patientName', $_POST['patientName']);
    $stmt->bindParam(':email', $_POST['email']);
    $stmt->bindParam(':service', $_POST['service']);
    $stmt->bindParam(':doctor', $_POST['doctor']);
    $stmt->bindParam(':date', $formattedDate);
    $stmt->bindParam(':time', $formattedTime);
    $stmt->bindParam(':notes', $_POST['notes']);
    
    $stmt->execute();

    // Email sending
    $mail = new PHPMailer(true);
    
    // Server settings
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'mr.devegadaniel@gmail.com';
    $mail->Password = 'evbiyyjoznkhidns';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    // Recipients
    $mail->setFrom('mr.devegadaniel@gmail.com', 'Dental Haven Malolos');
    $mail->addAddress($_POST['email'], $_POST['patientName']);

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'New Appointment - Dental Haven Malolos Contact Form';
    $mail->Body = "
        <h3>Hello, Your Appointment Details:</h3>
        <h4>Patient Name: {$_POST ['patientName']}</h4>
        <h4>Email: {$_POST ['email']}</h4>
        <h4>Service: {$_POST ['service']}</h4>
        <h4>Doctor: {$_POST ['doctor']}</h4>
        <h4>Date: {$originalDate}</h4>
        <h4>Time: {$originalTime}</h4>
        <h4>Notes: {$_POST ['notes']}</h4>
    ";

    $mail->send();
    
    $_SESSION['status'] = "Thank You for booking with Dental Haven Malolos";
    header("Location: confirmation.php");
    exit(0);

} catch (Exception $e) {
    $_SESSION['status'] = "Error: " . $e->getMessage();
    header("Location: confirmation.php");
    exit(0);
}

$conn = null;
?>