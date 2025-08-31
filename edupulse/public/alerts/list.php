<?php
session_start();
require_once "../../config/db.php";
include("../../includes/header.php");

// Only admin allowed
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../dashboard.php");
    exit;
}

// Fetch students with <75% attendance
$sql = "SELECT u.id, u.name, u.enrollment_no, c.code, c.name as course_name,
               COUNT(ae.id) as total_classes,
               SUM(ae.status='P') as present_classes,
               ROUND((SUM(ae.status='P')/COUNT(ae.id))*100,2) as attendance_pct
        FROM attendance_events ae
        JOIN users u ON ae.user_id = u.id
        JOIN classes cl ON ae.class_id = cl.id
        JOIN courses c ON cl.course_id = c.id
        WHERE u.role = 'student'
        GROUP BY u.id, u.name, u.enrollment_no, c.id
        HAVING attendance_pct < 75
        ORDER BY attendance_pct ASC";

$res = mysqli_query($conn, $sql);
?>

<div class="max-w-6xl mx-auto bg-white p-6 shadow rounded-lg">
  <h2 class="text-xl font-bold mb-4">Attendance Alerts (<75%)</h2>

  <?php if(mysqli_num_rows($res) > 0): ?>
    <table class="w-full border-collapse border">
      <tr class="bg-gray-200 text-left">
        <th class="p-2 border">Student</th>
        <th class="p-2 border">Enroll No</th>
        <th class="p-2 border">Course</th>
        <th class="p-2 border">Total Classes</th>
        <th class="p-2 border">Present</th>
        <th class="p-2 border">Attendance %</th>
      </tr>
      <?php while($row = mysqli_fetch_assoc($res)): ?>
      <tr>
        <td class="p-2 border"><?php echo $row['name']; ?></td>
        <td class="p-2 border"><?php echo $row['enrollment_no']; ?></td>
        <td class="p-2 border"><?php echo $row['code']." - ".$row['course_name']; ?></td>
        <td class="p-2 border"><?php echo $row['total_classes']; ?></td>
        <td class="p-2 border"><?php echo $row['present_classes']; ?></td>
        <td class="p-2 border text-red-600 font-bold"><?php echo $row['attendance_pct']."%"; ?></td>
      </tr>
      <?php endwhile; ?>
    </table>
  <?php else: ?>
    <p class="text-green-600 font-semibold">🎉 No students under 75% — all clear!</p>
  <?php endif; ?>
</div>

<?php include("../../includes/footer.php"); ?>
