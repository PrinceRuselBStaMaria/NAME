<?php
require_once 'auth_check.php';
include '../connection.php';

// Handle admin operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_admin':
                $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO admin_users (username, password) VALUES (?, ?)");
                $stmt->bind_param("ss", $_POST['username'], $hashed_password);
                $stmt->execute();
                break;
            case 'delete_admin':
                if ($_POST['id'] != $_SESSION['admin_id']) {
                    $stmt = $conn->prepare("DELETE FROM admin_users WHERE id=?");
                    $stmt->bind_param("i", $_POST['id']);
                    $stmt->execute();
                }
                break;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Management - Dental Clinic</title>
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
        
        .sidebar.collapsed { width: 80px; }
        .sidebar.collapsed .nav-link span { display: none; }
        .sidebar.collapsed h4 { display: none; }
        .sidebar.collapsed hr { margin: 0.5rem 0; }
        
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
                    <h2>Admin Users Management</h2>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAdminModal">
                        <i class='bx bx-plus'></i> Add Admin
                    </button>
                </div>

                <!-- Admin Users Table -->
                <div class="card">
                    <div class="card-body">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Username</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $result = $conn->query("SELECT id, username FROM admin_users");
                                while($row = $result->fetch_assoc()) {
                                    ?>
                                    <tr>
                                        <td><?php echo $row['id']; ?></td>
                                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                                        <td>
                                            <?php if ($row['id'] != $_SESSION['admin_id']) { ?>
                                            <button class="btn btn-sm btn-danger" onclick="deleteAdmin(<?php echo $row['id']; ?>)">
                                                <i class='bx bx-trash'></i>
                                            </button>
                                            <?php } ?>
                                        </td>
                                    </tr>
                                    <?php
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Admin Modal -->
    <div class="modal fade" id="addAdminModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Admin</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_admin">
                        <div class="mb-3">
                            <label>Username</label>
                            <input type="text" name="username" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Add Admin</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('collapsed');
        });

        function deleteAdmin(id) {
            if(confirm('Are you sure you want to delete this admin user?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete_admin">
                    <input type="hidden" name="id" value="${id}">
                `;
                document.body.append(form);
                form.submit();
            }
        }
    </script>
</body>
</html>