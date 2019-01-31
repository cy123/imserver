<?php

namespace App\im;

use App\config\ImConfig;
use App\service\Sqlite;
use App\im\dao\Users as usersDao;

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
            $chat = new Chat();
            $chat->handle($server, $frame);
        }

        // 用户登录
        if ($data['message_type'] == ImConfig::MESSAGE_TYPE_LOGIN) {
            $users = new Users();
            $users->login($server, $frame);
        }

        // 退出登录
        if ($data['message_type'] == ImConfig::MESSAGE_TYPE_LOGOUT) {
            $users = new Users();
            $users->logout($server, $frame);
        }

        // 添加好友
        if ($data['message_type'] == ImConfig::MESSAGE_TYPE_ADD_FRIENDS) {
            $users = new Users();
            $users->addfriends($server, $frame);
        }

        // 设置消息已读
        if ($data['message_type'] == ImConfig::MESSAGE_TYPE_READ) {
            $userDao = new usersDao();
            $userDao->setRead($data['qq'], $data['to_qq']);
        }

        // others
    }

    /**
     * 处理onopen
     */
    public static function handleOpen($server, $request)
    {
        $params = $request->get;
        $common = new Common();

        // 判断是否有登录
        if (!empty($params['session_id']) && $params['session_id'] != 'null') {
            // 断开之前的连接
            $usersDao = new usersDao();
            $user = $usersDao->getUserBySessionId($params['session_id']);
            if (!empty($user['fd'])) {
                $server->close($user['fd']);
            }
            // 拉取用户好友
            $friends = usersDao::getfriends($params['session_id']);
            $data = [
                'message_type'=> ImConfig::MESSAGE_TYPE_ONOPEN,
                'friends' => $friends,
                'unline_user_num' => count($server->connections)
            ];
            $common->sendSuccess($server, $request->fd, $data);
            // 更新用户fd
            usersDao::updateUserFdBySessionId($request->fd, $params['session_id']);
        }

        // 通知其它的在线用户更新在线人数
        $unline_user_num = count($server->connections);
        foreach ($server->connections as $fd) {
            // 判断是否是当前连接
            $online_user = [
                'message_type' => ImConfig::MESSAGE_TYPE_ONLINE_USER_NUM,
                'unline_user_num'=> $unline_user_num
            ];
            $common->sendSuccess($server, $fd, $online_user);
        }
    }

    /**
     * 处理
     * @param $server
     * @param $fd
     */
    public static function handleClose($server, $fd)
    {
        $unline_user_num = count($server->connections) - 1;
        $common = new Common();
        foreach ($server->connections as $fd_new) {
            if ($fd_new == $fd) continue;
            $online_user = [
                'message_type' => ImConfig::MESSAGE_TYPE_ONLINE_USER_NUM,
                'unline_user_num'=> $unline_user_num
            ];
            $common->sendSuccess($server, $fd_new, $online_user);
        }
    }
}