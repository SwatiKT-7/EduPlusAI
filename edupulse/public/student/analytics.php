<?php
session_start();
require_once "../../config/db.php";
include("../../includes/auth.php");
check_role('student');

$student_id = $_SESSION['user_id'];

// Fetch all student metrics
$mRes = mysqli_query($conn, "
    SELECT c.code, c.name, m.*
    FROM student_metrics m
    JOIN courses c ON m.course_id=c.id
    WHERE m.user_id=$student_id
");
$metrics = [];
while ($row = mysqli_fetch_assoc($mRes)) {
    $metrics[] = $row;
}
$name = $_SESSION['name'];
?>
<!DOCTYPE html>
<html lang="en" x-data="{ darkMode:false }" :class="{ 'dark': darkMode }">
<head>
  <meta charset="UTF-8">
  <title>My Performance Analytics</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="//unpkg.com/alpinejs" defer></script>
</head>
<body class="bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-900 dark:to-gray-800 text-gray-800 dark:text-gray-200 min-h-screen flex flex-col">

  <!-- Header -->
  <header class="flex items-center justify-between px-6 py-4 bg-white/70 dark:bg-gray-800/70 backdrop-blur shadow">
    <h1 class="text-xl font-bold text-indigo-600">📈 Performance Analytics</h1>
    <div class="flex items-center gap-3">
      <span class="hidden sm:inline font-medium">Hi, <?= htmlspecialchars($name) ?> 👋</span>
      <button @click="darkMode=!darkMode" class="px-3 py-1 rounded bg-gray-200 dark:bg-gray-700">🌙</button>
      <a href="../logout.php" class="text-red-500 font-semibold hover:underline">Logout</a>
    </div>
  </header>

  <!-- Main -->
  <main class="flex-1 p-6">
    <div class="max-w-6xl mx-auto bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-lg">
      <h2 class="text-2xl font-bold mb-6 text-indigo-500">📊 My Performance Overview</h2>

      <?php if (count($metrics)): ?>
      <div class="overflow-x-auto">
        <table class="w-full border-collapse rounded-xl overflow-hidden shadow">
          <thead class="bg-indigo-50 dark:bg-gray-700">
            <tr>
              <th class="p-3 text-left">Course</th>
              <th class="p-3 text-center">Attendance %</th>
              <th class="p-3 text-center">Trend</th>
              <th class="p-3 text-center">Risk</th>
              <th class="p-3 text-center">Projection</th>
              <th class="p-3 text-left">Reason</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($metrics as $m): ?>
              <tr class="border-b hover:bg-gray-50 dark:hover:bg-gray-700">
                <td class="p-3 font-semibold"><?= $m['code']." - ".$m['name'] ?></td>
                <td class="p-3 text-center <?= ($m['attn_cum']<75?'text-red-600 font-bold':'') ?>">
                  <?= $m['attn_cum'] ?>%
                </td>
                <td class="p-3 text-center">
                  <?= $m['attn_trend'] >=0 ? "📈 +".$m['attn_trend'] : "📉 ".$m['attn_trend'] ?>
                </td>
                <td class="p-3 text-center <?= ($m['risk_score']>=7?'text-red-600 font-bold':'text-green-600') ?>">
                  <?= $m['risk_score'] ?>/10
                </td>
                <td class="p-3 text-center"><?= $m['term_attn_projection'] ?>%</td>
                <td class="p-3 text-sm text-gray-600 dark:text-gray-400"><?= $m['risk_reason'] ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php else: ?>
        <p class="text-gray-500 italic">No analytics data available yet.</p>
      <?php endif; ?>
    </div>
  </main>

  <!-- Footer -->
  <footer class="bg-white/70 dark:bg-gray-800/70 text-center py-3 border-t">
    <p class="text-sm text-gray-500">EduPulse © <?= date("Y") ?> | Student Module</p>
  </footer>

</body>
</html>
