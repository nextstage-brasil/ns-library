<?php

if (!defined('SISTEMA_LIBRARY')) {
    die('Acesso direto não permitido');
}

class Autoloader {

    public static $loader;
    private static $dirs;

    private function __construct() {
        if (function_exists('__autoload')) {
            spl_autoload_register('__autoload');
        }
        spl_autoload_register(array($this, 'addClass'));
    }

    public static function init() {
        // diretório extras do APP que chamou a library
        $prefix = SistemaLibrary::getPath() . DIRECTORY_SEPARATOR;
        $prefixApp = str_replace('library' . DIRECTORY_SEPARATOR . 'src', '', __DIR__) . DIRECTORY_SEPARATOR;
        self::$dirs = [
            $prefix . 'src',
			$prefix . 'util',
            $prefixApp . 'auto/entidades',
            $prefixApp . 'src/controller'
        ];
        if (!function_exists('spl_autoload_register')) {
            throw new Exception("SistemaLibrary: Standard PHP Library (SPL) is required.");
            //return false;
        }
        if (self::$loader == null) {
            self::$loader = new Autoloader ();
        }
        return self::$loader;
    }

    private function addClass($class) {
        foreach (self::$dirs as $dir) {
            $file = $dir . DIRECTORY_SEPARATOR . $class . '.class.php';
            $file = str_replace("/", DIRECTORY_SEPARATOR, $file);
            //echo $file.'<br/>';
            if (file_exists($file) && is_file($file)) {
                require_once $file;
            }
        }
    }

}
