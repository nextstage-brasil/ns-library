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
        define('SISTEMA_LIBRARY', true);  // constante que garante acesso as classes unicamente após este script
        Config::init($cfg);
        if (strlen((string) $host) > 1) {
            Connection::getConnection();
        }
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
            self::$library = new SistemaLibrary((string) $host, $user, $pass, $dbname, $port, $psr4Name, $config);
        }
        return self::$library;
    }

    public static function initByConfig(array $SistemaConfig) {
        return self::init($SistemaConfig['database']['host'], $SistemaConfig['database']['user'], $SistemaConfig['database']['pass'], $SistemaConfig['database']['dbname'], $SistemaConfig['database']['port'], $SistemaConfig['psr4Name'], $SistemaConfig);
    }

    public static function isStarted(): bool {
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

    public final static function getPath(): string {
        return self::$path;
    }

    public final static function getPathRoot(): string {
        return self::$pathRoot;
    }

    public function __shutdown_check(): void {
        //Log::gravaFromSession();
    }

    public final static function setSecurity(int $errorReporting = 0, $maxUploadfile = '10M', $strictMode = 1, $cookieSecure = 1, $cookieHttpOnly = 1): void {
        foreach ([
    'session.use_strict_mode' => $strictMode,
    'session.cookie_secure' => $cookieSecure,
    'session.cookie_httponly' => $cookieHttpOnly,
    'upload_max_filesize' => $maxUploadfile,
        ] as $key => $value) {
            ini_set($key, $value);
        }
        error_reporting($errorReporting);
    }

    public static function setDevelopeMode(): void {
        ini_set('display_erros', 1);
        error_reporting(E_ERROR | E_WARNING | E_PARSE);
    }

    public static function encrypt($texto, $senha) {
        return (new \NsUtil\Crypto(Config::getData('TOKEN_CRYPTO')))->encrypt($texto, $senha);
    }

    public static function decrypt($texto, $senha) {
        return (new \NsUtil\Crypto(Config::getData('TOKEN_CRYPTO')))->decrypt($texto, $senha);
    }

    /**
     * Le as permissões do usuario e retorna um array associativo
     * @param int $idUser
     * @return array
     */
    public static function getUserByTablePermissions(int $idUser): array {
        $out = [];
        if ($idUser > 0) {
            $con = Connection::getConnection();
            $query = "select acao_funcao || '_' || grupo_funcao || '_' || subgrupo_funcao as k, b.id_usuario::boolean as u from app_sistema_funcao a
                left join app_usuario_permissao b on b.id_sistema_funcao = a.id_sistema_funcao and b.id_usuario = " . $idUser;
            $list = $con->execQueryAndReturn($query, false);
            foreach ($list as $item) {
                $out[$item['k']] = boolval($item['u']);
            }
        }
        return $out;
    }

}
