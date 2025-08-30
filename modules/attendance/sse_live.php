<?php
require_once __DIR__.'/../../config.php';
if(!isset($_GET['class_id'])){ http_response_code(400); echo "class_id required"; exit; }
$class_id=(int)$_GET['class_id']; $conn=db();

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache'); header('Connection: keep-alive');

while(true){
  $q=$conn->prepare("SELECT SUM(status='P') present, SUM(status='L') late, SUM(status='A') absent FROM attendance_events WHERE class_id=?");
  $q->bind_param("i",$class_id); $q->execute(); $r=$q->get_result()->fetch_assoc();
  $payload=json_encode([
    'present'=>intval($r['present']??0),
    'late'=>intval($r['late']??0),
    'absent'=>intval($r['absent']??0),
    't'=>time()
  ]);
  echo "data: $payload\n\n";
  @ob_flush(); @flush();
  sleep(2);
}
