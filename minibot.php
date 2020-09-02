<?php

// ==== CONFIGURACION INICIAL DE PHP ====

date_default_timezone_set('UTC');
mb_internal_encoding('UTF-8');

// ==== SE EJECUTA EN SEGUNDO PLANO PARA QUE TELEGRAM RECIBA UN OK LO ANTES POSIBLE SIN DEJAR CONEXIONES ABIERTAS ====

set_time_limit(0);
ignore_user_abort(true);
$out = '{"ok":true}';
header('Connection: close');
header('Content-Length: ' . strlen($out));
header("Content-type:application/json");
echo $out;
flush();
if (function_exists('fastcgi_finish_request')) {
    fastcgi_finish_request();
}

// ==== DEFINICION DE VARIABLES ====

require_once 'config.php';

define('apiurl', 'https://api.telegram.org/bot' . token);
define('parsehtml', ['parse_mode' => 'HTML']);

$GLOBALS['now']=date_create();
define('wicon',[
    '01'=>['d'=>'☀️','n'=>'☀️'],
    '02'=>['d'=>'🌤','n'=>'🌤'],
    '03'=>['d'=>'🌥️','n'=>'🌥️'],
    '04'=>['d'=>'☁️','n'=>'☁️'],
    '09'=>['d'=>'🌧','n'=>'🌧'],
    '10'=>['d'=>'🌦','n'=>'🌦'],
    '11'=>['d'=>'⛈','n'=>'⛈'],
    '13'=>['d'=>'🌨','n'=>'🌨'],
    '50'=>['d'=>'🌫','n'=>'🌫'],
]);

// ==== LOGS PARA VERIFICACION DE ERRORES ====

function debug($t = false) {
    if ($t === false) {
        $t = $GLOBALS['raw'];
    }
    if (!is_string($t)) {
        $t = json_encode($t);
    }
    return sendm(botadmin,$t);
}

function sendtolog($t, $d = false) {
    if ($d) {
        file_put_contents('log.txt', $t . "\n", FILE_APPEND);
    }
    return 1;
}

// ==== FUNCION DE RESPUESTAS ====

function getweb($url,$cachetime=300){
    $dir='wcache/'.md5($url).'.cache';
    if(file_exists($dir)){
        if((filemtime($dir)+$cachetime)>time()){
            return file_get_contents($dir);
        }
    }
    $r=file_get_contents($url);
    file_put_contents($dir, $r);
    return $r;
}

function send($m, $arr, $debug = false) {
    $return = true;
    $url = apiurl . "/$m?" . http_build_query($arr);
    $ch = curl_init();
    if ($debug) {
        sendtolog(json_encode($url));
    }
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_SSL_VERIFYPEER => 0
    ]);
    $rr = curl_exec($ch);
    $err = curl_error($ch);
    if (strlen($err) > 0) {
        sendtolog($err);
        $return = false;
    }
    curl_close($ch);
    return [$return, $rr];
}

function sendm($chat,$text,$opt=[]){
    $optf=[
        'chat_id' => $chat,
        'text' => $text,
        'parse_mode'=>'HTML'
    ];
    return send('sendMessage', array_merge($optf,$opt));
}

// ==== FUNCIONES PARA CREACION DE BOTONES ====

/* [] = separadores de celdas y lineas
 * parametro 1 = texto de boton
 * parametro 2 = dato del boton
 * parametro 3 = url (opcional)
 * 
 * Ejemplo:
 * [[['Boton 1','botones-idboton1']],[['Boton 2','botones-idboton2','https://google.com']]]
 * 
 * Resultado:
 * -----------
 * | Boton 1 |
 * -----------
 * | Boton 2 |  (al presionar, enviará a https://google.com)
 * -----------
 */

function tablabotones($a) {
    $botones = [];
    foreach ($a as $l) {
        $templ = [];
        foreach ($l as $c) {
            $tempb = [
                'text' => (string) $c[0],
                'callback_data' => (string) $c[1],
            ];
            if (isset($c[2])) {
                $tempb['url'] = $c[2];
            }
            $templ[] = $tempb;
        }
        $botones[] = $templ;
    }
    return json_encode(['inline_keyboard' => $botones]);
}

function randarray($arr){
    return $arr[array_rand($arr)];
}

// ==== COMANDOS ====

function command_banana($arr) {
//debug($m);
    if (isset($arr[0]['reply_to_message'])) {
        $text = 'ño '.randarray(['😌','😋','😝','🙃']);

        $r = $arr[0]['reply_to_message'];
        if (!in_array($r['from']['id'], array_merge(vip, [botadmin, botid]))) {
            $text = 'Usuario <b>' .
                    trim($r['from']['first_name'] . ' ' . (
                            isset($r['from']['last_name']) ? $r['from']['last_name'] : ''
                    )) . '</b>' . (
                    isset($r['from']['username']) ? (' (@' . $r['from']['username'] . ')') : ''
                    ) . ' (id: <code>' . $r['from']['id'] . '</code>) ha sido Bananeado! 🍌 :v';
        }
        return sendm($arr[0]['chat']['id'],$text);
    }
}

function command_f($arr) {
    return sendm($arr[0]['chat']['id'],'efe en el chat ⚰',['reply_to_message_id' => $arr[1]['message_id']]);
}

function command_botones($arr) {
    $botones = tablabotones([
        [['Boton 1', 'botones-idboton1']],
        [['Boton 2', 'botones-idboton2', 'https://google.com']],
    ]);
    $txt = "Presione Un Botón:";
    $tosend = [
        'chat_id' => $arr[0]['chat']['id'],
        'text' => $txt,
        'parse_mode' => 'HTML',
        'reply_markup' => $botones,
        'disable_web_page_preview' => true,
        'reply_to_message_id' => $arr[1]['message_id'],
    ];
    sendtolog(json_encode($tosend), 1);
    return send('sendMessage', $tosend, false);
}

function command_clima($arr){
    if(trim($arr[2])==''){
        return sendm();
    }
    $wurl='http://api.openweathermap.org/data/2.5/weather?q='.$arr[2].'&appid='.weatherapi.'&units=metric&lang=sp';
    $datar=getweb($wurl);
    if($datar===false){
        sendm($arr[0]['chat']['id'],'<i>Error de comando</i>');
    }
    $data=json_decode($datar,true);
    $codes= json_decode(file_get_contents("paises.json"),true);
    $dt=date_create('@'.$data['dt']);
    $intervalr = date_diff($GLOBALS['now'], $dt);
    $intervalr =$intervalr->format('%h:%i');
    $interval=explode(':',$intervalr);
    $iconr=[substr($data['weather'][0]['icon'],0,2),substr($data['weather'][0]['icon'],2,1)];
    $hace=($interval[0]==0?'':($interval[0].' '.($interval[0]==1?'hora':'horas').' ')).$interval[1].' '.($interval[1]==1?'minuto':'minutos');
    $icon=wicon[$iconr[0]][$iconr[1]];
    $text="Ciudad:  <b>{$data['name']}</b>
País:  <b>{$codes[$data['sys']['country']]}</b>
Temperatura:  <b>".round(floatval($data['main']['temp']))." °C</b> <i>aprox.</i>
Humedad:  <b>{$data['main']['humidity']}%</b>
Cielo:  <b>".ucfirst($data['weather'][0]['description'])."</b> $icon
Hora de muestra:  <i>hace $hace</i>";
    sendm($arr[0]['chat']['id'],$text);
}

// ==== VERIFICADOR DE COMANDOS ====

function execCommand($m, $c, $d) {
    $c = trim(iconv_substr(trim($c), 0, 1) === '/' ? iconv_substr(trim($c), 1) : $c);
    try {
        $c = explode(' ', $c);
        $command = str_replace('@' . botname, '', strtolower($c[0]));
        
        unset($c[0]);
        $data = array_values($c);
        $datatext = join(' ', $data);
        
        if (function_exists('command_' . $command)) {
            $function = 'command_' . $command;
            sendtolog(json_encode($function([$m, $d, $datatext])));
        }
    } catch (Exception $e) {
        sendtolog('error: ' . addslashes($e->getMessage()));
    }
}

// ==== PARSEO ====

function parsedata($d) {
    $types = ['edited_message', 'message', 'callback_query', 'channel_post'];
    $insid = [0, 0, 1, 0];

    $findtype = -10;
    foreach ($types as $k => $v) {
        if (array_key_exists($v, $d)) {
            $findtype = $k;
            break 1;
        }
    }
    if ($findtype > -1) {
        $m = $insid[$findtype] === 0 ? $d[$types[$findtype]] : $d[$types[$findtype]]['message'];

        // ==== VERIFICACION DE TIPOS DE MENSAJES ====

        switch ($findtype) {
            case 0:  //  ===== MENSAJES EDITADOS =====
                $tosend = [
                    'chat_id' => $m['chat']['id'],
                    'text' => json_encode($d),
                    'parse_mode' => 'HTML',
                ];
                //send('sendMessage',$tosend);
                break;
            case 1:   //  ===== MENSAJES ENVIADOS =====
                $content = $m['text'];
                if (iconv_substr($content, 0, 1) === '/') {
                    execCommand($m, $content, $d);     // ==== SE LLAMA AL VERIFICADOR DE COMANDOS ====
                    break;
                } else {
                    $text = '';
                    // ==== AMOR ====
                    if (preg_match('(bot|linux)', $content) === 1 && preg_match('#(te\samo|te\squiero|me\squieres|me\samas|eres\smi\samor|eres\smi\scariño|eres\sun\samor)#i', $content) === 1) {

                        $arrtext = ['ok', 'gracias', 'ajá', 'y luego?'];

                        if (in_array($m['from']['id'], array_merge(vip, [botadmin]))) {
                            $arrtext = [
                                'Yo también te quiero ❤️',
                                'Tú eres un amor 😳',
                                'Te amo muuuuucho 😍',
                                'Qué lindura 🙈',
                            ];
                        }
                        $text = randarray($arrtext);
                    }
                    // ==== SALUDO ====
                    elseif (stripos($content, 'hola') !== false && (stripos($content, 'linux') !== false || stripos($content, 'bot') !== false)) {
                        $ex = json_encode($d);
                        $text = randarray([
                            'Sí, soy LinuxBot',
                            'Hola hola!',
                            'Holi ❤️',
                        ]);
                    }
                    if ($text !== '') {
                        sendm($m['chat']['id'],$text);
                    }
                    break;
                }
            case 2:   //  ===== CALLBACK QUERY =====
                send('answerCallbackQuery', [
                    'callback_query_id' => $d['callback_query']['id'],
                    'text' => "ID Boton: " . $d['callback_query']['data']
                        ], false);     // ==== SE RESPONDE AL QUERY PARA QUE LA RUEDITA DE CARGA DESAPAREZCA ====

                $tosend = [
                    'chat_id' => $m['chat']['id'],
                    'text' => json_encode($d),
                    'parse_mode' => 'HTML',
                ];
                //send('sendMessage',$tosend);

                break;
        }
    }
}

// ==== OBTENCION DE DATOS Y LLAMADO DE PARSEO ====

$GLOBALS['raw'] = file_get_contents('php://input');
parsedata(json_decode($GLOBALS['raw'], true));
