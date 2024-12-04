<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = ""; // Your MySQL root password
$dbname = "dental";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$appointment = null;
$update_success = false;

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["appointment_id"])) {
    $appointment_id = $_GET["appointment_id"];

    // Fetch the selected appointment details
    $sql = "SELECT * FROM appointments WHERE id = $appointment_id";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $appointment = $result->fetch_assoc();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["update"])) {
    $appointment_id = $_POST["appointment_id"];
    $appointment_date = $_POST["appointment_date"];
    $appointment_time = $_POST["appointment_time"];

    // Update the appointment details
    $sql = "UPDATE appointments SET appointment_date='$appointment_date', appointment_time='$appointment_time' WHERE id=$appointment_id";

    if ($conn->query($sql) === TRUE) {
        $update_success = true;
    } else {
        echo "Error updating appointment: " . $conn->error;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Appointment</title>
    <link rel="stylesheet" href="style.css">
    <!-- FullCalendar CSS -->
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />

    <style>
        body {
            display: flex;
            flex-direction: column;
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        h2.update_head {
            text-align: center;
            color: #333;
            margin-top: 20px;
        }

        form {
            min-width: 800px;
            max-width: auto;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        label.head {
            display: flex;
            font-weight: bold;
            justify-content: center;
            background-color: #007bff;
            padding: 10px;
            border-radius: 10px;
        }

        #calendar {
            margin-bottom: 20px;
        }

        button {
            display: block;
            width: 100% auto;
            padding: 10px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background-color: #0056b3;
        }

        p {
            text-align: center;
            color: green;
            font-size: 20px;

        }

        button[onclick] {
            margin-top: 20px;
            background-color: #28a745;
        }

        button[onclick]:hover {
            background-color: #218838;
        }

        .confirm_btn {
            margin-top: 20px;
            background-color: #007bff;
            width: 100%;
        }

        .head {
            margin-bottom: -35px;
        }
    </style>


</head>

<body>
    <h2 class="update_head">Update Appointment</h2>
    <?php if ($update_success): ?>
        <p>Appointment updated successfully.</p>
        <button onclick="window.location.href='admin_dashboard.php'" class="back_btn">Back to Menu</button>
    <?php elseif ($appointment): ?>
        <form action="update_appointment.php" method="post">
            <input type="hidden" name="appointment_id" value="<?php echo $appointment['id']; ?>">
            <label for="appointment_date" class="head">Please Pick New Date and Time:</label>
            <div id='calendar'></div>
            <input type="hidden" id="appointment_date" name="appointment_date" required>
            <input type="hidden" id="appointment_time" name="appointment_time" required>
            <button type="submit" name="update" class="confirm_btn">Confirm</button>
        </form>
    <?php elseif ($_SERVER["REQUEST_METHOD"] == "GET"): ?>
        <p>No appointment found with the given ID.</p>
    <?php endif; ?>

    <!-- FullCalendar JS -->
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
    <script>
        let calendar;

        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');

            calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                selectable: true,
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek'
                },
                businessHours: {
                    daysOfWeek: [1, 2, 3, 4, 5], // Monday - Friday
                    startTime: '07:00',
                    endTime: '17:00',
                },
                slotDuration: '01:00:00', // Set 1-hour slots
                selectConstraint: 'businessHours',
                selectMinTime: '07:00:00',
                selectMaxTime: '17:00:00',
                events: './get_appointment.php', // Load existing appointments
                eventOverlap: false, // Prevent overlapping appointments
                dateClick: function(info) {
                    if (calendar.view.type === 'dayGridMonth') {
                        calendar.changeView('timeGridWeek', info.dateStr);
                    }
                },
                selectAllow: function(selectInfo) {
                    if (calendar.view.type === 'timeGridWeek') {
                        // Calculate duration in hours
                        const duration = (selectInfo.end - selectInfo.start) / (1000 * 60 * 60);
                        // Only allow 1-hour selections
                        return duration === 1;
                    }
                    return false;
                },
                select: function(info) {
                    if (calendar.view.type === 'timeGridWeek') {
                        // Store selected date and time
                        const selectedDate = new Date(info.startStr);
                        selectedDate.setMinutes(selectedDate.getMinutes() - selectedDate.getTimezoneOffset());
                        const formattedDate = selectedDate.toISOString().split('T')[0];
                        const formattedTime = selectedDate.toTimeString().split(' ')[0];

                        document.getElementById('appointment_date').value = formattedDate;
                        document.getElementById('appointment_time').value = formattedTime;

                        // Add event to calendar to visually mark the selection
                        calendar.addEvent({
                            title: 'Selected Time',
                            start: info.start,
                            end: info.end,
                            color: '#378006'
                        });

                        calendar.unselect();
                    }
                }
            });

            calendar.render();
        });
    </script>
</body>

</html>