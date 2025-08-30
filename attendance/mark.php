<?php
session_start();
require_once "../config/db.php";

if (!isset($_GET['session_id']) || !isset($_SESSION['user_id'])) {
    die("Invalid request!");
}

$session_id = $_GET['session_id'];
$student_id = $_SESSION['user_id'];

// Check if already marked
$check = $conn->prepare("SELECT * FROM attendance WHERE session_id=? AND student_id=?");
$check->bind_param("ii", $session_id, $student_id);
$check->execute();
$res = $check->get_result();

if ($res->num_rows > 0) {
    echo "<h2 style='color:red;text-align:center;margin-top:50px;'>⚠️ Already Marked!</h2>";
    exit();
}

// Mark attendance
$stmt = $conn->prepare("INSERT INTO attendance (session_id, student_id, status) VALUES (?, ?, 'Present')");
$stmt->bind_param("ii", $session_id, $student_id);
$stmt->execute();

echo "<h2 style='color:green;text-align:center;margin-top:50px;'>✅ Attendance Marked Successfully!</h2>";
?>
