<?php

class Api {

    public static $dao;
    public static $eficiencia;

    public static function result($codResponse, $result = []) {
        global $TokenAPI; // vem de api/index
        global $rota, $appKey;

        // Eficiencia
        if (!(self::$eficiencia instanceof Eficiencia)) {
            self::$eficiencia = new Eficiencia();
        }
        // pré sets obrigatórios para evitar erros de resposta. Assim posso retornar dentro das classes, direto pro view
        if ($codResponse === 200) {
            //$result['token'] = Token::refresh($TokenAPI); // para poder atualizar o token, quando o respnse vir de dentro de uma classe e não passar pela API
        }
        $result['content'] = (($result['content']) ? $result['content'] : []);
        $result['error'] = (($result['error']) ? $result['error'] : false);

        $r = (object) $result;
        $r->status = $codResponse;
        self::$eficiencia->end();

        if (Config::getData('dev')) {
            $r->timeElapsed = self::$eficiencia->getResult()->time;
            $r->memory = self::$eficiencia->getResult()->memory;
        }

        // Rotas que não devem contar as mensagens não lidas
        $r->mnr = 0;
        if (array_search($rota, ['App/uploadFile', 'Usuario/esqueciSenha']) === false) {
            // count messages not read
            $r->mnr = MensagemController::countMessageNotReadToUser();
        }



        // Rotas que não devem receber token na resposta, para nao atrapalhar o timesession, pois são requisições automaticas
        if (array_search($rota, ['Mensagem/getConversas', 'Usuario/esqueciSenha', 'Mensagem/getMessages']) > -1) {
            $r->token = false;
            $r->expire = false;
        } else {
            //Log::logTxt('api-res', var_export($_SESSION['user'], true));
            if ($r->token) {
                //$r->expire = Token::$expireSeconds;
                //$r->expire = $_SESSION['user']['sessionLimit'];
                //$r->token = Token::refresh($r->token);
            }
        }
        
        // Caso o bryan esteja ooperando, não precisa retornar alguns itens
        if ($_SESSION['user']['tipoUsuario'] === 6)   {
            unset($r->token);
            unset($r->expire);
            unset($r->mnr);
        }

        // se for result diferente de 200 e não for execução de query (pq o exception ja tratou, registrar um log de erro
        if ($codResponse > 401 && stripos($result['error'], 'SQLSTATE') === false && stripos($result['error'], 'sem permissão') === false) {
            Log::error('API Response > 200: ' . $result['details'] . ' | ' . $result['error']);
            unset($result['details']);
        }


        // Log de Response
        http_response_code($codResponse);
        $out = json_encode($r);

        // Salvar o response deste chamado
        if (self::$dao instanceof EntityManager) { // sef oi setado o DAO
            $obj = self::$dao->getObject();
            if ($obj instanceof ApiLog) { // se o objeto setado, é um ApiLog
                $obj->setResponseApiLog($out);
                self::$dao->setObject($obj);
                self::$dao->save();
            }
        }
        //Log::gravaFromSession();
        echo $out;
        die();
    }

    // para setar o DAO a ser utilizado
    public static function setDao(&$dao) {
        self::$dao = $dao;
    }

    // Método para validar as chamadas deste apikey.
    public static function validaApiKey($apiKey) {
        // Obter a empresa ou usuario que contém esse apikey
        // apikey é um token que contem: login, empresa, senha
        // Para renovar a apikey, basta alterar a senha, pois o login sera feito com a senha do apikey
        // falta fazer

        /*
          if ($apiKey !== Config::getData('apiKey')) {
          $result['error'] = 'Invalid Api-Key';
          self::result(403, $result);
          }
         * 
         */
    }

}
