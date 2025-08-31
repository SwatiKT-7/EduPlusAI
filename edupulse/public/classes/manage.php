<?php
session_start();
require_once "../../config/db.php";
include("../../includes/header.php");
include("../../includes/auth.php");
check_role('admin');

$message = "";

// Insert new class
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $course_id  = intval($_POST['course_id']);
    $faculty_id = intval($_POST['faculty_id']);
    $room       = mysqli_real_escape_string($conn, $_POST['room']);
    $geo_lat    = floatval($_POST['geo_lat']);
    $geo_lng    = floatval($_POST['geo_lng']);
    $radius     = intval($_POST['radius_m']);

    $sql = "INSERT INTO classes (tenant_id, course_id, faculty_id, room, geo_lat, geo_lng, radius_m, status, mode)
            VALUES (1, $course_id, $faculty_id, '$room', $geo_lat, $geo_lng, $radius, 'scheduled', 'offline')";
    if (mysqli_query($conn, $sql)) {
        $message = "✅ Class assigned successfully!";
    } else {
        $message = "❌ Error: " . mysqli_error($conn);
    }
}

// Fetch data
$courses = mysqli_query($conn, "SELECT id, code, name FROM courses");
$faculty = mysqli_query($conn, "SELECT id, name FROM users WHERE role='faculty'");
$classes = mysqli_query($conn, "SELECT cl.id, c.code, c.name as course, u.name as faculty, cl.room, cl.radius_m
                                FROM classes cl
                                JOIN courses c ON cl.course_id = c.id
                                JOIN users u ON cl.faculty_id = u.id");
?>

<div class="max-w-5xl mx-auto bg-white p-6 shadow rounded-lg">
  <h2 class="text-xl font-bold mb-4">Manage Classes</h2>

  <?php if($message): ?>
    <p class="mb-3 <?php echo strpos($message,'✅')!==false?'text-green-600':'text-red-600'; ?>">
      <?php echo $message; ?>
    </p>
  <?php endif; ?>

  <form method="POST" class="mb-6">
    <div class="grid grid-cols-2 gap-4">
      <div>
        <label class="block font-medium">Course</label>
        <select name="course_id" class="w-full border p-2 rounded" required>
          <?php while($c = mysqli_fetch_assoc($courses)): ?>
            <option value="<?php echo $c['id']; ?>"><?php echo $c['code']." - ".$c['name']; ?></option>
          <?php endwhile; ?>
        </select>
      </div>
      <div>
        <label class="block font-medium">Faculty</label>
        <select name="faculty_id" class="w-full border p-2 rounded" required>
          <?php while($f = mysqli_fetch_assoc($faculty)): ?>
            <option value="<?php echo $f['id']; ?>"><?php echo $f['name']; ?></option>
          <?php endwhile; ?>
        </select>
      </div>
      <div>
        <label class="block font-medium">Room</label>
        <input type="text" name="room" class="w-full border p-2 rounded" required>
      </div>
      <div>
        <label class="block font-medium">Latitude</label>
        <input type="text" name="geo_lat" class="w-full border p-2 rounded" value="28.6139" required>
      </div>
      <div>
        <label class="block font-medium">Longitude</label>
        <input type="text" name="geo_lng" class="w-full border p-2 rounded" value="77.2090" required>
      </div>
      <div>
        <label class="block font-medium">Radius (m)</label>
        <input type="number" name="radius_m" class="w-full border p-2 rounded" value="100" required>
      </div>
    </div>
    <button type="submit" class="mt-4 w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700">
      Assign Class
    </button>
  </form>

  <h3 class="text-lg font-bold mb-2">Existing Classes</h3>
  <table class="w-full border-collapse border">
    <tr class="bg-gray-200">
      <th class="p-2 border">Course</th>
      <th class="p-2 border">Faculty</th>
      <th class="p-2 border">Room</th>
      <th class="p-2 border">Radius</th>
    </tr>
    <?php while($cl = mysqli_fetch_assoc($classes)): ?>
      <tr>
        <td class="p-2 border"><?php echo $cl['code']." - ".$cl['course']; ?></td>
        <td class="p-2 border"><?php echo $cl['faculty']; ?></td>
        <td class="p-2 border"><?php echo $cl['room']; ?></td>
        <td class="p-2 border"><?php echo $cl['radius_m']." m"; ?></td>
        <td class="p-2 border">
  <a href="delete.php?id=<?php echo $cl['id']; ?>" 
     class="text-red-600 hover:underline"
     onclick="return confirm('Are you sure?')">Delete</a>
</td>

      </tr>
    <?php endwhile; ?>
  </table>
</div>

<?php include("../../includes/footer.php"); ?>
