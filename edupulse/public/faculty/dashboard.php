<?php
session_start();
require_once "../../config/db.php";
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'faculty') {
    header("Location: ../login.php");
    exit;
}

$name = $_SESSION['name'];
$faculty_id = intval($_SESSION['user_id']);

// ✅ Total courses assigned
$q1 = mysqli_query($conn, "SELECT COUNT(*) as c FROM faculty_courses WHERE faculty_id=$faculty_id");
$totalCourses = ($q1 && mysqli_num_rows($q1) > 0) ? mysqli_fetch_assoc($q1)['c'] : 0;

// ✅ Sessions today
$q2 = mysqli_query($conn, "SELECT COUNT(*) as c FROM sessions WHERE faculty_id=$faculty_id AND DATE(session_date)=CURDATE()");
$sessionsToday = ($q2 && mysqli_num_rows($q2) > 0) ? mysqli_fetch_assoc($q2)['c'] : 0;

// ✅ Pending reports (sessions not closed)
$q3 = mysqli_query($conn, "SELECT COUNT(*) as c FROM sessions WHERE faculty_id=$faculty_id AND status!='closed'");
$pendingReports = ($q3 && mysqli_num_rows($q3) > 0) ? mysqli_fetch_assoc($q3)['c'] : 0;

// ✅ Attendance overview for faculty’s classes
$q4 = mysqli_query($conn, "
    SELECT 
        SUM(CASE WHEN ae.status='P' THEN 1 ELSE 0 END) as present,
        SUM(CASE WHEN ae.status='A' THEN 1 ELSE 0 END) as absent
    FROM attendance_events ae
    JOIN classes c ON ae.class_id=c.id
    WHERE c.faculty_id=$faculty_id
");
$attData = ($q4 && mysqli_num_rows($q4) > 0) ? mysqli_fetch_assoc($q4) : ['present'=>0,'absent'=>0];

// ✅ Course performance (avg attendance per course)
$q5 = mysqli_query($conn, "
    SELECT cr.code as course_code,
           ROUND(SUM(CASE WHEN ae.status='P' THEN 1 ELSE 0 END)*100/COUNT(*),1) as avg_att
    FROM attendance_events ae
    JOIN classes c ON ae.class_id=c.id
    JOIN courses cr ON c.course_id=cr.id
    WHERE c.faculty_id=$faculty_id
    GROUP BY cr.id
");
$courseLabels = [];
$courseData = [];
if ($q5 && mysqli_num_rows($q5) > 0) {
    while ($row = mysqli_fetch_assoc($q5)) {
        $courseLabels[] = $row['course_code'];
        $courseData[]   = $row['avg_att'];
    }
}
?>
<!DOCTYPE html>
<html lang="en" x-data="{ darkMode:false, activeTab:'home' }" :class="{ 'dark': darkMode }">
<head>
  <meta charset="UTF-8">
  <title>Faculty Dashboard</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="//unpkg.com/alpinejs" defer></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-900 dark:to-gray-800 text-gray-800 dark:text-gray-200">

  <!-- Header -->
  <header class="flex items-center justify-between px-6 py-4 bg-white/70 dark:bg-gray-800/70 backdrop-blur-lg shadow-lg">
    <h1 class="text-xl font-semibold">👩‍🏫 Faculty Dashboard</h1>
    <div class="flex items-center gap-3">
      <button @click="darkMode=!darkMode" class="p-2 rounded bg-gray-200 dark:bg-gray-700">🌙</button>
      <a href="../logout.php" class="text-red-500 font-semibold">Logout</a>
    </div>
  </header>

  <!-- Content -->
  <main class="p-6 pb-24">
    <h2 class="text-2xl font-bold mb-6">Welcome, <?= htmlspecialchars($name) ?> 👋</h2>
    
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
      <div class="bg-gradient-to-r from-blue-500 to-indigo-500 text-white p-6 rounded-2xl shadow-lg">
        <p>Total Courses</p><h3 class="text-3xl font-bold"><?= $totalCourses ?></h3>
      </div>
      <div class="bg-gradient-to-r from-green-500 to-emerald-500 text-white p-6 rounded-2xl shadow-lg">
        <p>Sessions Today</p><h3 class="text-3xl font-bold"><?= $sessionsToday ?></h3>
      </div>
      <div class="bg-gradient-to-r from-purple-500 to-pink-500 text-white p-6 rounded-2xl shadow-lg">
        <p>Pending Reports</p><h3 class="text-3xl font-bold"><?= $pendingReports ?></h3>
      </div>
    </div>

    <!-- Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-lg">
        <h3 class="font-semibold mb-3">Attendance Overview</h3>
        <canvas id="attChart"></canvas>
      </div>
      <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-lg">
        <h3 class="font-semibold mb-3">Course Performance</h3>
        <canvas id="perfChart"></canvas>
      </div>
    </div>
  </main>

  <!-- Sticky Footer -->
  <footer class="fixed bottom-0 left-0 w-full bg-white/80 dark:bg-gray-800/80 backdrop-blur-lg border-t flex justify-around py-2 text-sm">
    <a href="dashboard.php" class="flex flex-col items-center" :class="activeTab==='home'?'text-indigo-600':'text-gray-500'">
      🏠<span class="text-xs">Home</span>
    </a>
    <a href="../courses/select.php" class="flex flex-col items-center" :class="activeTab==='courses'?'text-indigo-600':'text-gray-500'">
      📘<span class="text-xs">Courses</span>
    </a>
    <a href="../sessions/create.php" class="flex flex-col items-center" :class="activeTab==='sessions'?'text-indigo-600':'text-gray-500'">
      ➕<span class="text-xs">Session</span>
    </a>
    <a href="attendance_report.php" class="flex flex-col items-center" :class="activeTab==='reports'?'text-indigo-600':'text-gray-500'">
      📑<span class="text-xs">Reports</span>
    </a>
    <a href="analytics.php" class="flex flex-col items-center" :class="activeTab==='analytics'?'text-indigo-600':'text-gray-500'">
      📊<span class="text-xs">Analytics</span>
    </a>
  </footer>

  <!-- Charts -->
  <script>
    // Attendance chart
    new Chart(document.getElementById('attChart'), {
      type: 'doughnut',
      data: {
        labels:['Present','Absent'],
        datasets:[{
          data:[<?= $attData['present'] ?>, <?= $attData['absent'] ?>],
          backgroundColor:['#22c55e','#ef4444']
        }]
      }
    });

    // Course performance chart
    new Chart(document.getElementById('perfChart'), {
      type: 'bar',
      data: {
        labels: <?= json_encode($courseLabels) ?>,
        datasets:[{
          label:'Avg Attendance %',
          data: <?= json_encode($courseData) ?>,
          backgroundColor:'#6366f1'
        }]
      }
    });
  </script>
</body>
</html>
