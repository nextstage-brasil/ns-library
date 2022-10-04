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
        $type = ((!Config::getData('DBTYPE')) ? 'postgres' : Config::getData('DBTYPE'));
        if (!self::$con[$type]) {
            try {
                switch ($type) {
                    case 'postgres':
                        $config = Config::getData('database');
                        self::$con[$type] = new ConnectionPostgreSQL($config['host'], $config['user'], $config['pass'], $config['port'], $config['dbname']);
                        break;
                    case 'sqlite':
                        if (null === Config::getData('DBFILENAME')) {
                            throw new Exception("Connection config 'DBFILENAME' is not defined");
                        }
                        self::$con[$type] = new SQLite(Config::getData('DBFILENAME'));
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
