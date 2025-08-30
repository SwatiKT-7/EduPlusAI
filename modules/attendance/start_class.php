<?php
require_once __DIR__.'/../../config.php';
if($_SERVER['REQUEST_METHOD']!=='POST') json_out(['ok'=>false,'error'=>'POST required'],405);
require_post(['class_id']);
$class_id=(int)$_POST['class_id'];
$conn=db();

$u=$conn->prepare("UPDATE classes SET status='live' WHERE id=? AND status!='closed'");
$u->bind_param("i",$class_id); $u->execute();

$q=$conn->prepare("SELECT id, course_id, room, geo_lat, geo_lng, radius_m, status FROM classes WHERE id=?");
$q->bind_param("i",$class_id); $q->execute(); $res=$q->get_result();
if($res->num_rows===0) json_out(['ok'=>false,'error'=>'Class not found'],404);
json_out(['ok'=>true,'class'=>$res->fetch_assoc()]);
