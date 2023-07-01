<?php

namespace NsLibrary\Controller\ApiRest;

use Exception;
use NsLibrary\Controller\ControllerDefault;
use NsUtil\Api;
use NsUtil\Config;
use NsUtil\Log;
use ReflectionMethod;

use function NsUtil\dd;

/**
 * TODO Auto-generated comment.
 */
abstract class AbstractApiRestController extends ControllerDefault
{

    protected $api;
    protected $rest, $dados, $header, $token, $type, $action, $error;

    public function __construct(Api $api)
    {
        $this->init($api);
    }

    public function __invoke()
    {
        try {
            if (method_exists($this, $this->action)) {
                if ($this->error !== null) {
                    $this->api->error($this->error['error'], $this->error['code']);
                }
                $fn = $this->action;



                // injeção de dependências

                $reflectionMethod = new ReflectionMethod($this, $fn);
                $reflectionParameters = $reflectionMethod->getParameters();
                $dependencies = [];
                $dependencyResolver = new DependencyResolver();

                foreach ($reflectionParameters as $parameter) {
                    $dependencyName = $parameter->getName();
                    $dependencyClass = $parameter->getType();


                    switch (true) {
                        case $dependencyClass !== null && !$dependencyClass->isBuiltin():
                            $dependencyInstance = $dependencyResolver->resolve($dependencyClass->getName());
                            $dependencies[] = $dependencyInstance;
                            break;
                        case $dependencyName === 'id':
                            $dependencies[] = (int) $this->dados['id'];
                            break;
                        case $dependencyClass !== null && $dependencyClass->getName() === 'array':
                            $dependencies[] = $this->dados;
                            break;
                        case is_array($parameter->getDefaultValue()):
                            $dependencies[] = $this->dados;
                            break;
                        case $dependencyClass !== null && $dependencyClass->isBuiltin():
                            throw new Exception("Dependency '$dependencyName' is builtin and could not be resolved.");
                            break;
                        default:
                            throw new Exception("Dependency '$dependencyName' could not be resolved.");
                    }
                }

                // invocar funcao com dependencias
                $reflectionMethod->invokeArgs($this, $dependencies);
            } else {
                $this->api->error('', Api::HTTP_NOT_IMPLEMENTED);
            }
        } catch (Exception $exc) {

            Log::logTxt('/tmp/ns-library_dependency-error.log', $exc->getMessage());

            $this->api->error($exc->getMessage(), Api::HTTP_BAD_REQUEST);
        }
    }

    public function init(Api $api)
    {
        $api->setConfig();
        $this->rest = (object) Config::getData('rest');
        $this->dados = $api->getBody();
        $this->header = $api->getHeaders();
        $this->token = $api->getTokenFromAuthorizationHeaders();
        $this->api = $api;
        $this->action = 'null';
        $this->type = 'null';

        // Definições API Rest
        switch ($this->rest->method) {
            case 'GET':
                $this->type = ucwords($this->rest->resource);
                $this->dados['id'] = $this->rest->id > 0 ? $this->rest->id : null;

                // Definição de ação
                if ($this->rest->action) {
                    $this->action = $this->rest->action;
                } else {
                    $this->action = (((int) $this->rest->id > 0) ? 'read' : 'list');
                }
                break;
            case 'DELETE':
                $this->type = ucwords($this->rest->resource);
                $this->action = 'delete';
                $this->dados['id'] = (int) $this->rest->id;
                if ($this->dados['id'] === 0) {
                    $this->error = ['error' => 'ID not received  to delete', 'code' => Api::HTTP_BAD_REQUEST];
                }
                break;
            case 'POST':
                $this->type = ucwords($this->rest->resource);
                $this->action = 'create';
                unset($this->dados['id']);
                break;
            case 'PUT':
            case 'PATCH':
                $this->type = ucwords($this->rest->resource);
                $this->action = 'update';
                $this->dados['id'] = (int) $this->rest->id;
                if ($this->dados['id'] === 0) {
                    $this->error = ['error' => 'ID not received  to update', 'code' => Api::HTTP_BAD_REQUEST];
                }
                break;
            default: // post por enquanto
                $this->error = ['error' => '', 'code' => Api::HTTP_NOT_IMPLEMENTED];
        }
        $this->dados['id' . $this->type] = ($this->dados['id'] ?? null);
    }

    function response(array $response, int $code = 200): void
    {
        // Caso seja um GET para obter um ID, responser com 404 se não encontrar
        if ($this->rest->method === 'GET' && $this->dados['id'] > 0 && count($response) === 0 && $code !== Api::HTTP_NOT_IMPLEMENTED) {
            $code = Api::HTTP_NOT_FOUND;
        }
        $pagination = $response['pagination'] ?? [];
        unset($response['pagination']);
        // Resposta    
        $this->api->response([
            'content' => $response,
            'error' => $response['error'] ?? false,
            'pagination' => $pagination
        ], $code);
    }

    function errorResponse(string $message, int $code = Api::HTTP_BAD_REQUEST): void
    {
        $this->response(['error' => $message], $code);
    }
}
