<?php

namespace App\im;

use App\config\chatConst;
use App\config\ImConfig;
use App\service\RedisTool;
use App\service\Sqlite;

// 处理聊天消息类
class Chat
{
    /**
     * 判断是个人聊天，还是群聊
     * @param $data
     */
    public static function handle($server, $frame)
    {
        $data = $frame->data;
        $data = json_decode($data, true);

        if ($data['chat_type'] == chatConst::CHAT_TYPE_SIGNLE) {
            self::signle($server, $data);
        }
        if ($data['chat_type'] == chatConst::CHAT_TYPE_GROUP) {
            self::group($server, $data, $frame);
        }

    }

    /**
     * 一对一聊天
     * @param $re
     */
    public static function signle($server, $data)
    {
        // 直接把消息转发给对方即可
        $users = Users::getUserByqq($data['to_qq']);
        $fds = [];
        foreach ($server->connections as $fd) {
            $fds[] = $fd;
        }
        //$users = RedisTool::hGetAll($data['to_qq']);
        // 如果对方在线
        $msg = [
            'code' => 0,
            'message_type'=> ImConfig::MESSAGE_TYPE_CHAT,
            'chat_type' => chatConst::CHAT_TYPE_SIGNLE,
            'to_qq' => $data['user_qq'],
            'msg' => $data['msg'],
            'datetime'=> date('Y-m-d H:i:s')
        ];
        if (!empty($users['fd']) && in_array($users['fd'], $fds)) {
            $server->push($users['fd'], json_encode($msg));
        } else {

            // 等对方在线了，再发送消息，在用户上线的时候进行检查用户是否有未接收的消息
            self::addUnread($data);
        }

    }

    /**
     * 添加未读消息
     * @param $data
     */
    public static function addUnread($data)
    {
        $sql = "
             INSERT INTO message (qq, to_qq, message, send_success, is_read)
             VALUES ({$data['user_qq']}, '{$data['to_qq']}', '{$data['msg']}', 0, 0 );
        ";
        Sqlite::exec($sql);
    }
    /**
     * 群聊
     * @param $re
     */
    public static function group($server, $data, $frame)
    {
        // 查询出这个群里的所有用户的fd,然后发送消息
        // 发送给所有在线用户
        $users = Users::getUserByqq($data['user_qq']);
        if ($data['to_qq'] == 'all') {
            $msg = [
                'code' => 0,
                'message_type'=> ImConfig::MESSAGE_TYPE_CHAT,
                'chat_type' => chatConst::CHAT_TYPE_GROUP,
                'to_qq' => $data['to_qq'],
                'msg' => $data['msg'],
                'userinfo'=> $users,
                'datetime'=> date('Y-m-d H:i:s')
            ];

            // 发送给所有在线用户
            foreach ($server->connections as $fd) {
                // 不发送给自己
                if ($fd == $frame->fd) {
                    continue;
                }
                $server->push($fd, json_encode($msg));
            }
        }
    }

    /**
     * 发送数据
     * @param $server
     * @param $fds
     */
    public static function send($server, $fds)
    {

    }

}