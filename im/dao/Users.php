<?php

namespace App\im\dao;

class Users
{

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
        echo $sql;
        return Sqlite::select($sql);

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
}