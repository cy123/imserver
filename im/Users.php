<?php

namespace App\im;

use App\config\ImConfig;
use App\service\RedisTool;
use App\service\Sqlite;

class Users
{
    // 用户登录
    public function login($server, $frame)
    {
        $fd = $frame->fd;

        $data = json_decode($frame->data, true);

        // 判断用户是否存在
        $sql = "
            select * from users where qq = {$data['qq']}
        ";
        $user = Sqlite::find($sql);
        echo $sql;
        if (empty($user)) {
            $data = [
                'code'=> 1,
                'msg' => '用户不存在'
            ];
            $server->push($fd, json_encode($data));
            return false;
        }
        // 判断用户密码
        if ($data['pwd'] != $user['pwd']) {
            $data = [
                'code'=> 1,
                'msg' => '密码错误'
            ];
            $server->push($fd, json_encode($data));
            return false;
        }
        // 判断登录状态，是否已经登录 todo
        $session_id = uuid();
        // 设置登录状态，设置session

        // 设置key=>value 方便查询
        RedisTool::set($fd, $data['qq']);
        $sql = "
           UPDATE users set session_id='{$session_id}', fd={$fd} where qq={$data['qq']}
        ";
        Sqlite::exec($sql);
        $unread = self::getUnRead($data['qq']);
        $data = [
            'code'=> 0,
            'message_type'=> 3,
            'msg' => '登录成功',
            'user'=> $user,
            'unread' => $unread,
            'session_id' => $session_id
        ];
        // 查询好友列表
        $friends = self::getfriends($session_id);
        $data['friends'] = $friends;

        // 返回数据
        $server->push($fd, json_encode($data));
        return true;
    }

    // 用户注册
    public function register($data, $response)
    {
        // 分配qq号
        $qq = self::assignQQ();

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
    public function logout($fd)
    {

        $qq = RedisTool::get($fd);

        // 清空状态
        RedisTool::hSet($qq, 'online', '0');
        RedisTool::hSet($qq, 'fd', '');
    }

    /**
     * 获取好友列表
     * @param $qq
     * @return array
     */
    public static function getfriends($session_id)
    {
        // 查好对应的好友qq
        $sql = "
            select f.friend_qq from users as u 
            inner join friends f on u.qq=f.qq
            where u.session_id= '{$session_id}'
        ";
        $data = Sqlite::select($sql);
        $f_qq = array_column($data, 'friend_qq');
        if(empty($f_qq)) {
           return [];
        }
        $f_qq = implode(',', $f_qq);

        // 查询用户的好友数据
        $sql = "
            select * from  users where qq in ($f_qq)
        ";

        $res = Sqlite::select($sql);
        $friends = [];
        foreach ($res as $friend) {
            $friends[$friend['qq']] = $friend;
        }
        return  $friends;
    }


    /**
     * 添加好友
     * @param $user_qq
     * @param $friend_qq
     */
    public  function addfriends($server, $frame)
    {
        $fd = $frame->fd;
        $data = json_decode($frame->data, true);

        $user_qq = $data['user_qq'];
        $friend_qq = $data['friend_qq'];
        $friend = self::getUserByqq($friend_qq);
        // 判断好友是否存在
        if (empty($friend)) {
            $data = [
                'code' => 1,
                'msg' => 'qq号不存在',
                'message_type' => ImConfig::MESSAGE_TYPE_ADD_FRIENDS
            ];
            $server->push($fd, json_encode($data));
            return false;
        }
        // 判断是否已经添加过好友了
        $user_friend =self::getFriendByQQ($user_qq, $friend_qq);
        if (!empty($user_friend)) {
            $data = [
                'code' => 1,
                'msg' => '重复添加好友',
                'message_type' => ImConfig::MESSAGE_TYPE_ADD_FRIENDS
            ];
            $server->push($fd, json_encode($data));
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
        $friends = self::getfriends($data['session_id']);
        if ($res1 && $res2) {
            $data = [
                'code' => 0,
                'msg' => '添加成功',
                'message_type' => ImConfig::MESSAGE_TYPE_ADD_FRIENDS,
                'friends' => $friends
            ];
            $server->push($fd, json_encode($data));
        }

        // 查询用户资料
        $friend_info = self::getUserByqq($friend_qq);
        $friends = self::getfriends($friend_info['session_id']);

        $data = [
            'code' => 0,
            'message_type'=> \App\config\ImConfig::MESSAGE_TYPE_FRIENDS,
            'friends' => $friends
        ];

        $server->push($friend_info['fd'], json_encode($data));
    }

    /**
     * 查询好友
     * @param $qq
     * @param $to_qq
     */
    public static function getFriendByQQ($qq, $friend_qq)
    {
        $sql = "
            SELECT * FROM friends where qq = {$qq} and friend_qq={$friend_qq}
        ";
        return Sqlite::find($sql);
    }

    /**
     *
     * @param $qq
     * @return mixed
     */
    public static function getUserByqq($qq)
    {
        $sql = "
            select * from users where qq = {$qq}
        ";
        return Sqlite::find($sql);
    }

    /**
     * 更新用户fd
     * @param $fd
     * @param $session_id
     * @return mixed
     */
    public static function updateUserFdBySessionId($fd, $session_id)
    {
        $sql = "
           UPDATE users set fd={$fd} where session_id= '{$session_id}'
        ";
        return Sqlite::exec($sql);
    }

    /**
     * 查询用户未收到的消息
     * @param $qq
     * @return array
     */
    public static function getUnRead($qq)
    {
        $sql = "
            SELECT * FROM message WHERE to_qq = {$qq} AND is_read = 0
        ";
        return Sqlite::select($sql);
    }

    /**
     * 分配qq号,这是要设置一下锁
     */
    public static function assignQQ()
    {
        $sql = "
            SELECT max(qq) as maxqq FROM users 
        ";
        $res = Sqlite::find($sql);
        if (empty($res['maxqq'])) {
            return 6000;
        }
        return $res['maxqq'] + 1;
    }
}