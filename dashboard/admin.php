<?php
session_start();
if ($_SESSION['role_id'] != 1) {
    header("Location: ../auth/login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en" x-data="{darkMode: false}">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script defer src="https://unpkg.com/alpinejs"></script>
</head>
<body :class="darkMode ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-900'" class="flex">

  <!-- Sidebar -->
  <?php include("../assets/components/sidebar.php"); ?>

  <!-- Main Content -->
  <div class="flex-1">
    <?php include("../assets/components/header.php"); ?>

    <main class="p-6">
      <h1 class="text-2xl font-bold mb-4">📊 Dashboard</h1>

      <div class="grid grid-cols-3 gap-6">
        <div class="p-6 bg-white rounded-xl shadow hover:shadow-lg transition">
          <h2 class="text-xl font-semibold">Total Students</h2>
          <p class="text-3xl mt-2">120</p>
        </div>

        <div class="p-6 bg-white rounded-xl shadow hover:shadow-lg transition">
          <h2 class="text-xl font-semibold">Faculty</h2>
          <p class="text-3xl mt-2">15</p>
        </div>

        <div class="p-6 bg-white rounded-xl shadow hover:shadow-lg transition">
          <h2 class="text-xl font-semibold">Attendance Today</h2>
          <p class="text-3xl mt-2">92%</p>
        </div>
      </div>
    </main>
  </div>
</body>
</html>
