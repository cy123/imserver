<?php

namespace App\im;

use App\config\ImConfig;
use App\service\Sqlite;

// 处理消息
class Message
{

    public function __construct()
    {
        
    }

    /**
     * 处理消息
     */
    public static function handle($server, $frame)
    {

        $data = $frame->data;
        $data = json_decode($data, true);
        // 聊天相关
        if ($data['message_type'] == ImConfig::MESSAGE_TYPE_CHAT) {
            Chat::handle($server, $frame);
        }

        // 用户登录
        if ($data['message_type'] == ImConfig::MESSAGE_TYPE_LOGIN) {
            $users = new Users();
            $users->login($server, $frame);
        }

        // 用户注册
        if ($data['message_type'] == ImConfig::MESSAGE_TYPE_REGISTER) {
            $users = new Users();
            $users->register($frame->data);
        }

        // 添加好友
        if ($data['message_type'] == ImConfig::MESSAGE_TYPE_ADD_FRIENDS) {
            $users = new Users();
            $users->addfriends($server, $frame);
        }

        // 设置消息已读
        if ($data['message_type'] == ImConfig::MESSAGE_TYPE_READ) {
            self::setRead($data['qq'], $data['to_qq']);
        }

        // others
    }

    /**
     * 设置消息已读
     * @param $qq
     */
    public static function setRead($qq, $to_qq)
    {
        $sql = "
            UPDATE message set is_read = 1 WHERE qq={$qq} and to_qq = {$to_qq}
        ";
        Sqlite::exec($sql);
    }
}