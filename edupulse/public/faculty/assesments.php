<?php
session_start();
require_once "../../config/db.php";
include("../../includes/auth.php");
check_role('faculty');

$faculty_id = $_SESSION['user_id'];

// Fetch all courses assigned to this faculty
$sql = "SELECT c.id, c.code, c.name 
        FROM faculty_courses fc
        JOIN courses c ON fc.course_id = c.id
        WHERE fc.faculty_id = $faculty_id";
$courses = $conn->query($sql);

// If course filter applied
$selected_course = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;

$assessments = [];
if ($selected_course) {
    $a_sql = "SELECT a.*, c.code, c.name as course_name 
              FROM assessments a
              JOIN courses c ON a.course_id=c.id
              WHERE a.course_id=$selected_course
              ORDER BY a.date DESC";
    $assessments = $conn->query($a_sql);
}
?>
<?php include("../../includes/header.php"); ?>


<div class="container">
    <h2>📊 Manage Assessments</h2>

    <form method="GET">
        <label>Select Course:</label>
        <select name="course_id" onchange="this.form.submit()">
            <option value="">-- Choose Course --</option>
            <?php while($c = $courses->fetch_assoc()): ?>
                <option value="<?= $c['id'] ?>" <?= $selected_course==$c['id']?'selected':'' ?>>
                    <?= $c['code']." - ".$c['name'] ?>
                </option>
            <?php endwhile; ?>
        </select>
    </form>

    <br>
    <?php if ($selected_course): ?>
        <a href="add_assessment.php?course_id=<?= $selected_course ?>">➕ Add Assessment</a>
        <br><br>
        <table border="1" cellpadding="8">
            <tr>
                <th>ID</th>
                <th>Type</th>
                <th>Max Marks</th>
                <th>Weight</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
            <?php while($a = $assessments->fetch_assoc()): ?>
            <tr>
                <td><?= $a['id'] ?></td>
                <td><?= ucfirst($a['type']) ?></td>
                <td><?= $a['max_marks'] ?></td>
                <td><?= $a['weight'] ?>%</td>
                <td><?= $a['date'] ?></td>
                <td>
                    <a href="enter_scores.php?id=<?= $a['id'] ?>">✏️ Enter Marks</a> | 
                    <a href="view_scores.php?id=<?= $a['id'] ?>">📊 View Scores</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    <?php endif; ?>
</div>

<?php include("../../includes/footer.php"); ?>
