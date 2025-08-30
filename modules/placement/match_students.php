<?php
require_once __DIR__ . '/../../config.php';
if ($_SERVER['REQUEST_METHOD'] !== 'GET') json_out(['ok'=>false,'error'=>'GET required'],405);
if (!isset($_GET['jd_id'])) json_out(['ok'=>false,'error'=>'jd_id required'],400);

$jd_id = (int)$_GET['jd_id'];
$conn = db();

// Fetch JD
$q = $conn->prepare("SELECT company, role, text FROM placements_jd WHERE id=?");
$q->bind_param("i",$jd_id); $q->execute(); $jd = $q->get_result()->fetch_assoc();
if (!$jd) json_out(['ok'=>false,'error'=>'JD not found'],404);

// Get student skills (aggregated)
$sql = "SELECT user_id, GROUP_CONCAT(skill SEPARATOR ' ') AS skills
        FROM student_skills GROUP BY user_id";
$res = $conn->query($sql);
$skills_by_user = [];
if ($res) while($row = $res->fetch_assoc()) $skills_by_user[(int)$row['user_id']] = strtolower($row['skills'] ?? '');

// If no skills data, fallback to students only
$stud = $conn->query("SELECT id, name, email FROM users WHERE role='student'");
$students = []; while($s = $stud->fetch_assoc()) $students[] = $s;

// Tokenize JD text
$txt = strtolower($jd['role'].' '.$jd['text']);
$tokens = preg_split('~[^a-z0-9+#]+~',$txt,-1,PREG_SPLIT_NO_EMPTY);
$tokens = array_values(array_filter($tokens, fn($t)=>strlen($t)>=2));
$uniq = array_values(array_unique($tokens));

function overlap_score($hay, $needles){
  if(!$hay) return 0;
  $score = 0;
  foreach($needles as $w){
    if (strpos($hay, $w)!==false) $score++;
  }
  return $score;
}

$out = [];
foreach($students as $s){
  $uid = (int)$s['id'];
  $hay = $skills_by_user[$uid] ?? '';
  $score = overlap_score($hay, $uniq);
  $out[] = ['user_id'=>$uid,'name'=>$s['name'],'email'=>$s['email'],'score'=>$score];
}
usort($out, fn($a,$b)=> $b['score'] <=> $a['score']);
$out = array_slice($out, 0, 20);

json_out(['ok'=>true,'jd'=>$jd,'matches'=>$out]);
