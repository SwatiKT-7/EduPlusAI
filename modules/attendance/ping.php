<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../utils/crypto.php';
if($_SERVER['REQUEST_METHOD']!=='POST') json_out(['ok'=>false,'error'=>'POST required'],405);
foreach(['class_id','user_id'] as $k) if(!isset($_POST[$k])) json_out(['ok'=>false,'error'=>"Missing $k"],400);

$conn=db();
$class_id=(int)$_POST['class_id']; $user_id=(int)$_POST['user_id'];
$ip = $_SERVER['REMOTE_ADDR'] ?? null; $ua = $_SERVER['HTTP_USER_AGENT'] ?? null;

$q=$conn->prepare("SELECT status,mode FROM classes WHERE id=?"); $q->bind_param("i",$class_id);
$q->execute(); $c=$q->get_result()->fetch_assoc(); if(!$c) json_out(['ok'=>false,'error'=>'Class not found'],404);
if($c['status']!=='live') json_out(['ok'=>false,'error'=>'Class not live'],400);
if($c['mode']==='offline') json_out(['ok'=>false,'error'=>'Pings only for online/hybrid'],400);

// Mark 'L' (present via ping) if not already present
$stmt=$conn->prepare("
  INSERT INTO attendance_events(class_id,user_id,status,method,ip_addr,user_agent)
  VALUES(?,?,'L','ping',?,?)
  ON DUPLICATE KEY UPDATE status=IF(status='P',status,'L'), ip_addr=VALUES(ip_addr), user_agent=VALUES(user_agent)
");
$stmt->bind_param("iiss",$class_id,$user_id,$ip,$ua); $ok=$stmt->execute();
if(!$ok) json_out(['ok'=>false,'error'=>'DB error: '.$conn->error],500);
json_out(['ok'=>true]);
