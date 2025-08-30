<?php
session_start();
require '../vendor/autoload.php';
require_once "../config/db.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];

    // Check if user exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE email=? AND role_id=3"); // only students for now
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if ($user) {
        $otp = rand(100000, 999999);
        $_SESSION['otp'] = $otp;
        $_SESSION['otp_email'] = $email;
        $_SESSION['otp_expire'] = time() + 300; // 5 mins validity

        // Send OTP via Email
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = "smtp.gmail.com";
            $mail->SMTPAuth = true;
            $mail->Username = "your_email@gmail.com"; // change
            $mail->Password = "your_app_password";    // change
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom("your_email@gmail.com", "EduPlusAI OTP Login");
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = "Your EduPlusAI OTP Code";
            $mail->Body = "<h2>Your OTP is <b>$otp</b></h2><p>Valid for 5 minutes.</p>";

            $mail->send();

            header("Location: otp_verify.php");
            exit();
        } catch (Exception $e) {
            $error = "Mailer Error: " . $mail->ErrorInfo;
        }
    } else {
        $error = "❌ No student account found with this email.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>OTP Login - EduPlusAI</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex items-center justify-center h-screen bg-gradient-to-r from-blue-500 to-purple-600">
  <div class="bg-white p-8 rounded-xl shadow-lg w-full max-w-md">
    <h2 class="text-2xl font-bold mb-4 text-center">🔐 OTP Login</h2>
    <?php if (!empty($error)) echo "<p class='text-red-500 mb-3'>$error</p>"; ?>
    <form method="POST" class="space-y-4">
      <input type="email" name="email" placeholder="Enter your email" 
        required class="w-full p-3 border rounded-lg">
      <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 transition">
        Send OTP
      </button>
    </form>
  </div>
</body>
</html>
