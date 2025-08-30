<?php
require_once __DIR__ . '/../../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') json_out(['ok'=>false,'error'=>'GET required'], 405);
$conn = db();

$course_id = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;
$where = $course_id ? "WHERE sm.course_id = $course_id" : "";

$sql = "
SELECT
  sm.user_id,
  u.name AS student_name,
  u.enrollment_no,
  sm.course_id,
  c.code AS course_code,
  c.name AS course_name,
  sm.risk_score,
  sm.attn_cum,
  sm.term_attn_projection,
  sm.updated_at
FROM student_metrics sm
JOIN users u   ON u.id = sm.user_id
LEFT JOIN courses c ON c.id = sm.course_id
$where
ORDER BY sm.risk_score DESC, u.name ASC
";
$res = $conn->query($sql);
$data = [];
if ($res) {
  while ($row = $res->fetch_assoc()) {
    $row['risk_score'] = is_null($row['risk_score']) ? null : (int)$row['risk_score'];
    $row['attn_cum']   = is_null($row['attn_cum']) ? null : round((float)$row['attn_cum'], 2);
    $row['term_attn_projection'] = is_null($row['term_attn_projection']) ? null : round((float)$row['term_attn_projection'], 2);
    $data[] = $row;
  }
}
json_out(['ok'=>true, 'rows'=>$data]);
