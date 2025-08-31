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

// Department-wise attendance stats
$sql = "SELECT d.name as dept, COUNT(ae.id) as total_marks, 
               SUM(ae.status='P') as present_marks, 
               ROUND(SUM(ae.status='P')/COUNT(ae.id)*100,2) as percent
        FROM attendance_events ae
        JOIN users u ON ae.user_id=u.id
        JOIN departments d ON u.dept_id=d.id
        GROUP BY d.id";
$result = $conn->query($sql);
?>

<div class="max-w-6xl mx-auto bg-white dark:bg-gray-800 p-6 mt-6 rounded-2xl shadow-lg">
  <h2 class="text-2xl font-bold mb-6 text-indigo-600">📊 Reports Center</h2>

  <div class="overflow-x-auto">
    <table class="w-full border-collapse text-sm rounded-lg overflow-hidden shadow">
      <thead class="bg-indigo-50 dark:bg-gray-700 text-left">
        <tr>
          <th class="p-3">Department</th>
          <th class="p-3">Total Records</th>
          <th class="p-3">Present</th>
          <th class="p-3">% Attendance</th>
        </tr>
      </thead>
      <tbody>
        <?php while($row=$result->fetch_assoc()): ?>
        <?php 
          $percent = floatval($row['percent']);
          $color = $percent >= 75 ? 'bg-green-500' : ($percent >= 50 ? 'bg-yellow-500' : 'bg-red-500');
        ?>
        <tr class="border-b hover:bg-gray-50 dark:hover:bg-gray-700">
          <td class="p-3 font-medium"><?= htmlspecialchars($row['dept']) ?></td>
          <td class="p-3"><?= $row['total_marks'] ?></td>
          <td class="p-3"><?= $row['present_marks'] ?></td>
          <td class="p-3">
            <div class="flex items-center gap-2">
              <span><?= $row['percent'] ?>%</span>
              <div class="flex-1 h-2 bg-gray-200 dark:bg-gray-600 rounded">
                <div class="h-2 rounded <?= $color ?>" style="width: <?= $percent ?>%"></div>
              </div>
            </div>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include("../includes/footer.php"); ?>
