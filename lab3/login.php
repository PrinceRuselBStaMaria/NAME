<?php
// login.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

try {
    require_once 'connection.php';

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'];

        // Verify database connection
        if (!isset($pdo)) {
            throw new Exception("Database connection not established");
        }

        $stmt = $pdo->prepare("SELECT id, first_name, last_name, password FROM users WHERE email = ?");
        if (!$stmt) {
            throw new Exception("Failed to prepare statement");
        }

        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        $response = array();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name'] = $user['last_name'];
            
            $response['success'] = true;
            $response['message'] = 'Login successful';
        } else {
            $response['success'] = false;
            $response['message'] = 'Invalid email or password';
        }

        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }
} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Database connection error'
    ]);
    exit();
} catch (Exception $e) {
    error_log("General Error: " . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred'
    ]);
    exit();
}
?>