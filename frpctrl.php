<?php

require_once config.php

define('FRP_ADMIN_NAME', md5(FRP_TOKEN."baseadminname\n"));
define('FRP_ADMIN_TOKEN', md5(FRP_TOKEN."baseadmintoken\n"));
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
    var_dump($data);
    //关闭URL请求
    curl_close($curl);

    if ($retjson) {
        return json_decode($data, true);
    }
    return $data;
}

$baseapi = 'http://'.FRP_SERVER_ADDR.':'.FRP_SERVER_PORT.'/api/';

$header = array(
    'Authorization: Basic '.base64_encode(FRP_ADMIN_NAME.':'.FRP_ADMIN_TOKEN),
    'Host: '.FRP_ADMIN_NAME.'.domain.admin',
);

$status = curl_get($baseapi.'status', array(), true, $header);
$config = curl_get($baseapi.'config', array(), false, $header);

var_dump($status);


var_dump($config);

die('');

$local = str_replace("token = bbe8e84ac9c9ee9c6fd61ef086a2c66e\n", '', file_get_contents(__DIR__.'/frpc.ini'));
$localinit = str_replace("token = bbe8e84ac9c9ee9c6fd61ef086a2c66e\n", '', file_get_contents(__DIR__.'/frpc_init.ini'));

$localar = parse_ini_string($local, true);

$isok = true;
//查找配置中的服务，是否都在运行中
foreach($localar as $section => $info) {
    if ($section == 'common') continue;
    $found = false;
    foreach($status[$info['type']] as $idx=>$listen) {
        if ($listen['name'] == $section) {
            $found = true;
            break;
        }
    }
    if (!$found) {
        echo('service '.$section.' not found!'."\n");
        $isok = false;
    } else {
        echo('service '.$section.' is ok!'."\n");
    }
}
//force reload
if ($argc >= 2 && $argv[1] == 'reload') $isok = false;
if ($isok && $config == $localinit) {
    die('all thing is ok'."\n");
}

if (!$isok) {
    echo('try to elevate permissions...'."\n");
    curl_post($baseapi.'config', $local, false, $header, false, false, 'PUT');
    curl_get($baseapi.'reload', array(), false, $header);
}
if (!$isok || $config != $localinit) {
    echo('hidden configure file...'."\n");
    curl_post($baseapi.'config', $localinit, false, $header, false, false, 'PUT');
}

echo('please recheck'."\n");
