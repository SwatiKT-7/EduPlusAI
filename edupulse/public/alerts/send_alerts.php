<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/mailer.php';

// Fetch unresolved alerts
$sql = "SELECT a.id, a.message, u.email, u.name 
        FROM alerts a
        JOIN users u ON a.user_id = u.id
        WHERE a.resolved_at IS NULL
        ORDER BY a.created_at DESC";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $mail = getMailer();
        if ($mail) {
            try {
                $mail->addAddress($row['email'], $row['name']);
                $mail->Subject = "EduPulse Alert Notification 🚨";
                $mail->Body    = "Dear {$row['name']},\n\n{$row['message']}\n\n- EduPulse System";

                $mail->send();
                echo "✅ Alert mail sent to {$row['email']}<br>";

                // Mark alert as resolved after sending
                $update = $conn->prepare("UPDATE alerts SET resolved_at=NOW() WHERE id=?");
                $update->bind_param("i", $row['id']);
                $update->execute();
            } catch (Exception $e) {
                echo "❌ Failed to send to {$row['email']}: {$mail->ErrorInfo}<br>";
            }
        }
    }
} else {
    echo "No pending alerts.";
}
