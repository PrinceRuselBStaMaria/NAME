<?php
// register.php
session_start();
require_once 'connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate input
        if (!isset($_POST['firstName'], $_POST['lastName'], $_POST['email'], 
                   $_POST['phone'], $_POST['password'], $_POST['confirmPassword'], 
                   $_POST['privacyConsent'])) {
            throw new Exception('All fields are required');
        }

        if ($_POST['password'] !== $_POST['confirmPassword']) {
            throw new Exception('Passwords do not match');
        }

        // Hash password
        $hashedPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);

        // Prepare SQL
        $sql = "INSERT INTO users (first_name, last_name, email, phone, password) 
                VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", 
            $_POST['firstName'],
            $_POST['lastName'],
            $_POST['email'],
            $_POST['phone'],
            $hashedPassword
        );

        // Execute and check
        if ($stmt->execute()) {
            $_SESSION['message'] = "Registration successful!";
            header("Location: login.html");
            exit();
        } else {
            throw new Exception("Error registering user");
        }

    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        header("Location: registration.html");
        exit();
    }
}
?>