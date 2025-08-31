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

// Fetch enrolled students
$sRes = mysqli_query($conn, "SELECT u.id, u.name, u.enrollment_no
                             FROM student_courses sc
                             JOIN users u ON sc.student_id=u.id
                             WHERE sc.course_id=$course_id AND u.role='student'");

// On form submit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    foreach ($_POST['marks'] as $student_id => $marks) {
        $marks = trim($marks);
        if ($marks === "") continue; // skip empty

        $marks = floatval($marks);

        // Check if score exists
        $check = mysqli_query($conn, "SELECT id FROM assessment_scores 
                                      WHERE assessment_id=$assessment_id 
                                      AND user_id=$student_id");
        if (mysqli_num_rows($check) > 0) {
            // Update
            mysqli_query($conn, "UPDATE assessment_scores 
                                 SET marks=$marks 
                                 WHERE assessment_id=$assessment_id 
                                 AND user_id=$student_id");
        } else {
            // Insert
            mysqli_query($conn, "INSERT INTO assessment_scores (assessment_id, user_id, marks) 
                                 VALUES ($assessment_id, $student_id, $marks)");
        }
    }
    header("Location: view_scores.php?id=$assessment_id&msg=saved");
    exit;
}
?>
<?php include("../../includes/header.php"); ?>


<div class="container">
    <h2>✏️ Enter Marks for <?= $assessment['type'] ?> (<?= $assessment['course_name'] ?>)</h2>
    <form method="POST">
        <table border="1" cellpadding="8">
            <tr>
                <th>Enrollment No</th>
                <th>Name</th>
                <th>Marks (out of <?= $assessment['max_marks'] ?>)</th>
            </tr>
            <?php while($s = mysqli_fetch_assoc($sRes)): 
                // fetch existing mark if any
                $existing = mysqli_query($conn, "SELECT marks FROM assessment_scores 
                                                 WHERE assessment_id=$assessment_id 
                                                 AND user_id=".$s['id']);
                $row = mysqli_fetch_assoc($existing);
            ?>
            <tr>
                <td><?= $s['enrollment_no'] ?? '-' ?></td>
                <td><?= $s['name'] ?></td>
                <td>
                    <input type="number" step="0.01" 
                           name="marks[<?= $s['id'] ?>]" 
                           value="<?= $row ? $row['marks'] : '' ?>" 
                           max="<?= $assessment['max_marks'] ?>" 
                           min="0">
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
        <br>
        <button type="submit">💾 Save Marks</button>
    </form>
    <br>
    <a href="assessments.php?course_id=<?= $course_id ?>">⬅ Back to Assessments</a>
</div>

<?php include("../../includes/footer.php"); ?>
