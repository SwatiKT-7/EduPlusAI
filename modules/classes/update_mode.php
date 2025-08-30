<?php
require_once __DIR__ . '/../../config.php';
if($_SERVER['REQUEST_METHOD']!=='POST') json_out(['ok'=>false,'error'=>'POST required'],405);
foreach(['class_id','mode'] as $k) if(!isset($_POST[$k])) json_out(['ok'=>false,'error'=>"Missing $k"],400);
$class_id=(int)$_POST['class_id'];
$mode = $_POST['mode']; // 'offline' | 'online' | 'hybrid'
$meeting_url = trim($_POST['meeting_url'] ?? '');
$geo_lat = isset($_POST['geo_lat']) ? (float)$_POST['geo_lat'] : null;
$geo_lng = isset($_POST['geo_lng']) ? (float)$_POST['geo_lng'] : null;
$radius_m = isset($_POST['radius_m']) ? (int)$_POST['radius_m'] : null;
$wifi = trim($_POST['wifi_bssid'] ?? '');
$beacon = trim($_POST['ble_beacon_id'] ?? '');

$conn=db();
$sql="UPDATE classes SET mode=?, meeting_url=?, wifi_bssid=?, ble_beacon_id=?";
$params=[$mode,$meeting_url?:null,$wifi?:null,$beacon?:null];
$types="ssss";

if($geo_lat!==null && $geo_lng!==null){ $sql.=", geo_lat=?, geo_lng=?"; $types.="dd"; $params[]=$geo_lat; $params[]=$geo_lng; }
if($radius_m!==null){ $sql.=", radius_m=?"; $types.="i"; $params[]=$radius_m; }
$sql.=" WHERE id=?"; $types.="i"; $params[]=$class_id;

$stmt=$conn->prepare($sql);
$stmt->bind_param($types, ...$params);
if(!$stmt->execute()) json_out(['ok'=>false,'error'=>'DB error: '.$conn->error],500);
json_out(['ok'=>true]);
