<?php
session_start();
require_once "../config/db.php";

$message = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email='$email' LIMIT 1";
    $result = mysqli_query($conn, $sql);

    if ($row = mysqli_fetch_assoc($result)) {
        if ($row['password_hash'] == md5($password)) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['role']    = $row['role'];
            $_SESSION['name']    = $row['name'];
            $_SESSION['dept_id'] = $row['dept_id'];

            // ✅ Redirect based on role
            if ($row['role'] === 'admin') {
                header("Location: admin/dashboard.php");
            } elseif ($row['role'] === 'faculty') {
                header("Location: faculty/dashboard.php");
            } else {
                header("Location: student/dashboard.php");
            }
            exit;
        } else {
            $message = "❌ Invalid password!";
        }
    } else {
        $message = "❌ No user found with that email.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login | EduPulse</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen flex flex-col items-center justify-center bg-gradient-to-tr from-indigo-500 via-purple-500 to-pink-500 px-4">

  <!-- Login Card -->
  <div class="w-full max-w-md bg-white/90 backdrop-blur-md shadow-2xl rounded-2xl p-8 relative">
    <h2 class="text-3xl font-bold text-center text-indigo-700">EduPulse Login</h2>
    <p class="text-center text-gray-600 mb-6">Sign in to your dashboard</p>

    <?php if($message): ?>
      <p class="text-red-600 text-center font-semibold mb-4"><?= $message ?></p>
    <?php endif; ?>

    <!-- Login Form -->
    <form method="POST" class="space-y-5">
      <div>
        <label class="block text-gray-700 font-medium mb-1">Email</label>
        <input type="email" name="email" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-400 shadow-sm" placeholder="you@example.com" required>
      </div>
      <div>
        <label class="block text-gray-700 font-medium mb-1">Password</label>
        <input type="password" name="password" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-400 shadow-sm" placeholder="••••••••" required>
      </div>
      <button type="submit" class="w-full bg-indigo-600 text-white py-3 rounded-lg shadow-lg font-semibold hover:bg-indigo-700 hover:scale-[1.02] transition">
        🔑 Login
      </button>
    </form>

    <p class="mt-5 text-center text-sm">Don’t have an account? 
      <a href="register.php" class="text-indigo-700 font-semibold hover:underline">Register</a>
    </p>
  </div>

  <!-- Demo Credentials Card -->
  <div class="mt-8 w-full max-w-3xl bg-white/90 backdrop-blur-md rounded-xl shadow-lg p-5">
    <h3 class="text-lg font-bold text-gray-800 mb-3">📌 Demo Credentials</h3>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
      <div class="bg-indigo-50 p-4 rounded-lg shadow-sm">
        <p class="font-semibold text-indigo-700">Admin</p>
        <p>Email: <span class="font-mono">admin@demo.com</span></p>
        <p>Pass: <span class="font-mono">12345</span></p>
      </div>
      <div class="bg-green-50 p-4 rounded-lg shadow-sm">
        <p class="font-semibold text-green-700">Faculty</p>
        <p>Email: <span class="font-mono">faculty@demo.com</span></p>
        <p>Pass: <span class="font-mono">12345</span></p>
      </div>
      <div class="bg-pink-50 p-4 rounded-lg shadow-sm">
        <p class="font-semibold text-pink-700">Student</p>
        <p>Email: <span class="font-mono">student@demo.com</span></p>
        <p>Pass: <span class="font-mono">12345</span></p>
      </div>
    </div>
  </div>

</body>
</html>
