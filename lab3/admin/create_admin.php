<?php
include '../connection.php';

$username = "admin";
$password = password_hash("!password", PASSWORD_DEFAULT);
$email = "admin@example.com";

$sql = "INSERT INTO admin_users (username, password, email) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $username, $password, $email);

if ($stmt->execute()) {
    echo "Admin user created successfully";
} else {
    echo "Error creating admin user: " . $conn->error;
}

$stmt->close();
$conn->close();
?>