<?php

class Translate {

    private static $file;
    private static $defaultFile;

    public function __construct($lang) {
        self::init($lang);
    }

    public static function init($lang = 'pt_BR') {
        self::$file = Config::getData('translatePath') . '/' . $lang . '.php';
        self::$defaultFile = Config::getData('translatePath') . DIRECTORY_SEPARATOR . 'pt_BR.php';
        if (!file_exists(self::$file)) {
            Helper::saveFile(self::$file, false, json_encode([]));
        }
        $json = file_get_contents(self::$file);
        $_SESSION['translate'] = json_decode($json, true);
    }

    public static function get($key, $lang = false) {
        if (is_array($key)) {
            foreach ($key as $value) {
                $out[] = self::get($value);
            }
            return implode(' ', $out);
        }
        if (strlen($key) <= 1) {
            return $key;
        }
        Helper::upperByReference($key);
        $key = Helper::sanitize($key);

        if ($lang) {
            self::init($lang);
        }
        if (strlen($_SESSION['translate'])<5) {
            self::init();
        }

        if (isset($_SESSION['translate'][$key])) {
            $key = $_SESSION['translate'][$key];
        } else {
            self::addKey($key);
        }
        return $key;
    }

    public static function addKey($key, $valor = null) {
        // sempre no default ptBR
        $json = file_get_contents(self::$defaultFile);
        $arrayAtual = json_decode($json, true);
        $arrayAtual[$key] = (($valor) ? $valor : $key);
        file_put_contents(self::$defaultFile, json_encode($arrayAtual));
    }

}
