<?php

namespace NsLibrary;

class SistemaLibrary {

    const VERSION = "20210517";

    private static $library;
    private static $path;
    private static $pathRoot;

    private function __construct($host, $user, $pass, $dbname, $port, $psr4Name, array $config) {
        $cfg = array_merge($config, [
            'psr4Name' => $psr4Name,
            'database' => ['host' => $host, 'user' => $user, 'pass' => $pass, 'port' => (int) $port, 'dbname' => $dbname],
            'path' => \NsUtil\Helper::getPathApp(),
            'NsLibraryPath' => str_replace(DIRECTORY_SEPARATOR . 'src', '', __DIR__)
        ]);
        define('SISTEMA_LIBRARY', TRUE);  // constante que garante acesso as classes unicamente após este script
        Config::init($cfg);
        Connection::getConnection();
    }

    /**
     * Inicia a aplicação, configuração Config e Connection
     * @param type $host
     * @param type $user
     * @param type $pass
     * @param type $dbname
     * @param type $port
     * @param array $config
     * @return type
     */
    public static function init($host, $user, $pass, $dbname, $port, $psr4Name, array $config) {
        self::verifyDependencies();
        if (self::$library == null) {
            self::$library = new SistemaLibrary($host, $user, $pass, $dbname, $port, $psr4Name, $config);
        }
        return self::$library;
    }

    public static function initByConfig(array $SistemaConfig) {
        return self::init($SistemaConfig['database']['host'], $SistemaConfig['database']['user'], $SistemaConfig['database']['pass'], $SistemaConfig['database']['dbname'], $SistemaConfig['database']['port'], $SistemaConfig['psr4Name'], $SistemaConfig);
    }

    public static function isStarted() {
        return self::$library == null;
    }

    private static function verifyDependencies() {
        $dependencies = true;
        if (!function_exists('spl_autoload_register')) {
            die("Library: Standard PHP Library (SPL) is required.");
            throw new Exception("Library: Standard PHP Library (SPL) is required.");
            $dependencies = false;
        }

        if (!function_exists('curl_init')) {
            die('Library: cURL library is required.');
            throw new Exception('Library: cURL library is required.');
            $dependencies = false;
        }

        if (!class_exists('DOMDocument')) {
            die('Library: DOM XML extension is required.');
            throw new Exception('Library: DOM XML extension is required.');
            $dependencies = false;
        }
        return $dependencies;
    }

    public final static function getPath() {
        return self::$path;
    }

    public final static function getPathRoot() {
        return self::$pathRoot;
    }

    public function __shutdown_check() {
        //Log::gravaFromSession();
    }

}
