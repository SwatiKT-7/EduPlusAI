<?php
session_start();
require_once __DIR__ . "/../../config/db.php";
include(__DIR__ . "/../../includes/auth.php");

// only allow admins
check_role('admin');

$name = htmlspecialchars($_SESSION['name']);
?>
<!DOCTYPE html>
<html lang="en" x-data="{ menuOpen: false }">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="//unpkg.com/alpinejs" defer></script>
</head>
<body class="bg-gradient-to-br from-gray-50 to-white text-gray-800">

  <!-- Mobile Menu Overlay -->
  <div x-show="menuOpen" x-transition class="fixed inset-0 z-50 flex">
    <div class="fixed inset-0 bg-black bg-opacity-30" @click="menuOpen=false"></div>
    <div class="relative w-64 bg-white text-gray-800 p-6 shadow-xl z-50">
      <h2 class="text-xl font-bold mb-6 text-indigo-600">Admin Panel</h2>
      <ul class="space-y-3">
        <li><a href="dashboard.php" class="block px-3 py-2 rounded hover:bg-indigo-50">🏠 Dashboard</a></li>
        <li><a href="departments.php" class="block px-3 py-2 rounded hover:bg-indigo-50">🏫 Departments</a></li>
        <li><a href="courses.php" class="block px-3 py-2 rounded hover:bg-indigo-50">📘 Courses</a></li>
        <li><a href="users.php" class="block px-3 py-2 rounded hover:bg-indigo-50">👥 Users</a></li>
        <li><a href="classes.php" class="block px-3 py-2 rounded hover:bg-indigo-50">📅 Classes</a></li>
        <li><a href="attendance.php" class="block px-3 py-2 rounded hover:bg-indigo-50">📝 Attendance</a></li>
        <li><a href="assessments.php" class="block px-3 py-2 rounded hover:bg-indigo-50">🧾 Assessments</a></li>
        <li><a href="analytics.php" class="block px-3 py-2 rounded hover:bg-indigo-50">📈 Analytics</a></li>
        <li><a href="alerts.php" class="block px-3 py-2 rounded hover:bg-indigo-50">🚨 Alerts</a></li>
        <li><a href="reports.php" class="block px-3 py-2 rounded hover:bg-indigo-50">📑 Reports</a></li>
        <li><a href="settings.php" class="block px-3 py-2 rounded hover:bg-indigo-50">⚙️ Settings</a></li>
      </ul>
    </div>
  </div>

  <!-- Top Navbar -->
  <header class="flex items-center justify-between bg-white shadow px-6 py-4 sticky top-0 z-40">
    <div class="flex items-center gap-3">
      <button class="md:hidden p-2 rounded bg-indigo-100 text-indigo-600" @click="menuOpen=true">☰</button>
      <h1 class="text-lg font-semibold text-indigo-600">🛠️ Admin Dashboard</h1>
    </div>
    <div class="flex items-center gap-4">
      <a href="../logout.php" class="text-red-500 font-semibold hover:underline">Logout</a>
    </div>
  </header>

  <!-- Dashboard Content -->
  <main class="p-6">
    <p class="mb-6 text-gray-600">Welcome back, <b><?= $name ?></b>!</p>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <div class="bg-white p-6 rounded-xl shadow hover:shadow-lg transition">
        <p class="text-gray-500">Total Users</p>
        <h3 class="text-2xl font-bold text-indigo-600">1,240</h3>
      </div>
      <div class="bg-white p-6 rounded-xl shadow hover:shadow-lg transition">
        <p class="text-gray-500">Active Courses</p>
        <h3 class="text-2xl font-bold text-green-600">32</h3>
      </div>
      <div class="bg-white p-6 rounded-xl shadow hover:shadow-lg transition">
        <p class="text-gray-500">Attendance Avg</p>
        <h3 class="text-2xl font-bold text-orange-500">87%</h3>
      </div>
      <div class="bg-white p-6 rounded-xl shadow hover:shadow-lg transition">
        <p class="text-gray-500">Assessments</p>
        <h3 class="text-2xl font-bold text-purple-600">120</h3>
      </div>
    </div>

    <!-- Sections -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

      <!-- Management -->
      <div class="bg-white rounded-2xl shadow-lg p-6 hover:shadow-xl transition">
        <h2 class="text-lg font-semibold mb-4 text-indigo-500">📂 Management</h2>
        <ul class="space-y-3">
          <li><a href="users.php" class="block p-3 rounded-lg bg-indigo-50 hover:bg-indigo-100">👥 Manage Users</a></li>
          <li><a href="courses.php" class="block p-3 rounded-lg bg-indigo-50 hover:bg-indigo-100">📘 Manage Courses</a></li>
          <li><a href="departments.php" class="block p-3 rounded-lg bg-indigo-50 hover:bg-indigo-100">🏫 Manage Departments</a></li>
          <li><a href="classes.php" class="block p-3 rounded-lg bg-indigo-50 hover:bg-indigo-100">📅 Manage Classes</a></li>
        </ul>
      </div>

      <!-- Academic Control -->
      <div class="bg-white rounded-2xl shadow-lg p-6 hover:shadow-xl transition">
        <h2 class="text-lg font-semibold mb-4 text-green-500">📊 Academic Control</h2>
        <ul class="space-y-3">
          <li><a href="attendance.php" class="block p-3 rounded-lg bg-green-50 hover:bg-green-100">📝 Attendance</a></li>
          <li><a href="assessments.php" class="block p-3 rounded-lg bg-green-50 hover:bg-green-100">🧾 Assessments</a></li>
          <li><a href="analytics.php" class="block p-3 rounded-lg bg-green-50 hover:bg-green-100">📈 Performance Analytics</a></li>
        </ul>
      </div>

      <!-- System Monitoring -->
      <div class="bg-white rounded-2xl shadow-lg p-6 hover:shadow-xl transition">
        <h2 class="text-lg font-semibold mb-4 text-red-500">⚡ System Monitoring</h2>
        <ul class="space-y-3">
          <li><a href="alerts.php" class="block p-3 rounded-lg bg-red-50 hover:bg-red-100">🚨 Alerts</a></li>
          <li><a href="reports.php" class="block p-3 rounded-lg bg-red-50 hover:bg-red-100">📑 Reports</a></li>
          <li><a href="settings.php" class="block p-3 rounded-lg bg-red-50 hover:bg-red-100">⚙️ Settings</a></li>
        </ul>
      </div>

    </div>
  </main>
</body>
</html>
