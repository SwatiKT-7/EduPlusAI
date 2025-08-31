<?php
session_start();
require_once "../../config/db.php";
include("../../includes/auth.php");
check_role('faculty');

$faculty_id = $_SESSION['user_id'];

// Validate course
if (!isset($_GET['course_id'])) {
    die("❌ No course selected!");
}
$course_id = intval($_GET['course_id']);

// Fetch course details
$courseRes = mysqli_query($conn, "SELECT * FROM courses WHERE id=$course_id");
$course = mysqli_fetch_assoc($courseRes);

$message = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $type = mysqli_real_escape_string($conn, $_POST['type']);
    $max_marks = intval($_POST['max_marks']);
    $weight = intval($_POST['weight']);
    $date = mysqli_real_escape_string($conn, $_POST['date']);

    $sql = "INSERT INTO assessments (course_id, type, max_marks, weight, date)
            VALUES ($course_id, '$type', $max_marks, $weight, '$date')";
    if (mysqli_query($conn, $sql)) {
        header("Location: assessments.php?course_id=$course_id&msg=added");
        exit;
    } else {
        $message = "❌ Error: " . mysqli_error($conn);
    }
}
?>
<?php include("../../includes/header.php"); ?>

<div class="max-w-2xl mx-auto bg-white dark:bg-gray-800 p-6 mt-6 rounded-2xl shadow-lg">
  <h2 class="text-2xl font-bold mb-4 text-indigo-600">➕ Add Assessment</h2>
  <p class="mb-6 text-gray-600 dark:text-gray-400">
    For course: <b><?= $course['code']." - ".$course['name'] ?></b>
  </p>

  <?php if ($message): ?>
    <div class="mb-4 p-3 rounded-lg bg-red-100 text-red-700 text-sm">
      <?= $message ?>
    </div>
  <?php endif; ?>

  <form method="POST" class="space-y-4">
    <div>
      <label class="block mb-2 font-medium">Type</label>
      <select name="type" class="w-full p-3 border rounded-lg dark:bg-gray-700 dark:border-gray-600" required>
        <option value="quiz">Quiz</option>
        <option value="mid">Mid Exam</option>
        <option value="lab">Lab</option>
        <option value="assignment">Assignment</option>
      </select>
    </div>

    <div>
      <label class="block mb-2 font-medium">Max Marks</label>
      <input type="number" name="max_marks" 
             class="w-full p-3 border rounded-lg dark:bg-gray-700 dark:border-gray-600" 
             placeholder="e.g., 100" required>
    </div>

    <div>
      <label class="block mb-2 font-medium">Weight (%)</label>
      <input type="number" name="weight" 
             class="w-full p-3 border rounded-lg dark:bg-gray-700 dark:border-gray-600" 
             placeholder="e.g., 20" required>
    </div>

    <div>
      <label class="block mb-2 font-medium">Date</label>
      <input type="date" name="date" 
             class="w-full p-3 border rounded-lg dark:bg-gray-700 dark:border-gray-600" required>
    </div>

    <button type="submit" 
            class="w-full bg-indigo-600 text-white py-3 rounded-lg font-semibold hover:bg-indigo-700 transition transform hover:scale-105">
      💾 Save Assessment
    </button>
  </form>

  <div class="mt-6">
    <a href="assessments.php?course_id=<?= $course_id ?>" 
       class="text-indigo-600 hover:underline">⬅ Back to Assessments</a>
  </div>
</div>

<?php include("../../includes/footer.php"); ?>
