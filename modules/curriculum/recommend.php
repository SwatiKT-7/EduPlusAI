<?php
require_once __DIR__.'/../../config.php';
if($_SERVER['REQUEST_METHOD']!=='GET') json_out(['ok'=>false,'error'=>'GET required'],405);
if(!isset($_GET['user_id'])) json_out(['ok'=>false,'error'=>'user_id required'],400);
$conn=db();

$sql="SELECT c.id, c.code, c.name, c.capacity,
            (SELECT COUNT(*) FROM classes cl WHERE cl.course_id=c.id) AS sections
     FROM courses c";
$res=$conn->query($sql); $out=[];
while($row=$res->fetch_assoc()){
  $row['reason']='Prereqs ok, capacity available'; $out[]=$row;
}
json_out(['ok'=>true,'recommendations'=>$out]);
