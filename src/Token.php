<?php

namespace NsLibrary;

use hash;
use json_decode;
use json_encode;
use NsUtil\Api;
use stdClass;

class Token {

    public static $timeToExpire;
    public static $expireSeconds;

    public function __construct() {
        
    }

    public static function createGuide(int $idUsuario, string $login, string $userName, int $userType, int $sessionLimit) {
        return self::create([
                    'idUsuario' => $idUsuario,
                    'username' => $login,
                    'nomeUsuario' => $userName,
                    'tipoUsuario' => $userType,
                    'sessionLimit' => $sessionLimit
        ]);
    }

    public static function create($user) {
        self::$expireSeconds = ((isset(self::$expireSeconds)) ? self::$expireSeconds : 5);
        $senha = (string) time();
        $token = [
            'datetime' => $senha,
            'user' => SistemaLibrary::encrypt(json_encode((object) $user), $senha),
            'end' => time() + (self::$expireSeconds * 60),
            'ipsource' => self::getSameSite()
        ];
        $token['ns21'] = self::calculaHash($token['user'], $token['datetime'], $token['end'], $token['ipsource']);
        self::$timeToExpire = $token['end'];
        return SistemaLibrary::encrypt(json_encode($token));
    }

    public static function validate($token, $response = true) {
        $open = self::open($token);
        $hash = self::calculaHash($open->userEncoded, $open->datetime, $open->end, $open->ipsource);
        $result = false;

        switch (true) {
            case ($hash !== $open->ns21):
                $message = 'Integridade da autenticação violada (TKN-42)';
                break;
            case ($open->user->idUsuario <= 0) :
                $message = 'Necessário autenticação (TKN-38)';
                break;
            case ($open->end < time()) :
                $message = 'Sessão expirada (TKN-44)';
                break;
            default:
                $result = true;
                $message = '';
                break;
        }

        // Caso seja somente validação da API, deve retornar pois será validado na rota
        if (!$result && $response) {
            Api::result(Api::HTTP_UNAUTHORIZED, ['error' => $message]);
        }
        return [$result, $message, Api::HTTP_UNAUTHORIZED, $open];
    }

    public static function refresh($token) {
        $open = self::open($token);
        return self::create($open->user);
    }

    public static function open($token) {
        $out = json_decode(SistemaLibrary::decrypt($token));
        $opened = ((null !== $out) ? $out : new stdClass());
        $opened->userEncoded = $opened->user;
        $opened->user = json_decode(SistemaLibrary::decrypt((string) $opened->user, (string) $opened->datetime));
        return $opened;
    }

    public static function calculaHash($user, $data, $end, $ip): string {
        return hash('sha256', "$user $data $end $ip");
    }

    private static function getSameSite(): string {
        return md5((string) 'token' . $_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT']);
    }

    public static function decideRenew($token, $minutes = 5) {
        // Aqui controla se esta vencido
        $t = self::validate($token);

        // Renovação do token quando faltar $minutes minuto para expirar.
        return ($t->end - time()) < ($minutes * 60);
    }

}
