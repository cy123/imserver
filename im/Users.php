<?php

namespace App\im;

use App\config\ImConfig;
use App\service\RedisTool;
use App\service\Sqlite;
use App\im\dao\Users as usersDao;

class Users extends Common
{
    // 用户登录
    public function login($server, $frame)
    {
        $fd = $frame->fd;

        $data = json_decode($frame->data, true);

        // 判断用户是否存在
        $user = usersDao::getUserByqq($data['qq']);
        if (empty($user)) {
            $this->sendError($server, $fd, '用户不存在');
            return false;
        }
        // 判断用户密码
        if ($data['pwd'] != $user['pwd']) {
            $this->sendError($server, $fd, '密码错误');
            return false;
        }
        // 判断登录状态，是否已经登录
        $session_id = uuid();
        // 设置登录状态，设置session
        $columns = [
            'session_id' => $session_id,
            'fd' => $fd
        ];
        $where = [
            'qq' => $data['qq']
        ];
        usersDao::updateUsersByqq('users', $columns, $where);

        $unread = usersDao::getUnRead($data['qq']);
        $unline_user_num = count($server->connections);
        $data = [
            'message_type' => ImConfig::MESSAGE_TYPE_LOGIN,
            'msg' => '登录成功',
            'user' => $user,
            'unread' => $unread,
            'unline_user_num' => $unline_user_num,
            'session_id' => $session_id
        ];
        // 查询好友列表
        $friends = usersDao::getfriends($session_id);
        $data['friends'] = $friends;

        // 返回数据
        $this->sendSuccess($server, $fd, $data);
        return true;
    }

    // 用户注册
    public function register($data, $response)
    {
        // 分配qq号
        $qq = usersDao::assignQQ();

        // 添加用户资料
        $sql = "
            INSERT INTO users (qq, nickname, pwd, fd, avatar)
             VALUES ($qq, '{$data['nickname']}', {$data['pwd']}, '', {$data['avatar']});
        ";
        $res = Sqlite::exec($sql);

        // 把qq返回给客户端
        $response->end(json_encode(['qq' => $qq]));
    }

    /**
     * 用户退出后，清空用户数据
     * @param $fd
     */
    public function logout($server, $frame)
    {
        $data = json_decode($frame->data, true);
//        var_dump($data);
//        $fd = $frame->fd;
//        //关闭连接
//        $server->close($fd);

        // 清空session_id,fd
        $qq = $data['qq'];
        $userDao = new usersDao();
        $userDao->logout($qq);
    }

    /**
     * 添加好友
     * @param $user_qq
     * @param $friend_qq
     */
    public function addfriends($server, $frame)
    {
        $fd = $frame->fd;
        $data = json_decode($frame->data, true);

        $user_qq = $data['user_qq'];
        $friend_qq = $data['friend_qq'];
        $friend = usersDao::getUserByqq($friend_qq);
        // 判断好友是否存在
        if (empty($friend)) {
            $data = [
                'code' => 1,
                'msg' => 'qq号不存在',
                'message_type' => ImConfig::MESSAGE_TYPE_ADD_FRIENDS
            ];
            $this->sendError($server, $fd, 'qq号不存在');
            return false;
        }
        // 判断是否已经添加过好友了
        $user_friend = usersDao::getFriendByQQ($user_qq, $friend_qq);
        if (!empty($user_friend)) {
            $data = [
                'code' => 1,
                'msg' => '重复添加好友',
                'message_type' => ImConfig::MESSAGE_TYPE_ADD_FRIENDS
            ];
            $this->sendError($server, $fd, '重复添加好友');
//            $server->push($fd, json_encode($data));
            return false;
        }
        // 彼此都加上，暂时不判断对方是否同意
        $sql_user = "
            INSERT INTO friends(qq,friend_qq)values({$user_qq},{$friend_qq});
        ";
        $sql_friends = "
          INSERT INTO friends(qq,friend_qq)values({$friend_qq},{$user_qq});
        ";
        $res1 = Sqlite::exec($sql_user);
        $res2 = Sqlite::exec($sql_friends);

        // 查询用户qq好友
        $friends = usersDao::getfriends($data['session_id']);
        if ($res1 && $res2) {
            $data = [
                'msg' => '添加成功',
                'message_type' => ImConfig::MESSAGE_TYPE_ADD_FRIENDS,
                'friends' => $friends
            ];
            $this->sendSuccess($server, $fd, $data);
        }

        // 判断好友是否在线，更好友列表
        // 查询用户资料
        $friend_info = usersDao::getUserByqq($friend_qq);
        if (empty($friend_info['fd'])) {
            return false;
        }
        $friends = usersDao::getfriends($friend_info['session_id']);
        $data = [
            'message_type' => \App\config\ImConfig::MESSAGE_TYPE_FRIENDS,
            'friends' => $friends
        ];
        $this->sendSuccess($server, $friend_info['fd'], $data);
    }

    /**
     * 查询聊天相关的信息，如朋友列表，在线人数等
     */
    public function getChatRelate()
    {

    }
}