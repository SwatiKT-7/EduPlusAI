<?php
session_start();
require_once "../../config/db.php";
include("../../includes/header.php");

if ($_SESSION['role'] != 'student') {
    header("Location: ../dashboard.php");
    exit;
}

$student_id = $_SESSION['user_id'];

// ✅ Fetch student’s enrolled courses
$res_courses = mysqli_query($conn, "
    SELECT c.id, c.code, c.name 
    FROM student_courses sc
    JOIN courses c ON sc.course_id = c.id
    WHERE sc.student_id = $student_id
");

$selected_course = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;

// ✅ Fetch overall stats
$stats = [];
if ($selected_course) {
    $stats = mysqli_query($conn, "
        SELECT c.code, c.name,
               COUNT(ae.id) as total_classes,
               SUM(ae.status='P') as present_classes,
               ROUND((SUM(ae.status='P')/COUNT(ae.id))*100,2) as pct
        FROM attendance_events ae
        JOIN classes cl ON ae.class_id = cl.id
        JOIN courses c ON cl.course_id = c.id
        WHERE ae.user_id = $student_id
          AND c.id = $selected_course
        GROUP BY c.id, c.code, c.name
    ");
}

// ✅ Fetch detailed records
$records = [];
if ($selected_course) {
    $records = mysqli_query($conn, "
        SELECT ae.id, ae.status, ae.lat, ae.lng, ae.photo_url, ae.created_at,
               c.code, c.name
        FROM attendance_events ae
        JOIN classes cl ON ae.class_id = cl.id
        JOIN courses c ON cl.course_id = c.id
        WHERE ae.user_id = $student_id
          AND c.id = $selected_course
        ORDER BY ae.created_at DESC
    ");
}
?>

<div class="max-w-5xl mx-auto bg-white p-6 shadow rounded-lg">
  <h2 class="text-2xl font-bold mb-4">📊 My Attendance Report</h2>

  <!-- Course Filter -->
  <form method="GET" class="mb-4">
    <label class="block mb-2 font-medium">Select Course</label>
    <select name="course_id" class="w-full border p-2 rounded" onchange="this.form.submit()">
      <option value="">-- Choose Course --</option>
      <?php while($c=mysqli_fetch_assoc($res_courses)): ?>
        <option value="<?php echo $c['id']; ?>" <?php if($selected_course==$c['id']) echo "selected"; ?>>
          <?php echo $c['code']." - ".$c['name']; ?>
        </option>
      <?php endwhile; ?>
    </select>
  </form>

  <!-- Summary -->
  <?php if($selected_course && $stats && mysqli_num_rows($stats)>0): 
        $s = mysqli_fetch_assoc($stats); ?>
    <div class="mb-4 p-4 bg-gray-100 rounded-lg">
      <p><b>Course:</b> <?php echo $s['code']." - ".$s['name']; ?></p>
      <p><b>Total Classes:</b> <?php echo $s['total_classes']; ?></p>
      <p><b>Present:</b> <?php echo $s['present_classes']; ?></p>
      <p><b>Attendance %:</b> 
        <span class="<?php echo $s['pct']>=75 ? 'text-green-600 font-bold':'text-red-600 font-bold'; ?>">
          <?php echo $s['pct']."%"; ?>
        </span>
      </p>
    </div>
  <?php endif; ?>

  <!-- Detailed Records -->
  <?php if($selected_course && $records && mysqli_num_rows($records)>0): ?>
    <table class="w-full border-collapse border">
      <thead>
        <tr class="bg-gray-200">
          <th class="p-2 border">Date</th>
          <th class="p-2 border">Status</th>
          <th class="p-2 border">Latitude</th>
          <th class="p-2 border">Longitude</th>
          <th class="p-2 border">Photo</th>
        </tr>
      </thead>
      <tbody>
        <?php while($r=mysqli_fetch_assoc($records)): ?>
        <tr>
          <td class="p-2 border"><?php echo $r['created_at']; ?></td>
          <td class="p-2 border">
            <?php echo $r['status']=='P' ? 
              "<span class='text-green-600 font-bold'>Present</span>" :
              "<span class='text-red-600 font-bold'>Absent</span>"; ?>
          </td>
          <td class="p-2 border"><?php echo $r['lat'] ?: '-'; ?></td>
          <td class="p-2 border"><?php echo $r['lng'] ?: '-'; ?></td>
          <td class="p-2 border text-center">
            <?php if($r['photo_url']): ?>
              <a href="../../public/<?php echo $r['photo_url']; ?>" target="_blank">
                <img src="../../public/<?php echo $r['photo_url']; ?>" 
                     class="w-16 h-16 object-cover rounded hover:scale-110 transition">
              </a>
            <?php else: ?> - <?php endif; ?>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  <?php elseif($selected_course): ?>
    <p class="text-gray-500">⚠️ No attendance records yet for this course.</p>
  <?php endif; ?>
</div>

<?php include("../../includes/footer.php"); ?>
