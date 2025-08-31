<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

function check_login() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../login.php");
        exit;
    }
}

function check_role($role) {
    if (!isset($_SESSION['role']) || $_SESSION['role'] != $role) {
        header("Location: ../dashboard.php");
        exit;
    }
}
?>
