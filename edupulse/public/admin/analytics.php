<?php
session_start();
require_once "../../config/db.php";
include("../../includes/auth.php");
check_role('admin');

$dept_id = isset($_GET['dept_id']) ? intval($_GET['dept_id']) : 0;

// Fetch departments
$deptRes = mysqli_query($conn, "SELECT * FROM departments");

// Fetch analytics for department
$metrics = [];
if ($dept_id) {
    $mRes = mysqli_query($conn, "SELECT u.name, u.enrollment_no, c.code, c.name as course_name, m.*
                                 FROM student_metrics m
                                 JOIN users u ON m.user_id=u.id
                                 JOIN courses c ON m.course_id=c.id
                                 WHERE c.dept_id=$dept_id");
    while ($row = mysqli_fetch_assoc($mRes)) {
        $metrics[] = $row;
    }
}
?>
<?php include("../../includes/header.php"); ?>
<?php include("includes/sidebar.php"); ?>

<div class="max-w-7xl mx-auto bg-white dark:bg-gray-800 p-6 mt-6 rounded-2xl shadow-lg">
  <h2 class="text-2xl font-bold mb-6 text-indigo-600">🏫 Department Analytics</h2>

  <!-- Department Selector -->
  <form method="GET" class="mb-6">
    <label class="block mb-2 font-medium">Select Department:</label>
    <select name="dept_id" onchange="this.form.submit()" 
            class="w-full p-3 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
      <option value="">-- All --</option>
      <?php while($d = mysqli_fetch_assoc($deptRes)): ?>
        <option value="<?= $d['id'] ?>" <?= $dept_id==$d['id']?'selected':'' ?>>
          <?= htmlspecialchars($d['name']) ?>
        </option>
      <?php endwhile; ?>
    </select>
  </form>

  <?php if ($dept_id && count($metrics)): ?>
    <div class="overflow-x-auto">
      <table class="w-full border-collapse rounded-lg overflow-hidden shadow text-sm">
        <thead class="bg-indigo-50 dark:bg-gray-700">
          <tr>
            <th class="p-3 text-left">Enrollment No</th>
            <th class="p-3 text-left">Name</th>
            <th class="p-3 text-left">Course</th>
            <th class="p-3 text-left">Attendance %</th>
            <th class="p-3 text-left">Risk Score</th>
            <th class="p-3 text-left">Projection</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($metrics as $m): ?>
          <tr class="border-b hover:bg-gray-50 dark:hover:bg-gray-700">
            <td class="p-3"><?= htmlspecialchars($m['enrollment_no']) ?></td>
            <td class="p-3 font-medium"><?= htmlspecialchars($m['name']) ?></td>
            <td class="p-3"><?= htmlspecialchars($m['course_name']) ?> (<?= $m['code'] ?>)</td>
            <td class="p-3">
              <span class="px-2 py-1 rounded text-xs font-semibold 
                <?= $m['attn_cum'] >= 75 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
                <?= $m['attn_cum'] ?>%
              </span>
            </td>
            <td class="p-3">
              <span class="px-2 py-1 rounded text-xs font-semibold 
                <?= $m['risk_score'] >= 7 ? 'bg-red-100 text-red-700' : 
                   ($m['risk_score'] >= 4 ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700') ?>">
                <?= $m['risk_score'] ?>/10
              </span>
            </td>
            <td class="p-3"><?= $m['term_attn_projection'] ?>%</td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php elseif ($dept_id): ?>
    <p class="text-gray-500 mt-4">No analytics data found for this department.</p>
  <?php endif; ?>
</div>

<?php include("../../includes/footer.php"); ?>
