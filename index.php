<?php

require './config/ImConfig.php';

//  websocket服务器入口
// 接收参数
$opt = !empty($argv[1]) ? $argv[1] : '';

switch ($opt) {

    case 'start':
        echo "\033[32;32m 服务器开始启动... \033[0m".PHP_EOL;
        require 'im.php';
        break;
        // 重启服务器
    case 'reload':
        reload();
        break;
        //
    case 'stop':
        stop();
        break;
    default:
//        echo "\033[32;32m 服务器开始启动... \033[0m".PHP_EOL;
        require 'im.php';
}

// 重启服务器 通过http去通知服务器重启
function reload()
{
    $url = '127.0.0.1?opt=reload&key='.\App\config\ImConfig::SERVER_OPT_KEY;
    $res = curl_get($url);
    if (empty($res)) {
        echo "\033[32;32m 服务器未启动 \033[0m";
        echo PHP_EOL;
        return;
    }
    if ($res->reload == true) {
       echo "\033[32;32m reload success \033[0m";
       echo PHP_EOL;
    }
}

function stop()
{
    $url = '127.0.0.1?opt=stop&key='.\App\config\ImConfig::SERVER_OPT_KEY;
    $res = curl_get($url);
    if ($res->stop === true) {
        echo '\033[32;32m stop success \033[0m'.PHP_EOL;
    }
}

function curl_get($url)
{
    $curl = curl_init();
    //设置抓取的url
    curl_setopt($curl, CURLOPT_URL, $url);
    // 设置端口
    curl_setopt($curl, CURLOPT_PORT, 9501);
    //设置头文件的信息作为数据流输出
    curl_setopt($curl, CURLOPT_HEADER, 1);
    //设置获取的信息以文件流的形式返回，而不是直接输出。
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    //执行命令
    $data = curl_exec($curl);
    //关闭URL请求
    curl_close($curl);
    if(empty($data)) {
        return '';
    }

    list($header, $body) = explode("\r\n\r\n", $data, 2);
    return json_decode($body);
}
