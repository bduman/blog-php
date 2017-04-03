<?php
namespace Model;

class Config {

    static private $config = [];

    public static function setConf($key, $val) {
        self::$config[$key] = $val;
    }

    public static function getConf($key) {
        return self::$config[$key];
    }
}