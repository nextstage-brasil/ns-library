<?php

namespace NsLibrary\Controller\ApiRest\Default;

use NsApp\NsLibrary\Entities\Uf as Entitie;
use NsLibrary\Config;
use NsLibrary\Controller\ApiRest\AbstractApiRestController;
use NsUtil\Api;
//use Poderes;

/** Created by NsLibrary Framework **/
if (!defined("SISTEMA_LIBRARY")) {
    die("UfRestController: Direct access not allowed. Define the SISTEMA_LIBRARY contant to use this class.");
}


/**
 * Rest Controller da rota
 * Basta seguir o padrão ApiREST com os verbos HTTP para ação
 * Caso seja uma ação especifica, ex.: /another, use a rota: 
 * @date 2022-10-03T00:46:08+00:00
 */

class Uf extends AbstractApiRestController
{

    private $entitieName =  'Uf';

    public function __construct(Api $api)
    {
        $this->init($api);
        $this->controllerInit(
            $this->entitieName,
            new Entitie(),
            'Uf',
            'Uf',
            Config::getData('entitieConfig')[$this->entitieName]['camposDate'],
            Config::getData('entitieConfig')[$this->entitieName]['camposDouble'],
            Config::getData('entitieConfig')[$this->entitieName]['camposJson'],
        );
    }

    public function list(): void
    {
        $out = $this->ws_getAll($this->dados);
        $this->response($out);
    }

    public function read(): void
    {
        $out = $this->ws_getById($this->dados);
        $this->response($out);
    }

    //    public function create(): void {
    //        $out = $this->ws_save($this->dados);
    //        $this->response($out);
    //    }
    //
    //    public function update(): void {
    //        $this->create();
    //    }
    //
    //    public function delete(): void {
    //        $out = $this->ws_remove($this->dados);
    //        $this->response($out);
    //    }
}
