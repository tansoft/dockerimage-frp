<?php

define('FRP_TOKEN','[FRP_TOKEN]');
define('FRP_SERVER_ADDR','[FRP_SERVER_ADDR]');
define('FRP_SERVER_PORT','[FRP_SERVER_PORT]');

$mappings = [
	//serverip,serverport,localport
	['10.21.0.88',6379,6379],
	//['127.0.0.1',22,822],
];

define('FRP_ADMIN_NAME', md5(FRP_TOKEN."baseadminname\n"));
define('FRP_ADMIN_TOKEN', md5(FRP_TOKEN."baseadmintoken\n"));
define('FRP_CLIENT_NAME', md5(FRP_TOKEN."clientadminname\n"));
define('FRP_CLIENT_TOKEN', md5(FRP_TOKEN."clientadmintoken\n"));
define('FRP_TOKEN_MD5',md5(FRP_TOKEN."basefrptoken\n"));

function curl_post($url, $postData, $ispostjson = false, $headers = array(), $retjson = true, $retheader = false, $customheader = 'POST') {
    $tmp = $postData;
    //初始化
    $curl = curl_init();
    //设置抓取的url
    curl_setopt($curl, CURLOPT_URL, $url);
    //设置头文件的信息作为数据流输出
    curl_setopt($curl, CURLOPT_HEADER, 0);
    //设置获取的信息以文件流的形式返回，而不是直接输出。
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    //设置post方式提交
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $customheader);
    //设置post数据
    if ($ispostjson) {
        $headers[] = 'Content-Type: application/json;charset=utf8';
        $postData = json_encode($postData);
    } else {
        if (is_array($postData)) {
            $postData = http_build_query($postData);
        }
    }
    curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
    if (!empty($headers)) {
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    }
    if ($retheader) {
        curl_setopt($curl, CURLOPT_HEADER, true);
    }
    $mtime = microtime(true);
    //执行命令
    $data = curl_exec($curl);
    //关闭URL请求
    curl_close($curl);
    if ($retjson) {
        return json_decode($data, true);
    }
    return $data;
}

function curl_get($url, $params = array(), $retjson = true, $headers = array()) {
    //初始化
    $curl = curl_init();
    //设置抓取的url
    $query = !empty($params) ? http_build_query($params) : '';
    $url = !empty($query) ? $url . '?' . $query : $url;
    curl_setopt($curl, CURLOPT_URL, $url);
    //设置头文件的信息作为数据流输出
    curl_setopt($curl, CURLOPT_HEADER, 0);
    if (!empty($headers)) {
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    }
    //设置获取的信息以文件流的形式返回，而不是直接输出。
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $mtime = microtime(true);
    //执行命令
    $data = curl_exec($curl);
    //关闭URL请求
    curl_close($curl);

    if ($retjson) {
        return json_decode($data, true);
    }
    return $data;
}

function save_conversion($key, $value) {
    if ($value === false || $key == 'login_fail_exit') {
        return 'false';
    }
    return $value;
}

function iniar_to_string($ar) {
    $out = '';
    foreach($ar as $section => $values) {
        $out .= '['.$section."]\n";
        foreach($values as $key=>$value) {
            $out .= $key.'='.save_conversion($key, $value)."\n";
        }
        $out .= "\n";
    }
    return $out;
}

$baseapi = 'http://'.FRP_SERVER_ADDR.':'.FRP_SERVER_PORT.'/api/';

$header = array(
    'Authorization: Basic '.base64_encode(FRP_ADMIN_NAME.':'.FRP_ADMIN_TOKEN),
    'Host: '.FRP_ADMIN_NAME.'.domain.admin',
);

$cliheader = array(
    'Authorization: Basic '.base64_encode(FRP_CLIENT_NAME.':'.FRP_CLIENT_TOKEN),
    'Host: '.FRP_CLIENT_NAME.'.domain.admin',
);

//$status = curl_get($baseapi.'status', array(), true, $header);
$config = curl_get($baseapi.'config', array(), false, $header);

//$clistatus = curl_get($baseapi.'status', array(), true, $cliheader);
$cliconfig = curl_get($baseapi.'config', array(), false, $cliheader);

$configar = parse_ini_string($config, true);
$cliconfigar = parse_ini_string($cliconfig, true);

foreach($configar as $section => $info) {
    if ($section == 'common' || substr($section, 0, 8) == 'adminweb') continue;
    unset($configar[$section]);
}
foreach($cliconfigar as $section => $info) {
    if ($section == 'common' || substr($section, 0, 8) == 'adminweb') continue;
    unset($cliconfigar[$section]);
}
if (empty($mappings)) {
    die('need $mappings');
}
foreach($mappings as $idx => $mapping) {
    $sk = md5(implode('.',$mapping));
    $key = 'map_'.$sk;
    $configar[$key] = ['type'=>'stcp', 'sk'=>$sk, 'local_ip'=>$mapping[0], 'local_port'=>$mapping[1]];
    $cliconfigar[$key.'_visitor'] = ['type'=>'stcp',
        'role'=>'visitor', 'server_name'=>$key,
        'sk'=>$sk, 'bind_addr'=>'0.0.0.0', 'bind_port'=>$mapping[2]];
}
$newconfig = "# frpc.ini\n".iniar_to_string($configar);
$clinewconfig = "# frpc.ini\n".iniar_to_string($cliconfigar);
if ($newconfig != $config || $clinewconfig != $cliconfig) {
    echo("need update!\n");
    curl_post($baseapi.'config', $newconfig, false, $header, false, false, 'PUT');
    curl_get($baseapi.'reload', array(), false, $header);
    curl_post($baseapi.'config', $clinewconfig, false, $cliheader, false, false, 'PUT');
    curl_get($baseapi.'reload', array(), false, $cliheader);
} else {
    echo("all thing is ok\n");
}
echo($newconfig);
echo($clinewconfig);
