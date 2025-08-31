<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../../vendor/autoload.php';

function getMailer() {
    $mail = new PHPMailer(true);

    try {
        // SMTP settings
        $mail->isSMTP();
        $mail->Host = 'smtp.hostinger.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'info@allumnova.site';
        $mail->Password = 'Yuv@12345678';
         $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->setFrom('info@allumnova.site', 'EDUPLUS AI');
       
        

        return $mail;
    } catch (Exception $e) {
        error_log("Mailer Error: " . $mail->ErrorInfo);
        return null;
    }
}
