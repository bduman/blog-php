<?php
namespace Model;

use \Slim\PDO\Database as PdoDatabase;

class Database {

    private $_connection;
    private static $_instance;

    public static function getInstance() {
        if(!self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance->getConnection();
    }

    private function __construct() {
        $dbConfig = Config::getConf('database');
        $this->_connection = new PdoDatabase($dbConfig['dsn'], $dbConfig['usr'], $dbConfig['pwd']);
        //$this->_connection->setAttribute(\PDO::MYSQL_ATTR_INIT_COMMAND, "SET NAMES 'utf8mb4' COLLATE 'utf8mb4_unicode_ci'");
    }

    private function getConnection() {
        return $this->_connection;
    }
}