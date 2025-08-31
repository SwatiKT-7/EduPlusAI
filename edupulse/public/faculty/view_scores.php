<?php
session_start();
require_once "../../config/db.php";
include("../../includes/auth.php");
check_role('faculty');

$faculty_id = $_SESSION['user_id'];

// Get assessment ID
if (!isset($_GET['id'])) {
    die("❌ No assessment selected!");
}
$assessment_id = intval($_GET['id']);

// Fetch assessment details
$aRes = mysqli_query($conn, "SELECT a.*, c.name as course_name, c.code 
                             FROM assessments a
                             JOIN courses c ON a.course_id=c.id
                             WHERE a.id=$assessment_id");
$assessment = mysqli_fetch_assoc($aRes);
if (!$assessment) {
    die("❌ Assessment not found!");
}
$course_id = $assessment['course_id'];

// Fetch scores + student names
$scoresRes = mysqli_query($conn, "SELECT u.id, u.name, u.enrollment_no, s.marks
                                  FROM student_courses sc
                                  JOIN users u ON sc.student_id=u.id
                                  LEFT JOIN assessment_scores s 
                                     ON s.user_id=u.id AND s.assessment_id=$assessment_id
                                  WHERE sc.course_id=$course_id AND u.role='student'
                                  ORDER BY u.name ASC");

// Collect stats
$marks = [];
while($row = mysqli_fetch_assoc($scoresRes)) {
    if ($row['marks'] !== null) {
        $marks[] = $row['marks'];
    }
    $students[] = $row;
}

$average = count($marks) ? round(array_sum($marks) / count($marks), 2) : 0;
$highest = count($marks) ? max($marks) : 0;
$lowest  = count($marks) ? min($marks) : 0;
?>
<?php include("../../includes/header.php"); ?>


<div class="container">
    <h2>📊 Scores for <?= ucfirst($assessment['type']) ?> (<?= $assessment['course_name'] ?>)</h2>

    <p><b>Max Marks:</b> <?= $assessment['max_marks'] ?> |
       <b>Weight:</b> <?= $assessment['weight'] ?>% |
       <b>Date:</b> <?= $assessment['date'] ?></p>

    <h3>Class Analytics</h3>
    <ul>
        <li>📈 Average: <?= $average ?></li>
        <li>🏆 Highest: <?= $highest ?></li>
        <li>⬇️ Lowest: <?= $lowest ?></li>
    </ul>

    <h3>Student Scores</h3>
    <table border="1" cellpadding="8">
        <tr>
            <th>Enrollment No</th>
            <th>Name</th>
            <th>Marks</th>
        </tr>
        <?php foreach($students as $s): ?>
        <tr>
            <td><?= $s['enrollment_no'] ?? '-' ?></td>
            <td><?= $s['name'] ?></td>
            <td><?= $s['marks'] !== null ? $s['marks'] : '<i>Not entered</i>' ?></td>
        </tr>
        <?php endforeach; ?>
    </table>

    <br>
    <a href="assessments.php?course_id=<?= $course_id ?>">⬅ Back to Assessments</a>
</div>

<?php include("../../includes/footer.php"); ?>
