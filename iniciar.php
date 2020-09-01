<?php

// ==== CONFIGURACION PARA WEBHOOK Y BOT ====

require_once 'config.php'; // editar config.php

// ==== NO TOCAR ESTO ====

header('Content-Type: text/json; charset=utf-8');
$url='https://api.telegram.org/bot'.token.'/setWebhook';
$boturl="https://".ip.'/minibot.php';
$fields = ['url'=>$boturl,'max_connections'=>100];
$headers=['Content-Type:multipart/form-data'];
$postfields = ['certificate' => new CURLFile(certificate)];
$url.='?'.http_build_query($fields);
$ch = curl_init();
curl_setopt_array($ch,[
  CURLOPT_URL => $url,
  CURLOPT_POST => true,
  CURLOPT_POSTFIELDS => $postfields,
  CURLOPT_HTTPHEADER => $headers,
  CURLOPT_RETURNTRANSFER => true
]);
$result = curl_exec($ch);
curl_close($ch);
$j= json_decode($result,true);
$j['url']=$url;
$j['cert']=$cert;
echo json_encode($j);
