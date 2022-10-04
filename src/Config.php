<?php

namespace NsLibrary;

use Exception;
use NsUtil\Config as NSUtilConfig;
use NsUtil\Helper;

class Config {

    private static $cfg;

    private function __construct() {
        
    }

    private static function checkIfIsInit() {
        if (!isset(self::$cfg)) {
            throw new Exception('NSLibrary: Config is not configured');
        }
    }

    public static function init(array $dados = []): void {
        $dados['path'] = Helper::getPathApp();
        $dados['psr4Name'] = Helper::getPsr4Name();
        self::$cfg = new NSUtilConfig($dados);
    }

    public static function getData($key) {
        self::checkIfIsInit();
        return self::$cfg->get($key);
    }

    public static function setData($key, $val) {
        self::checkIfIsInit();
        return self::$cfg->set($key, $val);
    }

    public static function getAll(): array {
        self::checkIfIsInit();
        return self::$cfg->getAll();
    }

}
