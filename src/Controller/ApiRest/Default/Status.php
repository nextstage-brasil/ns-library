<?php

namespace NsLibrary\Controller\ApiRest\Default;

use NsApp\NsLibrary\Entities\Status as Entitie;
use NsLibrary\Config;
use NsLibrary\Connection;
use NsLibrary\Controller\ApiRest\AbstractApiRestController;
use NsLibrary\SistemaLibrary;
use NsUtil\Api;
use NsUtil\Validate;

/** Created by NsLibrary Framework * */
if (!defined("SISTEMA_LIBRARY")) {
    die("StatusRestController: Direct access not allowed. Define the SISTEMA_LIBRARY contant to use this class.");
}

/**
 * Rest Controller da rota
 * Basta seguir o padrão ApiREST com os verbos HTTP para ação
 * Caso seja uma ação especifica, ex.: /another, use a rota: 
 * @date 2022-10-02T22:05:12+00:00
 */
class Status extends AbstractApiRestController
{

    private $entitieName = 'Status';

    public function __construct(Api $api)
    {
        $this->init($api);
        $this->controllerInit(
            $this->entitieName,
            new Entitie(),
            'Status',
            'Status',
            Config::getData('entitieConfig')[$this->entitieName]['camposDate'],
            Config::getData('entitieConfig')[$this->entitieName]['camposDouble'],
            Config::getData('entitieConfig')[$this->entitieName]['camposJson'],
        );
    }

    public function list(): void
    {
        $this->checkParameters();
        $this->dados['entidadeStatus'] = mb_strtoupper($this->dados['entidade']);
        $out = $this->ws_getAll($this->dados);
        $this->response($out);
    }

    public function read(): void
    {
        $out = $this->ws_getById($this->dados, false);
        $this->response($out);
    }

    public function create(): void
    {
        $out = $this->ws_save($this->dados);
        $this->response($out);
    }

    public function update(): void
    {
        $this->create();
    }

    public function delete(): void
    {
        $out = $this->ws_remove($this->dados);
        $this->response($out);
    }

    private function checkParameters()
    {
        (new Validate())
            ->addCampoObrigatorio('entidade', 'Informe a entidade')
            ->runValidateData($this->dados, $this->api, Api::HTTP_BAD_REQUEST);
    }

    public function _getListToArray(string $entidade): array
    {
        $this->dados['entidade'] = mb_strtoupper($entidade);
        $this->dados['entidadeStatus'] = mb_strtoupper($entidade);
        $this->checkParameters();
        $list = $this->ws_getAll($this->dados);
        $status = [];
        foreach ($list as $item) {
            $status[$item['orderStatus']] = $item['idStatus'];
        }
        return $status;
    }

    public function _getIdByOrder(string $entidade, int $order): int
    {
        $this->dados['entidade'] = mb_strtoupper($entidade);
        $this->dados['entidadeStatus'] = mb_strtoupper($entidade);
        $this->dados['orderStatus'] = $order;
        $this->checkParameters();
        $out = $this->ws_getAll($this->dados)[0] ?? [];
        return (int) $out['idStatus'] ?? -1;
    }
}
