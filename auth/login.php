<?php
session_start();
require_once "../config/db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role_id'] = $user['role_id'];

        if ($user['role_id'] == 1) {
            header("Location: ../dashboard/admin.php");
        } elseif ($user['role_id'] == 2) {
            header("Location: ../dashboard/faculty.php");
        } else {
            header("Location: ../dashboard/student.php");
        }
        exit();
    } else {
        $_SESSION['error'] = "Invalid email or password!";
    }
}
?>

<!DOCTYPE html>
<!DOCTYPE html>
<html lang="en" x-data="{show: false}">
<head>
  <meta charset="UTF-8">
  <title>Login - EduPlusAI</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script defer src="https://unpkg.com/alpinejs"></script>
</head>
<body class="flex items-center justify-center h-screen bg-gradient-to-r from-blue-500 to-purple-600">

  <div class="w-full max-w-md bg-white p-8 rounded-2xl shadow-2xl">
    <h2 class="text-3xl font-bold text-center text-gray-800 mb-6">Welcome Back 👋</h2>

    <form method="POST" class="space-y-4">
      <input type="email" name="email" placeholder="Email"
        class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-blue-400" required>

      <div class="relative">
        <input :type="show ? 'text' : 'password'" name="password" placeholder="Password"
          class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-blue-400" required>
        <button type="button" @click="show = !show"
          class="absolute right-3 top-3 text-gray-500 hover:text-gray-700">
          👁
        </button>
      </div>

      <button type="submit"
        class="w-full bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 transition">
        Login
      </button>
    </form>

    <p class="mt-4 text-center text-gray-600">
      Don’t have an account? <a href="register.php" class="text-blue-600 font-semibold">Register</a>
    </p>
  </div>

</body>
</html>
