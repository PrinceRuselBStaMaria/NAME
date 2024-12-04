<?php session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Appointment Confirmation</title>
    <link rel="stylesheet" href="form.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="confirmation"> 
        <h1>Appointment Confirmation</h1>
        <div id="confirmationDetails"></div>
        <form id="confirmForm" action="save_appointment.php" method="POST">
            <input type="hidden" name="patientName" id="patientName">
            <input type="hidden" name="email" id="email">
            <input type="hidden" name="service" id="service">
            <input type="hidden" name="doctor" id="doctor">
            <input type="hidden" name="date" id="date">
            <input type="hidden" name="time" id="time">
            <input type="hidden" name="notes" id="notes">
            <button type="submit" name="submitContact" class="confirm-btn">Confirm Appointment</button>
        </form>
    </div>

    <script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle appointment data
    const appointmentData = JSON.parse(sessionStorage.getItem('appointmentData'));
    const confirmationDetails = document.getElementById('confirmationDetails');
    
    if (appointmentData) {
        // Display the details
        confirmationDetails.innerHTML = `
            <p><strong>Patient Name:</strong> ${appointmentData.patientName}</p>
            <p><strong>Email:</strong> ${appointmentData.email}</p>
            <p><strong>Service:</strong> ${appointmentData.service}</p>
            <p><strong>Doctor:</strong> ${appointmentData.doctor}</p>
            <p><strong>Date:</strong> ${appointmentData.date}</p>
            <p><strong>Time:</strong> ${appointmentData.time}</p>
            <p><strong>Notes:</strong> ${appointmentData.notes}</p>
        `;

        // Set form values
        document.getElementById('patientName').value = appointmentData.patientName;
        document.getElementById('email').value = appointmentData.email;
        document.getElementById('service').value = appointmentData.service;
        document.getElementById('doctor').value = appointmentData.doctor;
        document.getElementById('date').value = appointmentData.date;
        document.getElementById('time').value = appointmentData.time;
        document.getElementById('notes').value = appointmentData.notes;
    }

    // Handle session message
    const messageText = "<?php echo isset($_SESSION['status']) ? $_SESSION['status'] : ''; ?>";
    if (messageText !== '') {
        Swal.fire({
            title: "Thank You",
            text: messageText,
            icon: "success"
        }).then(() => {
            // Redirect to the front page (index.html) after user dismisses the alert
            window.location.href = '../index.html'; // Ensure correct path if index.html is in the root directory
        });
        <?php unset($_SESSION['status']); ?>
    } 
});

    </script> 
    
</body>
</html>

