<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Remove session_start() since it's called in auth_check.php
require_once 'auth_check.php';
include '../connection.php';

try {
    $today = date('Y-m-d');

    // Modified query without status column
    $doctorQuery = "SELECT 
        doctor,
        COUNT(*) as total_appointments,
        COUNT(CASE WHEN DATE(appointment_date) < CURDATE() THEN 1 END) as completed,
        COUNT(CASE WHEN DATE(appointment_date) >= CURDATE() THEN 1 END) as confirmed,
        0 as cancelled  -- Placeholder until status column is added
    FROM appointments 
    WHERE DATE(appointment_date) = ?
    GROUP BY doctor";

    if (!($stmt = $conn->prepare($doctorQuery))) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    if (!$stmt->bind_param("s", $today)) {
        throw new Exception("Binding parameters failed: " . $stmt->error);
    }

    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    $doctorStats = $stmt->get_result();

    // Update the status badges display
    $badgeClass = [
        'Confirmed' => 'bg-success',
        'Completed' => 'bg-info'
    ];

} catch (Exception $e) {
    error_log("Error in doctors.php: " . $e->getMessage());
    die("Error: " . $e->getMessage());
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Schedules - Dental Clinic</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet">
    <!-- Include your existing CSS -->
    <style>
        /* Include your existing styles */
        .doctor-section {
            margin-bottom: 2rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .doctor-header {
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 10px 10px 0 0;
        }
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

        th {
            cursor: pointer;
            user-select: none;
        }

        th:hover {
            background-color: rgba(0,0,0,0.05);
        }

        .sort-icon {
            margin-left: 5px;
            font-size: 0.9em;
        }

        .bx-up-arrow-alt {
            color: #28a745;
        }

        .bx-down-arrow-alt {
            color: #dc3545;
        }
    </style>
</head>
<body>
<div class="d-flex">
    <!-- Replace the PHP include with this HTML sidebar -->
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
                <a class="nav-link text-white" href="staff.php">
                    <i class='bx bxs-user'></i>
                    <span>Admin Users</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white active" href="doctors.php">
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

    <div class="main-content">
        <div class="container-fluid">
            <h2 class="mb-4">Doctor Schedules - <?php echo date('F d, Y'); ?></h2>

            <?php
            if ($doctorStats->num_rows > 0) {
                while($doctorRow = $doctorStats->fetch_assoc()) {
                    // Get appointments for this doctor
                    $appointmentsQuery = "SELECT * FROM appointments 
                                        WHERE doctor = ? AND appointment_date = ? 
                                        ORDER BY appointment_time ASC";
                    $stmt = $conn->prepare($appointmentsQuery);
                    $stmt->bind_param("ss", $doctorRow['doctor'], $today);
                    $stmt->execute();
                    $appointments = $stmt->get_result();
                    ?>
                    
                    <div class="doctor-section mb-4">
                        <div class="doctor-header d-flex justify-content-between align-items-center">
                            <div>
                                <h4><?php echo htmlspecialchars($doctorRow['doctor']); ?></h4>
                                <div class="text-muted">
                                    Total: <?php echo $doctorRow['total_appointments']; ?> appointments today
                                </div>
                            </div>
                            <!-- Update the stats display -->
                            <div class="d-flex gap-2">
                                <span class="badge bg-success">Confirmed: <?php echo $doctorRow['confirmed']; ?></span>
                                <span class="badge bg-info">Completed: <?php echo $doctorRow['completed']; ?></span>
                                <span class="badge bg-danger">Cancelled: <?php echo $doctorRow['cancelled']; ?></span>
                            </div>
                        </div>
                        
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
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
                                        if ($appointments->num_rows > 0) {
                                            while($row = $appointments->fetch_assoc()) {
                                                $status = $row['status'] ?? "Pending";
                                                $badgeClass = $badgeClass[$status] ?? "bg-secondary";
                                                ?>
                                                <tr>
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
                                            echo "<tr><td colspan='5' class='text-center'>No appointments for today</td></tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo "<div class='alert alert-info'>No doctors have appointments scheduled for today.</div>";
            }
            ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function editAppointment(id) {
        window.location.href = 'edit_appointment.php?id=' + id;
    }

    function cancelAppointment(id) {
        if(confirm('Are you sure you want to cancel this appointment?')) {
            window.location.href = '../cancel.php?id=' + id;
        }
    }
</script>

<!-- Add this JavaScript before closing body tag -->
<script>
document.getElementById('sidebarToggle').addEventListener('click', function() {
    document.getElementById('sidebar').classList.toggle('collapsed');
    const mainContent = document.querySelector('.main-content');
    if (document.getElementById('sidebar').classList.contains('collapsed')) {
        mainContent.style.marginLeft = '80px';
    } else {
        mainContent.style.marginLeft = '250px';
    }
});

// Add active state to current page
document.addEventListener('DOMContentLoaded', function() {
    const currentPage = window.location.pathname.split('/').pop();
    const navLinks = document.querySelectorAll('.nav-link');
    
    navLinks.forEach(link => {
        if(link.getAttribute('href') === currentPage) {
            link.classList.add('active');
        } else {
            link.classList.remove('active');
        }
    });
});
</script>
</body>
</html>

<?php
$conn->close();
?>