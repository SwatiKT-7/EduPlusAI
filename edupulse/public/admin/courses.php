<?php
session_start();
require_once "../../config/db.php";
include("../../includes/header.php");

if ($_SESSION['role'] != 'admin') { 
    header("Location: ../dashboard.php"); 
    exit; 
}

$message="";
if ($_SERVER['REQUEST_METHOD']=="POST") {
    $code=mysqli_real_escape_string($conn,$_POST['code']);
    $name=mysqli_real_escape_string($conn,$_POST['name']);
    $dept_id=intval($_POST['dept_id']);
    $credits=intval($_POST['credits']);
    $semester=intval($_POST['semester']);

    $sql="INSERT INTO courses (tenant_id, dept_id, code, name, credits, semester, capacity) 
          VALUES (1,$dept_id,'$code','$name',$credits,$semester,60)";
    if (mysqli_query($conn,$sql)) $message="✅ Course added successfully!";
    else $message="❌ Error: ".mysqli_error($conn);
}

$depts=mysqli_query($conn,"SELECT id,name FROM departments");
$courses=mysqli_query($conn,"SELECT c.*,d.name as dept_name 
                             FROM courses c 
                             LEFT JOIN departments d ON c.dept_id=d.id
                             ORDER BY c.semester, c.code");
?>

<div class="flex min-h-screen bg-gray-100 dark:bg-gray-900">
  <?php include("includes/sidebar.php"); ?>

  <div class="flex-1 p-6">
    <h2 class="text-3xl font-bold text-indigo-600 mb-6">📘 Courses Management</h2>

    <?php if($message): ?>
      <div class="mb-4 p-3 rounded-lg 
                  <?= strpos($message,'✅')!==false ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
        <?= $message ?>
      </div>
    <?php endif; ?>

    <!-- Add Course Form -->
    <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-lg mb-8">
      <h3 class="text-xl font-semibold mb-4">➕ Add New Course</h3>
      <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <input type="text" name="code" class="border p-3 rounded-lg focus:ring-2 focus:ring-indigo-500" placeholder="Course Code" required>
        <input type="text" name="name" class="border p-3 rounded-lg focus:ring-2 focus:ring-indigo-500" placeholder="Course Name" required>

        <select name="dept_id" class="border p-3 rounded-lg focus:ring-2 focus:ring-indigo-500" required>
          <option value="">Select Department</option>
          <?php while($d=mysqli_fetch_assoc($depts)): ?>
            <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['name']) ?></option>
          <?php endwhile; ?>
        </select>

        <input type="number" name="credits" class="border p-3 rounded-lg focus:ring-2 focus:ring-indigo-500" placeholder="Credits" value="3" required>
        <input type="number" name="semester" class="border p-3 rounded-lg focus:ring-2 focus:ring-indigo-500" placeholder="Semester" required>

        <button class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg shadow col-span-1 md:col-span-2">
          Add Course
        </button>
      </form>
    </div>

    <!-- Course List -->
    <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-lg">
      <h3 class="text-xl font-semibold mb-4">📋 All Courses</h3>
      <div class="overflow-x-auto">
        <table class="w-full border-collapse text-sm rounded-lg overflow-hidden shadow">
          <thead class="bg-indigo-50 dark:bg-gray-700 text-left">
            <tr>
              <th class="p-3">Code</th>
              <th class="p-3">Name</th>
              <th class="p-3">Department</th>
              <th class="p-3">Credits</th>
              <th class="p-3">Semester</th>
            </tr>
          </thead>
          <tbody>
            <?php while($c=mysqli_fetch_assoc($courses)): ?>
            <tr class="border-b hover:bg-gray-50 dark:hover:bg-gray-700">
              <td class="p-3 font-medium"><?= htmlspecialchars($c['code']) ?></td>
              <td class="p-3"><?= htmlspecialchars($c['name']) ?></td>
              <td class="p-3"><?= htmlspecialchars($c['dept_name']) ?></td>
              <td class="p-3"><?= $c['credits'] ?></td>
              <td class="p-3"><?= $c['semester'] ?></td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php include("../../includes/footer.php"); ?>
