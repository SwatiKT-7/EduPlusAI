<?php
require_once __DIR__ . '/../../config.php';
if($_SERVER['REQUEST_METHOD']!=='POST') json_out(['ok'=>false,'error'=>'POST required'],405);
if(!isset($_POST['class_id'])) json_out(['ok'=>false,'error'=>'class_id required'],400);
$class_id=(int)$_POST['class_id']; $conn=db();
$stmt=$conn->prepare("UPDATE classes SET status='closed', closed_at=NOW() WHERE id=?");
$stmt->bind_param("i",$class_id);
if(!$stmt->execute()) json_out(['ok'=>false,'error'=>'DB error: '.$conn->error],500);
json_out(['ok'=>true]);
