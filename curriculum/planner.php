<?php
session_start();
if ($_SESSION['role_id'] != 1) { header("Location: ../auth/login.php"); exit(); }
require_once "../config/db.php";
require '../vendor/autoload.php';

use OpenAI\Client;
$client = OpenAI::client("YOUR_OPENAI_API_KEY");

// Fetch subjects
$subjects = $conn->query("SELECT * FROM subjects ORDER BY name");

// Handle add
if (isset($_POST['add'])) {
    $subject_id = $_POST['subject_id'];
    $outcome = $_POST['outcome'];
    $target_date = $_POST['target_date'];

    $stmt = $conn->prepare("INSERT INTO curriculum (subject_id,outcome,target_date) VALUES (?,?,?)");
    $stmt->bind_param("iss",$subject_id,$outcome,$target_date);
    $stmt->execute();
    header("Location: planner.php");
    exit();
}

// AI Suggestion
$suggestion = "";
if (isset($_POST['ai_suggest'])) {
    $subject_name = $_POST['subject_name'];
    $response = $client->chat()->create([
        'model' => 'gpt-4o-mini',
        'messages' => [
            ['role'=>'system','content'=>'You are a curriculum planner assistant.'],
            ['role'=>'user','content'=>"Suggest key learning outcomes and weekly plan for subject: $subject_name"]
        ],
    ]);
    $suggestion = $response['choices'][0]['message']['content'];
}

// Fetch existing curriculum
$plans = $conn->query("SELECT c.*, s.name as subject FROM curriculum c JOIN subjects s ON c.subject_id=s.id ORDER BY c.id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Curriculum Planner - EduPlusAI</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex">
  <?php include("../assets/components/sidebar.php"); ?>
  <div class="flex-1">
    <?php include("../assets/components/header.php"); ?>
    <main class="p-6">
      <h1 class="text-2xl font-bold mb-6">📘 Curriculum Planner</h1>

      <!-- AI Suggestion -->
      <form method="POST" class="bg-white p-6 rounded-lg shadow mb-6 space-y-4">
        <h2 class="text-lg font-semibold">🤖 AI Generate Outcomes</h2>
        <input type="text" name="subject_name" placeholder="Enter Subject Name" required class="w-full border p-2 rounded">
        <button type="submit" name="ai_suggest" class="bg-purple-600 text-white px-4 py-2 rounded">✨ Get Suggestions</button>
      </form>

      <?php if($suggestion): ?>
      <div class="bg-green-100 p-4 mb-6 rounded">
        <h3 class="font-bold">AI Suggested Curriculum:</h3>
        <pre class="whitespace-pre-wrap"><?= htmlspecialchars($suggestion) ?></pre>
      </div>
      <?php endif; ?>

      <!-- Add Curriculum -->
      <form method="POST" class="bg-white p-6 rounded-lg shadow mb-6 space-y-4">
        <h2 class="text-lg font-semibold">➕ Add Curriculum Plan</h2>
        <select name="subject_id" class="w-full border p-2 rounded" required>
          <option value="">Select Subject</option>
          <?php while($s=$subjects->fetch_assoc()) echo "<option value='{$s['id']}'>{$s['name']}</option>"; ?>
        </select>
        <textarea name="outcome" placeholder="Learning Outcomes" required class="w-full border p-2 rounded"></textarea>
        <input type="date" name="target_date" required class="w-full border p-2 rounded">
        <button type="submit" name="add" class="bg-blue-600 text-white px-4 py-2 rounded">Save</button>
      </form>

      <!-- Curriculum List -->
      <div class="bg-white p-6 rounded-lg shadow">
        <h2 class="text-lg font-semibold mb-4">📋 Existing Plans</h2>
        <table class="w-full border-collapse">
          <thead><tr class="bg-gray-200"><th class="p-3">Subject</th><th class="p-3">Outcome</th><th class="p-3">Target Date</th></tr></thead>
          <tbody>
            <?php while($row=$plans->fetch_assoc()): ?>
              <tr class="border-b">
                <td class="p-3"><?= $row['subject'] ?></td>
                <td class="p-3"><?= $row['outcome'] ?></td>
                <td class="p-3"><?= $row['target_date'] ?></td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </main>
  </div>
</body>
</html>
