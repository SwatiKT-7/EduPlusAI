<?php
require_once __DIR__.'/../../config.php';
require_once __DIR__.'/../utils/jwt.php';

if($_SERVER['REQUEST_METHOD']!=='POST') json_out(['ok'=>false,'error'=>'POST required'],405);
require_post(['email','password','device_id']);

$email=$_POST['email']; $password=$_POST['password']; $device_id=$_POST['device_id'];
$conn=db();

$stmt=$conn->prepare("SELECT id, tenant_id, role, password_hash, device_fingerprint, name FROM users WHERE email=?");
$stmt->bind_param("s",$email); $stmt->execute(); $res=$stmt->get_result();
if($res->num_rows===0) json_out(['ok'=>false,'error'=>'Invalid credentials'],401);
$u=$res->fetch_assoc();

$ok=false;
if(strlen($u['password_hash'])<60){
  $p=$conn->prepare("SELECT PASSWORD(?) AS p"); $p->bind_param("s",$password); $p->execute();
  $rp=$p->get_result()->fetch_assoc(); $ok=($rp['p']===$u['password_hash']);
}else{
  $ok=password_verify($password,$u['password_hash']);
}
if(!$ok) json_out(['ok'=>false,'error'=>'Invalid credentials'],401);

if(empty($u['device_fingerprint'])){
  $x=$conn->prepare("UPDATE users SET device_fingerprint=? WHERE id=?");
  $x->bind_param("si",$device_id,$u['id']); $x->execute();
}else if($u['device_fingerprint']!==$device_id){
  json_out(['ok'=>false,'error'=>'Device not recognized'],403);
}

global $CFG;
$token=jwt_sign(['uid'=>$u['id'],'role'=>$u['role'],'tenant_id'=>$u['tenant_id'],'device_id'=>$device_id],$CFG['JWT_SECRET'],$CFG['JWT_TTL']);
json_out(['ok'=>true,'token'=>$token,'name'=>$u['name'],'role'=>$u['role']]);
