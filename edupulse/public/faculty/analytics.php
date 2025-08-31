<?php
session_start();
require_once "../../config/db.php";
include("../../includes/auth.php");
check_role('faculty');

$faculty_id = $_SESSION['user_id'];

// Get courses taught by this faculty
$coursesRes = mysqli_query($conn, "SELECT c.id, c.code, c.name 
                                   FROM faculty_courses fc
                                   JOIN courses c ON fc.course_id=c.id
                                   WHERE fc.faculty_id=$faculty_id");

$selected_course = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;

// Fetch student metrics
$metrics = [];
if ($selected_course) {
    $mRes = mysqli_query($conn, "SELECT u.name, u.enrollment_no, m.*
                                 FROM student_metrics m
                                 JOIN users u ON m.user_id=u.id
                                 WHERE m.course_id=$selected_course");
    while ($row = mysqli_fetch_assoc($mRes)) {
        $metrics[] = $row;
    }
}
?>
<?php include("../../includes/header.php"); ?>

<div class="max-w-6xl mx-auto bg-white dark:bg-gray-800 p-6 mt-6 rounded-2xl shadow-lg">
  <h2 class="text-2xl font-bold mb-6 text-indigo-600">📊 Student Analytics</h2>

  <!-- Course Selector -->
  <form method="GET" class="mb-6">
    <label class="block mb-2 font-medium">Select Course:</label>
    <select name="course_id" onchange="this.form.submit()" 
            class="w-full p-3 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
      <option value="">-- Choose Course --</option>
      <?php while($c = mysqli_fetch_assoc($coursesRes)): ?>
        <option value="<?= $c['id'] ?>" <?= $selected_course==$c['id']?'selected':'' ?>>
          <?= $c['code']." - ".$c['name'] ?>
        </option>
      <?php endwhile; ?>
    </select>
  </form>

  <?php if ($selected_course && count($metrics)): ?>
    <div class="overflow-x-auto">
      <table class="w-full border-collapse rounded-lg overflow-hidden shadow text-sm">
        <thead class="bg-indigo-50 dark:bg-gray-700">
          <tr>
            <th class="p-3 text-left">Enrollment No</th>
            <th class="p-3 text-left">Name</th>
            <th class="p-3 text-left">Attendance %</th>
            <th class="p-3 text-left">Trend</th>
            <th class="p-3 text-left">Risk</th>
            <th class="p-3 text-left">Projection</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($metrics as $m): ?>
          <tr class="border-b hover:bg-gray-50 dark:hover:bg-gray-700">
            <td class="p-3"><?= htmlspecialchars($m['enrollment_no']) ?></td>
            <td class="p-3 font-medium"><?= htmlspecialchars($m['name']) ?></td>
            <td class="p-3">
              <span class="px-2 py-1 rounded text-xs font-semibold 
                <?= $m['attn_cum'] >= 75 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' ?>">
                <?= $m['attn_cum'] ?>%
              </span>
            </td>
            <td class="p-3"><?= $m['attn_trend'] ?></td>
            <td class="p-3">
              <span class="px-2 py-1 rounded text-xs font-semibold 
                <?= $m['risk_score'] >= 7 ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700' ?>">
                <?= $m['risk_score'] ?>/10
              </span>
            </td>
            <td class="p-3"><?= $m['term_attn_projection'] ?>%</td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php elseif ($selected_course): ?>
    <p class="text-gray-500 mt-4">No student metrics found for this course.</p>
  <?php endif; ?>
</div>

<?php include("../../includes/footer.php"); ?>
