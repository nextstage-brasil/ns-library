<?php

if (!defined('SISTEMA_LIBRARY')) {
    die('Acesso direto não permitido');
}

class SistemaException extends Exception {

    public function __construct($message, $code = 0) {
        //Log::error($message); 
        /*
        foreach (Config::getData('errors') as $chave => $value) {
            if (stripos($message, $chave) > -1) {
                $message = $value; 
                break;
            }
        }
         */
        $this->message = $message;
        $this->code = $code;
        //Api::result(200, ['error' => $message]);
    }

    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }

}
