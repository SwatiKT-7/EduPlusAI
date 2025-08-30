<?php
require_once __DIR__.'/../../config.php';
require_once __DIR__.'/../utils/crypto.php';

if($_SERVER['REQUEST_METHOD']!=='POST') json_out(['ok'=>false,'error'=>'POST required'],405);
require_post(['class_id','user_id','token','lat','lng','device_id']);

global $CFG; $conn=db();
$class_id=(int)$_POST['class_id']; $user_id=(int)$_POST['user_id'];
$token=$_POST['token']; $lat=(float)$_POST['lat']; $lng=(float)$_POST['lng']; $device_id=$_POST['device_id'];

$q=$conn->prepare("SELECT geo_lat, geo_lng, radius_m, status FROM classes WHERE id=?");
$q->bind_param("i",$class_id); $q->execute(); $res=$q->get_result();
if($res->num_rows===0) json_out(['ok'=>false,'error'=>'Class not found'],404);
$class=$res->fetch_assoc(); if($class['status']!=='live') json_out(['ok'=>false,'error'=>'Class not live'],400);

$uq=$conn->prepare("SELECT device_fingerprint FROM users WHERE id=?");
$uq->bind_param("i",$user_id); $uq->execute(); $ures=$uq->get_result();
if($ures->num_rows===0) json_out(['ok'=>false,'error'=>'User not found'],404);
$user=$ures->fetch_assoc();
if(!empty($user['device_fingerprint']) && $user['device_fingerprint']!==$device_id){
  json_out(['ok'=>false,'error'=>'Device mismatch'],403);
}

$ws=current_window_start($CFG['QR_WINDOW_SECONDS']);
$valid=false;
if(hash_equals(token_for_window($class_id,$ws),$token)) $valid=true;
if(!$valid && $CFG['ALLOW_PREV_WINDOW']){
  if(hash_equals(token_for_window($class_id,$ws-$CFG['QR_WINDOW_SECONDS']),$token)) $valid=true;
}
if(!$valid) json_out(['ok'=>false,'error'=>'Invalid/expired token'],400);

$dist=haversine_m($lat,$lng,(float)$class['geo_lat'],(float)$class['geo_lng']);
$radius=(float)($class['radius_m'] ?: $CFG['GEOFENCE_TOLERANCE_M']);
if($dist>$radius) json_out(['ok'=>false,'error'=>'Outside geofence','distance_m'=>round($dist,2)],403);
$windowStart = current_window_start($CFG['QR_WINDOW_SECONDS']);
$ip = $_SERVER['REMOTE_ADDR'] ?? null;
$ua = $_SERVER['HTTP_USER_AGENT'] ?? null;

// optional Wi-Fi/Beacon check if you decide to send from client later
$wifiBssid = $_POST['wifi_bssid'] ?? null;
$beaconId  = $_POST['ble_beacon_id'] ?? null;
$anomaly = 0; $reason = null;
// Simple rule: if class has wifi_bssid set and posted value mismatches → anomaly
$clsWifi = $class['wifi_bssid'] ?? null; $clsBeacon = $class['ble_beacon_id'] ?? null;
if ($clsWifi && $wifiBssid && $clsWifi !== $wifiBssid) { $anomaly=1; $reason='wifi_mismatch'; }
if ($clsBeacon && $beaconId && $clsBeacon !== $beaconId) { $anomaly=1; $reason=($reason? $reason.',':'').'beacon_mismatch'; }

$stmt=$conn->prepare("
  INSERT INTO attendance_events(class_id,user_id,status,method,device_id,lat,lng,window_start,anomaly_flag,anomaly_reason,ip_addr,user_agent)
  VALUES(?,?,'P','qr',?,?,?,?,?,?,?,?)
  ON DUPLICATE KEY UPDATE status='P', device_id=VALUES(device_id), lat=VALUES(lat), lng=VALUES(lng),
    window_start=VALUES(window_start), anomaly_flag=VALUES(anomaly_flag), anomaly_reason=VALUES(anomaly_reason),
    ip_addr=VALUES(ip_addr), user_agent=VALUES(user_agent)
");
$stmt->bind_param("iissddiisss",$class_id,$user_id,$device_id,$lat,$lng,$windowStart,$anomaly,$reason,$ip,$ua);

if(!$stmt->execute()) json_out(['ok'=>false,'error'=>'DB error: '.$conn->error],500);

json_out(['ok'=>true,'distance_m'=>round($dist,2)]);
