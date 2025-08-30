<?php
require_once __DIR__ . '/../../config.php';
if($_SERVER['REQUEST_METHOD']!=='GET') json_out(['ok'=>false,'error'=>'GET required'],405);
if(!isset($_GET['class_id'])) json_out(['ok'=>false,'error'=>'class_id required'],400);
$class_id=(int)$_GET['class_id']; $conn=db();
$q=$conn->prepare("SELECT id, course_id, room, status, mode, meeting_url, geo_lat, geo_lng, radius_m, wifi_bssid, ble_beacon_id, started_at, closed_at FROM classes WHERE id=?");
$q->bind_param("i",$class_id); $q->execute(); $r=$q->get_result()->fetch_assoc();
if(!$r) json_out(['ok'=>false,'error'=>'Class not found'],404);
json_out(['ok'=>true,'class'=>$r]);
