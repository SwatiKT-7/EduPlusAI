<?php
session_start();
require_once "../../config/db.php";
include("../includes/auth.php");
include("../includes/header.php");
include("includes/sidebar.php");

if ($_SESSION['role'] != 'admin') { 
    header("Location: ../dashboard.php"); 
    exit; 
}

$result = $conn->query("SELECT cl.id, c.name as course, u.name as faculty, cl.room, cl.start_at, cl.end_at 
                        FROM classes cl
                        JOIN courses c ON cl.course_id=c.id
                        JOIN users u ON cl.faculty_id=u.id
                        ORDER BY cl.start_at DESC");
?>

<div class="max-w-7xl mx-auto bg-white dark:bg-gray-800 p-6 mt-6 rounded-2xl shadow-lg">
  <div class="flex justify-between items-center mb-6">
    <h2 class="text-2xl font-bold text-indigo-600">🏫 Classes Management</h2>
    <a href="add_class.php" 
       class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg shadow text-sm">
       ➕ Add Class
    </a>
  </div>

  <div class="overflow-x-auto">
    <table class="w-full border-collapse rounded-lg overflow-hidden shadow text-sm">
      <thead class="bg-indigo-50 dark:bg-gray-700">
        <tr>
          <th class="p-3 text-left">ID</th>
          <th class="p-3 text-left">Course</th>
          <th class="p-3 text-left">Faculty</th>
          <th class="p-3 text-left">Room</th>
          <th class="p-3 text-left">Start</th>
          <th class="p-3 text-left">End</th>
          <th class="p-3 text-left">Action</th>
        </tr>
      </thead>
      <tbody>
        <?php while($row=$result->fetch_assoc()): ?>
        <tr class="border-b hover:bg-gray-50 dark:hover:bg-gray-700">
          <td class="p-3"><?= $row['id'] ?></td>
          <td class="p-3 font-medium"><?= htmlspecialchars($row['course']) ?></td>
          <td class="p-3"><?= htmlspecialchars($row['faculty']) ?></td>
          <td class="p-3"><?= htmlspecialchars($row['room']) ?></td>
          <td class="p-3"><?= date("d M Y, H:i", strtotime($row['start_at'])) ?></td>
          <td class="p-3"><?= date("d M Y, H:i", strtotime($row['end_at'])) ?></td>
          <td class="p-3 space-x-2">
            <a href="edit_class.php?id=<?= $row['id'] ?>" 
               class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600 text-xs">Edit</a>
            <a href="delete_class.php?id=<?= $row['id'] ?>" 
               class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600 text-xs"
               onclick="return confirm('Are you sure you want to delete this class?')">Delete</a>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include("../includes/footer.php"); ?>
