<?php

class Password {

    /**
     * Metodo que codifica senha com token e criptografia
     * Constante TOKEN definida em _config.php
     * @param unknown_type $senha
     */
    public static function codificaSenha($senha) {
        self::addToken($senha);
        return password_hash($senha, PASSWORD_DEFAULT);
    }

    public static function geraNovaSenha() {
        $senha = substr(md5(microtime()), 0, 8);
        return $senha;
    }

    public static function verify($senha, $hash) {
        self::addToken($senha);
        return password_verify($senha, $hash);
    }

    private static function addToken(&$senha) {
        $senha = md5(trim($senha) . Config::getData('token')); // incluir o token da aplicação
    } 

    public static function forcaSenha($senha) {
        $len = strlen($senha);
        if ($len < 6) {
            return -1;
        }
        $count = 0;
        if ($len >= 8) { // se tiver 8 ou mais ja ganha um ponto
            $count++;
        }
        $array = array("[[a-z]]", "[[A-Z]]", "[[0-9]]", "[!#_-]");
        foreach ($array as $a) { // a cada interação positiva, um ponto adicionado
            if (preg_match($a, $senha)) {
                $count++;
            }
        }
        return $count;
    }

}
