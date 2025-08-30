<?php
require_once __DIR__.'/../../config.php';

function current_window_start($sec){ return floor(time()/$sec)*$sec; }
function token_for_window($class_id,$winStart){
  global $CFG; return hash_hmac('sha256', $class_id.'|'.$winStart, $CFG['QR_SALT']);
}
function haversine_m($lat1,$lon1,$lat2,$lon2){
  $R=6371000.0; $dLat=deg2rad($lat2-$lat1); $dLon=deg2rad($lon2-$lon1);
  $a=sin($dLat/2)**2 + cos(deg2rad($lat1))*cos(deg2rad($lat2))*sin($dLon/2)**2;
  return 2*$R*atan2(sqrt($a),sqrt(1-$a));
}
