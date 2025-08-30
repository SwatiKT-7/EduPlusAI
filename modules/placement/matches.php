<?php
require_once __DIR__.'/../../config.php';
if($_SERVER['REQUEST_METHOD']!=='GET') json_out(['ok'=>false,'error'=>'GET required'],405);
$conn=db();
$role=$_GET['role'] ?? 'Software Intern';
$res=$conn->query("SELECT id, name, email FROM users WHERE role='student' LIMIT 5");
$students=[]; while($row=$res->fetch_assoc()) $students[]=$row;
json_out(['ok'=>true,'role'=>$role,'students'=>$students]);
