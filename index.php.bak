<?php

require 'Chat.php';
require 'Users.php';

$server = new Swoole\WebSocket\Server("0.0.0.0", 9501);

//$server->on('open', function (Swoole\WebSocket\Server $server, $request) {
//    echo "server: handshake success with fd{$request->fd}\n";
////    var_dump($request);
//    // 建
//});
$server->on('handshake', function (\swoole_http_request $request, \swoole_http_response $response) {

     //print_r( $request->server );
     echo $request->server['query_string'].'\\n';
     // 有这里进行判断，是否可以建立连接
    $user = new Users();
     if (!$user->login($request)) {
        $response->end();
         return false;
     }

    // websocket握手连接算法验证
    $secWebSocketKey = $request->header['sec-websocket-key'];
    $patten = '#^[+/0-9A-Za-z]{21}[AQgw]==$#';
    if (0 === preg_match($patten, $secWebSocketKey) || 16 !== strlen(base64_decode($secWebSocketKey))) {
        $response->end();
        return false;
    }
    echo $request->header['sec-websocket-key'];
    $key = base64_encode(sha1(
        $request->header['sec-websocket-key'] . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11',
        true
    ));

    $headers = [
        'Upgrade' => 'websocket',
        'Connection' => 'Upgrade',
        'Sec-WebSocket-Accept' => $key,
        'Sec-WebSocket-Version' => '13',
    ];

    // WebSocket connection to 'ws://127.0.0.1:9502/'
    // failed: Error during WebSocket handshake:
    // Response must not include 'Sec-WebSocket-Protocol' header if not present in request: websocket
    if (isset($request->header['sec-websocket-protocol'])) {
        $headers['Sec-WebSocket-Protocol'] = $request->header['sec-websocket-protocol'];
    }

    foreach ($headers as $key => $val) {
        $response->header($key, $val);
    }

    $response->status(101);
    $response->end();
});

$server->on('message', function (Swoole\WebSocket\Server $server, $frame) {
//    echo "receive from {$frame->fd}:{$frame->data},opcode:{$frame->opcode},fin:{$frame->finish}\n";
//    $server->push(1, '收到客户消息：'.$frame->data);
    // 处理聊天消息转发
    Chat::handle($server, $frame);
});

$server->on('close', function ($ser, $fd) {
    echo "client {$fd} closed\n";
});

$server->start();