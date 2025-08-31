<?php
session_start();
require_once "../../config/db.php";
include("../../includes/auth.php");
check_role('admin');

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $sql = "DELETE FROM classes WHERE id=$id";
    if (mysqli_query($conn, $sql)) {
        header("Location: manage.php?msg=deleted");
        exit;
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
