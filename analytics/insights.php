<?php
require '../vendor/autoload.php';
require_once "../config/db.php";

use OpenAI\Client;

$client = OpenAI::client("YOUR_OPENAI_API_KEY");

// Fetch quick summary data
$res = $conn->query("
  SELECT u.name, s.name as subject, COUNT(a.id) as total,
         SUM(a.status='Present') as present
  FROM users u
  JOIN attendance a ON u.id=a.student_id
  JOIN sessions ss ON a.session_id=ss.id
  JOIN subjects s ON ss.subject_id=s.id
  WHERE u.role_id=3
  GROUP BY u.id, s.id
  LIMIT 20
");

$summary = "";
while ($row = $res->fetch_assoc()) {
    $percent = $row['total'] > 0 ? round(($row['present'] / $row['total']) * 100, 2) : 0;
    $summary .= "{$row['name']} has {$percent}% in {$row['subject']}. ";
}

// Ask GPT for insights
$response = $client->chat()->create([
    'model' => 'gpt-4o-mini',
    'messages' => [
        ['role' => 'system', 'content' => 'You are an education analytics assistant.'],
        ['role' => 'user', 'content' => "Generate insights from this attendance data:\n$summary"]
    ],
]);

echo json_encode(["insight" => $response['choices'][0]['message']['content']]);
