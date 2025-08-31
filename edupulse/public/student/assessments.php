<?php
session_start();
require_once "../../config/db.php";
include("../../includes/auth.php");
check_role('student');

$student_id = $_SESSION['user_id'];

// Get all courses the student is enrolled in
$coursesRes = mysqli_query($conn, "
    SELECT c.id, c.code, c.name 
    FROM student_courses sc
    JOIN courses c ON sc.course_id=c.id
    WHERE sc.student_id=$student_id
");

$selected_course = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;

// Fetch assessments + marks if course selected
$assessments = [];
if ($selected_course) {
    $aRes = mysqli_query($conn, "
        SELECT a.*, 
               s.marks,
               (SELECT ROUND(AVG(s2.marks),2) 
                FROM assessment_scores s2 
                WHERE s2.assessment_id=a.id) as avg_marks
        FROM assessments a
        LEFT JOIN assessment_scores s 
               ON s.assessment_id=a.id AND s.user_id=$student_id
        WHERE a.course_id=$selected_course
        ORDER BY a.date ASC
    ");
    while ($row = mysqli_fetch_assoc($aRes)) {
        $assessments[] = $row;
    }
}
$name = $_SESSION['name'];
?>
<!DOCTYPE html>
<html lang="en" x-data="{ darkMode:false }" :class="{ 'dark': darkMode }">
<head>
  <meta charset="UTF-8">
  <title>My Assessments</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="//unpkg.com/alpinejs" defer></script>
</head>
<body class="bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-900 dark:to-gray-800 text-gray-800 dark:text-gray-200 min-h-screen flex flex-col">

  <!-- Header -->
  <header class="flex items-center justify-between px-6 py-4 bg-white/70 dark:bg-gray-800/70 backdrop-blur shadow">
    <h1 class="text-xl font-bold text-indigo-600">📘 My Assessments</h1>
    <div class="flex items-center gap-3">
      <span class="hidden sm:inline font-medium">Hi, <?= htmlspecialchars($name) ?> 👋</span>
      <button @click="darkMode=!darkMode" class="px-3 py-1 rounded bg-gray-200 dark:bg-gray-700">🌙</button>
      <a href="../logout.php" class="text-red-500 font-semibold hover:underline">Logout</a>
    </div>
  </header>

  <!-- Main -->
  <main class="flex-1 p-6">
    <div class="max-w-6xl mx-auto bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-lg">
      <h2 class="text-2xl font-bold mb-6 text-indigo-500">📑 Assessment Details</h2>

      <!-- Course Selector -->
      <form method="GET" class="mb-6">
        <label class="block mb-2 font-semibold">Select Course</label>
        <select name="course_id" onchange="this.form.submit()" 
                class="w-full border p-3 rounded-lg dark:bg-gray-700 dark:border-gray-600">
          <option value="">-- Choose Course --</option>
          <?php while($c = mysqli_fetch_assoc($coursesRes)): ?>
            <option value="<?= $c['id'] ?>" <?= $selected_course==$c['id']?'selected':'' ?>>
              <?= $c['code']." - ".$c['name'] ?>
            </option>
          <?php endwhile; ?>
        </select>
      </form>

      <!-- Assessments Table -->
      <?php if ($selected_course && count($assessments)): ?>
        <div class="overflow-x-auto">
          <table class="w-full border-collapse rounded-xl overflow-hidden shadow">
            <thead class="bg-indigo-50 dark:bg-gray-700">
              <tr>
                <th class="p-3 text-left">Type</th>
                <th class="p-3 text-left">Date</th>
                <th class="p-3 text-center">Max Marks</th>
                <th class="p-3 text-center">Weight</th>
                <th class="p-3 text-center">My Marks</th>
                <th class="p-3 text-center">Class Avg</th>
                <th class="p-3 text-center">Weighted</th>
              </tr>
            </thead>
            <tbody>
              <?php 
              $total_weighted = 0;
              $total_weight = 0;
              foreach($assessments as $a): 
                  $weighted = ($a['marks'] !== null) ? 
                              round(($a['marks']/$a['max_marks'])*$a['weight'], 2) : 0;
                  $total_weighted += $weighted;
                  $total_weight += $a['weight'];
              ?>
              <tr class="border-b hover:bg-gray-50 dark:hover:bg-gray-700">
                <td class="p-3"><?= ucfirst($a['type']) ?></td>
                <td class="p-3"><?= $a['date'] ?></td>
                <td class="p-3 text-center"><?= $a['max_marks'] ?></td>
                <td class="p-3 text-center"><?= $a['weight'] ?>%</td>
                <td class="p-3 text-center <?= $a['marks']!==null && $a['marks']<($a['max_marks']/2)?'text-red-600 font-semibold':'' ?>">
                  <?= $a['marks'] !== null ? $a['marks'] : '<i class="text-gray-400">Not graded</i>' ?>
                </td>
                <td class="p-3 text-center"><?= $a['avg_marks'] !== null ? $a['avg_marks'] : '-' ?></td>
                <td class="p-3 text-center font-semibold"><?= $weighted ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <!-- Total Weighted Score -->
        <div class="mt-6 bg-indigo-50 dark:bg-gray-700 p-4 rounded-lg shadow text-center">
          <h3 class="text-lg font-bold text-indigo-600 dark:text-indigo-300">
            📊 Total Weighted Score: <?= $total_weighted ?> / <?= $total_weight ?>%
          </h3>
        </div>

      <?php elseif ($selected_course): ?>
        <p class="text-gray-500 italic">No assessments found for this course.</p>
      <?php endif; ?>
    </div>
  </main>

  <!-- Footer -->
  <footer class="bg-white/70 dark:bg-gray-800/70 text-center py-3 border-t">
    <p class="text-sm text-gray-500">EduPulse © <?= date("Y") ?> | Student Module</p>
  </footer>

</body>
</html>
