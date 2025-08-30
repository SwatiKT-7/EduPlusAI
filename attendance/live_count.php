<?php
require_once "../config/db.php";

$session_id = $_GET['session_id'] ?? 0;
$res = $conn->query("SELECT COUNT(*) as cnt FROM attendance WHERE session_id=$session_id AND status='Present'");
$row = $res->fetch_assoc();
echo json_encode(["count"=>$row['cnt']]);
