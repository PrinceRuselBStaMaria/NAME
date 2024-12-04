<?php
session_start();
// Database connection
include '../connection.php';
// Fetch statistics
$today = date('Y-m-d');
$todayQuery = "SELECT COUNT(*) as today_count FROM appointments WHERE appointment_date = ?";
$stmt = $conn->prepare($todayQuery);
$stmt->bind_param("s", $today);
$stmt->execute();
$todayCount = $stmt->get_result()->fetch_assoc()['today_count'];

// Get pending appointments
$pendingQuery = "SELECT COUNT(*) as pending_count FROM appointments WHERE appointment_date >= ?";
$stmt = $conn->prepare($pendingQuery);
$stmt->bind_param("s", $today);
$stmt->execute();
$pendingCount = $stmt->get_result()->fetch_assoc()['pending_count'];

// Get completed and cancelled counts similarly
$completedQuery = "SELECT COUNT(*) as completed_count FROM appointments WHERE appointment_date < ?";
$cancelledQuery = "SELECT COUNT(*) as cancelled_count FROM appointments WHERE status = 'cancelled'";

// Update query to get only today's appointments
$sql = "SELECT * FROM appointments 
        WHERE DATE(appointment_date) = CURDATE() 
        ORDER BY appointment_time ASC";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Dental Appointments</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet">
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet'>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
    <style>
        .sidebar {
            height: 100vh;
            background: #2c3e50;
            color: white;
            position: fixed;
            width: 250px;
            transition: all 0.3s;
        }
        
        .sidebar.collapsed {
            width: 80px;
        }
        
        .sidebar.collapsed .nav-link span {
            display: none;
        }
        
        .sidebar.collapsed h4 {
            display: none;
        }
        
        .sidebar.collapsed hr {
            margin: 0.5rem 0;
        }
        
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .nav-link {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            transition: all 0.3s;
        }

        .nav-link i {
            margin-right: 0.75rem;
            font-size: 1.25rem;
        }

        .nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
        }

        .nav-link.active {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 4px;
        }
        .fc {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        .fc-event {
            cursor: pointer;
        }

        .fc-toolbar-title {
            font-size: 1.2em !important;
            font-weight: bold;
        }

        .fc-button-primary {
            background-color: #4e73df !important;
            border-color: #4e73df !important;
        }

        .fc-button-primary:hover {
            background-color: #2e59d9 !important;
            border-color: #2e59d9 !important;
        }
    </style>
</head>
<body>
<div class="d-flex">
        <!-- Sidebar -->
        <div class="sidebar p-4" id="sidebar">
            <div class="d-flex justify-content-between align-items-center">
                <h4>Admin Dashboard</h4>
                <button id="sidebarToggle" class="btn btn-link text-white">
                    <i class='bx bx-menu'></i>
                </button>
            </div>
            <hr>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link text-white" href="admin_dashboard.php">
                        <i class='bx bxs-dashboard'></i>
                        <span>Overview</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="appointments.php">
                        <i class='bx bxs-calendar'></i>
                        <span>Appointments</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white active" href="staff.php">
                        <i class='bx bxs-user'></i>
                        <span>Admin Users</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="doctors.php">
                        <i class='bx bxs-user-plus'></i>
                        <span>Doctors</span>
                    </a>
                </li>
                <li class="nav-item mt-5">
                    <a class="nav-link text-white" href="logout.php">
                        <i class='bx bx-log-out'></i>
                        <span>Logout</span>
                    </a>
                </li>
            </ul>
        </div>


        <!-- Main Content -->
        <div class="main-content">
            <div class="container-fluid">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Today's Schedule - <?php echo date('F d, Y'); ?></h2>
                    <div class="btn-group">
                        <button class="btn btn-outline-primary active" data-filter="all">All</button>
                        <button class="btn btn-outline-warning" data-filter="pending">Pending</button>
                        <button class="btn btn-outline-success" data-filter="confirmed">Confirmed</button>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6>Today's Total</h6>
                                    <h3><?php echo $todayCount; ?></h3>
                                </div>
                                <div class="stat-icon">
                                    <i class='bx bx-calendar-check fs-1 text-primary'></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <h6>Pending</h6>
                            <h3>12</h3>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <h6>Completed</h6>
                            <h3>45</h3>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <h6>Cancelled</h6>
                            <h3>3</h3>
                        </div>
                    </div>
                </div>

                <!-- Today's Appointments Table -->
                <div class="card">
                    <div class="card-header bg-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <i class='bx bx-time-five fs-4 me-2'></i>
                                <h5 class="mb-0">Today's Appointments</h5>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="appointmentsTable">
                                <thead>
                                    <tr>
                                        <th>Time</th>
                                        <th>Patient Name</th>
                                        <th>Service</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $result = $conn->query($sql);

                                    if ($result->num_rows > 0) {
                                        while($row = $result->fetch_assoc()) {
                                            $status = $row['status'] ?? "Pending";
                                            $badgeClass = match($status) {
                                                "Confirmed" => "bg-success",
                                                "Pending" => "bg-warning",
                                                "Completed" => "bg-info",
                                                default => "bg-secondary"
                                            };
                                            ?>
                                            <tr class="appointment-row" data-status="<?php echo strtolower($status); ?>">
                                                <td><?php echo date('h:i A', strtotime($row['appointment_time'])); ?></td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <i class='bx bxs-user-circle fs-4 me-2'></i>
                                                        <?php echo htmlspecialchars($row['patient_name']); ?>
                                                    </div>
                                                </td>
                                                <td><?php echo htmlspecialchars($row['service']); ?></td>
                                                <td>
                                                    <span class="badge <?php echo $badgeClass; ?>">
                                                        <?php echo $status; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-primary" 
                                                            onclick="editAppointment(<?php echo $row['id']; ?>)">
                                                        <i class='bx bxs-edit'></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-danger" 
                                                            onclick="cancelAppointment(<?php echo $row['id']; ?>)">
                                                        <i class='bx bxs-x-circle'></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php
                                        }
                                    } else {
                                        echo "<tr><td colspan='5' class='text-center'>No appointments today</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Appointments Calendar</h5>
                                <div id="calendar"></div>
                            </div>
                        </div>
                    </div>
                </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function searchTable() {
            // Your existing search function
        }

        function editAppointment(id) {
            window.location.href = 'edit_appointment.php?id=' + id;
        }

        function cancelAppointment(id) {
            if(confirm('Are you sure you want to cancel this appointment?')) {
                window.location.href = '../cancel.php?id=' + id;
            }
        }

        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('collapsed');
        });

        // Add active state to current page
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
            daysOfWeek: [1, 2, 3, 4, 5],
            startTime: '07:00',
            endTime: '17:00',
        },
        selectConstraint: 'businessHours',
        selectMinTime: '07:00:00',
        selectMaxTime: '17:00:00',
        eventSources: [
            {
                url: 'get_appointment_admin.php',
                color: '#378006'
            },
            {
                url: 'get_blocked_times.php',
                color: '#FF0000',
                rendering: 'background'
            }
        ],
        eventOverlap: false,
        dateClick: function(info) {
            if (calendar.view.type === 'dayGridMonth') {
                calendar.changeView('timeGridWeek', info.dateStr);
            }
        },
        select: function(info) {
            if (calendar.view.type === 'timeGridWeek') {
                const eventType = confirm('Create blocked time slot?') ? 'blocked' : 'appointment';
                
                const eventData = {
                    title: eventType === 'blocked' ? 'BLOCKED' : 'Available',
                    start: info.startStr,
                    end: info.endStr,
                    color: eventType === 'blocked' ? '#FF0000' : '#378006',
                    rendering: eventType === 'blocked' ? 'background' : 'auto'
                };

                fetch('save_blocked_time.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        start: info.startStr,
                        end: info.endStr,
                        type: eventType
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        calendar.addEvent(eventData);
                    } else {
                        alert('Error saving blocked time');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error saving blocked time');
                });
                
                calendar.unselect();
            }
        }
    });

    calendar.render();

    // Filter functionality
    const filterButtons = document.querySelectorAll('.btn-group .btn');
    if (filterButtons.length > 0) {
        filterButtons.forEach(button => {
            button.addEventListener('click', function() {
                const filter = this.dataset.filter;
                
                filterButtons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
                
                document.querySelectorAll('.appointment-row').forEach(row => {
                    row.style.display = (filter === 'all' || row.dataset.status === filter) ? '' : 'none';
                });
            });
        });
    }
});

        document.querySelectorAll('.btn-group .btn').forEach(button => {
            button.addEventListener('click', function() {
                const filter = this.dataset.filter;
                
                // Update active button
                document.querySelectorAll('.btn-group .btn').forEach(btn => 
                    btn.classList.remove('active'));
                this.classList.add('active');
                
                // Filter rows
                document.querySelectorAll('.appointment-row').forEach(row => {
                    if (filter === 'all' || row.dataset.status === filter) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        });
    </script>
</body>
</html>

<?php
$conn->close();
?>