<?php
session_start();
require_once "../../config/db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'faculty') {
    header("Location: ../login.php");
    exit;
}

$faculty_id = $_SESSION['user_id'];
$dept_id    = isset($_SESSION['dept_id']) ? intval($_SESSION['dept_id']) : 0;
$message = "";

// Assign course
if (isset($_GET['course_id'])) {
    $course_id = intval($_GET['course_id']);
    $check = mysqli_query($conn,"SELECT id FROM faculty_courses WHERE faculty_id=$faculty_id AND course_id=$course_id");
    if (mysqli_num_rows($check)==0) {
        $sql="INSERT INTO faculty_courses (faculty_id, course_id) VALUES ($faculty_id,$course_id)";
        if (mysqli_query($conn,$sql)) {
            $_SESSION['flash'] = "✅ Course assigned successfully!";
            header("Location: ../dashboard.php");
            exit;
        } else {
            $message="❌ Error: ".mysqli_error($conn);
        }
    } else {
        $message="⚠️ You are already teaching this course.";
    }
}

// Fetch courses
$res_courses = ($dept_id>0) ? mysqli_query($conn,"SELECT id,code,name FROM courses WHERE dept_id=$dept_id") : false;
$res_assigned = mysqli_query($conn,"SELECT fc.id,c.code,c.name
                                    FROM faculty_courses fc
                                    JOIN courses c ON fc.course_id=c.id
                                    WHERE fc.faculty_id=$faculty_id");
$name = $_SESSION['name'];
?>
<!DOCTYPE html>
<html lang="en" x-data="{ darkMode:false }" :class="{ 'dark': darkMode }">
<head>
  <meta charset="UTF-8">
  <title>Select Courses</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="//unpkg.com/alpinejs" defer></script>
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200 min-h-screen flex flex-col">

  <!-- Header -->
  <header class="flex items-center justify-between px-6 py-4 bg-white dark:bg-gray-800 shadow">
    <h1 class="text-xl font-bold text-indigo-600">👩‍🏫 Faculty Panel</h1>
    <div class="flex items-center gap-3">
      <span class="hidden sm:inline font-medium">Welcome, <?= htmlspecialchars($name) ?></span>
      <button @click="darkMode=!darkMode" class="px-3 py-1 rounded bg-gray-200 dark:bg-gray-700">🌙</button>
      <a href="../logout.php" class="text-red-500 font-semibold hover:underline">Logout</a>
    </div>
  </header>

  <!-- Main -->
  <main class="flex-1 p-6">
    <div class="max-w-6xl mx-auto bg-white dark:bg-gray-800 p-6 rounded-xl shadow-lg">
      <h2 class="text-2xl font-bold mb-4 text-indigo-500">📘 Select My Courses</h2>

      <?php if($message): ?>
        <p class="mb-4 text-red-500 font-semibold"><?= $message ?></p>
      <?php endif; ?>

      <!-- Available Courses -->
      <div class="mb-8">
        <h3 class="text-lg font-semibold mb-3">Available Courses</h3>
        <?php if($res_courses && mysqli_num_rows($res_courses)>0): ?>
          <div class="overflow-x-auto">
            <table class="w-full border-collapse">
              <thead class="bg-indigo-50 dark:bg-gray-700">
                <tr>
                  <th class="p-3 text-left">Code</th>
                  <th class="p-3 text-left">Course</th>
                  <th class="p-3 text-center">Action</th>
                </tr>
              </thead>
              <tbody>
                <?php while($c=mysqli_fetch_assoc($res_courses)): ?>
                <tr class="border-b hover:bg-gray-50 dark:hover:bg-gray-700">
                  <td class="p-3 font-semibold"><?= $c['code'] ?></td>
                  <td class="p-3"><?= $c['name'] ?></td>
                  <td class="p-3 text-center">
                    <a href="select.php?course_id=<?= $c['id'] ?>" 
                       class="px-4 py-2 bg-indigo-600 text-white rounded-lg shadow hover:bg-indigo-700 transition">
                       ➕ Select
                    </a>
                  </td>
                </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <p class="text-gray-500 italic">No courses available for your department.</p>
        <?php endif; ?>
      </div>

      <!-- Assigned Courses -->
      <div>
        <h3 class="text-lg font-semibold mb-3">✅ My Selected Courses</h3>
        <div class="overflow-x-auto">
          <table class="w-full border-collapse">
            <thead class="bg-green-50 dark:bg-gray-700">
              <tr>
                <th class="p-3 text-left">Code</th>
                <th class="p-3 text-left">Course</th>
              </tr>
            </thead>
            <tbody>
              <?php if(mysqli_num_rows($res_assigned)>0): ?>
                <?php while($a=mysqli_fetch_assoc($res_assigned)): ?>
                <tr class="border-b hover:bg-gray-50 dark:hover:bg-gray-700">
                  <td class="p-3 font-semibold"><?= $a['code'] ?></td>
                  <td class="p-3"><?= $a['name'] ?></td>
                </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr>
                  <td colspan="2" class="p-3 text-center text-gray-500">No courses assigned yet.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>

  <!-- Footer -->
  <footer class="bg-white dark:bg-gray-800 text-center py-3 border-t">
    <p class="text-sm text-gray-500">EduPulse © <?= date("Y") ?> | Faculty Module</p>
  </footer>
</body>
</html>
