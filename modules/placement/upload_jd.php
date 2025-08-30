<?php
require_once __DIR__ . '/../../config.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_out(['ok'=>false,'error'=>'POST required'],405);

$company = trim($_POST['company'] ?? '');
$role    = trim($_POST['role'] ?? '');
$text    = trim($_POST['text'] ?? '');
$tenant_id = 1; // simple single-tenant demo; wire from JWT later

if (!$company || !$role || !$text) json_out(['ok'=>false,'error'=>'company, role, text required'],400);

$conn = db();
$stmt = $conn->prepare("INSERT INTO placements_jd(tenant_id, company, role, text) VALUES(?,?,?,?)");
$stmt->bind_param("isss",$tenant_id,$company,$role,$text);
if(!$stmt->execute()) json_out(['ok'=>false,'error'=>'DB error: '.$conn->error],500);

json_out(['ok'=>true,'jd_id'=>$stmt->insert_id]);
