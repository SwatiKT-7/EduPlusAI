<?php
session_start();
require_once "../../config/db.php";
include("../../includes/auth.php");
check_role('faculty');

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $faculty_id = $_SESSION['user_id'];

    // 1. Close the session
    $sql = "UPDATE sessions SET status='closed' 
            WHERE id=$id AND faculty_id=$faculty_id";
    if (mysqli_query($conn, $sql)) {
        
        // 2. Find the class linked to this session
        $classRes = mysqli_query($conn, "SELECT class_id FROM sessions WHERE id=$id");
        $classRow = mysqli_fetch_assoc($classRes);
        $class_id = $classRow['class_id'];

        // 3. Get all students enrolled in that course
        $courseRes = mysqli_query($conn, "SELECT course_id FROM classes WHERE id=$class_id");
        $courseRow = mysqli_fetch_assoc($courseRes);
        $course_id = $courseRow['course_id'];

        $students = mysqli_query($conn, "SELECT student_id FROM student_courses WHERE course_id=$course_id");

        // 4. For each student, check if attendance already marked
        while ($s = mysqli_fetch_assoc($students)) {
            $student_id = $s['student_id'];

            $check = mysqli_query($conn, "SELECT id FROM attendance_events 
                                          WHERE class_id=$class_id AND user_id=$student_id");

            if (mysqli_num_rows($check) == 0) {
                // Not marked → insert absent automatically
                $ins = "INSERT INTO attendance_events 
                        (class_id, user_id, status, method, anomaly_flag, anomaly_reason) 
                        VALUES ($class_id, $student_id, 'A', 'geo', 1, 'Auto-marked absent on session close')";
                mysqli_query($conn, $ins);
            }
        }

        header("Location: active.php?msg=closed");
        exit;
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>
