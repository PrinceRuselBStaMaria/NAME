<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
ini_set('session.gc_maxlifetime', 1800); // 30 minutes
session_set_cookie_params(1800);

// Add CSRF protection
if (!isset($_SESSION['token'])) {
    $_SESSION['token'] = bin2hex(random_bytes(32));
}

// Check if connection.php exists and is accessible
$connectionFile = __DIR__ . '/../connection.php';
if (!file_exists($connectionFile)) {
    die("Connection file not found at: " . $connectionFile);
}

require_once $connectionFile;

// Verify database connection
if (!isset($conn) || $conn->connect_error) {
    die("Database connection failed: " . ($conn->connect_error ?? "Connection variable not set"));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            throw new Exception('Please fill all fields');
        }

        // Updated query for admin_users table
        $sql = "SELECT id, username, password FROM admin_users WHERE username = ?";
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            throw new Exception('Database prepare failed: ' . $conn->error);
        }
        
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_username'] = $user['username'];
                header("Location: admin_dashboard.php");
                exit();
            } else {
                throw new Exception('Invalid password');
            }
        } else {
            throw new Exception('User not found');
        }

    } catch (Exception $e) {
        echo "<script>
            alert('" . htmlspecialchars($e->getMessage()) . "');
            window.location.href='admin.html';
        </script>";
        exit();
    } finally {
        if (isset($stmt)) {
            $stmt->close();
        }
        if (isset($conn)) {
            $conn->close();
        }
    }
}
?>