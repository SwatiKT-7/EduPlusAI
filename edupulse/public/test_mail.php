<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/mailer.php';

// Example: fetch all students
$result = $conn->query("SELECT name, email FROM users WHERE role='student' AND email IS NOT NULL");

while ($row = $result->fetch_assoc()) {
    $mail = getMailer();
    if ($mail) {
        try {
            $mail->addAddress($row['email'], $row['name']);
            $mail->Subject = "Welcome to EduPulse!";
            $mail->Body    = "Hello {$row['name']},\n\nThis is a test mail from EduPulse system.";
            $mail->send();
            echo "Mail sent to {$row['email']}<br>";
        } catch (Exception $e) {
            echo "Mail could not be sent to {$row['email']}. Error: {$mail->ErrorInfo}<br>";
        }
    }
}
