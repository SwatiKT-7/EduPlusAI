<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/gpt_client.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_out(['ok'=>false,'error'=>'POST required'],405);
$course_id = isset($_POST['course_id']) ? (int)$_POST['course_id'] : 0;
$topic     = trim($_POST['topic'] ?? '');
$difficulty= trim($_POST['difficulty'] ?? 'easy');   // easy | medium | hard
$count     = max(1, min(20, (int)($_POST['count'] ?? 5)));

if (!$topic) json_out(['ok'=>false,'error'=>'topic required'],400);

$sys = "You are an examiner. Create high-quality MCQs for college students.
Return ONLY strict JSON with fields: questions:[{q, options:[A,B,C,D], answer, explanation}]. No extra text.";
$user = "CourseID:$course_id; Topic:$topic; Difficulty:$difficulty; Count:$count.
Bloom levels mix; avoid trick ambiguity; explanations must justify the answer.";

$resp = gpt_chat(
  [
    ['role'=>'system','content'=>$sys],
    ['role'=>'user','content'=>$user]
  ],
  'gpt-4o-mini',
  true // ask JSON
);
if(!$resp['ok']) json_out(['ok'=>false,'error'=>$resp['error'] ?? 'API error'],500);

// parse JSON content safely
$out = json_decode($resp['content'], true);
if (!$out || !isset($out['questions'])) {
  // fallback: try to extract json
  $try = preg_replace('~^[^{\[]+~','',$resp['content']);
  $out = json_decode($try,true);
}
if (!$out || !isset($out['questions'])) json_out(['ok'=>false,'error'=>'Bad JSON from model'],502);

json_out(['ok'=>true,'questions'=>$out['questions'],'meta'=>['topic'=>$topic,'difficulty'=>$difficulty,'count'=>$count]]);
