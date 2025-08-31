<?php
// ==============================================
// Daily Attendance Report Mailer
// Runs only at 5:00 PM IST
// ==============================================

date_default_timezone_set('Asia/Kolkata'); // ✅ Set timezone

$currentHour   = date("H"); // 24-hour format hour
$currentMinute = date("i"); // minute

// ✅ Only run mailing if time is exactly 17:00 (5:00 PM)
if ($currentHour != 3 || $currentMinute != 51) {
    echo "⏰ Not report time yet... Current time: " . date("H:i") . "\n";
    exit;
}

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/mailer.php';
require_once __DIR__ . '/../../config/pdf.php';

// ==============================================
// 1. Fetch all students with email
// ==============================================
$students = $conn->query("SELECT id, name, email FROM users WHERE role='student' AND email IS NOT NULL");

while ($student = $students->fetch_assoc()) {
    $studentId   = $student['id'];
    $studentName = $student['name'];
    $studentEmail = $student['email'];

    // ==============================================
    // 2. Fetch student’s enrolled courses
    // ==============================================
    $courses = $conn->query("
        SELECT c.id, c.name AS course_name
        FROM student_courses sc
        JOIN courses c ON sc.course_id = c.id
        WHERE sc.student_id = $studentId
    ");

    $courseData = [];
    while ($c = $courses->fetch_assoc()) {
        $courseId = $c['id'];

        // Total classes conducted for this course
        $total = $conn->query("SELECT COUNT(*) as total FROM classes WHERE course_id=$courseId")->fetch_assoc()['total'];

        // Present count
        $present = $conn->query("
            SELECT COUNT(*) as present FROM attendance_events 
            WHERE user_id=$studentId 
              AND class_id IN (SELECT id FROM classes WHERE course_id=$courseId) 
              AND status='P'
        ")->fetch_assoc()['present'];

        $absent     = $total - $present;
        $percentage = $total > 0 ? round(($present / $total) * 100, 2) : 0;

        $courseData[] = [
            "course_name"   => $c['course_name'],
            "total_classes" => $total,
            "present"       => $present,
            "absent"        => $absent,
            "percentage"    => $percentage
        ];
    }

    // ==============================================
    // 3. Generate PDF Report
    // ==============================================
    $pdfContent = generateAttendancePDF($studentName, $courseData);

    // ==============================================
    // 4. Send Email with PDF Attachment
    // ==============================================
    $mail = getMailer();
    try {
        $mail->addAddress($studentEmail, $studentName);
        $mail->Subject = "Daily Attendance Report - EduPulse";
        $mail->Body    = "Dear {$studentName},\n\nPlease find attached your course-wise attendance report as of today.\n\nIf any course shows <75%, it is highlighted in RED.\n\n- EduPulse System";
        $mail->addStringAttachment($pdfContent, "attendance_report.pdf");

        $mail->send();
        echo "✅ Report sent to {$studentEmail}\n";
    } catch (Exception $e) {
        echo "❌ Failed to send report to {$studentEmail} - {$mail->ErrorInfo}\n";
    }
}
