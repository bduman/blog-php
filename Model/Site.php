<?php
namespace Model;


class Site {

    public static $data = [];

    public static function getData() {
        self::$data = Database::getInstance()
                        ->select()
                        ->from('site')
                        ->execute()
                        ->fetchAll(\PDO::FETCH_KEY_PAIR);
        return self::$data;
    }

    public static function getAllVars() {
        return self::$data;
    }

    public static function getVar($var) {
        return self::$data[$var];
    }

    public static function update($var, $val) {
        Database::getInstance()
                ->update(["val" => trim($val)])
                ->where("var", "=", $var)
                ->table('site')
                ->execute();
    }
}