<?php

class BuscaCep {

    private static function getCepLocal($cepText) {
        $dd = false;
        if (class_exists('Cep')) { // dados contidos na API Local
            $dao = new EntityManager(new Cep());
            $app = new AppController();
            $cep = $dao->getAll(['cep' => $cepText], false, 0, 1)[0];
            if ($cep instanceof Cep) {
                $dd = $app->objectToArray($cep);
            } else {
                // Obter aqui e atualizar banco de dados local
                $resultado = file_get_contents("https://viacep.com.br/ws/$cepText/json/");
                $cep = (array) json_decode($resultado);
                if ($cep['erro'] !== true) {
                    $cep['cep'] = str_replace('-', '', $cep['cep']);
                    $c = new Cep($cep);
                    $c->getError();
                    Log::ver($c);
                    $dao->setObject($c);
                    $dao->save();
                    $dd = $app->objectToArray($c);
                }
            }
        } else { // dados contidos na ApiPark
            $dd = (array) Helper::consumeApi('api', 'App/cep', ['cep' => $cepText])->content;
        }
        return (object) $dd;
    }

    public static function get($cep) {
        $outInsert = [];
        $cep = preg_replace("/[^0-9]/", "", $cep);
        if (strlen($cep) !== 8) {
            return array('error' => true, 'CEP' => $cep);
        }
        Log::logTxt('debug', 'Busca CEP: ' . $cep);
        return self::getCepLocal($cep);
    }

}
