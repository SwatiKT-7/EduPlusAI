<?php
require_once __DIR__ . '/../../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') json_out(['ok'=>false,'error'=>'GET required'], 405);
if (!isset($_GET['user_id']))  json_out(['ok'=>false,'error'=>'user_id required'], 400);
$user_id = (int)$_GET['user_id'];
$course_id = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;

$conn = db();

// Basic profile
$pq = $conn->prepare("SELECT id, name, email, enrollment_no, role FROM users WHERE id=?");
$pq->bind_param("i",$user_id); $pq->execute(); $prof = $pq->get_result()->fetch_assoc();
if (!$prof) json_out(['ok'=>false,'error'=>'User not found'],404);

// Metrics (latest)
$mSql = "SELECT sm.*, c.code AS course_code, c.name AS course_name
         FROM student_metrics sm
         LEFT JOIN courses c ON c.id = sm.course_id
         WHERE sm.user_id = ?";
if ($course_id) $mSql .= " AND sm.course_id = ".$course_id;
$mSql .= " ORDER BY sm.updated_at DESC LIMIT 20";
$mr = $conn->prepare($mSql);
$mr->bind_param("i",$user_id); $mr->execute(); $mres = $mr->get_result();
$metrics = [];
while($row = $mres->fetch_assoc()){
  $row['risk_score'] = is_null($row['risk_score']) ? null : (int)$row['risk_score'];
  $row['attn_cum']   = is_null($row['attn_cum']) ? null : round((float)$row['attn_cum'],2);
  $row['term_attn_projection'] = is_null($row['term_attn_projection']) ? null : round((float)$row['term_attn_projection'],2);
  $metrics[] = $row;
}

// Recent attendance (last 10 classes across courses)
$aq = $conn->prepare("
  SELECT a.class_id, a.status, a.created_at, c.course_id, cr.code AS course_code
  FROM attendance_events a
  JOIN classes c ON c.id = a.class_id
  JOIN courses cr ON cr.id = c.course_id
  WHERE a.user_id=? ORDER BY a.created_at DESC LIMIT 10
");
$aq->bind_param("i",$user_id); $aq->execute();
$att = []; $ares = $aq->get_result(); while($r = $ares->fetch_assoc()) $att[] = $r;

// Quick reasons (explainable text from metrics)
$reasons = [];
foreach ($metrics as $m) {
  $course = ($m['course_code'] ?? '—');
  if ($m['attn_cum'] !== null && $m['attn_cum'] < 75) {
    $reasons[] = "Attendance below 75% in {$course} (" . $m['attn_cum'] . "%)";
  }
  if ($m['term_attn_projection'] !== null && $m['term_attn_projection'] < 75) {
    $reasons[] = "Projected term-end attendance < 75% in {$course} (" . $m['term_attn_projection'] . "%)";
  }
  if ($m['risk_score'] !== null && $m['risk_score'] >= 60) {
    $reasons[] = "High risk score in {$course} (" . $m['risk_score'] . ")";
  }
}
if (!$reasons) $reasons[] = "Looks stable based on current attendance. Continue consistent presence.";

json_out(['ok'=>true, 'profile'=>$prof, 'metrics'=>$metrics, 'recent_attendance'=>$att, 'reasons'=>$reasons]);
