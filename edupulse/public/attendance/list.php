<?php
session_start();
require_once "../../config/db.php";
include("../../includes/header.php");

if ($_SESSION['role'] != 'faculty') {
    header("Location: ../dashboard.php");
    exit;
}

$faculty_id = $_SESSION['user_id'];
$session_id = isset($_GET['session_id']) ? intval($_GET['session_id']) : 0;

// Get faculty sessions
$res_sessions = mysqli_query($conn, "SELECT s.id, c.code, c.name, s.session_date, s.status
                                     FROM sessions s
                                     JOIN courses c ON s.class_id = c.id
                                     WHERE s.faculty_id = $faculty_id
                                     ORDER BY s.session_date DESC");

$attendance = [];
if ($session_id) {
    $sql = "SELECT u.name, u.enrollment_no, ae.status, ae.photo_url, ae.lat, ae.lng, ae.anomaly_flag, ae.anomaly_reason
            FROM attendance_events ae
            JOIN users u ON ae.user_id = u.id
            WHERE ae.class_id = (SELECT class_id FROM sessions WHERE id=$session_id)";
    $res_att = mysqli_query($conn, $sql);
    while($row = mysqli_fetch_assoc($res_att)) $attendance[] = $row;
}
?>

<div class="max-w-5xl mx-auto bg-white p-6 shadow rounded-lg">
  <h2 class="text-xl font-bold mb-4">Attendance Records</h2>
  <form method="GET" class="mb-4">
    <label class="block mb-2 font-medium">Select Session</label>
    <select name="session_id" class="border p-2 rounded w-full mb-3" onchange="this.form.submit()">
      <option value="">-- Choose Session --</option>
      <?php while($s = mysqli_fetch_assoc($res_sessions)): ?>
        <option value="<?php echo $s['id']; ?>" <?php echo ($s['id']==$session_id?"selected":""); ?>>
          <?php echo $s['code']." - ".$s['name']." (".$s['session_date'].")"; ?>
        </option>
      <?php endwhile; ?>
    </select>
  </form>

  <?php if($session_id && count($attendance)>0): ?>
    <table class="w-full border-collapse border">
      <tr class="bg-gray-200">
        <th class="p-2 border">Name</th>
        <th class="p-2 border">Enroll No</th>
        <th class="p-2 border">Status</th>
        <th class="p-2 border">Photo</th>
        <th class="p-2 border">Location</th>
        <th class="p-2 border">Anomaly</th>
      </tr>
      <?php foreach($attendance as $a): ?>
      <tr>
        <td class="p-2 border"><?php echo $a['name']; ?></td>
        <td class="p-2 border"><?php echo $a['enrollment_no']; ?></td>
        <td class="p-2 border"><?php echo $a['status']=='P'?"<span class='text-green-600'>Present</span>":"<span class='text-red-600'>Absent</span>"; ?></td>
        <td class="p-2 border"><?php if($a['photo_url']): ?><img src="../<?php echo $a['photo_url']; ?>" class="h-12 rounded"><?php endif; ?></td>
        <td class="p-2 border text-sm"><?php echo $a['lat'].", ".$a['lng']; ?></td>
        <td class="p-2 border text-sm"><?php echo $a['anomaly_flag']?"⚠️ ".$a['anomaly_reason']:"-"; ?></td>
      </tr>
      <?php endforeach; ?>
    </table>
  <?php elseif($session_id): ?>
    <p class="text-red-500">No attendance found.</p>
  <?php endif; ?>
</div>

<?php include("../../includes/footer.php"); ?>
