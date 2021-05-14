<?php

if (!defined("SISTEMA_LIBRARY")) {
    die("Acesso direto não permitido");
}

/**
 * 
 * @date 2020-05-18T16:29:48-03:00
 */
class IndispController extends AbstractController {

    private static $poderesGrupo = 'Usuários';
    private static $poderesSubGrupo = 'Indisponibilidade';

    /**
     * @create 18/05/2020
     */
    public function __construct() {
        $this->ent = 'Indisp';
        $this->camposDate = ['createtimeIndisp', 'inicioIndisp', 'fimIndisp'];
        $this->camposDouble = [];
        $this->camposJson = ['extrasIndisp'];

        $this->condicao = [];
        $this->object = new $this->ent();

        $this->condicao['appUsuario.idEmpresa'] = 1;

        if (method_exists($this->object, 'setIsAlive' . $this->ent)) {
            $this->condicao['isAlive' . $this->ent] = 'true'; // somente deve mostrar tuplas vivas. Deletadas devem ser obtidas explicitamente.
        }

        /**
          // Models json a ser configurado:
          'extrasIndisp' =>
          'nome_variavel' =>[
          ['default' => '', 'grid' => 'col-sm-4', 'type' => 'text', 'class' => '', 'ro' => 'false', 'tip' => '', 'label' => '']
          ],
         * */
    }

    /**
     * @create 18/05/2020
     * Chama o método em parent e retorna. Caso seja necessário alguma intervenção nesta classe
     */
    public function toView($obj) {
        return parent::toView($obj);
    }

    ## Metodos padrão para WebService (ws)
    /**
     * @create 18/05/2020
     * Método responsavel por devolver uma entidade nova e vazia
     */

    public function ws_getNew() {
        Poderes::verify(self::$poderesGrupo, self::$poderesSubGrupo, 'inserir');
        return $this->toView($this->object);
    }

    /**
     * @create 18/05/2020
     * Método responsavel por devolver uma entidade nova e vazia
     */
    public function ws_getById($dados) {
        Poderes::verify(self::$poderesGrupo, self::$poderesSubGrupo, 'ler');
        $ent = parent::getById($dados['id'], true);
        return $this->toView($ent);
    }

    /**
     * @create 18/05/2020
     * Metodo responsavel por gerar relação de dados da entidade. Acesso via JSON.
     */
    public function ws_getAll($dados) {
        Poderes::verify(self::$poderesGrupo, self::$poderesSubGrupo, 'ler');

        // IDs esperados
        foreach (['idIndisp', 'idUsuario'] as $v) {
            if ((int) $dados[$v] > 0) {
                $this->condicao[$v] = (int) $dados[$v];
            }
        }

        if ($dados['count']) {
            return parent::count();
        }

        if ($dados['dataInicial']) {
            $this->condicao['inicioIndisp'] = ['>=', "'" . Helper::formatDate($dados['dataInicial'], 'arrumar') . "'"];
        }


        $inicio = (int) $dados['pagina'];
        $fim = $dados['idUsuario'] ? 1000 : 30; // paginação obrigatória
        $getRelacao = true;

        // set search padrão - ira procura por nomeENTIDADE
        $this->setSearch($dados);

        $entities = parent::getAll($dados, $getRelacao, $inicio, $fim, 'inicio_indisp asc');
        $out = Helper::parseDateToDatePTBR($entities, $this->camposDate, $this->camposDouble, $this->camposJson);
        $this->setDadosComboSearch($dados, $out, $this->ent);

        return $out;
    }

    /**
     * @create 18/05/2020
     * Metodo responsavel por salvar uma entidade
     */
    public function ws_save($dados) {
        $action = ( ((int) $dados['id' . $this->ent] > 0) ? 'Editar' : 'Inserir');
        Poderes::verify(self::$poderesGrupo, self::$poderesSubGrupo, $action);

        /*
          if (method_exists($this->object, 'setIdUsuario') && !Helper::compareString($this->ent, 'usuario')) {
          $dados['idUsuario'] = $this->condicao['idUsuario'];
          }

          if (method_exists($this->object, 'setIdEmpresa') && !Helper::compareString($this->ent, 'empresa')) {
          $dados['idEmpresa'] = $this->condicao['idEmpresa'];
          }
         */

        // Caso utilize o avatar no uploadfile
        //$dados['idUploadfile'] = Helper::jsonToArrayFromView($dados['Uploadfile'])['idUploadfile'];// para controle via avatar

        $dados['ignoreSetIdUser'] = true; // para nao setar o iduser la na hora de salvar
        $id = parent::save($dados);

        // Retornar o objeto persisitido
        $t = $this->ws_getById(['id' => $id]);
        $t['result'] = Translate::get('Salvo com sucesso');
        return $t;
    }

    /**
     * @create 18/05/2020
     * Metodo responsavel por remover uma entidade
     */
    public function ws_remove($dados) {
        Poderes::verify(self::$poderesGrupo, self::$poderesSubGrupo, 'remover');
        return parent::remove($dados['id']);
    }

    public function ws_getByUser($dados) {
        $dados['dataInicial'] = (($dados['dataInicial']) ? $dados['dataInicial'] : date('d/m/Y'));
        $dadosObrigatorios = [
            ['value' => $dados['idUsuario'], 'msg' => 'Usuário', 'type' => 'int'],
        ];
        // validação de campos obrigatórios
        $error = NsUtil\Helper::validarCamposObrigatorios($dadosObrigatorios);
        if (count($error) > 0) {
            return ['error' => $error];
        }

        return $this->ws_getAll($dados);
    }

}
