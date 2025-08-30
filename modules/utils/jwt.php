<?php
function base64url_encode($d){ return rtrim(strtr(base64_encode($d),'+/','-_'),'='); }
function base64url_decode($d){ return base64_decode(strtr($d,'-_','+/')); }

function jwt_sign($payload,$secret,$ttl=3600){
  $header = ['alg'=>'HS256','typ'=>'JWT'];
  $payload['iat']=time(); $payload['exp']=time()+$ttl;
  $h=base64url_encode(json_encode($header));
  $p=base64url_encode(json_encode($payload));
  $s=base64url_encode(hash_hmac('sha256',"$h.$p",$secret,true));
  return "$h.$p.$s";
}
function jwt_verify($token,$secret){
  $parts=explode('.',$token); if(count($parts)!==3) return [false,'Malformed'];
  [$h,$p,$s]=$parts;
  $chk=base64url_encode(hash_hmac('sha256',"$h.$p",$secret,true));
  if(!hash_equals($chk,$s)) return [false,'Bad signature'];
  $pl=json_decode(base64url_decode($p),true); if(!$pl) return [false,'Bad payload'];
  if(isset($pl['exp']) && time()>$pl['exp']) return [false,'Expired'];
  return [true,$pl];
}
