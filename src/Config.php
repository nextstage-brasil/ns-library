<?php

namespace NsLibrary;

class Config {

    private static $cfg;

    private function __construct() {
        
    }

    public static function init($dados) {
        self::$cfg = new \NsUtil\Config($dados);
    }

    public static function getData($key) {
        return self::$cfg->get($key);
    }

    public static function setData($key, $val) {
        return self::$cfg->set($key, $val);
    }
    
}
