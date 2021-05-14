<?php
if (!defined("SISTEMA_LIBRARY")) {die("Acesso direto não permitido");}               

/**
* 
* @date 21/11/18 01:19:24
*/
class UsuarioPermissaoController extends AbstractController {
    private static $camposDate = []; // relacionar os campos do tipo date que precisam ser tratados antes de enviar a resposta
    private static $camposDouble = []; // relacionar os campos do tipo double que precisam ser tratados antes de enviar a resposta
    private static $camposJson = []; // relacionar os campos do tipo double que precisam ser tratados antes de enviar a resposta    
    private static $ent = 'UsuarioPermissao';
    private $object;
    private $condicao;


    /**
     * @create 21/11/2018
     */
    public function __construct() {
        $this->condicao = [];
        $this->object = new self::$ent();
        if (method_exists($this->object, 'setIdUsuario'))   {
            $this->object->setIdUsuario($_SESSION['user']['idUsuario']);
            $this->condicao['idUsuario'] = $_SESSION['user']['idUsuario'];
        }
    }

    /**
     * @create 21/11/2018
     */
    private function toEntitie($object) {
        $out = parent::parseToView($object, self::$ent, self::$camposDate, self::$camposDouble, true);
        return $out;
    }
    

    ## Metodos padrão para WebService (ws)
    /**
    * @create 21/11/2018
    * Método responsavel por devolver uma entidade nova e vazia
    */
    public function ws_getNew()   {
        return $this->toEntitie($this->object);
    }
    
    /**
    * @create 21/11/2018
    * Método responsavel por devolver uma entidade nova e vazia
    */
    public function ws_getById($dados) {
        Poderes::verify('UsuarioPermissao', 'UsuarioPermissao', 'ler');
        $dao = new EntityManager($this->object);
        $this->condicao[$this->object->getCpoId()] = $this->object->getCpoId();
        $object = $dao->getAll($this->condicao, true, 0, 1)[0];
        return $this->toEntitie($object);
    }
    
    /**
    * @create 21/11/2018
    * Metodo responsavel por gerar relação de dados da entidade. Acesso via JSON.
    */
    public function ws_getAll($dados) {
        Poderes::verify('UsuarioPermissao', 'UsuarioPermissao', 'listar');
        
        // IDs esperados
                foreach (['idSistemaPermissao','idSistemaFuncao','idUsuario'] as $v) {
            if ((int) $dados[$v] > 0) {
                $this->condicao[$v] = (int) $dados[$v];
            }
        }
        
        $inicio = (int)$dados['pagina'];
        $fim = 50; // paginação obrigatória
        parent::setSearch(self::$ent, $this->condicao, $dados);
        
        $dao = new EntityManager($this->object);
        $getRelacao = ((isset($dados['getRelacao']))?$dados['getRelacao']:true);
        $entities = $dao->getAll($this->condicao, $getRelacao, $inicio, $fim);
        $out = Helper::parseDateToDatePTBR($entities, self::$camposDate, self::$camposDouble);
        
        parent::setDadosComboSearch($dados, $out, self::$ent);
        
        return $out;
   }
    

    /**
    * @create 21/11/2018
    * Metodo responsavel por salvar uma entidade
    */
    public function ws_save($dados) {
        if (method_exists($this->object, 'setIdUsuario'))   { 
            $dados['idUsuario'] = $this->condicao['idUsuario'];
        }
        Poderes::verify('UsuarioPermissao', 'UsuarioPermissao', 'editar');
        Helper::jsonRecebeFromView($dados, self::$camposJson);
        $dao = new EntityManager(new self::$ent($dados));
        $dao->beginTransaction();

        // Salvar entidade
        if ($dao->save()->getError()) {
            return ['error' => $dao->getObject()->getError()];
        }

        // Tratamento de uploadfiles
        $error = AppController::trataUploadFile($dados['Files'], self::$ent, $dao->getObject()->getId(), $dao)['error'];
        if ($error) {
            return ['error' => $error];
        }

        // Persistir, pois não houve erro
        $dao->commit();

        // Retornar o objeto persisitido
        $t = $this->ws_getById([$this->object->getCpoId() => $dao->getObject()->getId()]);    
        $t['result'] = Translate::get('Salvo com sucesso');
        return $t;
    }

    /**
    * @create 21/11/2018
    * Metodo responsavel por remover uma entidade
    */
    public function ws_remove($dados) {
        Poderes::verify('UsuarioPermissao', 'UsuarioPermissao', 'excluir');
       
        $trash = TrashController::move(self::$ent, $dados[$this->object->getCpoId()]);
        return ['error' => $trash->getError(), 'idTrash' => $trash->getId()];
    }

}
