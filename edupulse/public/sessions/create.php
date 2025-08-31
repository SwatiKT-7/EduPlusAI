<?php
session_start();
require_once "../../config/db.php";
include("../../includes/auth.php");
check_role('faculty');

$faculty_id = $_SESSION['user_id'];
$name = $_SESSION['name'];
$message = "";

// Handle create session
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $course_id = intval($_POST['course_id']);
    $lat = floatval($_POST['geo_lat']);
    $lng = floatval($_POST['geo_lng']);
    $radius = intval($_POST['radius_m']);
    $start = date("Y-m-d H:i:s");
    $end   = date("Y-m-d H:i:s", strtotime("+1 hour"));

    // Insert into classes
    $sql = "INSERT INTO classes (tenant_id, course_id, faculty_id, room, start_at, end_at, geo_lat, geo_lng, radius_m, status, mode)
            VALUES (1, $course_id, $faculty_id, 'Room-1', '$start', '$end', $lat, $lng, $radius, 'scheduled','offline')";
    if (mysqli_query($conn, $sql)) {
        $class_id = mysqli_insert_id($conn);

        // Insert into sessions
        $sql2 = "INSERT INTO sessions (class_id, faculty_id, session_date, status)
                 VALUES ($class_id, $faculty_id, '$start', 'live')";
        if (mysqli_query($conn, $sql2)) {
            $message = "✅ Session started successfully!";
        } else {
            $message = "❌ Session error: " . mysqli_error($conn);
        }
    } else {
        $message = "❌ Class error: " . mysqli_error($conn);
    }
}

// Fetch faculty’s assigned courses
$res_courses = mysqli_query($conn, "
    SELECT c.id, c.code, c.name
    FROM faculty_courses fc
    JOIN courses c ON fc.course_id=c.id
    WHERE fc.faculty_id = $faculty_id
");
?>
<!DOCTYPE html>
<html lang="en" x-data="{ sidebarOpen:false, darkMode:false }" :class="{ 'dark': darkMode }">
<head>
  <meta charset="UTF-8">
  <title>Create Session</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="//unpkg.com/alpinejs" defer></script>
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200 min-h-screen flex">

  <!-- Sidebar -->
  <aside class="w-64 bg-white dark:bg-gray-800 shadow-lg flex-shrink-0 hidden md:flex flex-col">
    <div class="p-4 border-b dark:border-gray-700">
      <h2 class="text-xl font-bold text-indigo-600">EduPulse</h2>
      <p class="text-sm mt-1 text-gray-500 dark:text-gray-400">Faculty Panel</p>
    </div>
    <nav class="flex-1 p-4 space-y-2">
      <a href="../dashboard.php" class="block p-2 rounded hover:bg-indigo-100 dark:hover:bg-gray-700">🏠 Dashboard</a>
      <a href="../courses/select.php" class="block p-2 rounded hover:bg-indigo-100 dark:hover:bg-gray-700">📘 My Courses</a>
      <a href="create.php" class="block p-2 rounded bg-indigo-600 text-white">➕ Create Session</a>
      <a href="../attendance_report.php" class="block p-2 rounded hover:bg-indigo-100 dark:hover:bg-gray-700">📝 Attendance Report</a>
      <a href="../analytics.php" class="block p-2 rounded hover:bg-indigo-100 dark:hover:bg-gray-700">📊 Analytics</a>
    </nav>
    <div class="p-4 border-t dark:border-gray-700">
      <button @click="darkMode=!darkMode" class="w-full bg-gray-200 dark:bg-gray-700 px-3 py-2 rounded">🌙 Toggle Mode</button>
      <a href="../../logout.php" class="block mt-2 text-center text-red-500 font-semibold">🚪 Logout</a>
    </div>
  </aside>

  <!-- Content -->
  <div class="flex-1 flex flex-col">
    <!-- Header -->
    <header class="flex items-center justify-between px-6 py-4 bg-white dark:bg-gray-800 shadow">
      <button class="md:hidden p-2 rounded hover:bg-gray-200 dark:hover:bg-gray-700" @click="sidebarOpen = true">☰</button>
      <h1 class="text-lg font-semibold">Welcome, <?= htmlspecialchars($name) ?> 👋</h1>
    </header>

    <!-- Main -->
    <main class="flex-1 p-6">
      <div class="max-w-3xl mx-auto bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-lg">
        <h2 class="text-2xl font-bold mb-4 text-indigo-500">📅 Start a New Session</h2>

        <?php if($message): ?>
          <div class="mb-4 p-3 rounded-lg text-sm <?= strpos($message,'✅')!==false ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
            <?= $message ?>
          </div>
        <?php endif; ?>

        <form method="POST" id="sessionForm" class="space-y-4">
          <!-- Select Course -->
          <div>
            <label class="block mb-2 font-medium">Select Course</label>
            <select name="course_id" class="w-full border p-3 rounded-lg dark:bg-gray-700 dark:border-gray-600" required>
              <option value="">-- Choose Course --</option>
              <?php while($c = mysqli_fetch_assoc($res_courses)): ?>
                <option value="<?= $c['id']; ?>"><?= $c['code']." - ".$c['name']; ?></option>
              <?php endwhile; ?>
            </select>
          </div>

          <!-- Hidden geo fields -->
          <input type="hidden" name="geo_lat" id="geo_lat">
          <input type="hidden" name="geo_lng" id="geo_lng">

          <!-- Radius -->
          <div>
            <label class="block mb-2 font-medium">Radius (meters)</label>
            <input type="number" name="radius_m" class="w-full border p-3 rounded-lg dark:bg-gray-700 dark:border-gray-600" value="100" min="10">
          </div>

          <!-- Submit -->
          <button type="submit" class="w-full bg-indigo-600 text-white py-3 rounded-lg font-semibold hover:bg-indigo-700 transition transform hover:scale-105">
            ▶ Start Session
          </button>
        </form>
      </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white dark:bg-gray-800 text-center py-3 border-t dark:border-gray-700">
      <p class="text-sm text-gray-500">EduPulse © <?= date("Y") ?> | Faculty Module</p>
    </footer>
  </div>

  <!-- Mobile Sidebar -->
  <div x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 bg-black bg-opacity-50 z-40 md:hidden" @click="sidebarOpen=false"></div>
  <aside class="fixed inset-y-0 left-0 w-64 bg-white dark:bg-gray-800 shadow-lg z-50 transform transition-transform duration-300 md:hidden"
         :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
    <div class="p-4 flex items-center justify-between border-b dark:border-gray-700">
      <h2 class="text-xl font-bold text-indigo-600">EduPulse</h2>
      <button class="p-2" @click="sidebarOpen=false">✖</button>
    </div>
    <nav class="p-4 space-y-2">
      <a href="../dashboard.php" class="block p-2 rounded hover:bg-indigo-100 dark:hover:bg-gray-700">🏠 Dashboard</a>
      <a href="../courses/select.php" class="block p-2 rounded hover:bg-indigo-100 dark:hover:bg-gray-700">📘 My Courses</a>
      <a href="create.php" class="block p-2 rounded bg-indigo-600 text-white">➕ Create Session</a>
      <a href="../attendance_report.php" class="block p-2 rounded hover:bg-indigo-100 dark:hover:bg-gray-700">📝 Attendance Report</a>
      <a href="../analytics.php" class="block p-2 rounded hover:bg-indigo-100 dark:hover:bg-gray-700">📊 Analytics</a>
    </nav>
    <div class="p-4 border-t dark:border-gray-700">
      <button @click="darkMode=!darkMode" class="w-full bg-gray-200 dark:bg-gray-700 px-3 py-2 rounded">🌙 Toggle Mode</button>
      <a href="../../logout.php" class="block mt-2 text-center text-red-500 font-semibold">🚪 Logout</a>
    </div>
  </aside>

  <!-- Geolocation -->
  <script>
  document.getElementById("sessionForm").addEventListener("submit", function(e){
      if (!document.getElementById("geo_lat").value) {
          e.preventDefault();
          navigator.geolocation.getCurrentPosition(function(pos){
              document.getElementById("geo_lat").value = pos.coords.latitude;
              document.getElementById("geo_lng").value = pos.coords.longitude;
              e.target.submit();
          }, function(){
              alert("⚠️ Location access denied. Please allow location to start a session.");
          });
      }
  });
  </script>
</body>
</html>
