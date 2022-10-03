<?php

namespace NsLibrary\Controller\ApiRest\Default;

use NsApp\NsLibrary\Entities\Cep as Entitie;
use NsLibrary\Config;
use NsLibrary\Controller\ApiRest\AbstractApiRestController;
use NsUtil\Api;
//use Poderes;

/** Created by NsLibrary Framework **/
if (!defined("SISTEMA_LIBRARY")) {
    die("CepRestController: Direct access not allowed. Define the SISTEMA_LIBRARY contant to use this class.");
}

/**
 * Rest Controller da rota
 * Basta seguir o padrão ApiREST com os verbos HTTP para ação
 * Caso seja uma ação especifica, ex.: /another, use a rota: 
 * @date 2022-10-03T00:46:07+00:00
 */

class Cep extends AbstractApiRestController
{

    private $entitieName =  'Cep';

    public function __construct(Api $api)
    {
        $this->init($api);
        $this->controllerInit(
            $this->entitieName,
            new Entitie(),
            'Cep',
            'Cep',
            Config::getData('entitieConfig')[$this->entitieName]['camposDate'],
            Config::getData('entitieConfig')[$this->entitieName]['camposDouble'],
            Config::getData('entitieConfig')[$this->entitieName]['camposJson'],
        );
        $this->dados['cep'] = (string) $this->dados['id'];
    }

    public function list(): void
    {
        $out = $this->ws_getAll($this->dados, false);
        $this->response($out);
    }

    public function read(): void
    {
        $out = $this->ws_getAll($this->dados, false)[0] ?? [];
        $this->response($out);
    }
}
