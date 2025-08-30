<?php
session_start();
require_once "../config/db.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $otp = $_POST['otp'];

    if (isset($_SESSION['otp']) && $otp == $_SESSION['otp'] && time() < $_SESSION['otp_expire']) {
        // Valid OTP
        $email = $_SESSION['otp_email'];
        $stmt = $conn->prepare("SELECT * FROM users WHERE email=?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role_id'] = $user['role_id'];

            // Clear OTP session
            unset($_SESSION['otp'], $_SESSION['otp_email'], $_SESSION['otp_expire']);

            // Redirect based on role
            if ($user['role_id'] == 3) {
                header("Location: ../dashboard/student.php");
            } elseif ($user['role_id'] == 2) {
                header("Location: ../dashboard/faculty.php");
            } else {
                header("Location: ../dashboard/admin.php");
            }
            exit();
        }
    } else {
        $error = "❌ Invalid or expired OTP.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Verify OTP - EduPlusAI</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex items-center justify-center h-screen bg-gradient-to-r from-green-500 to-blue-600">
  <div class="bg-white p-8 rounded-xl shadow-lg w-full max-w-md">
    <h2 class="text-2xl font-bold mb-4 text-center">✅ Verify OTP</h2>
    <?php if (!empty($error)) echo "<p class='text-red-500 mb-3'>$error</p>"; ?>
    <form method="POST" class="space-y-4">
      <input type="text" name="otp" placeholder="Enter 6-digit OTP" maxlength="6" 
        required class="w-full p-3 border rounded-lg">
      <button type="submit" class="w-full bg-green-600 text-white py-3 rounded-lg hover:bg-green-700 transition">
        Verify
      </button>
    </form>
  </div>
</body>
</html>
