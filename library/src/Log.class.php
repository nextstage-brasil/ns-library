<?php

if (!defined('SISTEMA_LIBRARY')) {
    die('Acesso direto não permitido');
}

class Log {

    private static $log;
    private static $active;
    private static $fileLocation;
    private static $logsSistemicos = ['error', 'lentidao', 'entityManager-error', 'entidades_create'];

    private function __construct() {
        self::reLoad();
    }

    public static function init() {
        if (self::$log == null) {
            self::$log = new Log();
        }
        return self::$log;
    }

    public static function reLoad() {

        self::$active = Config::logIsActive();
        if (self::$active) {
            $fileLocation = Config::getLogFileLocation();

            if (file_exists($fileLocation) && is_file($fileLocation)) {
                self::$fileLocation = $fileLocation;
            } else {
                self::createFile();
            }
        }
    }

    /**
     * Creates the log file
     * @throws Exception
     * @return boolean
     */
    public static function createFile($defaultName = null) {
        if (!Config::getData('dev') && Config::getData('ip') !== '189.4.100.56') {
            return true;
        }
        if (!self::$active) {
            return false;
        }
        $defaultName = (($defaultName) ? str_replace('.log', '.php', $defaultName) : 'geral.php');
        self::$fileLocation = Config::getData('log', 'fileLocation') . DIRECTORY_SEPARATOR . $defaultName;

        // mover para archive caso esteja perto de 1MB. 
        if (file_exists(self::$fileLocation)) {
            if (filesize(self::$fileLocation) >= 1046000) {
                $novaPasta = Config::getData('log', 'fileLocation') . DIRECTORY_SEPARATOR . 'archive' . DIRECTORY_SEPARATOR . explode('.', $defaultName)[0];
                $novoArquivo = date('Y-m-d_His') . '_' . explode('.', $defaultName)[0] . '.log';
                Helper::saveFile($novaPasta . DIRECTORY_SEPARATOR . $novoArquivo);
                rename(self::$fileLocation, $novaPasta . DIRECTORY_SEPARATOR . $novoArquivo);
            }
        } else {
            try {
                Helper::saveFile(self::$fileLocation, false, "<?php // default created\nheader('Location:/'); die();\n ?>\n ");
            } catch (Exception $e) {
                $message = $e->getMessage() . " - Can't create log file. Permission denied. File location: " . self::$fileLocation;
                Log::error($message);
                //echo $e->getMessage() . " - Can't create log file. Permission denied. File location: " . self::$fileLocation;
            }
        }
    }

    public static function logTxt($tipo, $message) {

        if (!Config::getData('dev')) {
            //return true; 
        }
        if (is_array($message) || is_object($message)) {
            $message = var_export($message, true);
        }
        if (stripos($message, 'sistema_funcao') !== false || stripos($message, 'usuario_permissao.*') !== false) { // nao quero logs de permissão
            return true;
        }
        $b = debug_backtrace();
        $origem = ';-- {ORIGEM: ' . $b[1]['class'] . '::' . $b[1]['function'] . ':' . $b[1]['line'] . '}';
        self::createFile($tipo . ".log");
        //$message = $message . "\n    " . json_encode($b);
        $fp = fopen(self::$fileLocation, "a");
        $date_message = "[" . date('d/m/Y H:i:s') . '|' . Config::getData('ip') . "][" . $_SESSION['user']['nomeUsuario'] . "]" . $message . $origem . "\r\n";
        fwrite($fp, $date_message);


        fclose($fp);
    }

    public static function log($type, $message, $id = false, $entidade = false, $datasend = []) { // geral para todos tipos de logs necessários
        if ($type === 'permissao' || stripos(json_encode($message), 'sistema_funcao') > -1) {
            if (Config::getData('dev')) {
                return true;
            }
            $temp = explode(" ", $message);
            $message = $temp[0];
        }
        $b = debug_backtrace();
        $datasend = ((is_array($datasend) ? $datasend : []));
        for ($i = count($b); $i > 0; $i--) { // de tras pra frente
            $datasend['BACKTRACE'][] = ['Classe' => $b[$i]['class'], 'Function' => $b[$i]['function'], 'Line' => $b[$i]['line']];
        }
        self::logMessage($message, $type, $entidade, $id, $datasend);
    }

    public static function storage($message, $entidade = false, $id = false, $json = []) {
        self::logMessage($message, 'Storage', $entidade, $id);
    }

    public static function getIpGeoOnSession() {
        return GeoLocalizacao::get(getenv('REMOTE_ADDR'));
    }

    public static function tl($message, $entidade = false, $id = false, $json = []) {
        $entidade = Config::getEntidadeName($entidade);


        // validar se não existir descrição, obter nomeEntidade
        if ($json['entidade'] && (!isset($json['texto']) || strlen($json['texto']) === 0)) {
            $dao = new EntityManager();
            $n = Config::getEntidadeName($json['entidade']);
            $item = $dao->setObject(new $n())->getById($json['id']);
            $method = 'getNome' . Helper::name2CamelCase($json['entidade']);
            if (method_exists($item, $method)) {
                $json['texto'] = $item->$method();
            }
        }



        self::logMessage($message, 'TL', Helper::upper($entidade), (int) $id, $json);
    }

    /**
     * 
     * @param type $entidade Entidade referenciada, ex uploadfile
     * @param type $id ID da entidade referencia
     * @param type $descricao Texto que será exibido na timeline
     * @param type $link Link para saber mais
     * @param type $icone icone, caso seja especifico
     * @return type array
     */
    public static function getJsonEntidade($entidade, $id, $descricao = '', $link = '', $icone = '') {
        return [
            'entidade' => $entidade,
            'id' => (int) $id,
            'texto' => $descricao,
            'link' => $link,
            'icone' => $icone
        ];
    }

    /**
     * 
     * @param type $entidade
     * @param type $message
     * @param type $json incluir idEntidade no json para captar no log
     * @return boolean
     */
    public static function auditoria($entidade, $message, $json = [], $id = false) {
        if ($entidade === 'Trash' || (Helper::compareString('agencia', $message) && Helper::compareString('inserir', $message) )) { // não preciso logar trash, é sistemico
            return true;
        }
        if (Helper::compareString('validaLogin', $message)) {
            return true;
        }
        $id = (($id) ? (int) $id : $json['id' . $entidade]);
        $json['entidade'] = $entidade;


        // log
        self::log('AUDITORIA', $message, $id, $entidade, $json);
    }

    public static function navegacao($rota, $json = []) {
        self::logMessage('Listar', 'NAVEGACAO', $rota);
    }

    public static function navegacaoApi($rota, $dados, $forceLogName = false) {
        $t = explode('/', $rota);
        $action = $t[1];
        $tipo = $t[0];
        if (strlen($dados['Search']) > 0) {
            $action = 'Search';
            $forceLogName = true;
        }

        if (strlen($tipo) < 2) {
            return true;
        }
        $log = 'NAVEGACAO';
        $json = [];

        // manipulação de tipos (entidades)
        switch ($tipo) {
            case 'Historico':
                $tipo = (((int) $dados['tipoHistorico'] === 2) ? 'Alerta' : 'Registro');
                break;
            case 'Agencia':
                return true;
                break;
            default:
                break;
        }

        // Manipulação de actions
        switch ($action) {
            case 'autoSave':
            case 'validaLogin':
                $log = 'SISTEMA';
                break;
            case 'getById':
            case 'save':
            case 'getNew':
                $json = ['id' . $tipo => $dados['id' . $tipo]];
                break;
            default:
                return true;
        }
        $log = (($forceLogName) ? 'NAVEGACAO' : $log);
        $json['dados'] = $dados;
        $json['system'] = [
            'type' => $tipo,
            'action' => $action,
            'url' => $_SERVER['REQUEST_URI']
        ];
        $id = (($dados['id']) ? $dados['id'] : $dados['id' . $tipo]);
        unset($dados['id']);
        unset($dados['id' . $tipo]);
        $action = Config::getAliasesAction($action);
        self::logMessage($action, Helper::upper($log), $tipo, $id, $json);
    }

    /**
     * Prints an error message in the log file
     * @param String $message
     */
    public static function error($message, $json = []) {
        if (stripos($message, 'sistema_funcao_un') > -1) {
            return true;
        }
        $backtrace = debug_backtrace();
        $trace[] = '[TraceRoute: Arquivo origem: ' . $backtrace[0]['file'];
        $trace[] = 'Method : ' . $backtrace[1]['class'] . '::' . $backtrace[1]['function'] . ' (line ' . $backtrace[0]['line'] . ')]';
        //$message = '{' . $method . '} - ' . $message . implode(', ', $trace);
        unset($trace);
        unset($backtrace);

        if (Config::getData('dev')) { // verifica se o ambiente esta em desenvolvimento ou em produção
            self::logTxt('error', json_encode($message));
            //die('LOG-AMB-DEV: ' . __METHOD__ . ': ' . $message);
        }

        self::log('ERROR', $message, $id, $entidade, $json);
    }

    /**
     * imprimir na tela o texto
     */
    public static function ver($var, $label = false) {
        $backtrace = debug_backtrace();
        echo '<div style="background-color:#f5f5f5"><hr/><pre>'
        . '<strong>LOG DE VARIAVEL ' . (($label) ? "['$" . $label . "'] " : ' ') . gettype($var) . '</strong>: '
        //. '<b>Arquivo Master:</b> ' . $backtrace[1]['file'].': <strong>['. $backtrace[1]['class'] . '::' . $backtrace[1]['function'] . ' (' . $backtrace[1]['line'].')]</strong>'
        . '<br/><b>Arquivo origem:</b> ' . $backtrace[0]['file'] . ': <strong>[' . $backtrace[1]['class'] . '::' . $backtrace[1]['function'] . ' (' . $backtrace[0]['line'] . ')]</strong>'
        . '<br/>';
        //var_dump($backtrace);
        if (is_object($var)) {
            if (get_class($var) === 'stdClass') {
                $var = (array) $var;
            } else {
                $app = new AppController();
                $var = $app->objectToArray($var);
            }
        }

        if (is_array($var)) {
            var_export($var);
        } elseif ($var == '') {
            echo 'Variavel não possui nenhum valor';
        } else {
            if (is_bool($var)) {
                $var = (($var) ? 'TRUE' : 'FALSE');
            }
            echo $var;
        }
        echo '</pre><hr/></div>';
    }

    /**
     * Logs a message
     * @param String $message
     * @param String $type
     * @throws Exception
     */
    private static function logMessage($message, $type = null, $entidade = null, $valorid = null, $datasend = []) {
        if (!self::$active) {
            return false;
        }
        try {
            if (!is_array($message)) {
                $messagearray[0] = $message;
                $message = false;
            } else {
                $messagearray = $message;
            }
            $username = (($_SESSION['user']) ? $_SESSION['user']['nomeUsuario'] : 'Usuário não logado');
            $idUser = (($_SESSION['user']['idUsuario']) ? $_SESSION['user']['idUsuario'] : -1);
            $ip = getenv('REMOTE_ADDR');

            if (!is_array($datasend)) {
                $datasend = json_decode($datasend, true);
            }
            //$datasend['GeopIP'] = self::getIpGeoOnSession();

            $json = json_encode($datasend, JSON_HEX_QUOT | JSON_HEX_APOS);
            foreach ($messagearray as $message) {
                $message = ((is_array($message)) ? json_encode($message) : $message);
                if (strlen($message) > 0) {
                    $texto = str_replace("'", '"', $message);
                    $texto = substr($texto, 0, 249);
                    $valorid = (int) $valorid;
                    //$values[] = "('$ip','$username', '$type', '$texto', '$entidade', $valorid)";
                    $date = date('Y-m-d H:i:s');
                    $var = "('$date', '$ip','$username', '$type', '$texto', '$entidade', $valorid, '$json'::jsonb, $idUser)";
                    //Log::logTxt('session-log', json_encode($var));
                    $_SESSION['LOGS'][] = $var;
                }
            }
            self::gravaFromSession();
        } catch (Exception $e) {
            echo $e->getMessage() . " - Can't create log file. Permission denied. File location: " . self::$fileLocation;
            die();
        }
    }

    public static function gravaFromSession() {
        if (is_array($_SESSION['LOGS'])) {
            if (count($_SESSION['LOGS']) > 0) {
                try {

                   $con = new ConnectionPDO('postgres');
                    $query = "INSERT INTO app_sistema_log (createtime_log, ip_log, user_log, tipo_log, texto_log, entidade_log, valorid_log, datasend_log, usuario_id) VALUES "
                            . implode(',', $_SESSION['LOGS']);
                    $con->executeQuery($query, false);
                } catch (Exception $ex) {
                    self::logTxt('ERROR', __METHOD__ . __LINE__ . ' - ' . $ex->getMessage());
                }
            }
            $_SESSION['LOGS'] = [];
        }
    }

    public function __destruct() {
        if (is_array($_SESSION['LOGS'])) {
            if (count($_SESSION['LOGS']) > 0) {
                try {

                    $con = new ConnectionPDO('postgres');
                    $query = "INSERT INTO app_sistema_log (createtime_log, ip_log, user_log, tipo_log, texto_log, entidade_log, valorid_log, datasend_log, usuario_id) VALUES "
                            . implode(',', $_SESSION['LOGS']);
                    $con->exec($query, false);
                } catch (Exception $ex) {
                    self::logTxt('ERROR', __METHOD__ . __LINE__ . ' - ' . $ex->getMessage());
                }
            }
            $_SESSION['LOGS'] = [];
        }
    }

    /**
     * Retrieves the log messages
     * @param integer $negativeOffset
     * @param boolean $reverse
     */
    public static function getHtml($negativeOffset = null, $reverse = null) {
        if (!self::$active) {
            return false;
        }
        if (file_exists(self::$fileLocation) && $file = file(self::$fileLocation)) {
            if ($negativeOffset !== null) {
                $file = array_slice($file, (-$negativeOffset), null, true);
            }
            if ($reverse) {
                $file = array_reverse($file, true);
            }
            $content = '<div style="font-size: 0.8em;font-family: \'tahoma\'">';
            foreach ($file as $value) {
                $html = ("<p>" . str_replace("\n", "<br>", $value) . "</p>");
                $html = str_replace("[", " <strong>", $html);
                $html = str_replace("]", "</strong> ", $html);
                $html = str_replace("{", '<span style="font-size: 0.7em; font-style: italic;">', $html);
                $html = str_replace("}", "</span>", $html);
                $content .= $html;
            }
            $content .= '</div>';
        } else {
            $content = 'File ' . self::$fileLocation . ' not exists';
        }
        return isset($content) ? $content : false;
    }

}
