<?php

if (!defined('SISTEMA_LIBRARY')) {
    die('Acesso direto não permitido');
}

class Config {

    private static $config;
    private static $data;
    private static $configPath;
    public static $path;
    public static $app;
    private static $actions = [
        'getall' => 'Listar',
        'getbyid' => 'Ler',
        'getall' => 'Listar',
        'save' => 'Concluir',
        'remove' => 'Remover',
        'getnew' => 'Novo',
        'read' => 'Ler',
        'sharedlist' => 'Relação de colaboração',
        'newpublic' => 'Nova colaboração pública',
        'getorganogramaporcargo' => 'Ler organograma',
    ];

    const varName = 'SistemaConfig';

    private function __construct() {
        require_once self::$configPath;
        $varName = self::varName;
        if (isset($$varName)) {
            self::$data = $$varName;
            unset($$varName);
        } else {
            throw new Exception("Config is undefined.");
        }
    }

    public static function init() {
        self::$path = str_replace(DIRECTORY_SEPARATOR . 'library' . DIRECTORY_SEPARATOR . 'src', '', __DIR__);
        $configPath = self::$path . DIRECTORY_SEPARATOR . 'library' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.php';
        self::$configPath = $configPath; // str_replace(DIRECTORY_SEPARATOR . 'library', '', SistemaLibrary::getPath()) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'Config.php';


        if (file_exists($configPath)) {
            self::$config = null;
            self::$configPath = $configPath;
        } else {
            throw new Exception("Config file not exists: {$configPath}");
        }

        if (self::$config == null) {
            self::$config = new Config();
        }
        return self::$config;
    }

    /**
     * Retorna o Path detse microservico
     */
    public static function getPath() {
        return self::$path;
    }

    public static function getData($key1, $key2 = null) {
        $key2Original = $key2;
        if ($key2 !== null) {
            if ($key1 === 'titlePagesAliases' || $key1 === 'aliasesField' || $key1 === 'entidadeName') { // igonar case sensitivy
                $key2 = mb_strtolower($key2);
            }

            if (isset(self::$data[$key1][$key2])) {
                return self::$data[$key1][$key2];
            } else {
                // nem todos exisitirão, nesse caso, retorna o key2
                if ($key1 !== 'titlePagesAliases' && $key1 !== 'aliasesField' && $key1 !== 'entidadeName') {
                    throw new Exception("Config keys {$key1}, {$key2} not found.");
                } else {
                    return $key2Original;
                }
            }
        } else {
            if (isset(self::$data[$key1])) {
                return self::$data[$key1];
            } else {
                throw new Exception("Config key '{$key1}' not found.");
            }
        }
    }


    public static function getAliasesField($key) {
        return self::getData('aliasesField', $key);
    }

    public static function getAliasesTable($key) {
        return self::getData('titlePagesAliases', $key);
    }

    public static function getAliasesAction($key) {
        $chave = mb_strtolower($key);
        if (isset(self::$actions[$chave])) {
            return self::$actions[$chave];
        } else {
            return $key;
        }
    }

    public static function getEntidadeName($entidadeName) {
        return $entidadeName;
        //return self::getData('entidadeName', $entidadeName);
    }

    /**
     * 
     * @param type $key
     * @return type
     */
    public static function getHint($key) {
        $key = mb_strtolower(str_replace('.', '_', $key));
        if (isset(self::$data['hints'][$key])) {
            return self::$data['hints'][$key];
        }
        return false;
    }

    public static function setData($key1, $value, $key2 = false) {
        if (!$key2) {
            self::$data[$key1] = $value;
        } else {
            self::$data[$key1][$key2] = $value;
        }
        /*
          if (isset(self::$data[$key1])) {

          } else {
          throw new Exception("Config keys {$key1} not found.");
          }
         * 
         */
    }

    public static function setDataByFile($key, $filename) {
        include $filename;
        self::setData($key, $$key);
    }

    public static function logIsActive() {
        if (isset(self::$data['log']) && isset(self::$data['log']['active'])) {
            return (bool) self::$data['log']['active'];
        } else {
            throw new Exception("Log activation flag not set.");
        }
    }

    public static function activeLog($fileName = null) {
        self::setData('log', 'active', true);
        self::setData('log', 'fileLocation', $fileName ? $fileName : '');
        LogPagSeguro::reLoad();
    }

    public static function getLogFileLocation() {
        if (isset(self::$data['log']) && isset(self::$data['log']['fileLocation'])) {
            return self::$data['log']['fileLocation'];
        } else {
            throw new Exception("Log file location not set.");
        }
    }

    public static function rewriteAppConfigJs() {
        // get new ApiKey from REST
        Helper::consumeApi('/api', 'auth/getTokenApiKey', ['']);

        $dirSave = Config::$path . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'appConfig.js';
        $text = "
            var t = window.location.href;
            var appConfig = {
                ApiKey: '" . self::$data['apiKey'] . "',
                urlCloud: '" . self::$data['url'] . "/',
                dev: " . self::$data['dev'] . ",
                timeExibeError: " . self::$data['timeShowError'] . ", // segundos para mensagem de erro permanecer na tela
                urlRoot: '" . str_replace(constant('APP_NAME'), '', self::$data['url']) . "',
                apiPark: JSON.parse('" . json_encode(self::$data['apiPark']) . "')
            };
            appConfig.rest = appConfig.urlCloud + 'api';
            ";
        $fp = fopen($dirSave, 'w+');
        fwrite($fp, $text);
        fclose($fp);
    }

    public static function getModelJson($key) {
        if (isset(self::$data['modelJson'][$key])) {
            return self::$data['modelJson'][$key];
        } else {
            return [
                'a_definir' => ['default' => 'Modelo não definido: ' . $key, 'grid' => 'col-sm-4', 'type' => 'text', 'class' => '', 'ro' => 'false', 'tip' => 'Exibindo o default, pois campo ' . $key . ' não foi configurado em /src/config/model_json.php', 'label' => 'Modelo não definido']
            ];
        }
    }

}
