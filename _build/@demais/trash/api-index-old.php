<?php

require_once '../library/SistemaLibrary.php';

// Criação do roteador
$router = (new Router([]));


//$includeFile = $router->routes();
$type = ucwords(Config::getData('entidadeName', filter_var($router->allParam[1], FILTER_SANITIZE_STRING)));
$action = filter_var($router->allParam[2], FILTER_SANITIZE_STRING);
$rota = "$type/$action";
$headers = getallheaders();
Helper::_objectToArray($object);
//$TokenAPI = $headers['Token'];
$TokenAPI = substr($headers['Authorization'], 6);
$appKey = $headers['App-Key'];



$_SESSION['user'] = false; // zerando a session a cada request, pois deve ser stateless
// para poder usar os nomes padrõe snas apis
$MethodsAliases = [
    'read' => 'getById',
    'list' => 'getAll',
    'create' => 'save',
    'delete' => 'remove',
    'update' => 'save',
    //'search' => 'getAll',
    'new' => 'getNew'
];
$action = ((isset($MethodsAliases[$action])) ? $MethodsAliases[$action] : $action);

// Obtendo dados enviados
$dados = $_POST;
if (count($dados) === 0) {
    $dados = json_decode(file_get_contents('php://input'), true);
}

if ((int) $router->allParam[3] > 0 && $action !== 'save') {
    $dados['id' . $type] = (int) $router->allParam[3];
    $dados['id'] = (int) $router->allParam[3];
}


$dados['ParamsRouter'] = $router->allParam;

// Criar objeto do log do request. Precisa estar antes do removeUndefined, pois tratara de inconsistencia. O qual mais original vier melhor
//if (!Config::getData('dev')) {
$dao = new EntityManager();
$apiLog = new ApiLog();
$apiLog->setNameApiLog('default');
$apiLog->setRotaApiLog($rota);
$apiLog->setHeadersApiLog(json_encode($headers));
$apiLog->setRequestApiLog(json_encode($dados));
$dao->setObject($apiLog);
Api::setDao($dao);
//}
//$dados = Helper::removeUndefinedFromJavascript($dados);
\NsUtil\Helper::recebeDadosFromView($dados);

Api::$eficiencia = new Eficiencia("$type/$action");
$app = new AppLibraryController();

// Respose padrão
$result = ['content' => [], 'error' => false];

// Atender as requisições do site, para configurar as requisições conforme esperado pela classe Site
if ($type === 'Site') {
    $dados['_nsm'] = $action;
    $rota = "App/site";
}

// tratamento full para campos daterange
if (strlen($dados['_periodoRange']) > 0) {
    $t = explode('à', $dados['_periodoRange']);
    $dados['dataInicial'] = trim($t[0]);
    $dados['dataFinal'] = trim($t[1]);
}

// Definição de Rotas. 
switch ($rota) {
    case '/':
        $result['content'] = Config::getData('name') . ' - Página inicial da API';
        $result['error'] = '';
        Api::result(200, $result);
        break;
    case 'App/sessionRenew':
    case 'Login/renew':
        Token::validate($TokenAPI);
        $result['token'] = Token::refresh($TokenAPI);
        $result['expire'] = Token::$timeToExpire;
        $result['content']['result'] = 'Sessão renovada!';
        $result['content']['icon'] = 'success';
        Api::result($codResponse, $result);
        break;
    case 'Login/enter': // login
        // executar solicitação
        try {
            $app = new UsuarioController();

            $dt = explode(':', base64_decode(substr($headers['Authorization'], 6)));
            $dados['username'] = $dt[0];
            $dados['password'] = $dt[1];

            $result['content'] = $app->login($dados['username'], $dados['password'], $dados['idEmpresa'], $appKey);
            $result['error'] = $result['content']->error;
            $result['token'] = $result['content']->token;
            $result['validade'] = Token::$timeToExpire;
            unset($result['content']->token);
            Api::result(200, $result);
        } catch (Exception $ex) {
            $result['error'] = 'Ocorreu um erro ao efetuar o login ' . $ex->getMessage();
            Api::result(500, $result);
        }
        break;

    case 'App/getAux':
        UsuarioController::loginByToken($TokenAPI);
        $result['content'] = AppLibraryController::getAux(Helper::jsonToArrayFromView($dados['extras']), $dados['entidade']);
        Api::result(200, $result);
        break;

    case 'assync/ocrapply':
        // serve para aplicar OCR assincrono em uploadfile. Espera id de um existente
        Log::logTxt('assync', 'Atendido. ID: ' . $router->allParam[3]);
        if ($dados['id']) {
            $ctr = new UploadfileController();
            $ctr->ws_ocrApply(['idUploadfile' => (int) $router->allParam[3]]);
        }
        Api::result(200, $result);
        break;

    case 'Usuario/esqueciSenha':
        Log::log('navigation-api', $rota, false, false, ['DADOS' => $dados]);
        // executar solicitação
        try {
            $app = new UsuarioController();
            $result['content'] = $app->ws_esqueciSenha($dados);
            $result['error'] = $result['content']['error'];
            $result['token'] = $result['content']->token;
            Api::result(200, $result);
        } catch (Exception $ex) {
            $result['error'] = 'Ocorreu um erro ao reenviar a senha ' . $ex->getMessage();
            Api::result(500, $result);
        }
        break;
    case 'Usuario/alteraSenha':
        try {
            $user = new UsuarioController();
            $result['content'] = $user->alteraSenha($dados);
            $result['error'] = $result['content']['error'];
            unset($result['token']);
            Api::result(200, $result);
        } catch (Exception $ex) {
            
        }
        break;
    case 'Usuario/cadastro':
        try {
            $user = new UsuarioController();
            $result['content'] = $user->ws_cadastro($dados);
            $result['error'] = $result['content']['error'];
            unset($result['token']);
            Api::result(200, $result);
        } catch (Exception $ex) {
            
        }
        break;
    case 'Licenca/status':
        Log::log('navigation-api', $rota, false, false, ['DADOS' => $dados]);
        $licenca = (string) $router->allParam[3];
        $app = new LicencaController();
        try {
            $result['content'] = $app->getStatus($licenca);
            $result['content']['is_valid'] = (($result['content']['error'] === false) ? true : false);
            $result['error'] = $result['content']['error'];
            unset($result['token']);
            Api::result(200, $result);
        } catch (Exception $ex) {
            $result['error'] = 'Ocorreu um erro ao reenviar a senha ' . $ex->getMessage();
            Api::result(500, $result);
        }
        break;
    case 'App/uploadFile':
        Api::$eficiencia->setLimits(2, 50);
        UsuarioController::loginByToken($TokenAPI);
        $app = new AppLibraryController();
        parse_str($headers['Data'], $data);
        //Log::logTxt('upload-data', $data);
        foreach ($data as $key => $value) {
            $dados[$key] = $value;
        }
        //Log::logTxt('upload-data', $dados);
        //$dados = array_merge($dados, $data);
        //Log::logTxt('uploadfile', $dados);
        $result['content'] = $app->ws_uploadFile($dados);
        $result['error'] = $result['content']['error'];
        $result['success'] = $result['error'] === false;
        Api::result(200, $result);
        break;
    case 'App/uploadFileNEW':
        Log::logTxt('upload-new', var_export($dados, true));
        Api::result(200, ['success' => true, 'error' => false]);
        break;

    case 'App/fsConnect':
        UsuarioController::loginByToken($TokenAPI);
        $app = new AppLibraryController();
        $result['content'] = $app::getTokenFileserver();
        Api::result(200, $result);
        break;
    case 'App/site': // rot apara atender as chamadas do site. Obrigatorio vir no corpo um campo com nome _nsm, indicando o metodo procurado
        $ctr = new SiteController();
        $fn = 'ws_' . $dados['_nsm'];
        if (!isset($dados['_nsm']) || !method_exists($ctr, $fn)) {
            Log::log('error', 'Acionada ação não prevista em SITE', false, false, $dados);
            $result['error'] = 'Action is not defined';
            $codResponse = 501;
        } else {
            $result['content'] = $ctr->$fn($dados);
            $codResponse = 200;
            if ($result['content']['error']) {
                $result['error'] = $result['content']['error'];
            }
        }
        Api::result($codResponse, $result);

        break;
    default:
        // Method not allowed
        $notAllowed = ['App/login', 'Usuario/esqueciSenha', 'App/uploadFile'];
        $noTokenNeed = ['App/cep']; // relação de rotas que não precisam da validação de token, pois não dependem de usuário
        $allowed = array_search(strtolower($rota), array_map('strtolower', $notAllowed));
        if ($allowed > -1) {
            $result['error'] = "Method not allowed in this API ($allowed)";
            Api::result(405, $result);
        }

        $tokenLiberado = array_search(strtolower($rota), array_map('strtolower', $noTokenNeed));
        if (!($tokenLiberado > -1)) { // validar token, caso não esteja na lista liberada acima
            // login By Token
            UsuarioController::loginByToken($TokenAPI);
        }

        // validar type (Entidade)
        $entidade = Config::getData('path') . '/auto/entidades/' . $type . '.class.php';
        if (!file_exists($entidade)) {
            // Entidade não existe, vamos tentar o ApiPark
            $result['error'] = 'Type "' . $type . '" not exists';
            Api::result(501, $result);
        }

        // validar action (Method)
        try {
            $entidade = new $type();
            $controller = $type . "Controller";
            $controller = new $controller($dados);
            $action = "ws_" . $action;
            if (method_exists($controller, $action)) { // executar ação e responder
                Log::navegacaoApi($rota, $dados);
                $result['content'] = $controller->$action($dados);
                $codResponse = 200;
                if ($result['content']['error']) {
                    $result['error'] = $result['content']['error'];
                }
            } else {
                $result['error'] = 'Action not exists ' . $action;
                $codResponse = 501;
            }

            // Renovação do token quando faltar 1 minuto para expirar. A renovação manual esta disponivel pela rota especifica
            if ((Token::$timeToExpire - time()) < 300) { // 5 minutos, renova
                $result['token'] = Token::refresh($TokenAPI);
            }
            $result['expire'] = Token::$timeToExpire;
            Api::result($codResponse, $result);
        } catch (Exception $exc) {
            if (Config::getData('dev')) {
                $result['error'] = $exc->getMessage() . '<br/>' . $exc->getTraceAsString();
            } else {
                Log::error('API-141' . $exc->getMessage());
                $result['error'] = 'Ocorreu um erro ao executar o pedido (API-141)';
            }

            // @rever somente para testes
            $result['error'] = $exc->getMessage() . '<br/>' . $exc->getTraceAsString();

            Api::result(500, $result);
        }

        break;
}