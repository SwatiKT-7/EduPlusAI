<?php
require_once __DIR__ . '/../../config.php';
if($_SERVER['REQUEST_METHOD']!=='POST') json_out(['ok'=>false,'error'=>'POST required'],405);
if(!isset($_POST['alert_id'])) json_out(['ok'=>false,'error'=>'alert_id required'],400);
$alert_id=(int)$_POST['alert_id'];
$conn=db();
$stmt=$conn->prepare("UPDATE alerts SET resolved_at=NOW() WHERE id=?");
$stmt->bind_param("i",$alert_id);
if(!$stmt->execute()) json_out(['ok'=>false,'error'=>'DB error: '.$conn->error],500);
json_out(['ok'=>true]);
