<?php

namespace NsLibrary;

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
    public static function getConnection(): ConnectionPostgreSQL {
        if (!self::$con) {
            try {
                $config = Config::getData('database');
                self::$con = new ConnectionPostgreSQL($config['host'], $config['user'], $config['pass'], $config['port'], $config['dbname']);
            } catch (Exception $exc) {
                echo $exc->getMessage();
            }
        }
        return self::$con;
    }

}
