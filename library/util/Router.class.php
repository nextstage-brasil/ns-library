<?php

class Router {

    private $valorTotalURl;
    private $serverUri;
    private $pathView;
    public $param;
    private $route;
    public $allParam, $entidade;
    private $arrayValues;
    private $includeFile;

    /**
     * Inicia os dados do construtor
     * @return $this->pathView retorna o caminho = view/
     * @return $this->serverUri retorna o valor da REQUEST_URI ( LINK )
     * @return $this->valorTotalURl retorna o valoar da ( path quebrada PHP_URL_PATH ) para validação
     */
    public function __construct($arrayValues) {
        $this->pathView = '/';
        if (is_array($arrayValues)) {
            $this->arrayValues = $arrayValues;
        } else {
            $this->arrayValues = [];
        }

        //retorna o valor da REQUEST_URI ( LINK )
        $this->serverUri = $_SERVER['REQUEST_URI'];

        //retorna o valoar da ( path quebrada PHP_URL_PATH ) para validação
        $this->valorTotalURl = parse_url($this->serverUri, PHP_URL_PATH);

        $this->routes();
    }

    public function getIncludeFile() {
        return $this->includeFile;
    }

    public function getRoute() {
        return $this->route;
    }

    /**
     * 
     * @todo Gera a url para route
     * @return $diretorioAtual = Mostra o diretorio atual com getcwd + basename
     * @return $url  = explode a url
     * @return $search_array = procura na array o valor da base name
     * @return $url -> last = pega o ultimo nome da array
     */
    public function genereteUrl() {
        //Explode url vindo do $this->serverUri
        $url = explode('/', $this->serverUri);
        //Pega o nome do diretorio atual a base name do 
        $diretorioAtual = basename(getcwd());
        //Procura o valor do diretorio atual se tem na array $url
        $search_array = array_search($diretorioAtual, $url);

        //Contador de Valores para achar a dir na array
        $countValuesDir = 0;

        foreach ($url as $key) {
            if ($key == $diretorioAtual) {
                break;
            }
            $countValuesDir = $countValuesDir + 1;
        }

        //Se array search for falso 
        if (!$search_array) {
            //Retorna o caminho da url toda
            $url = $this->valorTotalURl;
            //retorna $url para rota
            return $url;
        }

        //Começa a contar os campos apartir da nimeração do $countValuesDir, a partir dai puxa a rota!
        $url = preg_replace("/(.+)$url[$countValuesDir]/", '', $this->valorTotalURl);
        //$url = $this->routePrefix . $url;
        //Retorna a Rota
        $this->allParam = Helper::filterSanitize(explode('/', $url));

        return $url;
    }

    /**
     * @todo pageError404() Erro se não encontrar pagina 404
     * @return http_response_code -> Adiciona no header error 404
     * @return $path -> Valor da Pasta e caminho do erro 404
     */
    public function pageError404() {
        return './view/error404.php';
    }

    /**
     * @todo   routes() Faz o caminho de Rotas
     * @param  $valorArray = valor de uso da rota exemple:
     * @return http_response_code -> Adiciona no header error 404
     * @return $path -> Valor da Pasta e caminho do erro 404
     */
    public function routes() {
        // IDENTIFICAR PARAMETROS
        $this->param = [];
        $temp = explode('/', self::genereteUrl());
        $genereteUrl = '/' . $temp[1];
        $this->param[] = (int) $temp[2]; // obrigatoriamente um ID deve ser um inteiro
        unset($temp[0]); // zero, vazio
        unset($temp[1]); // 1: rota
        unset($temp[2]); // 2: ID
        foreach ($temp as $value) {
            $this->param[] = $value;
        }

        //$generateUrl = Helper::decodifica($generateUrl);

        $this->validaOd1($genereteUrl);
        $this->includeFile = $this->pageError404();

        //Foreach dos dados da array $valorArray 
        foreach ($this->arrayValues as $valores) {
            if (empty($genereteUrl)) {
                $genereteUrl = '/';
            }
            if (Helper::compareString($genereteUrl, $valores['prefix']) || Helper::compareString($genereteUrl, $valores['prefix'] . '/')) {
                $this->entidade = explode('/', $valores['archive'])[0];
                $this->route = $valores['prefix'];
                $this->includeFile = Helper::directorySeparator($valores['archive']);
            }
        }
        return $this;
    }

    public static function rotaJS($link) {
        return Config::getData('url') . '/ns_tr/' . Helper::codifica(json_encode(
                                [
                                    'lk' => $link,
                                    'dt' => Helper::dateToMktime()
                                ]
        ));
    }

    private function validaOd1(&$rota) {
        if (Helper::compareString((string) $this->param[0], '-999')) {
            return true;
        }

        if ($rota === '/') {
            $rota = '/logout';
            return true;
        }
        //soente develoopes
        foreach (Config::getData('onlyDev') as $item) {
            $item = '/' . $item;
            if ((Helper::compareString($rota, $item) || Helper::compareString($rota, $item . '/')) && !$_SESSION['od1']) {
                $_SESSION['oldRoute'] = $item;
                $rota = '/od1';
                return true;
            }
        }

        //somente adminsitradores
        foreach (Config::getData('onlyAdm') as $item) {
            $item = '/' . $item;
            if ((Helper::compareString($rota, $item) || Helper::compareString($rota, $item . '/')) && !UsuarioController::isUserAdmin()) {
                $_SESSION['oldRoute'] = $item;
                $rota = '/adminOnly';
                Log::auditoria('ACESSO-INDEVIDO', $_SERVER['REQUEST_URI']);
                return true;
            }
        }
    }

}
