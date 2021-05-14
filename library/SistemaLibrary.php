<?php

####################################################################
// inits de segurança
foreach ([
'session.use_strict_mode' => 1,
 'session.cookie_secure' => 1,
 'session.cookie_httponly' => 1,
 'upload_max_filesize' => '10M',
 'error_log' => str_replace(DIRECTORY_SEPARATOR . 'library', 'app', __DIR__) . '/45h/perr.php'
] as $key => $value) {
    ini_set($key, $value);
}

function LIB_CALCULA_SESSION_NAME() {
    // para garantir o somente o proprio site ira lidar com os cookies de sessão
    $LIB_VERSION = '2020-04-07';
    $LIB_TOKEN = 'Afg568Ujnfh*73589*&23';
    return md5($LIB_VERSION . $LIB_TOKEN . $_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT']);
}

$sessionName = LIB_CALCULA_SESSION_NAME();
$maxlifetime = 60 * 60 * 8;  // se for 0, encerrar somente ao fechar o browser.
session_set_cookie_params($maxlifetime, '/; samesite=strict', $_SERVER['HTTP_HOST'], true, true);
session_name($sessionName);
//@rever Avaliando a necessidade desta linha session_cache_expire(($maxlifetime/60));  // 240 ou 4 horas minutos

session_start();

$username = filter_input(INPUT_POST, 'username');
$flag = (int) filter_input(INPUT_POST, 'nsInputFromLogin');

if (strlen($username) > 5 && $flag === 1521) {
    $newid = md5($username . microtime() . '-' . session_id());
    session_destroy();
    ini_set('session.use_strict_mode', 0);  // desativar para poder inserir manualmente o ID da sessao para cada usuario diferente
    session_name($sessionName);
    session_id($newid);
    session_start();
}


date_default_timezone_set('America/Recife');


header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");  // HTTP/1.1
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");  // HTTP/1.0
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");  // Date in the past
header('Content-Type: text/html; charset=UTF-8');
ini_set('default_charset', 'utf-8');  // fixa que todo arquivo aberto usara esta codificação
define('APP_NAME', 'app');

//setlocale(LC_CTYPE, "pt_BR");  // ste que estava causando erros no javascript no servidor
//setlocale(LC_TIME, "pt_BR");  // estava causando um dia a mais nas datas

define('SISTEMA_LIBRARY', TRUE);  // constante que garante acesso as classes unicamente após este script

require_once __DIR__ . '/src/AutoLoader.class.php';

/**
 * Biblioteca do sistema
 */
class SistemaLibrary {

    const VERSION = "20190202";

    private static $library;
    private static $path;
    private static $pathRoot;
    public static $config;
    public static $log;
    public static $js;
    private static $od1 = ['ApiLog', 'App', 'LoginAttempts', 'LtRel', 'Municipio', 'Pais', 'SistemaFuncao', 'SistemaLog', 'Status', 'Trash'];

    private function __construct() {
        self::$path = (dirname(__FILE__));
        self::$pathRoot = str_replace(DIRECTORY_SEPARATOR . 'library', '', dirname(__FILE__));
        Autoloader::init();

        self::$config = Config::init();
        self::$log = Log::init();
    }

    public static function getOd1() {
        return self::$od1;
    }

    public static function init() {
        self::verifyDependencies();
        if (self::$library == null) {
            self::$library = new SistemaLibrary();
        }
        return self::$library;
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

    public final static function getJs() {
        return self::$js;
    }

    public function __shutdown_check() {
        //Log::gravaFromSession();
    }

    public static function createTriggerAuditoria() {
        /*
          if (!$_SESSION['user']) {
          die('Usuario não está logado');
          return false;
          }
         * 
         */
        $con = Connection::getConnection();
        $queryDrop = 'DROP TRIGGER if exists %s_audit ON %s';
        $query = 'CREATE TRIGGER %s_audit  AFTER INSERT OR UPDATE OR DELETE ON %s FOR EACH ROW EXECUTE PROCEDURE audit.if_modified_func()';
        $out = [];

        foreach (glob(Config::getData('path') . '/auto/entidades/*.class.php') as $arquivo) {
            $t = explode('/', $arquivo);
            $ent = str_replace('.class.php', '', $t[count($t) - 1]);
            if (array_search($ent, self::$od1) > -1) {
                echo "$ent<br/>";
                continue;
            }
            $out[$ent] = $ent;
            $entidade = new $ent();
            $table = Helper::setTable($entidade->getTable());
            $q1 = sprintf($queryDrop, $ent, $table);
            $q2 = sprintf($query, $ent, $table);
            $con->executeQuery($q1);
            $con->executeQuery($q2);
        }
        return $out;
    }

}

SistemaLibrary::init();


// vendor
require_once Config::getData('path') . '/vendor/autoload.php';


if (class_exists('App')) { /* If necessário no caso de priemiro build. */
    if (!defined('CONFIG')) {
        $s = hash('md5', $_SERVER['HTTP_HOST']);
        die('ERROR[' . $s . ']: Aplicação não habilitada para execução neste servidor');  // (' . $s . ')');
    }

    // Ultima atividade da sessao. Se estiver há mais de X miutos parado, sessão esta expirada
    if (stripos($_SERVER['REQUEST_URI'], '/logout') > -1 || (
            $_SESSION['user']['idUsuario'] > 0 && time() > $_SESSION['validade'])) {
        session_destroy();
        unset($_SESSION['user']);
    }
    $_SESSION['validade'] = time() + (60 * (int) (((int) $_SESSION['user']['sessionLimit']) ? $_SESSION['user']['sessionLimit'] : 10));  // mktime(date('H'), (date('i') + (int) Config::getData('session_limit')), 0, date('m'), date('d'), date('Y'));

    // importar os modelos JSON
    if (file_exists(Config::getData('fileModelJson'))) {
        require Config::getData('fileModelJson');
        Config::setData('modelJson', $MODEL_JSON);
    }

    // Obter carga de permissoes
    //@rever removido para localhost buscar sempre if ($_SESSION['user']['idUsuario']) {
    $dao = new EntityManager();
    $list = $dao->execQueryAndReturn("select upper(a.grupo_funcao || a.subgrupo_funcao  || a.acao_funcao)  as chave from app_sistema_funcao a
        inner join app_usuario_permissao b on b.id_sistema_funcao = a.id_sistema_funcao 
        where b.id_usuario = (select coalesce(perfil_usuario, 10) from app_usuario where id_usuario= " . (int) $_SESSION['user']['idUsuario'] . ");");
    foreach ($list as $item) {
        Config::setData('permissao', true, $item['chave']);
    }

    //}
    // para minificar todo código HTML gerado, somente no ambiente de produção

    function ob_html_compress($buf) {
        Log::gravaFromSession();
        // minificar codigo enviado ao browser
        return $buf;
        return preg_replace(array('/<!--(.*)-->/Uis', "/[[:blank:]]+/"), array('', ' '), str_replace(array("\n", "\r", "\t"), '', $buf));
    }

    if (!function_exists('getallheaders')) {

        function getallheaders() {
            $headers = [];
            foreach ($_SERVER as $name => $value) {
                if (substr($name, 0, 5) == 'HTTP_') {
                    $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
                }
            }
            return Helper::filterSanitize($headers);
        }

    }

    if (!defined('FROMCRON')) {
        //ob_start("ob_html_compress");
    }

    // FUNCAO PARA TRATAMENTO DE ERROS PHP
    register_shutdown_function('handler_error_config');

    function handler_error_config() {
        $error = error_get_last();
        if ($error && ($error['type'] === 'E_ERROR' || $error['type'] === 'E_WARNING' || $error['type'] === 'E_FATAL')) {
            $codErro = substr(md5(time()), 0, 8);
            $text = 'Ocorreu um erro no sistema. Os responsáveis já foram informados e logo verificarão.<br/>Código do erro registrado: ' . $codErro;
            $error['DATA-HORA'] = date('d/m/Y h:i:s');
            $error['IP'] = getenv('REMOTE_ADDR');
            $error['USER'] = $_SESSION['user']['nome'];
            $error['ACTION'] = $_SESSION['debug']['action'];
            $error['BACKTRACE'] = debug_backtrace();
            $e = var_export($error, true);
            Log::error('PHP-ERROR: [' . $codErro . '] ' . $e);
            if (Config::getData('dev')) {
                die($text);
            }
            Header("Location:" . Config::getData('url') . "/Error/0/err/$text");
            ob_clean();
            die($text);
        }
    }

}
