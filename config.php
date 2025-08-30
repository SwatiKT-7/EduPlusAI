<?php
$CFG = [
  'DB_HOST' => '127.0.0.1',
  'DB_USER' => 'root',
  'DB_PASS' => '',
  'DB_NAME' => 'edupulse',
  'APP_URL' => 'http://localhost/edupulse/public',
  'JWT_SECRET' => 'change-me-please',
  'JWT_TTL' => 3600,
  'QR_SALT' => 'rotate-me',
  'QR_WINDOW_SECONDS' => 20,
  'ALLOW_PREV_WINDOW' => true,       // accept previous 20s window token
  'GEOFENCE_TOLERANCE_M' => 60,
  'OPENAI_API_KEY' => 'sk-your-key'
];

function db(){
  global $CFG;
  static $conn = null;
  if($conn===null){
    $conn = new mysqli($CFG['DB_HOST'],$CFG['DB_USER'],$CFG['DB_PASS'],$CFG['DB_NAME']);
    if($conn->connect_error){
      http_response_code(500);
      header('Content-Type: application/json');
      echo json_encode(['ok'=>false,'error'=>'DB connection failed: '.$conn->connect_error]);
      exit;
    }
    $conn->set_charset('utf8mb4');
  }
  return $conn;
}

function json_out($data,$code=200){
  http_response_code($code);
  header('Content-Type: application/json');
  echo json_encode($data);
  exit;
}
function require_post($keys){
  foreach($keys as $k){
    if(!isset($_POST[$k])) json_out(['ok'=>false,'error'=>"Missing field: $k"],400);
  }
}
