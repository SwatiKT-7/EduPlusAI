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

// Reverse geocoding function (Nominatim)
function getAddress($lat,$lng){
    if(!$lat || !$lng) return null;
    $url = "https://nominatim.openstreetmap.org/reverse?format=json&lat=$lat&lon=$lng";
    $opts = ["http" => ["header" => "User-Agent: EduPulseApp/1.0"]];
    $context = stream_context_create($opts);
    $json = @file_get_contents($url,false,$context);
    if($json){
        $data = json_decode($json,true);
        return $data['display_name'] ?? null;
    }
    return null;
}

// Handle manual status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $student_id = intval($_POST['student_id']);
    $session_id = intval($_POST['session_id']);
    $new_status = $_POST['new_status'] === 'P' ? 'P' : 'A';

    $classRes = mysqli_query($conn,"SELECT class_id FROM sessions WHERE id=$session_id");
    $classRow = mysqli_fetch_assoc($classRes);
    $class_id = $classRow['class_id'];

    $sql = "UPDATE attendance_events 
            SET status='$new_status'
            WHERE user_id=$student_id AND class_id=$class_id";
    mysqli_query($conn,$sql);
}

// Faculty’s sessions
$res_sessions = mysqli_query($conn, "
    SELECT s.id, c.code, c.name, s.session_date, cl.geo_lat as f_lat, cl.geo_lng as f_lng
    FROM sessions s
    JOIN classes cl ON s.class_id = cl.id
    JOIN courses c ON cl.course_id = c.id
    WHERE s.faculty_id = $faculty_id
    ORDER BY s.session_date DESC
");

$attendance = [];
$faculty_lat = null; $faculty_lng = null;
$faculty_address = null;

if ($session_id) {
    $classInfo = mysqli_query($conn, "
        SELECT cl.geo_lat, cl.geo_lng
        FROM sessions s
        JOIN classes cl ON s.class_id=cl.id
        WHERE s.id=$session_id
    ");
    if ($ci = mysqli_fetch_assoc($classInfo)) {
        $faculty_lat = $ci['geo_lat'];
        $faculty_lng = $ci['geo_lng'];
        $faculty_address = getAddress($faculty_lat,$faculty_lng);
    }

    $sql = "SELECT u.id as student_id, u.name, u.email, 
                   ae.status, ae.photo_url, ae.lat, ae.lng, 
                   ae.anomaly_flag, ae.anomaly_reason, ae.created_at
            FROM attendance_events ae
            JOIN users u ON ae.user_id = u.id
            JOIN sessions s ON ae.class_id = s.class_id
            WHERE s.id = $session_id
            ORDER BY ae.created_at ASC";
    $attendance = mysqli_query($conn, $sql);
}

// Distance calc
function calcDistance($lat1, $lon1, $lat2, $lon2) {
    if(!$lat1 || !$lon1 || !$lat2 || !$lon2) return null;
    $theta = $lon1 - $lon2;
    $dist = sin(deg2rad($lat1))*sin(deg2rad($lat2)) +
            cos(deg2rad($lat1))*cos(deg2rad($lat2))*cos(deg2rad($theta));
    $dist = acos(min(max($dist, -1), 1));
    $dist = rad2deg($dist);
    $km = $dist * 60 * 1.1515 * 1.609344;
    return round($km*1000);
}
?>

<div class="max-w-7xl mx-auto bg-white p-6 shadow rounded-lg">
  <h2 class="text-2xl font-bold mb-4">Faculty Attendance Report</h2>

  <!-- Session Selector -->
  <form method="GET" class="mb-6">
    <label class="block mb-2 font-medium">Select Session</label>
    <select name="session_id" class="w-full border p-2 rounded mb-4" onchange="this.form.submit()">
      <option value="">-- Choose Session --</option>
      <?php while($s = mysqli_fetch_assoc($res_sessions)): ?>
        <option value="<?php echo $s['id']; ?>" <?php if($session_id==$s['id']) echo "selected"; ?>>
          <?php echo $s['code']." - ".$s['name']." (".$s['session_date'].")"; ?>
        </option>
      <?php endwhile; ?>
    </select>
  </form>

  <?php if ($session_id && $attendance && mysqli_num_rows($attendance)>0): ?>
    <p class="mb-3 text-sm text-gray-600">
      <strong>Faculty Location:</strong> (<?php echo $faculty_lat.", ".$faculty_lng; ?>)<br>
      <?php echo $faculty_address ? "<em>$faculty_address</em>" : ""; ?>
    </p>

    <div class="overflow-x-auto">
      <table class="w-full border-collapse border text-sm">
        <thead class="bg-gray-100">
          <tr>
            <th class="p-2 border">Student</th>
            <th class="p-2 border">Email</th>
            <th class="p-2 border">Status</th>
            <th class="p-2 border">Change</th>
            <th class="p-2 border">Photo</th>
            <th class="p-2 border">Student Location</th>
            <th class="p-2 border">Distance (m)</th>
            <th class="p-2 border">Map</th>
            <th class="p-2 border">Marked At</th>
          </tr>
        </thead>
        <tbody>
        <?php while($a=mysqli_fetch_assoc($attendance)): ?>
          <?php 
            $distance = calcDistance($faculty_lat,$faculty_lng,$a['lat'],$a['lng']); 
            $student_address = getAddress($a['lat'],$a['lng']);
          ?>
          <tr class="hover:bg-gray-50">
            <td class="p-2 border font-medium"><?php echo $a['name']; ?></td>
            <td class="p-2 border"><?php echo $a['email']; ?></td>
            <td class="p-2 border">
              <?php echo ($a['status']=='P' ? "<span class='text-green-600 font-bold'>Present</span>" : "<span class='text-red-600 font-bold'>Absent</span>"); ?>
              <?php if($a['anomaly_flag']): ?>
                <span class="text-yellow-600 text-xs">⚠ <?php echo $a['anomaly_reason']; ?></span>
              <?php endif; ?>
            </td>
            <td class="p-2 border">
              <form method="POST">
                <input type="hidden" name="student_id" value="<?php echo $a['student_id']; ?>">
                <input type="hidden" name="session_id" value="<?php echo $session_id; ?>">
                <select name="new_status" class="border p-1 rounded text-sm">
                  <option value="P" <?php if($a['status']=='P') echo "selected"; ?>>Present</option>
                  <option value="A" <?php if($a['status']=='A') echo "selected"; ?>>Absent</option>
                </select>
                <button type="submit" name="update_status" class="ml-1 text-blue-600 underline text-xs">Update</button>
              </form>
            </td>
            <td class="p-2 border text-center">
              <?php if($a['photo_url']): ?>
                <a href="../../public/<?php echo $a['photo_url']; ?>" target="_blank">
                  <img src="../../public/<?php echo $a['photo_url']; ?>" class="h-16 w-16 object-cover rounded shadow">
                </a>
              <?php else: ?>
                <span class="text-gray-500">No photo</span>
              <?php endif; ?>
            </td>
            <td class="p-2 border text-xs">
              <?php echo "(".$a['lat'].", ".$a['lng'].")"; ?><br>
              <?php echo $student_address ? "<em>$student_address</em>" : ""; ?>
            </td>
            <td class="p-2 border text-center">
              <?php echo $distance!==null ? $distance." m" : "N/A"; ?>
            </td>
            <td class="p-2 border">
              <?php if($a['lat'] && $a['lng']): ?>
                <a href="https://www.google.com/maps?q=<?php echo $a['lat']; ?>,<?php echo $a['lng']; ?>" target="_blank" class="text-blue-600 underline">View</a>
              <?php else: ?>
                <span class="text-gray-500">N/A</span>
              <?php endif; ?>
            </td>
            <td class="p-2 border"><?php echo $a['created_at']; ?></td>
          </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  <?php elseif ($session_id): ?>
    <p class="text-red-500">❌ No attendance records found for this session.</p>
  <?php endif; ?>
</div>
