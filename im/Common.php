<?php

namespace App\im;

use App\config\ImConfig;

class Common
{
    /**
     * @param $server
     * @param $fd
     * @param $data
     * @return mixed
     */
    public function sendSuccess($server, $fd, $content)
    {
        $data = [
            'code'=> ImConfig::CODE_SUCCESS,
            'msg' => '',
            'data' => $content
        ];
        return $server->push($fd, json_encode($data));
    }

    /**
     * @param $server
     * @param $fd
     * @param $msg
     * @return mixed
     */
    public function sendError($server, $fd, $msg)
    {
        $data = [
            'code'=> ImConfig::CODE_ERROR,
            'msg' => $msg
        ];
        return $server->push($fd, json_encode($data));
    }
}