<?php
require_once __DIR__.'/../../config.php';

function gpt_chat($messages,$model='gpt-4o-mini',$json=false){
  global $CFG;
  $url="https://api.openai.com/v1/chat/completions";
  $data=['model'=>$model,'messages'=>$messages];
  if($json){ $data['response_format']=['type'=>'json_object']; }
  $ch=curl_init($url);
  curl_setopt_array($ch,[
    CURLOPT_RETURNTRANSFER=>true,
    CURLOPT_HTTPHEADER=>['Content-Type: application/json','Authorization: Bearer '.$CFG['OPENAI_API_KEY']],
    CURLOPT_POST=>true,
    CURLOPT_POSTFIELDS=>json_encode($data)
  ]);
  $resp=curl_exec($ch); if($resp===false) return ['ok'=>false,'error'=>curl_error($ch)];
  $code=curl_getinfo($ch,CURLINFO_HTTP_CODE); curl_close($ch);
  $j=json_decode($resp,true); if($code>=400||!$j) return ['ok'=>false,'error'=>'API error','raw'=>$resp];
  $content=$j['choices'][0]['message']['content'] ?? '';
  return ['ok'=>true,'content'=>$content];
}
