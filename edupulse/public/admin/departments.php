<?php
session_start();
require_once "../../config/db.php";
include("../../includes/header.php");

if ($_SESSION['role'] != 'admin') { 
    header("Location: ../dashboard.php"); 
    exit; 
}

$message = "";
if ($_SERVER['REQUEST_METHOD']=="POST") {
    $name = mysqli_real_escape_string($conn,$_POST['name']);
    if (!empty($name)) {
        mysqli_query($conn,"INSERT INTO departments (tenant_id, name) VALUES (1,'$name')");
        $message="✅ Department added.";
    }
}
$depts=mysqli_query($conn,"SELECT * FROM departments ORDER BY id ASC");
?>

<div class="flex min-h-screen bg-gray-100 dark:bg-gray-900">
  <?php include("includes/sidebar.php"); ?>

  <div class="flex-1 p-6">
    <h2 class="text-3xl font-bold text-indigo-600 mb-6">🏫 Departments</h2>

    <!-- Message -->
    <?php if($message): ?>
      <div class="mb-4 p-3 rounded-lg bg-green-100 text-green-700 shadow">
        <?= $message ?>
      </div>
    <?php endif; ?>

    <!-- Add Department Form -->
    <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-lg mb-8">
      <h3 class="text-xl font-semibold mb-4">➕ Add Department</h3>
      <form method="POST" class="flex gap-3">
        <input type="text" name="name" 
               class="flex-1 border p-3 rounded-lg focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600" 
               placeholder="New Department Name" required>
        <button class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2 rounded-lg shadow">
          Add
        </button>
      </form>
    </div>

    <!-- Department List -->
    <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-lg">
      <h3 class="text-xl font-semibold mb-4">📋 All Departments</h3>
      <div class="overflow-x-auto">
        <table class="w-full border-collapse text-sm rounded-lg overflow-hidden shadow">
          <thead class="bg-indigo-50 dark:bg-gray-700 text-left">
            <tr>
              <th class="p-3">ID</th>
              <th class="p-3">Name</th>
            </tr>
          </thead>
          <tbody>
            <?php while($d=mysqli_fetch_assoc($depts)): ?>
            <tr class="border-b hover:bg-gray-50 dark:hover:bg-gray-700">
              <td class="p-3"><?= $d['id'] ?></td>
              <td class="p-3 font-medium"><?= htmlspecialchars($d['name']) ?></td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php include("../../includes/footer.php"); ?>
