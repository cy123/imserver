<?php

namespace App\im\dao;

use App\service\SqlBuild;
use App\service\Sqlite;

class Users
{
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
     * 通过qq号查找用户
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
     * 更新用户资料
     */
    public static function updateUsersByqq($table, $columns, $where)
    {
        $sql = SqlBuild::update($table, $columns, $where);
        echo $sql;
        return Sqlite::exec($sql);
    }

    public static function updateUserSession($session_id, $fd, $qq)
    {
        $sql = "
           UPDATE users set session_id='{$session_id}', fd={$fd} where qq={$qq}
        ";

        return Sqlite::exec($sql);
    }

    /**
     * 设置消息已读
     * @param $qq
     */
    public function setRead($qq, $to_qq)
    {
        $sql = "
            UPDATE message set is_read = 1 WHERE qq={$qq} and to_qq = {$to_qq}
        ";
        return Sqlite::exec($sql);
    }

    /**
     * 通过session_id查找用户
     * @param $session_id
     */
    public function getUserBySessionId($session_id)
    {
        $sql = "
            SELECT * FROM users WHERE session_id = '{$session_id}'
        ";
        return Sqlite::find($sql);
    }

    /**
     * 退出登录，清空session_id,fd
     * @param $qq
     */
    public function logout($qq)
    {
        $sql = "
             UPDATE users set session_id='', fd='' where qq={$qq}
        ";
        return Sqlite::exec($sql);
    }
}