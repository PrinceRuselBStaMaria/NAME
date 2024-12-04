<?php
// login_user.php
session_start();
require_once 'connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Sanitize inputs
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'];

        // Validate inputs
        if (empty($email) || empty($password)) {
            throw new Exception("All fields are required");
        }

        // Prepare SQL statement
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verify user and password
        if ($user && password_verify($password, $user['password'])) {
            // Set session variables
            header("Location: app.html");
            exit();
        } else {
            throw new Exception("Invalid email or password");
        }

    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        header("Location: login_user.php");
        exit();
    }
}
?>

<!-- Add this HTML before the form to display errors -->
<?php if (isset($_SESSION['error'])): ?>
    <div style="color: red; margin-bottom: 10px;">
        <?php 
            echo htmlspecialchars($_SESSION['error']);
            unset($_SESSION['error']);
        ?>
    </div>
<?php endif; ?>

