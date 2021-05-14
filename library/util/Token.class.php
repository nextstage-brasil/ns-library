<?php

/**
 * @date 01/02/2019
 * @author NS
 */
if (!defined("SISTEMA_LIBRARY")) {
    die("Acesso direto não permitido");
}

class Token {

    public static $timeToExpire;
    public static $expireSeconds;

    //public static $end;

    public function __construct() {
        
    }

    /**
     * Retorna uma chave extra para utilização na criptografia do token
     * @return type
     */
    public static function getExtraKey() {
        return date('Ymd');
    }

    public static function create($user) { 
        $user = (object) $user; //array ((gettype($user)) ? :);
        $token['datetime'] = (string) Helper::dateToMktime();
        $token['user'] = Helper::codifica72(json_encode($user), $token['datetime']);
        self::$expireSeconds = Config::getData('sessionTimeout'); //(($user->sessionLimit) ? (int) $user->sessionLimit : 10);
        $token['end'] = time() + self::$expireSeconds * 60;
        //self::$end = date('Y-m-d H:i:s');
        //$token['end'] = time() + (6);
        self::setValidade($token['end']);
        $token['ipsource'] = getenv('REMOTE_ADDR');
        $token['ns21'] = self::calculaHash($token['user'], $token['datetime'], $token['end'], $token['ipsource']);
        $token = Helper::codifica72(json_encode($token), self::getExtraKey());
        return $token;
    }

    public static function validate($token) {
        $open = self::open($token);
        $cod = 401;
        
        // validação de hash
        $token = json_decode(Helper::decodifica72($token, self::getExtraKey()), true);
        $hash = self::calculaHash($token['user'], $token['datetime'], $token['end'], $token['ipsource']);
        if ($hash !== $token['ns21']) { // sha para garantir que nenhum dado foi alterado
            //Log::logTxt('token', "Integridade violada: $hash !== $token[ns21]");
            Api::result($cod, ['error' => 'Integridade da autenticação violada (TKN42)']);
        }
        // Usuário
        if ($open->user->idUsuario <= 0) {
            Api::result($cod, ['error' => 'Necessário autenticação (TKN-38)']);
        }

        // validade prazo
        $agora = time();
        //Log::logTxt('token', 'Validade time: ' . date('Y-m-d H:i:s', $open->end) . ', Agora: ' . date('Y-m-d H:i:s', time()));
        if ($open->end < $agora) {
            Api::result($cod, ['error' => 'Sessão expirada (TKN-44)']);
        }

        // IP de origem
        if ($open->ipsource !== getenv('REMOTE_ADDR')) {
            Api::result($cod, ['error' => 'Origem de chamada inválida (TKN-49)']);
        }

        return $open;
    }

    public static function refresh($token) {
        $open = self::open($token);
        return self::create($open->user);
    }

    private static function setValidade($end) {
        self::$timeToExpire = $end; // date('dd/mm/yyyy H:i:s', $end);
    }

    public static function open($token) {
        $out = json_decode(Helper::decodifica72($token, self::getExtraKey()));
        self::setValidade($out->end);
        $out->user = json_decode(Helper::decodifica72($out->user, $out->datetime));
        return $out;
    }

    public static function calculaHash($user, $data, $end, $ip) {
        return hash('sha256', Helper::codifica("$user $data $end $ip"));
    }

}
