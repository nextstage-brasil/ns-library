<?php

namespace NsLibrary;

use Exception;
use NsUtil\Connection\SQLite;
use NsUtil\ConnectionPostgreSQL;

class Connection {

    private static $con;

    public function __construct() {
        
    }

    /**
     * Retorna uma conexão
     * É necessário existir a configuração de Config para tal
     * @return ConnectionPostgreSQL
     */
    public static function getConnection() {
        $config = ((null === Config::getData('database')) ?
                [
            'host' => Config::getData('DBHOST'),
            'user' => Config::getData('DBUSER'),
            'pass' => Config::getData('DBPASS'),
            'port' => Config::getData('DBPORT'),
            'dbname' => Config::getData('DBNAME'),
            'type' => Config::getData('DBTYPE'),
                ] : Config::getData('database')
                );
        $type = ((!$config['type']) ? 'postgres' : $config['type']);
        if (!self::$con[$type]) {
            try {
                switch ($type) {
                    case 'postgres':
                        self::$con[$type] = new ConnectionPostgreSQL($config['host'], $config['user'], $config['pass'], $config['port'], $config['dbname']);
                        break;
                    case 'sqlite':
                        if (null === $config['dbname']) {
                            throw new Exception("Connection config 'DBNAME' is not defined");
                        }
                        self::$con[$type] = new SQLite($config['dbname']);
                        break;
                    default:
                        throw new Exception("Connection $type is not enabled");
                }
            } catch (Exception $exc) {
                echo $exc->getMessage();
                die();
            }
        }
        return self::$con[$type];
    }

}
