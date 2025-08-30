<?php
require_once __DIR__ . '/../../config.php';
if($_SERVER['REQUEST_METHOD']!=='GET') json_out(['ok'=>false,'error'=>'GET required'],405);
$conn=db();
$course_id = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;
$where = $course_id ? "WHERE a.course_id=$course_id" : "";
$res=$conn->query("
  SELECT a.*, u.name AS student_name, c.code AS course_code
  FROM alerts a
  JOIN users u ON u.id=a.user_id
  LEFT JOIN courses c ON c.id=a.course_id
  $where
  ORDER BY a.severity DESC, a.created_at DESC
");
$rows=[]; if($res) while($r=$res->fetch_assoc()) $rows[]=$r;
json_out(['ok'=>true,'rows'=>$rows]);
