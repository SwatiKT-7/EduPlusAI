<?php
require '../vendor/autoload.php';
require_once "../config/db.php";

use OpenAI\Client;

// Create OpenAI client
$client = OpenAI::client("YOUR_OPENAI_API_KEY");

// Read user input
$data = json_decode(file_get_contents("php://input"), true);
$userMessage = $data['message'] ?? "";

// Optional: You can preprocess queries (like checking DB if student asks "my attendance")
$reply = "I'm not sure how to answer that yet.";

// Example: If user asks for overall attendance
session_start();
if (strpos(strtolower($userMessage), "my attendance") !== false && $_SESSION['role_id'] == 3) {
    $student_id = $_SESSION['user_id'];
    $res = $conn->query("SELECT COUNT(*) as total, SUM(status='Present') as present FROM attendance WHERE student_id=$student_id");
    $row = $res->fetch_assoc();
    $percent = $row['total'] > 0 ? round(($row['present'] / $row['total']) * 100, 2) : 0;
    $reply = "Your overall attendance is {$percent}%.";
} else {
    // Let GPT handle the query
    $response = $client->chat()->create([
        'model' => 'gpt-4o-mini',
        'messages' => [
            ['role' => 'system', 'content' => 'You are EduPlusAI assistant. Answer attendance-related queries clearly.'],
            ['role' => 'user', 'content' => $userMessage]
        ],
    ]);

    $reply = $response['choices'][0]['message']['content'];
}

echo json_encode(["reply" => $reply]);
