<?php

namespace App\im;

use App\config\ImConfig;
use App\im\Users;

// 处理request请求
class HandleRequest
{

    public static function handle($sever, $request, $response)
    {
        $path_info = $request->server['path_info'];
        $post = $request->post;
        $get = $request->get;
        // 重启
        if ($get['opt'] == 'reload' && $get['key'] == ImConfig::SERVER_OPT_KEY) {
            // 重启服务器
            $res = $sever->reload();
            $response->end(json_encode(['reload' => $res]));
            return;
        }
        // 重启服务器
        if ($get['opt'] == 'stop' && $get['key'] == ImConfig::SERVER_OPT_KEY) {

            $res = $sever->shutdown();
            $response->end(json_encode(['stop' => $res]));
            return;
        }
        // 用户注册
        if ($path_info == '/users/register'){
            $users = new Users();
            $users->register($post, $response);
        }
    }
}