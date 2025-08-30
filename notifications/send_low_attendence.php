<?php
require '../vendor/autoload.php';
require_once "../config/db.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Step 1: Fetch students with <75% attendance
$sql = "
    SELECT u.id, u.name, u.email, s.name as subject,
           COUNT(a.id) as total, SUM(a.status='Present') as present
    FROM users u
    JOIN attendance a ON u.id = a.student_id
    JOIN sessions ss ON a.session_id = ss.id
    JOIN subjects s ON ss.subject_id = s.id
    WHERE u.role_id = 3
    GROUP BY u.id, s.id
    HAVING (SUM(a.status='Present') / COUNT(a.id)) * 100 < 75
";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    die("✅ No low-attendance students found.");
}

// Step 2: Setup PHPMailer
$mail = new PHPMailer(true);

try {
    // SMTP settings
    $mail->isSMTP();
    $mail->Host = "smtp.gmail.com"; // Change if using another SMTP
    $mail->SMTPAuth = true;
    $mail->Username = "your_email@gmail.com";  // 🔑 your SMTP email
    $mail->Password = "your_app_password";     // 🔑 your app password (NOT raw password)
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom("your_email@gmail.com", "EduPlusAI Notifications");

    // Step 3: Send emails
    while ($row = $result->fetch_assoc()) {
        $percent = $row['total'] > 0 ? round(($row['present'] / $row['total']) * 100, 2) : 0;

        $mail->clearAddresses();
        $mail->addAddress($row['email'], $row['name']);

        $mail->isHTML(true);
        $mail->Subject = "⚠️ Low Attendance Alert - {$row['subject']}";
        $mail->Body = "
            <p>Dear <b>{$row['name']}</b>,</p>
            <p>Your attendance in <b>{$row['subject']}</b> is <b>{$percent}%</b>, which is below the required threshold (75%).</p>
            <p>Please make sure to attend upcoming classes to avoid shortage.</p>
            <br>
            <p>Regards,<br>EduPlusAI Team</p>
        ";

        $mail->send();
        echo "📧 Sent to {$row['name']} ({$row['email']}) - {$percent}%<br>";
    }

} catch (Exception $e) {
    echo "❌ Email could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
