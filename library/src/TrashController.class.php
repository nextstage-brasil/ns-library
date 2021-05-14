<?php

if (!defined("SISTEMA_LIBRARY")) {
    die("Acesso direto não permitido");
}

/**
 * 
 * @date 08/10/18 10:45:28
 */
class TrashController extends AbstractController {

    /**
     * @create 08/10/2018
     */
    public function __construct() {
        $this->ent = 'Trash';
        $this->camposDate = ['createtimeTrash'];
        $this->camposDouble = [];
        $this->camposJson = ['jsonTrash'];

        $this->condicao = [];
        $this->object = new $this->ent();
    }

    public static function toEntitie(Trash $Trash) {
        $ctr = new TrashController();
        return $ctr->TrashEntidade($Trash);
    }

    private function TrashEntidade(Trash $Trash) {
        $dao = new EntityManager();
        $id = $Trash->getId();
        $Trash = Helper::parseDateToDatePTBR($Trash, $this->camposDate, $this->camposDouble);
        $Trash += [
            'Files' => UploadfileController::getFiles(['entidade' => 'Trash', 'valorid' => $id], $dao),
        ];

        return $Trash;
    }

    ## Metodos padrão para WebService (ws)
    /**
     * @create 03/02/2019
     * Método responsavel por devolver uma entidade nova e vazia
     */

    public function ws_getNew() {
        return parent::toView($this->object);
    }

    /**
     * @create 03/02/2019
     * Método responsavel por devolver uma entidade nova e vazia
     */
    public function ws_getById($dados) {
        Poderes::verify('EventoStatus', 'EventoStatus', 'ler');
        $ent = parent::getById($dados['id'], true);
        return parent::toView($ent);
    }

    /**
    * @create 03/02/2019
    * Metodo responsavel por gerar relação de dados da entidade. Acesso via JSON.
    */
    public function ws_getAll($dados) {
        Poderes::verify('Trash', 'Trash', 'listar');       
        
        // IDs esperados
                foreach (['idGrade'] as $v) {
            if ((int) $dados[$v] > 0) {
                $this->condicao[$v] = (int) $dados[$v];
            }
        }
        $inicio = (int)$dados['pagina'];
        $fim = 100; // paginação obrigatória
        $getRelacao = ((isset($dados['getRelacao']))?$dados['getRelacao']:true);
        
        // set search padrão - ira procura por nomeENTIDADE
        $this->setSearch($dados);

        $entities = parent::getAll($dados, $getRelacao, $inicio, $fim, $order);
        $out = Helper::parseDateToDatePTBR($entities, $this->camposDate, $this->camposDouble, $this->camposJson);
        $this->setDadosComboSearch($dados, $out, $this->ent);
        
        return $out;

   }
    

    public static function getList($idRelacao, $dados) {
        $t = new TrashController();
        $dados['idTrash'] = (int) (($idRelacao > 0) ? $idRelacao : -1);
        return $t->ws_getAll($dados);
    }

    /**
    * @create 03/02/2019
    * Metodo responsavel por salvar uma entidade
    */
    public function ws_save($dados) {
        Poderes::verify('EventoStatus', 'EventoStatus', 'editar');
        if (method_exists($this->object, 'setIdUsuario'))   { 
            $dados['idUsuario'] = $this->condicao['idUsuario'];
        }
        if (method_exists($this->object, 'setIdEmpresa'))   { 
            $dados['idUsuario'] = $this->condicao['idEmpresa'];
        }        

        $id = parent::save($dados);
        
        // Retornar o objeto persisitido
        $t = $this->ws_getById(['id' => $id]);    
        $t['result'] = Translate::get('Salvo com sucesso');
        return $t;
    }

    /**
     * @create 08/10/2018
     * Metodo responsavel por remover uma entidade
     */
    public function ws_remove($dados) {
        Poderes::verify('Trash', 'Trash', 'excluir');
        return parent::remove($dados['id']);
    }

    public static function move($entidade, $id) {
        $dao = new EntityManager();
        $dao->beginTransaction();
        $entObject = new $entidade();
        $dao->setObject($entObject);
        $trash = new Trash();
        // obter entidade a remover
        $ent = $dao->getAll(["id$entidade" => (int) $id], false)[0];
        if (!($ent instanceof $entidade)) {
            $trash->setError('Erro ao remover (TRA-168)');
            return $trash;
        }
        $t = new TrashController();
        $trash->setEntidadeTrash($entidade);
        $trash->setJsonTrash($t->objectToArray($ent));
        $trash->setUsuarioTrash($_SESSION['user']['nomeUsuario']);
        $dao->setObject($trash);
        $dao->save();
        if ($dao->getObject()->getError() === false) {
            $dao->setObject($ent);
            $ret = $dao->remove();
            if ($ret !== true) { // tudo certo, removido com segurança
                $dao->getObject()->setError('Erro ao remover (TRA-183)');
            } else {
                $dao->getObject()->setError(false);
            }
        }
        $dao->commit();
        return $dao->getObject();
    }

    public function ws_undo($dados) {
        $dao = new EntityManager(new Trash());
        $dao->beginTransaction();
        $trash = $dao->getAll(['idTrash' => (int) $dados['idTrash']], false)[0];
        if ($trash instanceof Trash) {
            $data = (array) json_decode($trash->getJsonTrash(), true);
            $nomeEntidade = $trash->getEntidadeTrash();
            $entidade = $trash->getEntidadeTrash();
            $entidade = new $entidade($data);
            $idOriginal = $entidade->getId(); // salvar o ID original
            $entidade->setId(0); // zerar para acitrar o insert
            $dao->setObject($entidade);
            $ret = $dao->save();
            $idNovo = $ret->getId(); // pegar id gerado, para atualizar o ID
            $query = "update $nomeEntidade set id_$nomeEntidade= $idOriginal where id_$nomeEntidade= $idNovo"; // alterar o novo registro para ID original
            $dao->execQueryAndReturn($query);
            $ret->setId($idOriginal);
            if ($ret->getError() === false) {
                $dao->commit();
            }
            return parent::objectToArray($ret);
        } else {
            return ['error' => 'Não foi possível recuperar o item'];
        }
    }

}
