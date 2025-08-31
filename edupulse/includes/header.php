<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>EduPulse</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans">
  <nav class="bg-blue-600 text-white p-4 shadow-lg flex justify-between">
    <div class="text-lg font-bold">EduPulse</div>
    <div>
      <?php if(isset($_SESSION['user_id'])): ?>
        <a href="dashboard.php" class="px-3">Dashboard</a>
        <a href="logout.php" class="px-3">Logout</a>
      <?php else: ?>
        <a href="login.php" class="px-3">Login</a>
      <?php endif; ?>
    </div>
  </nav>
  <div class="p-6">
