<?php
session_start();
require_once "../../config/db.php";
include("../../includes/auth.php");
check_role('admin');

$dept_id   = isset($_GET['dept_id']) ? intval($_GET['dept_id']) : 0;
$course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;

// Departments
$deptRes = mysqli_query($conn, "SELECT * FROM departments");

// Courses
$coursesRes = [];
if ($dept_id) {
    $coursesRes = mysqli_query($conn, "SELECT * FROM courses WHERE dept_id=$dept_id");
}

// Assessments
$assessments = [];
if ($course_id) {
    $aRes = mysqli_query($conn, "SELECT a.*, c.code, c.name as course_name, d.name as dept_name 
                                 FROM assessments a
                                 JOIN courses c ON a.course_id=c.id
                                 JOIN departments d ON c.dept_id=d.id
                                 WHERE a.course_id=$course_id
                                 ORDER BY a.date DESC");
    while ($row = mysqli_fetch_assoc($aRes)) {
        $assessments[] = $row;
    }
}

// Export CSV
if (isset($_GET['export']) && $course_id) {
    header("Content-Type: text/csv");
    header("Content-Disposition: attachment; filename=course_{$course_id}_assessments.csv");

    $out = fopen("php://output", "w");
    fputcsv($out, ["Assessment ID", "Course", "Type", "Max Marks", "Weight", "Date"]);

    foreach ($assessments as $a) {
        fputcsv($out, [$a['id'], $a['course_name'], $a['type'], $a['max_marks'], $a['weight'], $a['date']]);
    }
    fclose($out);
    exit;
}
?>
<?php include("../../includes/header.php"); ?>
<?php include("includes/sidebar.php"); ?>

<div class="max-w-7xl mx-auto bg-white dark:bg-gray-800 p-6 mt-6 rounded-2xl shadow-lg">
  <h2 class="text-2xl font-bold mb-6 text-indigo-600">📊 Assessments Overview</h2>

  <!-- Filters -->
  <form method="GET" class="flex flex-wrap gap-4 mb-6">
    <!-- Department -->
    <div class="flex-1">
      <label class="block mb-2 font-medium">Department</label>
      <select name="dept_id" onchange="this.form.submit()"
              class="w-full border p-3 rounded-lg dark:bg-gray-700 dark:border-gray-600">
        <option value="">-- All --</option>
        <?php while($d = mysqli_fetch_assoc($deptRes)): ?>
          <option value="<?= $d['id'] ?>" <?= $dept_id==$d['id']?'selected':'' ?>>
            <?= htmlspecialchars($d['name']) ?>
          </option>
        <?php endwhile; ?>
      </select>
    </div>

    <!-- Course -->
    <?php if ($dept_id): ?>
    <div class="flex-1">
      <label class="block mb-2 font-medium">Course</label>
      <select name="course_id" onchange="this.form.submit()"
              class="w-full border p-3 rounded-lg dark:bg-gray-700 dark:border-gray-600">
        <option value="">-- Select Course --</option>
        <?php while($c = mysqli_fetch_assoc($coursesRes)): ?>
          <option value="<?= $c['id'] ?>" <?= $course_id==$c['id']?'selected':'' ?>>
            <?= $c['code']." - ".$c['name'] ?>
          </option>
        <?php endwhile; ?>
      </select>
    </div>
    <?php endif; ?>
  </form>

  <!-- Results -->
  <?php if ($course_id && count($assessments)): ?>
    <div class="flex justify-end mb-4">
      <a href="?dept_id=<?= $dept_id ?>&course_id=<?= $course_id ?>&export=1"
         class="bg-indigo-600 text-white px-4 py-2 rounded-lg shadow hover:bg-indigo-700">
        ⬇ Export CSV
      </a>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full border-collapse rounded-lg overflow-hidden shadow text-sm">
        <thead class="bg-indigo-50 dark:bg-gray-700">
          <tr>
            <th class="p-3 text-left">ID</th>
            <th class="p-3 text-left">Course</th>
            <th class="p-3 text-left">Type</th>
            <th class="p-3 text-left">Max Marks</th>
            <th class="p-3 text-left">Weight</th>
            <th class="p-3 text-left">Date</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($assessments as $a): ?>
          <tr class="border-b hover:bg-gray-50 dark:hover:bg-gray-700">
            <td class="p-3"><?= $a['id'] ?></td>
            <td class="p-3 font-medium"><?= htmlspecialchars($a['course_name']) ?> (<?= $a['code'] ?>)</td>
            <td class="p-3">
              <span class="px-2 py-1 rounded text-xs font-semibold bg-blue-100 text-blue-700">
                <?= ucfirst($a['type']) ?>
              </span>
            </td>
            <td class="p-3"><?= $a['max_marks'] ?></td>
            <td class="p-3"><?= $a['weight'] ?>%</td>
            <td class="p-3"><?= date("d M Y", strtotime($a['date'])) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php elseif ($course_id): ?>
    <p class="text-gray-500 mt-4">No assessments found for this course.</p>
  <?php endif; ?>
</div>

<?php include("../../includes/footer.php"); ?>
