<?php
require_once __DIR__.'/../../config.php';
if($_SERVER['REQUEST_METHOD']!=='POST') json_out(['ok'=>false,'error'=>'POST required'],405);
$conn=db();
$course_id = isset($_POST['course_id']) ? (int)$_POST['course_id'] : 0;

// For each student-course, compute attendance %, 14-day rate, absence streak, projection, risk, reasons, and alerts
$agg = $conn->query("
  SELECT u.id AS user_id, c.course_id,
         SUM(a.status='P' OR a.status='L') AS attended,
         COUNT(a.id) AS total
  FROM classes c
  LEFT JOIN attendance_events a ON a.class_id=c.id
  JOIN users u ON u.role='student'
  GROUP BY u.id, c.course_id
");

$now = date('Y-m-d H:i:s');
while($row = $agg && $agg->fetch_assoc() ? $agg->fetch_assoc() : null){
  if(!$row) break;
  $uid=(int)$row['user_id']; $cid=(int)$row['course_id'];
  $total=(int)$row['total']; $att=(int)$row['attended'];
  $attn_cum = $total>0 ? ($att/$total)*100.0 : 0.0;

  // Last 14 days rate
  $q14 = $conn->prepare("
    SELECT SUM(status='P' OR status='L') AS a14, COUNT(*) AS t14
    FROM attendance_events ae
    JOIN classes cl ON cl.id=ae.class_id
    WHERE ae.user_id=? AND cl.course_id=? AND ae.created_at>=DATE_SUB(NOW(), INTERVAL 14 DAY)
  "); $q14->bind_param("ii",$uid,$cid); $q14->execute();
  $r14=$q14->get_result()->fetch_assoc(); $a14=(int)($r14['a14']??0); $t14=(int)($r14['t14']??0);
  $attn14 = $t14>0 ? ($a14/$t14)*100.0 : null;

  // Absence streak (consecutive most recent 'A' from last 10 sessions)
  $qs = $conn->prepare("
    SELECT status FROM attendance_events ae
    JOIN classes cl ON cl.id=ae.class_id
    WHERE ae.user_id=? AND cl.course_id=? ORDER BY ae.created_at DESC LIMIT 10
  "); $qs->bind_param("ii",$uid,$cid); $qs->execute(); $sres=$qs->get_result();
  $streak=0; while($s=$sres->fetch_assoc()){ if($s['status']==='A'){ $streak++; } else break; }

  // Projection (simple): current rate applied to remaining (assume 40 classes per term)
  $TERM=40; $remaining = max(0,$TERM-$total); $proj = $total>0 ? min(100.0, (($att + ($att/$total)*$remaining)/$TERM)*100.0 ) : 0.0;

  // Risk (explainable): weights
  $risk = 0.35*(100-($attn14??$attn_cum)) + 0.35*(100-$attn_cum) + 0.30*min(100,$streak*20);
  $risk = max(0,min(100, round($risk)));

  $reasons = [];
  if ($attn_cum < 75) $reasons[] = "Cumulative attendance below 75% ($attn_cum%)";
  if ($attn14 !== null && $attn14 < 70) $reasons[] = "Last-14-days rate low ($attn14%)";
  if ($streak >= 2) $reasons[] = "Absence streak ($streak)";
  if ($proj < 75) $reasons[] = "Projected term-end below 75% ($proj%)";
  $reasons_json = json_encode($reasons);

  $stmt=$conn->prepare("INSERT INTO student_metrics(user_id,course_id,risk_score,attn_cum,attn_streak_absent,attn_last14,term_attn_projection,risk_reason,updated_at)
    VALUES(?,?,?,?,?,?,?, ?, NOW())
    ON DUPLICATE KEY UPDATE risk_score=VALUES(risk_score), attn_cum=VALUES(attn_cum), attn_streak_absent=VALUES(attn_streak_absent),
      attn_last14=VALUES(attn_last14), term_attn_projection=VALUES(term_attn_projection), risk_reason=VALUES(risk_reason), updated_at=NOW()");
  $stmt->bind_param("iiddidds",$uid,$cid,$risk,$attn_cum,$streak,$attn14,$proj,$reasons_json);
  $stmt->execute();

  // Alerts (idempotent-ish: just insert; you can dedupe by (user_id,course_id,type,severity,message,created_at))
  if ($attn_cum < 75) {
    $msg = "Cumulative attendance $attn_cum% (policy 75%)";
    $al=$conn->prepare("INSERT INTO alerts(user_id,course_id,type,severity,message) VALUES(?,?,'low_attendance','medium',?)");
    $al->bind_param("iis",$uid,$cid,$msg); $al->execute();
  }
  if ($streak >= 3) {
    $msg = "Absent $streak consecutive classes";
    $al=$conn->prepare("INSERT INTO alerts(user_id,course_id,type,severity,message) VALUES(?,?,'absence_streak','high',?)");
    $al->bind_param("iis",$uid,$cid,$msg); $al->execute();
  }
  if ($proj < 75) {
    $msg = "Projected to end at $proj% (<75%)";
    $al=$conn->prepare("INSERT INTO alerts(user_id,course_id,type,severity,message) VALUES(?,?,'projection_fail','high',?)");
    $al->bind_param("iis",$uid,$cid,$msg); $al->execute();
  }
}
echo json_encode(['ok'=>true]);
