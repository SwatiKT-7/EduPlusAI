<?php
session_start();
require_once "../../config/db.php";
include("../includes/auth.php");
check_role('admin');

$sql = "SELECT a.id, u.name as user, c.name as course, a.type, a.severity, a.message, a.created_at, a.resolved_at
        FROM alerts a
        JOIN users u ON a.user_id=u.id
        LEFT JOIN courses c ON a.course_id=c.id
        ORDER BY a.created_at DESC LIMIT 50";
$result = $conn->query($sql);
?>
<?php include("../includes/header.php"); ?>
<?php include("includes/sidebar.php"); ?>

<div class="max-w-7xl mx-auto bg-white dark:bg-gray-800 p-6 mt-6 rounded-2xl shadow-lg">
  <h2 class="text-2xl font-bold mb-6 text-indigo-600">🚨 System Alerts</h2>

  <div class="overflow-x-auto">
    <table class="w-full border-collapse rounded-lg overflow-hidden shadow text-sm">
      <thead class="bg-indigo-50 dark:bg-gray-700">
        <tr>
          <th class="p-3 text-left">#</th>
          <th class="p-3 text-left">User</th>
          <th class="p-3 text-left">Course</th>
          <th class="p-3 text-left">Type</th>
          <th class="p-3 text-left">Severity</th>
          <th class="p-3 text-left">Message</th>
          <th class="p-3 text-left">Date</th>
          <th class="p-3 text-left">Status</th>
        </tr>
      </thead>
      <tbody>
        <?php while($row=$result->fetch_assoc()): ?>
        <tr class="border-b hover:bg-gray-50 dark:hover:bg-gray-700">
          <td class="p-3"><?= $row['id'] ?></td>
          <td class="p-3 font-medium"><?= htmlspecialchars($row['user']) ?></td>
          <td class="p-3"><?= $row['course'] ?? '-' ?></td>
          <td class="p-3"><?= ucfirst($row['type']) ?></td>
          <td class="p-3">
            <span class="px-2 py-1 rounded text-xs font-semibold 
              <?= $row['severity']=='high' ? 'bg-red-100 text-red-700' : 
                 ($row['severity']=='medium' ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700') ?>">
              <?= ucfirst($row['severity']) ?>
            </span>
          </td>
          <td class="p-3 text-gray-700 dark:text-gray-300"><?= htmlspecialchars($row['message']) ?></td>
          <td class="p-3"><?= date("d M Y, h:i A", strtotime($row['created_at'])) ?></td>
          <td class="p-3">
            <?php if($row['resolved_at']): ?>
              <span class="px-2 py-1 rounded text-xs font-semibold bg-green-100 text-green-700">Resolved</span>
            <?php else: ?>
              <span class="px-2 py-1 rounded text-xs font-semibold bg-yellow-100 text-yellow-700">Pending</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include("../includes/footer.php"); ?>
