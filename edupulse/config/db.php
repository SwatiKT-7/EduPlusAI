<?php
$host = "localhost";
$user = "root";   // change if needed
$pass = "";       // change if needed
$db   = "edupulse";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Database Connection Failed: " . mysqli_connect_error());
}
?>
