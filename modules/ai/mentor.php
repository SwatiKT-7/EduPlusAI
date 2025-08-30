<?php
require_once __DIR__.'/../../config.php';
require_once __DIR__.'/gpt_client.php';

if($_SERVER['REQUEST_METHOD']!=='POST') json_out(['ok'=>false,'error'=>'POST required'],405);
require_post(['user_name','risk_score','courses']); // courses JSON

$user_name=$_POST['user_name']; $risk=(float)$_POST['risk_score'];
$courses=json_decode($_POST['courses'],true);

$prompt="You are a college mentor assistant. Build a one-week plan for student $user_name.
Risk score: $risk (0 good, 100 bad).
Courses with attendance and pending tasks: ".json_encode($courses)."
Respond with bullet points and concrete actions (study slots, practice sets, micro-modules).";

$resp=gpt_chat([['role'=>'user','content'=>$prompt]],'gpt-4o-mini');
if(!$resp['ok']) json_out(['ok'=>false,'error'=>$resp['error']],500);
json_out(['ok'=>true,'plan'=>$resp['content']]);
