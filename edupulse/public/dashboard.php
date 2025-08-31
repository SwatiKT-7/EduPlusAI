<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$role = $_SESSION['role'];
$name = $_SESSION['name'];
?>
<!DOCTYPE html>
<html lang="en" x-data="{ sidebarOpen: false, darkMode: false, activeTab: 'home' }" :class="{ 'dark': darkMode }">
<head>
  <meta charset="UTF-8">
  <title>Dashboard</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="//unpkg.com/alpinejs" defer></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    /* Smooth transitions */
    .slide-enter { transform: translateX(-100%); opacity: 0; }
    .slide-enter-active { transform: translateX(0); opacity: 1; transition: all 0.3s ease-in-out; }
    .slide-leave { transform: translateX(0); opacity: 1; }
    .slide-leave-active { transform: translateX(-100%); opacity: 0; transition: all 0.3s ease-in-out; }
  </style>
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200">

  <!-- Mobile Sidebar Overlay -->
  <div x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 bg-black bg-opacity-50 z-40 md:hidden" @click="sidebarOpen=false"></div>

  <!-- Sidebar -->
  <aside class="fixed inset-y-0 left-0 w-64 bg-white dark:bg-gray-800 shadow-lg z-50 transform transition-transform duration-300"
         :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'">
    <div class="p-4 flex items-center justify-between border-b border-gray-200 dark:border-gray-700">
      <h2 class="text-xl font-bold text-indigo-600 dark:text-indigo-400">EduManage</h2>
      <button class="md:hidden p-2" @click="sidebarOpen=false">✖</button>
    </div>
    <nav class="p-4 space-y-2">
      <?php if ($role == 'admin'): ?>
        <a href="users/list.php" class="block p-2 rounded hover:bg-indigo-100 dark:hover:bg-gray-700">👥 Manage Users</a>
        <a href="courses/list.php" class="block p-2 rounded hover:bg-indigo-100 dark:hover:bg-gray-700">📘 Courses</a>
        <a href="departments/list.php" class="block p-2 rounded hover:bg-indigo-100 dark:hover:bg-gray-700">🏫 Departments</a>
        <a href="analytics.php" class="block p-2 rounded hover:bg-indigo-100 dark:hover:bg-gray-700">📈 Analytics</a>
        <a href="reports.php" class="block p-2 rounded hover:bg-indigo-100 dark:hover:bg-gray-700">📑 Reports</a>
        <a href="settings.php" class="block p-2 rounded hover:bg-indigo-100 dark:hover:bg-gray-700">⚙️ Settings</a>
      <?php elseif ($role == 'faculty'): ?>
        <a href="courses/select.php" class="block p-2 rounded hover:bg-indigo-100 dark:hover:bg-gray-700">📘 My Courses</a>
        <a href="sessions/create.php" class="block p-2 rounded hover:bg-indigo-100 dark:hover:bg-gray-700">➕ Create Session</a>
        <a href="faculty/attendance_report.php" class="block p-2 rounded hover:bg-indigo-100 dark:hover:bg-gray-700">📝 Attendance Report</a>
        <a href="faculty/analytics.php" class="block p-2 rounded hover:bg-indigo-100 dark:hover:bg-gray-700">📊 Analytics</a>
      <?php else: ?>
        <a href="courses/enroll.php" class="block p-2 rounded hover:bg-indigo-100 dark:hover:bg-gray-700">📘 Enroll</a>
        <a href="attendance/stats.php" class="block p-2 rounded hover:bg-indigo-100 dark:hover:bg-gray-700">📊 My Attendance</a>
        <a href="student/analytics.php" class="block p-2 rounded hover:bg-indigo-100 dark:hover:bg-gray-700">📈 Performance</a>
      <?php endif; ?>
    </nav>
  </aside>

  <!-- Main Content -->
  <div class="md:ml-64 flex flex-col min-h-screen">
    
    <!-- Top Navbar -->
    <header class="flex items-center justify-between bg-white dark:bg-gray-800 shadow px-6 py-4">
      <button class="md:hidden p-2 rounded hover:bg-gray-200 dark:hover:bg-gray-700" @click="sidebarOpen = true">☰</button>
      <h1 class="text-lg font-semibold">Welcome, <?= htmlspecialchars($name) ?> 👋</h1>
      <div class="flex items-center space-x-4">
        <button @click="darkMode=!darkMode" class="p-2 rounded bg-gray-200 dark:bg-gray-700 transition">🌙</button>
        <a href="logout.php" class="text-red-500 hover:underline">Logout</a>
      </div>
    </header>

    <!-- Dashboard Cards -->
    <main class="flex-1 p-6">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow hover:shadow-lg transition transform hover:scale-105">
          <p class="text-gray-500 dark:text-gray-400">Total Users</p>
          <h3 class="text-2xl font-bold">1,240</h3>
        </div>
        <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow hover:shadow-lg transition transform hover:scale-105">
          <p class="text-gray-500 dark:text-gray-400">Active Courses</p>
          <h3 class="text-2xl font-bold">32</h3>
        </div>
        <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow hover:shadow-lg transition transform hover:scale-105">
          <p class="text-gray-500 dark:text-gray-400">Attendance Rate</p>
          <h3 class="text-2xl font-bold">87%</h3>
        </div>
        <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow hover:shadow-lg transition transform hover:scale-105">
          <p class="text-gray-500 dark:text-gray-400">Assessments</p>
          <h3 class="text-2xl font-bold">120</h3>
        </div>
      </div>

      <!-- Chart Section -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow transition transform hover:scale-[1.02]">
          <h2 class="text-lg font-bold mb-4">Attendance Overview</h2>
          <canvas id="attendanceChart"></canvas>
        </div>
        <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow transition transform hover:scale-[1.02]">
          <h2 class="text-lg font-bold mb-4">Performance Trend</h2>
          <canvas id="performanceChart"></canvas>
        </div>
      </div>
    </main>

    <!-- Sticky Mobile Footer -->
    <footer class="fixed bottom-0 left-0 w-full bg-white dark:bg-gray-800 border-t flex justify-around md:hidden py-2">
      <template x-for="tab in ['home','courses','stats','logout']">
        <a href="#"
           @click="activeTab = tab"
           :class="activeTab === tab ? 'text-indigo-600' : 'text-gray-500'"
           class="flex flex-col items-center transition transform hover:scale-110">
          <span x-text="tab==='home'?'🏠':tab==='courses'?'📘':tab==='stats'?'📊':'🚪'"></span>
          <span class="text-xs capitalize" x-text="tab"></span>
        </a>
      </template>
    </footer>
  </div>

  <!-- Chart.js Config -->
  <script>
    const ctx1 = document.getElementById('attendanceChart');
    new Chart(ctx1, {
      type: 'doughnut',
      data: {
        labels: ['Present', 'Absent'],
        datasets: [{ data: [87, 13], backgroundColor: ['#4ade80', '#f87171'] }]
      },
      options: { animation: { animateScale: true } }
    });

    const ctx2 = document.getElementById('performanceChart');
    new Chart(ctx2, {
      type: 'line',
      data: {
        labels: ['Jan','Feb','Mar','Apr','May'],
        datasets: [{
          label: 'Performance %',
          data: [70,75,80,85,90],
          borderColor: '#6366f1',
          backgroundColor: 'rgba(99,102,241,0.3)',
          fill: true,
          tension: 0.3
        }]
      },
      options: { animation: { duration: 1500 } }
    });
  </script>
</body>
</html>
