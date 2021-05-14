<?php

if (!defined("SISTEMA_LIBRARY")) {
    die("Acesso direto não permitido");
}

/**
 * 
 * @date 30/05/19 04:03:59
 */
class StatusController extends AbstractController {

    /**
     * @create 30/05/2019
     */
    public function __construct() {
        $this->ent = 'Status';
        $this->camposDate = [];
        $this->camposDouble = [];
        $this->camposJson = [];

        $this->condicao = [];
        $this->object = new $this->ent();

        if (method_exists($this->object, 'setIdUsuario') && !Helper::compareString($this->ent, 'usuario')) {
            $this->object->setIdUsuario($_SESSION['user']['idUsuario']);
            $this->condicao['idUsuario'] = $_SESSION['user']['idUsuario'];
        }
        if (method_exists($this->object, 'setIdEmpresa') && !Helper::compareString($this->ent, 'empresa')) {
            $this->object->setIdEmpresa($_SESSION['user']['idEmpresa']);
            $this->condicao['idEmpresa'] = $_SESSION['user']['idEmpresa'];
        }
        if (method_exists($this->object, 'setIsAlive' . $this->ent)) {
            $this->condicao['isAlive' . $this->ent] = 'true'; // somente deve mostrar tuplas vivas. Deletadas devem ser obtidas explicitamente.
        }


        // Campos default para o JSON. Chave => Config. 
    }

    /**
     * @create 30/05/2019
     * Chama o método em parent e retorna. Caso seja necessário alguma intervenção nesta classe
     */
    public function toView($obj) {
        return parent::toView($obj);
    }

    ## Metodos padrão para WebService (ws)

    /**
     * @create 30/05/2019
     * Método responsavel por devolver uma entidade nova e vazia
     */
    public function ws_getNew() {
        return $this->toView($this->object);
    }

    /**
     * @create 30/05/2019
     * Método responsavel por devolver uma entidade nova e vazia
     */
    public function ws_getById($dados) {
        Poderes::verify('Status', 'Status', 'ler');
        $ent = parent::getById($dados['id'], true);
        return $this->toView($ent);
    }

    /**
     * @create 30/05/2019
     * Metodo responsavel por gerar relação de dados da entidade. Acesso via JSON.
     */
    public function ws_getAll($dados) {
        Poderes::verify('Status', 'Status', 'listar');

        // IDs esperados
        foreach (['idStatus', 'idEmpresa'] as $v) {
            if ((int) $dados[$v] > 0) {
                $this->condicao[$v] = (int) $dados[$v];
            }
        }
        if ($dados['entidadeStatus']) {
            $this->condicao['entidadeStatus'] = $dados['entidadeStatus'];
        }
        $inicio = (int) $dados['pagina'];
        $fim = 300; // paginação obrigatória
        $getRelacao = ((isset($dados['getRelacao'])) ? $dados['getRelacao'] : true);

        // set search padrão - ira procura por nomeENTIDADE
        $this->setSearch($dados);

        $entities = parent::getAll($dados, $getRelacao, $inicio, $fim, $order);
        $out = Helper::parseDateToDatePTBR($entities, $this->camposDate, $this->camposDouble, $this->camposJson);
        $this->setDadosComboSearch($dados, $out, $this->ent);

        return $out;
    }

    /**
     * @create 30/05/2019
     * Metodo responsavel por salvar uma entidade
     */
    public function ws_save($dados) {
        $action = ( ((int) $dados['id' . $this->ent] > 0) ? 'Editar' : 'Inserir');
        Poderes::verify('Status', 'Status', $action);
        if (method_exists($this->object, 'setIdUsuario')) {
            $dados['idUsuario'] = $this->condicao['idUsuario'];
        }
        if (method_exists($this->object, 'setIdEmpresa')) {
            $dados['idUsuario'] = $this->condicao['idEmpresa'];
        }

        // Caso utilize o avatar no uploadfile
        //$dados['idUploadfile'] = Helper::jsonToArrayFromView($dados['Uploadfile'])['idUploadfile'];// para controle via avatar

        $id = parent::save($dados);

        // Retornar o objeto persisitido
        $t = $this->ws_getById(['id' => $id]);
        $t['result'] = Translate::get('Salvo com sucesso');
        return $t;
    }

    /**
     * @create 30/05/2019
     * Metodo responsavel por remover uma entidade
     */
    public function ws_remove($dados) {
        Poderes::verify('Status', 'Status', 'excluir');
        return parent::remove($dados['id']);
    }

    public function getToAux($entidade) {
        if (strlen($entidade) < 3) {
            return [];
        }
        return $this->ws_getAll(['entidadeStatus' => mb_strtoupper($entidade)]);
    }

    public static function getListToArray($entidade) {
        $temp = new StatusController();
        $list = $temp->getToAux($entidade);
        $status = [];
        foreach ($list as $item) {
            $status[$item['orderStatus']] = $item['idStatus'];
        }
        return $status;
    }

    public static function getIdByOrder($entidade, $order, $getQuery = false) {
        $query = "select id_status from app_status where entidade_status='" . mb_strtoupper($entidade) . "' and order_status= " . (int) $order;
        if ($getQuery) {
            return $query;
        }
        $item = (new EntityManager())->execQueryAndReturn($query);
        return (int) $item[0]['idStatus'];
    }

}
