<?php
session_start();
if ($_SESSION['role_id'] != 2) { // only Faculty
    header("Location: ../auth/login.php");
    exit();
}
require_once "../config/db.php";
require '../vendor/autoload.php';

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

$faculty_id = $_SESSION['user_id'];

// Start a live session
if (isset($_POST['start'])) {
    $subject_id = $_POST['subject_id'];
    $stmt = $conn->prepare("INSERT INTO sessions (subject_id, faculty_id, session_date, is_live) VALUES (?, ?, NOW(), 1)");
    $stmt->bind_param("ii",$subject_id,$faculty_id);
    $stmt->execute();
    $session_id = $stmt->insert_id;

    header("Location: live.php?session_id=$session_id");
    exit();
}

// Fetch subjects taught by faculty
$subjects = $conn->query("SELECT * FROM subjects WHERE faculty_id=$faculty_id");

$session_id = $_GET['session_id'] ?? null;
?>
<!DOCTYPE html>
<html lang="en" x-data="{ refreshQR:true }">
<head>
  <meta charset="UTF-8">
  <title>Live Attendance - EduPlusAI</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex">
  <?php include("../assets/components/sidebar.php"); ?>
  <div class="flex-1">
    <?php include("../assets/components/header.php"); ?>
    <main class="p-6">
      <h1 class="text-2xl font-bold mb-6">📡 Real-Time Attendance</h1>

      <!-- Start Live Session -->
      <?php if(!$session_id): ?>
      <form method="POST" class="bg-white p-6 rounded-lg shadow space-y-4">
        <h2 class="text-lg font-semibold">Start Live Session</h2>
        <select name="subject_id" class="w-full border p-2 rounded" required>
          <option value="">Select Subject</option>
          <?php while($s=$subjects->fetch_assoc()) echo "<option value='{$s['id']}'>{$s['name']}</option>"; ?>
        </select>
        <button type="submit" name="start" class="bg-blue-600 text-white px-4 py-2 rounded">🚀 Start</button>
      </form>
      <?php endif; ?>

      <!-- Live Session QR -->
      <?php if($session_id): ?>
        <div class="bg-white p-6 rounded-lg shadow text-center">
          <h2 class="text-lg font-semibold mb-4">📲 Scan to Mark Attendance</h2>
          <img id="qrImage" class="mx-auto" src="qr_refresh.php?session_id=<?= $session_id ?>&t=<?= time() ?>" alt="QR Code">
          <p class="text-sm text-gray-600 mt-2">QR refreshes every 2 min</p>
        </div>

        <!-- Live Count -->
        <div class="bg-white p-6 rounded-lg shadow mt-6">
          <h2 class="text-lg font-semibold mb-4">👥 Live Attendance Count</h2>
          <p id="liveCount" class="text-3xl font-bold text-blue-600">Loading...</p>
        </div>
      <?php endif; ?>
    </main>
  </div>

  <?php if($session_id): ?>
  <script>
    // Auto-refresh QR every 120 sec
    setInterval(()=>{
      document.getElementById("qrImage").src = "qr_refresh.php?session_id=<?= $session_id ?>&t=" + new Date().getTime();
    },120000);

    // Fetch live count every 10 sec
    async function fetchCount(){
      let res = await fetch("live_count.php?session_id=<?= $session_id ?>");
      let data = await res.json();
      document.getElementById("liveCount").innerText = data.count+" Present";
    }
    setInterval(fetchCount,10000);
    fetchCount();
  </script>
  <?php endif; ?>
</body>
</html>
