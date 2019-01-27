<?php

namespace App\service;
// 使用sqlite
class Sqlite
{
    //
    private static $sqlite;
    function __construct()
    {
        if (empty(self::$sqlite)) {
            self::$sqlite = new \SQLite3('webqq.db');
        }
    }

    public static function getinstence()
    {
        if (empty(self::$sqlite)) {
            self::$sqlite = new \SQLite3('webqq.db');
            self::$sqlite->busyTimeout(1000*30);
        }
        return static::$sqlite;
    }
    public static function __callStatic($name, $arguments)
    {
        $instence = self::getinstence();
        return $instence->$name(...$arguments);
    }

    /**
     * @param $sql
     * @return array
     */
    public static function select($sql)
    {
        $ret = self::getinstence()->query($sql);
        if (empty($ret)) {
            return [];
        }
        $rows = [];
        while($row = $ret->fetchArray(SQLITE3_ASSOC) ){
            array_push($rows, $row);
        }
        return $rows;
    }

    /**
     * 查询单条
     * @param $sql
     */
    public static function find($sql)
    {
        return self::getinstence()->querySingle($sql, true);
    }
}