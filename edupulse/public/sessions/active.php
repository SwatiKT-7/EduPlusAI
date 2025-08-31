<?php
session_start();
require_once "../../config/db.php";
include("../../includes/auth.php");
check_role('faculty');

$faculty_id = $_SESSION['user_id'];

$sql = "SELECT s.id, c.code, c.name, s.session_date, s.status
        FROM sessions s
        JOIN classes cl ON s.class_id = cl.id
        JOIN courses c ON cl.course_id = c.id
        WHERE s.faculty_id = $faculty_id
        ORDER BY s.session_date DESC LIMIT 5";
$res = mysqli_query($conn, $sql);
?>
<?php include("../../includes/header.php"); ?>

<div class="max-w-5xl mx-auto bg-white dark:bg-gray-800 p-6 shadow rounded-2xl">
  <h2 class="text-2xl font-bold mb-6 text-indigo-600">📅 Recent Sessions</h2>

  <div class="overflow-x-auto">
    <table class="w-full border-collapse rounded-lg overflow-hidden shadow">
      <thead class="bg-indigo-50 dark:bg-gray-700 text-left">
        <tr>
          <th class="p-3">Course</th>
          <th class="p-3">Date</th>
          <th class="p-3">Status</th>
          <th class="p-3 text-center">Action</th>
        </tr>
      </thead>
      <tbody>
        <?php while($row = mysqli_fetch_assoc($res)): ?>
        <tr class="border-b hover:bg-gray-50 dark:hover:bg-gray-700">
          <td class="p-3 font-medium"><?= $row['code']." - ".$row['name'] ?></td>
          <td class="p-3"><?= date("d M Y, h:i A", strtotime($row['session_date'])) ?></td>
          <td class="p-3">
            <?php if($row['status']=='live'): ?>
              <span class="px-2 py-1 text-xs rounded bg-green-100 text-green-700 font-semibold">Live</span>
            <?php else: ?>
              <span class="px-2 py-1 text-xs rounded bg-gray-200 text-gray-600">Closed</span>
            <?php endif; ?>
          </td>
          <td class="p-3 text-center">
            <?php if($row['status']=='live'): ?>
              <a href="close.php?id=<?= $row['id']; ?>" 
                 class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 transition">
                Close
              </a>
            <?php else: ?>
              <span class="text-gray-400 italic">—</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include("../../includes/footer.php"); ?>
