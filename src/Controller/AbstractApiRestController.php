<?php

namespace NsLibrary\Controller;

/**
 * TODO Auto-generated comment.
 */
abstract class AbstractApiRestController {

    protected $rest, $dados, $header, $token, $type, $action;

    public function __construct(\NsUtil\Api $api) {

        $api->setConfig();
        $router = $api->getRouter();
        $this->rest = (object) \NsUtil\Config::getData('rest');
        $this->dados = $api->getBody();
        $this->header = $api->getHeaders();
        $this->token = $api->getTokenFromAuthorizationHeaders();

        // Definições API Rest
        switch ($this->rest->method) {
            case 'GET':
                $this->type = ucwords($this->rest->resource);
                $this->dados['id'] = $this->rest->id;

                // Definição de ação
                if ($this->rest->action) {
                    $this->action = $this->rest->action;
                } else {
                    $this->action = (((int) $this->rest->id > 0) ? 'read' : 'index');
                }
                break;
            case 'DELETE':
                $this->type = ucwords($this->rest->resource);
                $this->action = 'delete';
                $this->dados['id'] = (int) $this->rest->id;
                break;
            case 'POST':
                $this->type = ucwords($this->rest->resource);
                $this->action = 'create';
                break;
            case 'PUT':
                $this->type = ucwords($this->rest->resource);
                $this->action = 'update';
                $this->dados['id'] = (int) $this->rest->id;
                break;
            default: // post por enquanto
                throw new Exception('Action is not implements');
        }
    }

    public abstract function index();

    public abstract function read();

    public abstract function create();

    public abstract function update();

    public abstract function delete();
}
