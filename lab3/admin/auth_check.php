<?php
// auth_check.php
session_start();

function checkAdminAuth() {
    if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_username'])) {
        header("Location: admin.html");
        exit();
    }
}
?>