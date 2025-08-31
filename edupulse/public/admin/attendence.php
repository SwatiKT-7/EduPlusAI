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

$sql = "SELECT ae.id, u.name as student, c.name as course, ae.status, ae.created_at 
        FROM attendance_events ae
        JOIN users u ON ae.user_id=u.id
        JOIN classes cl ON ae.class_id=cl.id
        JOIN courses c ON cl.course_id=c.id
        ORDER BY ae.created_at DESC LIMIT 100";
$result = $conn->query($sql);
?>

<div class="max-w-7xl mx-auto bg-white dark:bg-gray-800 p-6 mt-6 rounded-2xl shadow-lg">
  <h2 class="text-2xl font-bold mb-6 text-indigo-600">📝 Attendance Control</h2>

  <div class="overflow-x-auto">
    <table class="w-full border-collapse rounded-lg overflow-hidden shadow text-sm">
      <thead class="bg-indigo-50 dark:bg-gray-700">
        <tr>
          <th class="p-3 text-left">ID</th>
          <th class="p-3 text-left">Student</th>
          <th class="p-3 text-left">Course</th>
          <th class="p-3 text-left">Status</th>
          <th class="p-3 text-left">Date</th>
          <th class="p-3 text-left">Action</th>
        </tr>
      </thead>
      <tbody>
        <?php while($row=$result->fetch_assoc()): ?>
        <tr class="border-b hover:bg-gray-50 dark:hover:bg-gray-700">
          <td class="p-3"><?= $row['id'] ?></td>
          <td class="p-3 font-medium"><?= htmlspecialchars($row['student']) ?></td>
          <td class="p-3"><?= htmlspecialchars($row['course']) ?></td>
          <td class="p-3">
            <?php if ($row['status'] == 'P'): ?>
              <span class="px-2 py-1 text-xs font-semibold bg-green-100 text-green-700 rounded">Present</span>
            <?php elseif ($row['status'] == 'A'): ?>
              <span class="px-2 py-1 text-xs font-semibold bg-red-100 text-red-700 rounded">Absent</span>
            <?php else: ?>
              <span class="px-2 py-1 text-xs font-semibold bg-yellow-100 text-yellow-700 rounded">Late</span>
            <?php endif; ?>
          </td>
          <td class="p-3"><?= date("d M Y, H:i", strtotime($row['created_at'])) ?></td>
          <td class="p-3 space-x-2">
            <a href="edit_attendance.php?id=<?= $row['id'] ?>" 
               class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600 text-xs">Edit</a>
            <a href="delete_attendance.php?id=<?= $row['id'] ?>" 
               class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600 text-xs"
               onclick="return confirm('Are you sure you want to delete this record?')">Delete</a>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include("../includes/footer.php"); ?>
