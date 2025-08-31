<?php
session_start();
require_once "../../config/db.php";
include("../../includes/header.php");

if ($_SESSION['role'] != 'student') {
    header("Location: ../dashboard.php");
    exit;
}

$student_id = $_SESSION['user_id'];
$message = "";

// Handle attendance submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $session_id = intval($_POST['session_id']);
    $lat = floatval($_POST['lat']);
    $lng = floatval($_POST['lng']);
    $photo_url = null;

    // 📸 Decode base64 image from canvas
    if (!empty($_POST['captured_image'])) {
        $imgData = $_POST['captured_image'];
        $imgData = str_replace('data:image/png;base64,', '', $imgData);
        $imgData = str_replace(' ', '+', $imgData);
        $data = base64_decode($imgData);

        $dir = __DIR__ . "/../../public/uploads/attendance/";
        if (!is_dir($dir)) mkdir($dir, 0777, true);

        $fileName = "att_" . $student_id . "_" . time() . ".png";
        $filePath = $dir . $fileName;

        file_put_contents($filePath, $data);
        $photo_url = "uploads/attendance/" . $fileName;
    }

    // ✅ Fetch session + class details
    $sql = "SELECT s.id, s.class_id, cl.geo_lat, cl.geo_lng, cl.radius_m, cl.course_id
            FROM sessions s
            JOIN classes cl ON s.class_id = cl.id
            WHERE s.id = $session_id AND s.status='live'
              AND EXISTS (
                SELECT 1 FROM student_courses sc 
                WHERE sc.course_id = cl.course_id AND sc.student_id=$student_id
              )";
    $res = mysqli_query($conn, $sql);
    $session = mysqli_fetch_assoc($res);

    if ($session) {
        // 🌍 Haversine distance calculation
        $earthRadius = 6371000; // meters
        $lat1 = deg2rad($lat);
        $lon1 = deg2rad($lng);
        $lat2 = deg2rad($session['geo_lat']);
        $lon2 = deg2rad($session['geo_lng']);

        $dlat = $lat2 - $lat1;
        $dlon = $lon2 - $lon1;

        $a = sin($dlat/2) * sin($dlat/2) +
             cos($lat1) * cos($lat2) *
             sin($dlon/2) * sin($dlon/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        $meters = $earthRadius * $c;

        if ($meters <= $session['radius_m']) {
            $status = 'P'; $flag = 0; $reason = NULL;
        } else {
            $status = 'A'; $flag = 1; $reason = "Out of range";
        }

        // ✅ Insert or update attendance
        $sql = "INSERT INTO attendance_events 
                   (class_id, user_id, status, method, photo_url, lat, lng, anomaly_flag, anomaly_reason)
                VALUES ({$session['class_id']}, $student_id, '$status', 'photo', '$photo_url', $lat, $lng, $flag, " . ($reason ? "'$reason'" : "NULL") . ")
                ON DUPLICATE KEY UPDATE 
                   status=VALUES(status), 
                   photo_url=VALUES(photo_url), 
                   lat=VALUES(lat), 
                   lng=VALUES(lng), 
                   anomaly_flag=VALUES(anomaly_flag), 
                   anomaly_reason=VALUES(anomaly_reason)";
        if (mysqli_query($conn, $sql)) {
            $message = "Attendance marked: " . ($status == 'P' ? "✅ Present" : "❌ Absent");
        } else {
            $message = "DB Error: " . mysqli_error($conn);
        }
    } else {
        $message = "⚠️ Invalid session or not enrolled.";
    }
}

// Fetch active sessions for this student
$res_sessions = mysqli_query($conn, "
   SELECT s.id, c.code, c.name, s.session_date
   FROM sessions s
   JOIN classes cl ON s.class_id = cl.id
   JOIN courses c ON cl.course_id = c.id
   WHERE s.status='live'
   AND EXISTS (SELECT 1 FROM student_courses sc WHERE sc.course_id = cl.course_id AND sc.student_id=$student_id)
");
?>

<div class="max-w-lg mx-auto bg-white p-6 shadow rounded-lg">
  <h2 class="text-xl font-bold mb-4">Mark Attendance</h2>
  <?php if($message): ?>
    <p class="text-blue-600 font-semibold mb-3"><?php echo $message; ?></p>
  <?php endif; ?>

  <form method="POST" id="attendanceForm">
    <label class="block mb-2 font-medium">Active Session</label>
    <select name="session_id" class="w-full border p-2 rounded mb-4" required>
      <option value="">-- Choose Session --</option>
      <?php while($s=mysqli_fetch_assoc($res_sessions)): ?>
        <option value="<?php echo $s['id']; ?>">
          <?php echo $s['code']." - ".$s['name']." (".$s['session_date'].")"; ?>
        </option>
      <?php endwhile; ?>
    </select>

    <!-- Live Camera -->
    <label class="block mb-2 font-medium">Capture Photo</label>
    <video id="video" width="100%" autoplay class="border rounded mb-2"></video>
    <canvas id="canvas" style="display:none;"></canvas>
    <input type="hidden" name="captured_image" id="captured_image">

    <input type="hidden" id="lat" name="lat">
    <input type="hidden" id="lng" name="lng">

    <button type="button" onclick="capturePhoto()" 
            class="w-full bg-yellow-500 text-white py-2 rounded mb-2 hover:bg-yellow-600">
      📸 Capture Photo
    </button>

    <button type="submit" 
            class="w-full bg-green-600 text-white py-2 rounded hover:bg-green-700">
      Submit Attendance
    </button>
  </form>
</div>

<script>
// ✅ Open camera
navigator.mediaDevices.getUserMedia({ video: true })
  .then(stream => { document.getElementById('video').srcObject = stream; })
  .catch(err => { alert("Camera access denied: " + err); });

function capturePhoto() {
  let video = document.getElementById('video');
  let canvas = document.getElementById('canvas');
  canvas.width = video.videoWidth;
  canvas.height = video.videoHeight;
  canvas.getContext('2d').drawImage(video, 0, 0);
  document.getElementById('captured_image').value = canvas.toDataURL('image/png');
  alert("✅ Photo captured, now submit attendance.");
}

// ✅ Capture GPS before submit
document.getElementById("attendanceForm").addEventListener("submit", function(e){
  e.preventDefault();
  navigator.geolocation.getCurrentPosition(function(pos){
    document.getElementById("lat").value = pos.coords.latitude;
    document.getElementById("lng").value = pos.coords.longitude;
    e.target.submit();
  }, function(){ alert("⚠️ Location access denied"); });
});
</script>

<?php include("../../includes/footer.php"); ?>
