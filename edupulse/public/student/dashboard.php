<?php
session_start();
require_once "../../config/db.php";
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit;
}
$name = $_SESSION['name'];
$student_id = $_SESSION['user_id'];

// ✅ Enrolled courses
$res = mysqli_query($conn, "SELECT COUNT(*) as c FROM student_courses WHERE student_id=$student_id");
$enrolled = mysqli_fetch_assoc($res)['c'] ?? 0;

// ✅ Attendance %
$res = mysqli_query($conn, "SELECT 
    ROUND(SUM(CASE WHEN status='P' THEN 1 ELSE 0 END)*100/COUNT(*),2) as percent 
    FROM attendance_events WHERE user_id=$student_id");
$attPercent = mysqli_fetch_assoc($res)['percent'] ?? 0;

// ✅ Upcoming assessments
$res = mysqli_query($conn, "SELECT COUNT(*) as c FROM assessments a 
    JOIN student_courses sc ON a.course_id=sc.course_id 
    WHERE sc.student_id=$student_id AND a.date>=CURDATE()");
$upcoming = mysqli_fetch_assoc($res)['c'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en" x-data="{ darkMode:false, activeTab:'home' }" :class="{ 'dark': darkMode }">
<head>
  <meta charset="UTF-8">
  <title>Student Dashboard</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="//unpkg.com/alpinejs" defer></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-900 dark:to-gray-800 text-gray-800 dark:text-gray-200">

  <!-- Header -->
  <header class="flex items-center justify-between px-6 py-4 bg-white/70 dark:bg-gray-800/70 backdrop-blur-lg shadow-lg">
    <h1 class="text-xl font-semibold">🎓 Student Dashboard</h1>
    <div class="flex items-center gap-3">
      <button @click="darkMode=!darkMode" class="p-2 rounded bg-gray-200 dark:bg-gray-700">🌙</button>
      <a href="../logout.php" class="text-red-500 font-semibold">Logout</a>
    </div>
  </header>

  <!-- Content -->
  <main class="p-6 pb-20">
    <h2 class="text-2xl font-bold mb-6">Hello, <?= htmlspecialchars($name) ?> 👋</h2>
    
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
      <div class="bg-gradient-to-r from-blue-500 to-indigo-500 text-white p-6 rounded-2xl shadow-lg hover:scale-105 transition">
        <p>Enrolled Courses</p><h3 class="text-3xl font-bold"><?= $enrolled ?></h3>
      </div>
      <div class="bg-gradient-to-r from-green-500 to-emerald-500 text-white p-6 rounded-2xl shadow-lg hover:scale-105 transition">
        <p>Attendance %</p><h3 class="text-3xl font-bold"><?= $attPercent ?>%</h3>
      </div>
      <div class="bg-gradient-to-r from-purple-500 to-pink-500 text-white p-6 rounded-2xl shadow-lg hover:scale-105 transition">
        <p>Upcoming Assessments</p><h3 class="text-3xl font-bold"><?= $upcoming ?></h3>
      </div>
    </div>

    <!-- Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-lg">
        <h3 class="font-semibold mb-3">My Attendance</h3>
        <canvas id="studAtt"></canvas>
      </div>
      <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-lg">
        <h3 class="font-semibold mb-3">Performance</h3>
        <canvas id="studPerf"></canvas>
      </div>
    </div>
  </main>

  <!-- Sticky Footer Navigation -->
  <footer class="fixed bottom-0 left-0 w-full bg-white/80 dark:bg-gray-800/80 backdrop-blur-lg border-t flex justify-around py-2">
    <a href="dashboard.php" class="flex flex-col items-center" :class="activeTab==='home'?'text-indigo-600':'text-gray-500'">🏠<span class="text-xs">Home</span></a>
    <a href="../courses/enroll.php" class="flex flex-col items-center" :class="activeTab==='courses'?'text-indigo-600':'text-gray-500'">📘<span class="text-xs">Courses</span></a>
    <a href="../attendance/stats.php" class="flex flex-col items-center" :class="activeTab==='attendance'?'text-indigo-600':'text-gray-500'">✅<span class="text-xs">Attendance</span></a>
    <a href="../student/analytics.php" class="flex flex-col items-center" :class="activeTab==='analytics'?'text-indigo-600':'text-gray-500'">📈<span class="text-xs">Analytics</span></a>
  </footer>

  <!-- Charts -->
  <script>
    new Chart(document.getElementById('studAtt'), {
      type: 'doughnut',
      data: { labels:['Present','Absent'], datasets:[{ data:[<?= $attPercent ?>, <?= 100-$attPercent ?>], backgroundColor:['#22c55e','#ef4444'] }] }
    });
    new Chart(document.getElementById('studPerf'), {
      type: 'line',
      data: { labels:['Sem1','Sem2','Sem3','Sem4'], datasets:[{ label:'Performance %', data:[70,78,85,90], borderColor:'#6366f1', backgroundColor:'rgba(99,102,241,0.3)', fill:true }] }
    });
  </script>
</body>
</html>
