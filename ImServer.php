<?php

use App\im\Chat;
use App\im\Message;
use App\im\Users;
use App\im\HandleRequest;
use App\im\Test;
use App\config\ImConfig;

class ImServer
{

    public $server;
    public function __construct() {
        $this->server = new Swoole\WebSocket\Server("0.0.0.0", 9501);
//        $this->setConfig();
        self::onopen();
        self::onmessage();
        self::onclose();
        self::onrequest();
        $this->server->start();
    }

    public  function setConfig()
    {
        $this->server->set(
          [
              'daemonize' => false,
              'log_file' => '/var/log/swoole.log'
          ]
        );

    }

    /**
     *
     */
    private function onopen ()
    {

        $this->server->on('open', function (swoole_websocket_server $server, $request) {
            echo "server: handshake success with fd{$request->fd}\n";
            // 处理
            Message::handleOpen($this->server, $request);
        });
    }

    /**
     * 处理发送过来的消息
     */
    private function onmessage()
    {
        $this->server->on('message', function (Swoole\WebSocket\Server $server, $frame) {
//            echo "receive from {$frame->fd}:{$frame->data},opcode:{$frame->opcode},fin:{$frame->finish}\n";
//            $server->push($frame->fd, "this is server");
            Message::handle($server, $frame);
        });
    }

    /**
     * 断开连接
     */
    private function onclose()
    {
        $this->server->on('close', function ($server, $fd) {
            echo "client {$fd} closed\n";
             // 通知其它的在线用户更新在线人数
            Message::handleClose($server, $fd);
        });
    }

    private function onrequest()
    {
        $this->server->on('request', function ($request, $response) {
            $response->header('Access-Control-Allow-Origin', $request->header['origin'] ?? '');
            $response->header('Access-Control-Allow-Methods', 'OPTIONS');
            $response->header('Access-Control-Allow-Headers', 'x-requested-with,session_id,Content-Type,token,Origin');
            $response->header('Access-Control-Max-Age', '86400');
            $response->header('Access-Control-Allow-Credentials', 'true');

            if ($request->server['request_method'] == 'OPTIONS') {
                $response->status(200);
                $response->end();
                return;
            };
            HandleRequest::handle($this->server, $request, $response);
            // 接收http请求从get获取message参数的值，给用户推送
            // $this->server->connections 遍历所有websocket连接用户的fd，给所有用户推送
//            foreach ($this->server->connections as $fd) {
//                $this->server->push($fd, $request->get['message']);
//            }
        });
    }
}