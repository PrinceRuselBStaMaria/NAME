<?php
require_once 'auth_check.php';
checkAdminAuth();

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
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Dental Appointments</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet">
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
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
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
            background-color: rgba(0, 0, 0, 0.05);
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
                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="stat-card">
                            <h6>Today's Appointments</h6>
                            <h3><?php echo $todayCount; ?></h3>
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

                <!-- Appointments Table -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Recent Appointments</h5>
                        <div>
                            <input type="text" class="form-control" id="searchInput" onkeyup="searchTable()" placeholder="Search appointments...">
                        </div>
                    </div>
                    <div class="card-body">
                        <table class="table" id="appointmentsTable">
                            <thead>
                                <tr>
                                    <th onclick="sortTable('id')">
                                        ID <i id="sort-id" class="bx bx-sort sort-icon"></i>
                                    </th>
                                    <th onclick="sortTable('patient')">
                                        Patient Name <i id="sort-patient" class="bx bx-sort sort-icon"></i>
                                    </th>
                                    <th onclick="sortTable('service')">
                                        Service <i id="sort-service" class="bx bx-sort sort-icon"></i>
                                    </th>
                                    <th onclick="sortTable('date')">
                                        Date <i id="sort-date" class="bx bx-sort sort-icon"></i>
                                    </th>
                                    <th onclick="sortTable('time')">
                                        Time <i id="sort-time" class="bx bx-sort sort-icon"></i>
                                    </th>
                                    <th onclick="sortTable('status')">
                                        Status <i id="sort-status" class="bx bx-sort sort-icon"></i>
                                    </th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql = "SELECT * FROM appointments ORDER BY appointment_date DESC, appointment_time DESC";
                                $result = $conn->query($sql);

                                if ($result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        $status = "Confirmed";
                                        $badgeClass = "bg-success";
                                        if (strtotime($row['appointment_date']) < strtotime('today')) {
                                            $status = "Completed";
                                            $badgeClass = "bg-info";
                                        }
                                ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                                            <td><?php echo htmlspecialchars($row['patient_name']); ?></td>
                                            <td><?php echo htmlspecialchars($row['service']); ?></td>
                                            <td><?php echo date('Y-m-d', strtotime($row['appointment_date'])); ?></td>
                                            <td><?php echo date('h:i A', strtotime($row['appointment_time'])); ?></td>
                                            <td><span class="badge <?php echo $badgeClass; ?>"><?php echo $status; ?></span></td>
                                            <td>
                                                <button class="btn btn-sm btn-primary" onclick="window.location.href='reched.php?id=<?php echo $row['id']; ?>'">Edit</button>
                                                <button class="btn btn-sm btn-danger" onclick="cancelAppointment(<?php echo $row['id']; ?>)">Cancel</button>
                                            </td>
                                        </tr>
                                <?php
                                    }
                                } else {
                                    echo "<tr><td colspan='7' class='text-center'>No appointments found</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
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
            if (confirm('Are you sure you want to cancel this appointment?')) {
                window.location.href = '../cancel.php?id=' + id;
            }
        }

        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('collapsed');
        });

        // Add active state to current page
        document.addEventListener('DOMContentLoaded', function() {
            const currentPage = window.location.pathname;
            const navLinks = document.querySelectorAll('.nav-link');

            navLinks.forEach(link => {
                if (link.getAttribute('href') === currentPage) {
                    link.classList.add('active');
                }
            });
        });

        // Add this JavaScript after your existing scripts
        function sortTable(column) {
            const table = document.getElementById('appointmentsTable');
            const tbody = table.getElementsByTagName('tbody')[0];
            const rows = Array.from(tbody.getElementsByTagName('tr'));
            const sortIcon = document.getElementById(`sort-${column}`);

            // Get current sort direction
            const isAscending = sortIcon.classList.contains('bx-up-arrow-alt');

            // Reset all sort icons
            document.querySelectorAll('.sort-icon').forEach(icon => {
                icon.classList.remove('bx-up-arrow-alt', 'bx-down-arrow-alt');
                icon.classList.add('bx-sort');
            });

            // Update clicked sort icon
            sortIcon.classList.remove('bx-sort');
            sortIcon.classList.add(isAscending ? 'bx-down-arrow-alt' : 'bx-up-arrow-alt');

            // Sort rows
            rows.sort((a, b) => {
                const aValue = a.cells[getColumnIndex(column)].textContent.trim();
                const bValue = b.cells[getColumnIndex(column)].textContent.trim();

                if (column === 'date') {
                    return isAscending ?
                        new Date(bValue) - new Date(aValue) :
                        new Date(aValue) - new Date(bValue);
                }

                return isAscending ?
                    bValue.localeCompare(aValue) :
                    aValue.localeCompare(bValue);
            });

            // Reorder table rows
            rows.forEach(row => tbody.appendChild(row));
        }

        function getColumnIndex(column) {
            const columnMap = {
                'id': 0,
                'patient': 1,
                'service': 2,
                'date': 3,
                'time': 4,
                'status': 5
            };
            return columnMap[column];
        }
    </script>
</body>

</html>

<?php
$conn->close();
?>