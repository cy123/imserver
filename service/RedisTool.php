<?php

namespace App\service;

// redis  sAddArray sMembers
class RedisTool
{
    private static $redis;

    private function getinstence()
    {
        //
        if (empty(self::$redis)) {
            self::$redis = new \Redis();
            self::$redis->connect('127.0.0.1', 6379);
        }
        return self::$redis;
    }

    /**
     * @param $method
     * @param $args
     */
    public static function __callStatic($method, $args)
    {
          $redis = self::getinstence();
          return $redis->$method(...$args);
    }

    // 不允许直接new
    private  function __construct()
    {

    }
    //
    private function __clone()
    {

    }

}