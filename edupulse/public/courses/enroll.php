<?php
session_start();
require_once "../../config/db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit;
}

$student_id = $_SESSION['user_id'];
$dept_id    = isset($_SESSION['dept_id']) ? intval($_SESSION['dept_id']) : 0;
$message = "";

// Handle enrollment
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['course_id'])) {
    $course_id = intval($_POST['course_id']);
    $sql = "INSERT INTO student_courses (student_id, course_id) VALUES ($student_id, $course_id)";
    if (mysqli_query($conn, $sql)) {
        $message = "✅ Enrolled successfully!";
    } else {
        $message = "❌ Error: " . mysqli_error($conn);
    }
}

// Fetch available courses (exclude already enrolled)
if ($dept_id > 0) {
    $res_courses = mysqli_query($conn, "
        SELECT id, code, name 
        FROM courses 
        WHERE dept_id = $dept_id
        AND id NOT IN (
            SELECT course_id FROM student_courses WHERE student_id = $student_id
        )"
    );
} else {
    $res_courses = mysqli_query($conn, "
        SELECT id, code, name 
        FROM courses 
        WHERE id NOT IN (
            SELECT course_id FROM student_courses WHERE student_id = $student_id
        )"
    );
}

// Fetch enrolled courses
$res_enrolled = mysqli_query($conn, "
    SELECT c.code, c.name 
    FROM student_courses sc
    JOIN courses c ON sc.course_id = c.id
    WHERE sc.student_id = $student_id
");
$name = $_SESSION['name'];
?>
<!DOCTYPE html>
<html lang="en" x-data="{ darkMode:false }" :class="{ 'dark': darkMode }">
<head>
  <meta charset="UTF-8">
  <title>Enroll in Courses</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="//unpkg.com/alpinejs" defer></script>
</head>
<body class="bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-900 dark:to-gray-800 text-gray-800 dark:text-gray-200 min-h-screen flex flex-col">

  <!-- Header -->
  <header class="flex items-center justify-between px-6 py-4 bg-white/70 dark:bg-gray-800/70 backdrop-blur shadow">
    <h1 class="text-xl font-bold text-indigo-600">🎓 Student Panel</h1>
    <div class="flex items-center gap-3">
      <span class="hidden sm:inline font-medium">Hi, <?= htmlspecialchars($name) ?> 👋</span>
      <button @click="darkMode=!darkMode" class="px-3 py-1 rounded bg-gray-200 dark:bg-gray-700">🌙</button>
      <a href="../logout.php" class="text-red-500 font-semibold hover:underline">Logout</a>
    </div>
  </header>

  <!-- Main -->
  <main class="flex-1 p-6">
    <div class="max-w-4xl mx-auto bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-lg">
      <h2 class="text-2xl font-bold mb-4 text-indigo-500">📘 Enroll in Courses</h2>

      <?php if($message): ?>
        <div class="mb-4 p-3 rounded-lg text-sm <?= strpos($message,'✅')!==false ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
          <?= $message ?>
        </div>
      <?php endif; ?>

      <!-- Enrollment Form -->
      <?php if ($res_courses && mysqli_num_rows($res_courses) > 0): ?>
        <form method="POST" class="mb-8">
          <label class="block mb-2 font-semibold">Select a Course</label>
          <select name="course_id" class="w-full border p-3 rounded-lg mb-4 dark:bg-gray-700 dark:border-gray-600" required>
            <option value="">-- Choose Course --</option>
            <?php while($c = mysqli_fetch_assoc($res_courses)): ?>
              <option value="<?= $c['id'] ?>"><?= $c['code']." - ".$c['name'] ?></option>
            <?php endwhile; ?>
          </select>
          <button type="submit" class="w-full bg-indigo-600 text-white py-3 rounded-lg font-semibold hover:bg-indigo-700 transition">
            ➕ Enroll
          </button>
        </form>
      <?php else: ?>
        <p class="text-gray-600 italic mb-8">No new courses available for your department.</p>
      <?php endif; ?>

      <!-- Enrolled Courses -->
      <h3 class="text-xl font-semibold mb-3">✅ My Enrolled Courses</h3>
      <div class="overflow-x-auto">
        <table class="w-full border-collapse rounded-xl overflow-hidden shadow">
          <thead class="bg-indigo-50 dark:bg-gray-700">
            <tr>
              <th class="p-3 text-left">Code</th>
              <th class="p-3 text-left">Course Name</th>
            </tr>
          </thead>
          <tbody>
            <?php if (mysqli_num_rows($res_enrolled) > 0): ?>
              <?php while($en = mysqli_fetch_assoc($res_enrolled)): ?>
              <tr class="border-b hover:bg-gray-50 dark:hover:bg-gray-700">
                <td class="p-3 font-semibold"><?= $en['code'] ?></td>
                <td class="p-3"><?= $en['name'] ?></td>
              </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr>
                <td colspan="2" class="p-3 text-center text-gray-500 italic">You haven’t enrolled in any courses yet.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>

  <!-- Footer -->
  <footer class="bg-white/70 dark:bg-gray-800/70 text-center py-3 border-t">
    <p class="text-sm text-gray-500">EduPulse © <?= date("Y") ?> | Student Module</p>
  </footer>

</body>
</html>
