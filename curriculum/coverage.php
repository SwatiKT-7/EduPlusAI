<?php
session_start();
if ($_SESSION['role_id'] != 2) { // only Faculty
    header("Location: ../auth/login.php");
    exit();
}
require_once "../config/db.php";
require '../vendor/autoload.php';

use OpenAI\Client;
$client = OpenAI::client("YOUR_OPENAI_API_KEY");

$faculty_id = $_SESSION['user_id'];

// Fetch subjects assigned to this faculty
$subjects = $conn->query("SELECT * FROM subjects WHERE faculty_id=$faculty_id");

// Handle Add Coverage
if (isset($_POST['add'])) {
    $subject_id = $_POST['subject_id'];
    $topic = $_POST['topic'];
    $date_taught = $_POST['date_taught'];

    $stmt = $conn->prepare("INSERT INTO lesson_map (subject_id, faculty_id, topic, date_taught) VALUES (?,?,?,?)");
    $stmt->bind_param("iiss", $subject_id,$faculty_id,$topic,$date_taught);
    $stmt->execute();
    header("Location: coverage.php");
    exit();
}

// Fetch Coverage
$coverage = $conn->query("
    SELECT l.*, s.name as subject FROM lesson_map l
    JOIN subjects s ON l.subject_id=s.id
    WHERE l.faculty_id=$faculty_id
    ORDER BY l.date_taught DESC
");

// AI Insights: Compare planned vs actual coverage
$ai_insight = "";
if (isset($_POST['analyze'])) {
    $subject_id = $_POST['subject_id_ai'];

    // Get curriculum
    $plan = $conn->query("SELECT outcome FROM curriculum WHERE subject_id=$subject_id");
    $planned_outcomes = [];
    while($p=$plan->fetch_assoc()) $planned_outcomes[] = $p['outcome'];

    // Get coverage
    $done = $conn->query("SELECT topic FROM lesson_map WHERE subject_id=$subject_id AND faculty_id=$faculty_id");
    $covered = [];
    while($d=$done->fetch_assoc()) $covered[] = $d['topic'];

    $summary = "Planned: ".implode(", ",$planned_outcomes).". Covered: ".implode(", ",$covered);

    $response = $client->chat()->create([
        'model'=>'gpt-4o-mini',
        'messages'=>[
            ['role'=>'system','content'=>'You are an academic analytics assistant.'],
            ['role'=>'user','content'=>"Analyze curriculum vs coverage and highlight gaps:\n$summary"]
        ],
    ]);

    $ai_insight = $response['choices'][0]['message']['content'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Faculty Coverage - EduPlusAI</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex">
  <?php include("../assets/components/sidebar.php"); ?>
  <div class="flex-1">
    <?php include("../assets/components/header.php"); ?>
    <main class="p-6">
      <h1 class="text-2xl font-bold mb-6">📖 Faculty Coverage</h1>

      <!-- Add Coverage -->
      <form method="POST" class="bg-white p-6 rounded-lg shadow mb-6 space-y-4">
        <h2 class="text-lg font-semibold">➕ Add Topic Coverage</h2>
        <select name="subject_id" required class="w-full border p-2 rounded">
          <option value="">Select Subject</option>
          <?php while($s=$subjects->fetch_assoc()) echo "<option value='{$s['id']}'>{$s['name']}</option>"; ?>
        </select>
        <input type="text" name="topic" placeholder="Topic Taught" required class="w-full border p-2 rounded">
        <input type="date" name="date_taught" required class="w-full border p-2 rounded">
        <button type="submit" name="add" class="bg-blue-600 text-white px-4 py-2 rounded">Save</button>
      </form>

      <!-- AI Analysis -->
      <form method="POST" class="bg-white p-6 rounded-lg shadow mb-6 space-y-4">
        <h2 class="text-lg font-semibold">🤖 Analyze Coverage vs Plan</h2>
        <select name="subject_id_ai" required class="w-full border p-2 rounded">
          <option value="">Select Subject</option>
          <?php 
          $subjects->data_seek(0);
          while($s=$subjects->fetch_assoc()) echo "<option value='{$s['id']}'>{$s['name']}</option>"; 
          ?>
        </select>
        <button type="submit" name="analyze" class="bg-purple-600 text-white px-4 py-2 rounded">✨ Analyze</button>
      </form>

      <?php if($ai_insight): ?>
      <div class="bg-green-100 p-4 mb-6 rounded">
        <h3 class="font-bold">AI Insight:</h3>
        <pre class="whitespace-pre-wrap"><?= htmlspecialchars($ai_insight) ?></pre>
      </div>
      <?php endif; ?>

      <!-- Coverage List -->
      <div class="bg-white p-6 rounded-lg shadow">
        <h2 class="text-lg font-semibold mb-4">📋 Topics Covered</h2>
        <table class="w-full border-collapse">
          <thead><tr class="bg-gray-200"><th class="p-3">Subject</th><th class="p-3">Topic</th><th class="p-3">Date</th></tr></thead>
          <tbody>
            <?php while($row=$coverage->fetch_assoc()): ?>
              <tr class="border-b">
                <td class="p-3"><?= $row['subject'] ?></td>
                <td class="p-3"><?= $row['topic'] ?></td>
                <td class="p-3"><?= $row['date_taught'] ?></td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </main>
  </div>
</body>
</html>
