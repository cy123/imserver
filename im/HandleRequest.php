<?php

namespace App\im;

use App\im\Users;

// 处理request请求
class HandleRequest
{

    public static function handle($sever, $request, $response)
    {
        $path_info = $request->server['path_info'];
        $post = $request->post;

        // 用户注册
        if ($path_info == '/users/register'){
            $users = new Users();
            $users->register($post, $response);
        }
    }
}