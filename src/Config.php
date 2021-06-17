<?php

namespace NsLibrary;

class Config {

    private static $cfg;

    private function __construct() {
        
    }

    public static function init(array $dados = []) {
        $dados['path'] = \NsUtil\Helper::getPathApp();
        $dados['psr4Name'] = \NsUtil\Helper::getPsr4Name();
        self::$cfg = new \NsUtil\Config($dados);
    }

    public static function getData($key) {
        return self::$cfg->get($key);
    }

    public static function setData($key, $val) {
        return self::$cfg->set($key, $val);
    }

}
