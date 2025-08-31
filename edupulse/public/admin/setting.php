<?php
session_start();
require_once "../../config/db.php";
include("../includes/auth.php");
include("../includes/header.php");
include("includes/sidebar.php");

if ($_SESSION['role'] != 'admin') { 
    header("Location: ../dashboard.php"); 
    exit; 
}

$settingsFile = "../../config/settings.json";
$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $report_hour = intval($_POST['report_hour']);
    $report_min  = intval($_POST['report_min']);
    $threshold   = intval($_POST['threshold']);
    file_put_contents($settingsFile, json_encode([
        "report_hour" => $report_hour,
        "report_min"  => $report_min,
        "threshold"   => $threshold
    ]));
    $message = "✅ Settings saved!";
}

$settings = file_exists($settingsFile) 
    ? json_decode(file_get_contents($settingsFile), true) 
    : ["report_hour"=>17,"report_min"=>0,"threshold"=>75];
?>

<div class="max-w-3xl mx-auto bg-white dark:bg-gray-800 p-6 mt-6 rounded-2xl shadow-lg">
  <h2 class="text-2xl font-bold mb-6 text-indigo-600">⚙️ System Settings</h2>

  <?php if($message): ?>
    <div class="mb-4 p-3 rounded-lg bg-green-100 text-green-700 shadow">
      <?= $message ?>
    </div>
  <?php endif; ?>

  <form method="POST" class="space-y-6">
    <!-- Daily Report Time -->
    <div>
      <label class="block font-medium mb-2">⏰ Daily Report Time</label>
      <div class="flex items-center gap-2">
        <input type="number" name="report_hour" 
               value="<?= $settings['report_hour'] ?>" min="0" max="23"
               class="w-20 border p-2 rounded-lg focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600">
        <span class="text-lg">:</span>
        <input type="number" name="report_min" 
               value="<?= $settings['report_min'] ?>" min="0" max="59"
               class="w-20 border p-2 rounded-lg focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600">
      </div>
      <p class="text-gray-500 text-sm mt-1">24-hour format (e.g., 17:00 = 5:00 PM)</p>
    </div>

    <!-- Attendance Threshold -->
    <div>
      <label class="block font-medium mb-2">📊 Attendance Threshold (%)</label>
      <div class="flex items-center gap-2">
        <input type="number" name="threshold" 
               value="<?= $settings['threshold'] ?>" min="1" max="100"
               class="w-28 border p-2 rounded-lg focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600">
        <span class="text-gray-600 dark:text-gray-300">%</span>
      </div>
    </div>

    <button type="submit" 
            class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg shadow">
      💾 Save Settings
    </button>
  </form>
</div>

<?php include("../includes/footer.php"); ?>
