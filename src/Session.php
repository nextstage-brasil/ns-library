<?php

namespace NsLibrary;

use json_decode;
use json_encode;
use NsLibrary\Config;
use NsLibrary\Token;

class Session {

    private function __construct() {
        
    }

    public static function initByToken($token) {
        list($resut, $message, $code, $open) = Token::validate($token);
        $data = json_decode(json_encode($open->user), true);
        self::init(['user' => $data]);
        return self::get('user', 'idUsuario') > 0;
    }

    public static function init(array $data) {
        Config::setData('session', $data);
    }

    public static function get(string $key, string $keyB = null) {
        $out = Config::getData('session')[$key][$keyB] ?? Config::getData('session')[$key] ?? null;
        if (substr($key, 0, 2) === 'id' || substr((string) $keyB, 0, 2) === 'id') {
            $out = (int) is_null($out) ? 0 : $out;
        }
        return $out;
    }

    public static function set(string $key, $val = null, bool $merge = true): void {
        $session = Config::getData('session');

        // Unset
        if (is_null($val)) {
            Config::setData('session', []);
        }
        // Merge
        else if ($merge === true && is_array($val)) {
            $session[$key] = array_merge(($session[$key] ?? []), $val);
        }
        // Set
        else {
            $session[$key] = $val;
        }
        // Force INT do Ids
        self::init((array) self::setIdToInt($session));
    }

    public static function clearAll() {
        self::init([]);
    }

    /**
     * Garante que todas chaves "ids" serão do tipo INT
     * @param type $var
     */
    private static function setIdToInt($var) {

        foreach ($var as $key => $item) {
            if (is_object($item)) {
                $item = json_decode(json_encode($item), true);
            }

            if (is_array($item)) {
                $var[$key] = self::setIdToInt($item);
            } else {
                // Setar int
                if (substr($item, 0, 2) === 'id') {
                    $var[$key] = (int) $item;
                }
            }
        }
        return $var;
    }

}
