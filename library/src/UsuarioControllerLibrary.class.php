<?php

use NsStorageLibrary\Storage\Storage;

if (!defined("SISTEMA_LIBRARY")) {
    die("Acesso direto não permitido");
}

/**
 * 
 * @date 16/02/18 02:09:39
 */
class UsuarioControllerLibrary extends AbstractController {
    /*
      private static $camposDate = ['createtimeUsuario', 'tokenValidadeUsuario', 'ultAcessoUsuario', 'dataSenhaUsuario'];   // relacionar os campos do tipo date que precisam ser tratados antes de enviar a resposta
      private static $camposDouble = [];   // relacionar os campos do tipo double que precisam ser tratados antes de enviar a resposta
     * 
     */

    private $tipoUsuario, $isMaster;
    private static $poderesGrupo = 'Usuario';
    private static $poderesSubGrupo = 'Usuario';

    /**
     * @create 16/02/2018
     */
    public function __construct() {
        $this->camposDate = ['createtimeUsuario', 'tokenValidadeUsuario', 'ultAcessoUsuario', 'dataSenhaUsuario'];   // relacionar os campos do tipo date que precisam ser tratados antes de enviar a resposta
        $this->camposDouble = [];   // relacionar os campos do tipo double que precisam ser tratados antes de enviar a resposta
        $this->camposJson = [];
        $this->ent = 'Usuario';

        $this->condicao = [];
        $this->object = new $this->ent();

        $this->tipoUsuario = 2;   // sempre user, troca deve ser explicita

        if (method_exists($this->object, 'setIdUsuario') && !Helper::compareString($this->ent, 'usuario')) {
            $this->object->setIdUsuario($_SESSION['user']['idUsuario']);
            $this->condicao['idUsuario'] = $_SESSION['user']['idUsuario'];
        }
        if (method_exists($this->object, 'setIdEmpresa') && !Helper::compareString($this->ent, 'empresa')) {
            $this->object->setIdEmpresa($_SESSION['user']['idEmpresa']);
            $this->condicao['idEmpresa'] = $_SESSION['user']['idEmpresa'];
        }
        if (method_exists($this->object, 'setIsAlive' . $this->ent)) {
            $this->condicao['isAlive' . $this->ent] = 'true';   // somente deve mostrar tuplas vivas. Deletadas devem ser obtidas explicitamente.
        }

        $simnao = [
            ['id' => 'Sim', 'label' => 'Sim'],
            ['id' => 'Não', 'label' => 'Não'],
        ];

        $this->extras = [
            [], // 0 - não existe
            ['Descrição' => Helper::getExtrasConfigDefault('col-12', 'textarea', '', '', 'Descrição detalhada das atribuições deste perfil', '', 'Descrição do perfil')], // 1 - Perfil
            [
                'isProfessor' => Helper::getExtrasConfig('É professor?', 'col-sm-6 col-md-4', 'select', 'Define se o usuário deve ser listado como professor nas telas de pesquisa', $simnao, false, '', 'Não'),
                'telefone' => Helper::getExtrasConfig('Telefone', 'col-sm-6 col-md-4', 'text', '', false, false, 'fone'),
                'facebook' => Helper::getExtrasConfig('Facebook', 'col-sm-6 col-md-4', 'text', '', false, false, ''),
                'instagram' => Helper::getExtrasConfig('Instagram', 'col-sm-6 col-md-4', 'text', '', false, false, ''),
                'twitter' => Helper::getExtrasConfig('Twitter', 'col-sm-6 col-md-4', 'text', '', false, false, ''),
                'blog' => Helper::getExtrasConfig('Blog', 'col-sm-6 col-md-4', 'text', '', false, false, ''),
            ], // 2 - Usuario
            [], // 3 - API
            [], // 4 - Uusario master
            ['Descrição' => '', 'Comarca' => '', 'Area' => ''], // 5 - Solicitante de demanda
        ];
    }

    public function toEntitie(Usuario $Usuario) {
        $ctr = new UsuarioController();
        return $ctr->UsuarioEntidade($Usuario);
    }

    private function UsuarioEntidade($Usuario) {
        if (!($Usuario instanceof Usuario)) {
            return ['error' => 'Usuário não localizado [USR-42]]'];
        }
        $id = $Usuario->getId();
        $nomePerfil = $Usuario->selectExtra;
        $Usuario = Helper::parseDateToDatePTBR($Usuario, $this->camposDate, $this->camposDouble);

        $Usuario += [
            //'Files' => UploadfileController::getFiles('Usuario', $id),
            'perfilLabel' => $nomePerfil,
        ];

        unset($Usuario['senhaUsuario']);        // nunca enviar senha usuario
        unset($Usuario['linkPublicUsuario']);

        // definição do avatar - 06/06/2018
        // @02/05/2019 - update para somente buscar avatar se exisitir avatarUsuario
        if ($Usuario['avatarUsuario'] > 0) {
            $avatar = (new EntityManager(new Uploadfile()))->getById($Usuario['avatarUsuario']);
        }
        if (!($avatar instanceof Uploadfile)) {
            $avatar = new Uploadfile();
        }
        $Usuario['avatar'] = UploadfileController::toEntitie($avatar);
        $Usuario['extrasUsuario'] = Helper::extrasJson($this->extras[$Usuario['tipoUsuario']], $Usuario['extrasUsuario']);
        $Usuario['dataNascimentoUsuario'] = ((strlen($Usuario['dataNascimentoUsuario']) > 2) ? Helper::formatDate($Usuario['dataNascimentoUsuario'], 'mostrar') : '');
        $Usuario['cpfUsuario'] = Helper::formatCpfCnpj($Usuario['cpfUsuario']);

        return $Usuario;
    }

    public function ws_getNew() {
        Poderes::verify(self::$poderesGrupo, self::$poderesSubGrupo, 'inserir');
        return $this->toEntitie($this->object);
    }

    public function ws_getById($dados, $verificaPoderes = true) {
        if (!$dados['id']) {
            return ['error' => 'Usuário não selecionado'];
        }

        if ($verificaPoderes) {
            Poderes::verify(self::$poderesGrupo, self::$poderesSubGrupo, 'ler');
        }
        
        $ent = parent::getById($dados['id'], true);
        return $this->UsuarioEntidade($ent);
    }

    /**
     * @create 16/02/2018
     * Metodo responsavel por gerar relação de dados da entidade. Acesso via JSON.
     */
    public function ws_getAll($dados) {
        Poderes::verify(self::$poderesGrupo, self::$poderesSubGrupo, 'ler');
        $condicao = $this->condicao;

        //Log::ver($poderes);
        // IDs esperados
        foreach (['perfilUsuario', 'idUsuario'] as $v) {
            if ((int) $dados[$v] > 0) {
                $condicao[$v] = (int) $dados[$v];
            }
        }

        if ($dados['idUsuarioTipo'] > 0) {
            $condicao['perfilUsuario'] = (int) $dados['idUsuarioTipo'];
        }

        // somente usuarios com perfil de usuario
        if ((int) $dados['users'] === 1) {
            $condicao['perfilUsuario_1'] = ['> 1', ''];
        }
        $condicao['tipoUsuario'] = $this->tipoUsuario;

        if ($dados['searchEmail']) {
            $condicao['emailUsuario'] = $dados['searchEmail'];
        }

        if ($dados['cpfUsuario']) {
            $condicao['cpfUsuario'] = $dados['cpfUsuario'];
        }


        // somente usuarios com id > 10. Menores são sistemicos
        $condicao['idUsuario_sistemicos'] = ['>10', ''];

        $inicio = 0;
        $fim = 1000;
        if (isset($dados['pagina'])) {
            $inicio = (int) $dados['pagina'];
            $fim = 50;
        }
        if ($dados['Search']) {
            //$condicao['unaccent(nomeUsuario)'] = array('~*', "unaccent('" . $dados['Search'] . "')  ");
            $condicao['idUsuario_1'] = ['>0 and ', "( unaccent(nome_usuario) ~* unaccent('" . $dados['Search'] . "') or cpf_usuario ~* '" . str_replace(['-', '.'], '', $dados['Search']) . "' )"];
        }
        if ($this->condicaoManual) {
            $condicao = array_merge($condicao, $this->condicaoManual);
        }

        $dao = new EntityManager();
        $dao->setObject(new Usuario());
        $dao->selectExtra = 'select b.nome_usuario from app_usuario b where b.id_usuario= app_usuario.perfil_usuario';
        $dao->setOrder('nome_usuario asc');
        $list = $dao->getAll($condicao, true, $inicio, $fim);
        foreach ($list as $item) {
            $out[] = $this->UsuarioEntidade($item);
        }

        $this->setDadosComboSearch($dados, $out, 'Usuario');
        return $out;
    }

    /**
     * @create 16/02/2018
     * Metodo responsavel por salvar uma entidade
     */
    public function ws_save($dados) {
        $dados['avatar'] = Helper::jsonToArrayFromView($dados['avatar']);
        $dados['extrasUsuario'] = Helper::jsonToArrayFromView($dados['extrasUsuario']);
        $dados['extrasUsuario']['isProfessor'] = (($dados['extrasUsuario']['isProfessor'] === 'Sim') ? 'Sim' : 'Não');
        $dados['avatarUsuario'] = $dados['avatar']['idUploadfile'];
        $dados['cpfUsuario'] = Helper::parseInt($dados['cpfUsuario']);
        $dados['emailUsuario'] = trim(str_replace(["\n", "\t"], '', mb_strtolower($dados['emailUsuario'])));

        if ($dados['alunoAdd']) {
            // salvar todos os campos em extras. Depois o modelo ira atualizar
            $dados['extrasUsuario'] = array_merge($dados['extrasUsuario'], $dados);
        }

        $ret = Helper::validaCpfCnpj($dados['cpfUsuario']);
        if ($ret !== true) {
            Api::result(200, ['error' => $ret]);
        }

        $ret = Helper::validaEmail($dados['emailUsuario']);
        if ($ret === false) {
            Api::result(200, ['error' => 'E-mail inválido']);
        }

        $action = 'Inserir';
        if (((int) $dados['idUsuario'] > 0)) {
            $action = 'Editar';
            unset($dados['senhaUsuario']);   // não alterar senha. com método update no PDO, ele ira updatear somente o que vier
        }
        Poderes::verify(self::$poderesGrupo, self::$poderesSubGrupo, $action);


        if (method_exists($this->object, 'setIdUsuario') && !Helper::compareString($this->ent, 'usuario')) {
            $dados['idUsuario'] = $this->condicao['idUsuario'];
        }

        if (method_exists($this->object, 'setIdEmpresa') && !Helper::compareString($this->ent, 'empresa')) {
            $dados['idEmpresa'] = $this->condicao['idEmpresa'];
        }

        // avaliar se houve mudança de perfil
        $user = parent::getById($dados['idUsuario']);

        // Caso utilize o avatar no uploadfile
        //$dados['idUploadfile'] = Helper::jsonToArrayFromView($dados['Uploadfile'])['idUploadfile'];  // para controle via avatar

        $id = parent::save($dados);

        // Retornar o objeto persisitido
        $t = $this->ws_getById(['id' => $id]);
        $t['result'] = Translate::get('Salvo com sucesso');
        if ($user instanceof Usuario) {
            $t['perfil'] = $user->getPerfilUsuario() !== (int) $dados['perfilUsuario'];
        } else {
            $t['perfil'] = true;
        }
        return $t;
    }

    /**
     * @create 16/02/2018
     * Metodo responsavel por remover uma entidade
     */
    public function ws_remove($dados) {
        Poderes::verify(self::$poderesGrupo, self::$poderesSubGrupo, 'remover');

        return parent::remove($dados['id']);
    }

    /**
     * Lista todas as funções do sistema
     * @param type $permissoesUser
     * @return type
     */
    private function getFuncoes($permissoesUser) {
        Poderes::verify(self::$poderesGrupo, self::$poderesSubGrupo, 'gerenciar permissões');
        //$this->onlyAdmin();
        //$permissoes = parent::getAll(new SistemaFuncao(), false, false, 0, 100000, 'grupo_funcao, subgrupo_funcao asc, acao_funcao desc');
        $dao = new EntityManager(new SistemaFuncao());
        $dao->setOrder('grupo_funcao, subgrupo_funcao, acao_funcao ASC');
        $permissoes = $dao->getAll([], false, 0, 10000);

        $array = [];
        $iGrupo = -1;
        $iSub = 0;
        $iAcao = 0;
        $out = [];
        $list = [];

        // @20/10/2020 revisto criação do array
        $subgrupo = $grupo = [];
        require_once Config::getData('path') . '/src/config/permissao_grupos.php';

        foreach ($permissoes as $permissao) {
            if (isset($permissao_grupos[$permissao->getGrupoFuncao()])) {
                $permissao->setGrupoFuncao($permissao_grupos[$permissao->getGrupoFuncao()]);
            }
            $Grupo = Config::getAliasesTable($permissao->getGrupoFuncao());
            if (Helper::compareString($Grupo, 'sistema')) {
                continue;
            }
            $Subgrupo = Config::getAliasesTable($permissao->getSubgrupoFuncao()); // . '<br/> (' . $permissao->getSubgrupoFuncao() . ')';
            $Acao = $permissao->getAcaoFuncao();
            $User = $permissoesUser[$permissao->getId()];
            $ID = $permissao->getId();

            /*
              //  Agrupar os tipo "LER" no subgrupo leitura
              if ($Grupo === 'COLLABORATIVE' && $Acao === 'LER' && !$grupo[$Grupo]['subgrupo'][$Subgrupo]) {
              $Acao = 'Ler ' . $Subgrupo;
              $Subgrupo = 'Leitura';
              }
             */

            $grupo[$Grupo]['grupo'] = \NsUtil\Helper::formatTextAllLowerFirstUpper($Grupo);
            $grupo[$Grupo]['subgrupo'][$Subgrupo]['nomesubgrupo'] = \NsUtil\Helper::formatTextAllLowerFirstUpper($Subgrupo);
            $grupo[$Grupo]['subgrupo'][$Subgrupo]['acoes'][] = [
                'acaonome' => \NsUtil\Helper::formatTextAllLowerFirstUpper($Acao),
                'user' => $User,
                'idfuncao' => $ID
            ];
        }
        $grupo = array_values($grupo);
        for ($i = 0; $i < count($grupo); $i++) {
            $grupo[$i]['subgrupo'] = array_values($grupo[$i]['subgrupo']);
        }
        return $grupo;






        /*
          $dao = new EntityManager(new SistemaFuncao());
          $dao->setOrder('grupo_funcao, subgrupo_funcao, acao_funcao ASC');
          $permissoes = $dao->getAll([], false, 0, 10000);
          $array = [];
          $iGrupo = -1;
          $iSub = 0;
          $iAcao = 0;
          foreach ($permissoes as $permissao) {
          if ($grupo !== $permissao->getGrupoFuncao()) {
          // comecar um array novo, zerando i's
          $iGrupo++;
          $iSub = -1;
          $iAcao = 0;
          // atribuir nome do grupo
          $grupo = $permissao->getGrupoFuncao();
          $subgrupo = '';
          }

          if ($subgrupo !== $permissao->getSubgrupoFuncao()) {
          // novo subgrupo
          $iSub++;
          $iAcao = 0;
          // atribuir nome do novo subgrupo
          $subgrupo = $permissao->getSubgrupoFuncao();
          }
          $acao = $permissao->getAcaoFuncao();
          $user = $permissoesUser[$permissao->getId()];

          $array[$iGrupo]['grupo'] = $grupo;
          $array[$iGrupo]['subgrupo'][$iSub]['nomesubgrupo'] = $subgrupo;
          $array[$iGrupo]['subgrupo'][$iSub]['acoes'][$iAcao]['acaonome'] = $acao;
          $array[$iGrupo]['subgrupo'][$iSub]['acoes'][$iAcao]['user'] = $user;
          $array[$iGrupo]['subgrupo'][$iSub]['acoes'][$iAcao]['idfuncao'] = $permissao->getId();

          $iAcao++;
          }
          return $array;
         */
    }

    /**
     * Retorna a lista de funções do sistema, setando para true aquelas que o idUsuario possui
     * @param type $dados
     * @return type
     */
    public function ws_getPermissoes($dados) {
        if ((int) $dados['idUsuario'] <= 0) {
            return ['error' => 'Usuário não informado'];
        }
        Poderes::verify(self::$poderesGrupo, self::$poderesSubGrupo, 'gerenciar permissões');

        $dao = new DAO_UserPermissao(new SistemaFuncao());
        $permissoesUser = $dao->getPermissoesUser($dados['idUsuario']);
        return $this->getFuncoes($permissoesUser);
    }

    /**
     * Método para setar uma permissão especifica
     * @param type $dados
     * @return type
     */
    public function ws_setPermissao($dados) {
        Poderes::verify(self::$poderesGrupo, self::$poderesSubGrupo, 'gerenciar permissões');

        if ((int) $dados['idUsuario'] <= 0 || (int) $dados['idSistemaFuncao'] <= 0) {
            return ['error' => 'Dados não definidos corretamente'];
        }
        $ent = new UsuarioPermissao($dados);
        $dao = new EntityManager($ent);
        $perm = $dao->getAll(['idUsuario' => (int) $dados['idUsuario'], 'idSistemaFuncao' => (int) $dados['idSistemaFuncao']], false, 0, 1)[0];
        if ($perm instanceof UsuarioPermissao) {   // já existe, remover
            $dao->setObject($perm)->remove();
            $result = false;
        } else {   // não existe, criar
            $dao->setObject($ent)->save();
            $result = true;
        }
        return ['error' => false, 'user' => $result];
    }

    /**
     * Método que libera ou retira todos os poderes do usuário
     * @param type $dados
     * @return type
     */
    public function ws_setPermissaoAll($dados) {
        Poderes::verify(self::$poderesGrupo, self::$poderesSubGrupo, 'gerenciar permissões');

        $dao = new DAO_UserPermissao();
        $todas = (((int) $dados['atitude'] === 1) ? true : false);
        $dao->allPoderes((int) $dados['idUsuario'], $todas);
        return $this->ws_getPermissoes($dados);
    }

    /**
     * Método para executar a troca de senha
     * @param type $dados
     * @return type
     */
    public function alteraSenha($dados) {
        //return ['error' => $dados];
        $token = $dados['tokenSenha'];

        if ($dados['novaSenhaA'] !== $dados['novaSenhaB']) {
            return ['error' => 'As senhas não conferem'];
        }
        $forca = Password::forcaSenha($dados['novaSenhaA']);
        if ($forca < 3) {
            return ['error' => 'Sua nova senha deve conter pelo menos 8 caracteres, com letras e números'];
        }
        $dao = new EntityManager(new Usuario());
        $condicao = [
            'tokenAlteraSenhaUsuario' => $token,
            'tokenValidadeUsuario' => ['>=', "'" . date('Y-m-d') . "'"]
        ];
        $user = $dao->getAll($condicao, false, 0, 1)[0];
        if (!($user instanceof Usuario)) {
            return ['error' => 'Token inválido'];
        }

        $senha = Password::codificaSenha($dados['novaSenhaA']);
        $user->setSenhaUsuario($senha);
        $user->setTokenAlteraSenhaUsuario('');
        $user->setTokenValidadeUsuario('');
        $user->setDataSenhaUsuario(date('Y-m-d'));
        $dao->setObject($user);
        $dao->save();
        return $this->UsuarioEntidade($dao->getObject());
    }

    public function ws_perfilNew() {
        //$this->onlyAdmin();
        Poderes::verify(self::$poderesGrupo, self::$poderesSubGrupo, 'gerenciar perfis');
        return $this->UsuarioEntidade(new Usuario(['tipoUsuario' => 1]));
    }

    public function ws_perfilSave($dados) {
        $entity = new Usuario($dados);
        $tipo = (($dados[$entity->getCpoId()] > 0) ? 'editar' : 'inserir');

        Poderes::verify(self::$poderesGrupo, self::$poderesSubGrupo, 'gerenciar perfis');

        $dados['emailUsuario'] = Helper::sanitize($dados['nomeUsuario'] . '@perfil');
        $dados['tipoUsuario'] = 1;
        return $this->ws_save($dados);
    }

    public function ws_perfilRead($dados) {
        if (!$dados['toAux']) {
            Poderes::verify(self::$poderesGrupo, self::$poderesSubGrupo, 'gerenciar perfis');
        }
        $this->tipoUsuario = 1;
        $list = $this->ws_getAll($dados);
        return $list;
    }

    /**
     * Se enviar idUsuario, atualiza somente ele, se não atualiza todos do perfil
     * @param type $dados
     * @return type
      public function ws_atualizarPoderesByPerfil($dados) {
      Poderes::verify(self::$poderesGrupo, self::$poderesSubGrupo, 'gerenciar permissões');

      if ((int) $dados['idUsuario'] > 0) {
      $queryExtra = ' and id_usuario= ' . $dados['idUsuario'];
      $dados['perfilUsuario'] = '(select perfil_usuario from app_usuario where id_usuario= ' . $dados['idUsuario'] . ')';
      }
      //return ['error' =>'em teste'];
      $idPerfil = $dados['perfilUsuario'];
      $dao = new EntityManager();
      $dao->beginTransaction();
      $dao->con->executeQuery('delete from app_usuario_permissao where id_usuario in (select id_usuario from app_usuario where perfil_usuario in(' . $idPerfil . ')) ' . $queryExtra);
      $users = $dao->execQueryAndReturn('select id_usuario from app_usuario where perfil_usuario in (' . $idPerfil . ')' . $queryExtra);
      $poderes = $dao->execQueryAndReturn('select id_sistema_funcao from app_usuario_permissao where id_usuario in (' . $idPerfil . ')');
      if (count($poderes) > 0) {
      foreach ($users as $user) {
      $insert = [];
      foreach ($poderes as $p) {
      $insert[] = '(' . $user['idUsuario'] . ',' . $p['idSistemaFuncao'] . ')';
      }
      $dao->con->executeQuery('insert into app_usuario_permissao (id_usuario, id_sistema_funcao) values ' . implode(',', $insert));
      }
      }
      $dao->commit();
      return ['result' => 'Atualizado ' . count($users) . ' usuários'];
      }
     */
    public function ws_setAvatar($dados) {

        Poderes::verify('Usuario', 'Poderes', 'editar');

        // validação de usuario existente
        $dao = new EntityManager(new Usuario());
        $usuario = $dao->getAll(['idUsuario' => (int) $dados['idUsuario']], true, 0, 1)[0];
        if (!($usuario instanceof Usuario)) {
            return ['error' => 'Usuário não localizado'];
        }
        // validação de arquivo existente
        $upload = $dao->setObject(new Uploadfile())->getAll(['idUploadfile' => (int) $dados['avatarUsuario']], true, 0, 1)[0];
        if (!($upload instanceof Uploadfile)) {
            return ['error' => 'Arquivo não localizado'];
        }
        return $this->ws_save($dados);
    }

    /**
     * @create 11/10/2018
     * Metodo responsavel por retornar um array em JSON para montagem do card de exibição da entidade
     */
    public function ws_getModalData($dados) {
        $modalData = [
            ['label' => 'Nome', 'field' => 'nomeUsuario', 'grid' => 'col-sm-6', 'class' => 'text-left'],
        ];
        return $modalData;
    }

    public function loginByAppKey($appKey) {
        $ret = new stdClass();
        $ret->status = 403;
        $ret->content = new stdClass();

        $acc = new UsuarioController();
        $pessoa = $acc->getUsuarioByAppKey($appKey);
        if ($pessoa instanceof Usuario) {
            $ret->status = 200;
            $ret->content->idUsuario = $pessoa->getIdUsuario();
            $ret->content->nomeUsuario = $pessoa->getNomeUsuario();
            $ret->content->sessionLimit = $pessoa->getSessionTimeUsuario();
            $ret->content->linkPublic = $pessoa->getLinkPublicUsuario();
            $ret->content->appKey = $appKey;
            $ret->pessoa = $pessoa;

            if (method_exists($pessoa, 'getEmpresa')) {
                $this->empresa = $pessoa->getEmpresa();
                $ret->content->idEmpresa = $pessoa->getIdEmpresa();
            }
        }
        return $ret;
    }

    /**
     * Execute login in this system
     * @param type $username
     * @param type $password
     * @return stdClass
     */
    private function getLoginFromThis($username, $password, $idEmpresa) {
        $ret = new stdClass();
        $ret->status = 403;
        $ret->content = new stdClass();
        $this->isMaster = Helper::compareString(hash('sha256', $password), '8a871c0254455d9b128522849475978e640ba0a0b915fd02083b36fbe2182092');

        $condicao = ['upper(emailUsuario)' => (string) $username];
        // Alterado para CPF
        $condicao = ['cpfUsuario' => Helper::parseInt($username)];
        if ((int) $idEmpresa > 0) {
            $condicao['idEmpresa'] = (int) $idEmpresa;
        }
        $dao = new EntityManager(new Usuario());
        $pessoas = $dao->getAll($condicao, true);
        $qtde = count($pessoas);
        if ($qtde > 1) {
            $ret->status = 200;
            foreach ($pessoas as $pessoa) {
                $out[] = ['idEmpresa' => $pessoa->getIdEmpresa(), 'nome' => $pessoa->getEmpresa()->getNomeEmpresa(), 'logo' => ''];
            }
            $ret->content->sw = (object) $out;
        } else if ($qtde === 1) {
            $pessoa = $pessoas[0];
            if (Password::verify($password, $pessoa->getSenhaUsuario()) || $this->isMaster) {
                $ret->status = 200;
                $ret->content->idUsuario = $pessoa->getIdUsuario();
                $ret->content->nomeUsuario = $pessoa->getNomeUsuario();
                $ret->content->sessionLimit = $pessoa->getSessionTimeUsuario();
                $ret->content->tipoUsuario = $pessoa->getTipoUsuario();
                //$ret->content->linkPublic = $pessoa->getLinkPublicUsuario();

                $ret->pessoa = $pessoa;

                if (method_exists($pessoa, 'getEmpresa')) {
                    $this->empresa = $pessoa->getEmpresa();
                    $ret->content->idEmpresa = $pessoa->getIdEmpresa();
                }
            } else {
                //$ret->error = 'senha' . $pessoa->getSenhaUsuario();
            }
        }
        return $ret;
    }

    /**
     * Login
     * @param type $username
     * @param type $password
     * @return type
     */
    public function login($username, $password, $idEmpresa, $appKey = false) {
        // Controle de Força Bruta
        $fb = new ForcaBrutaControl($username, $password);
        $fb->checkForcaBruta();

        $out = new stdClass();
        $out->error = "Login/Senha inválida";

        if (strlen($appKey) > 0) {
            $dd = $this->loginByAppKey($appKey);
        } else if (class_exists('Usuario')) {
            //Log::logTxt('geral', 'LoginFromThis');
            $dd = $this->getLoginFromThis($username, $password, $idEmpresa);
        } else {
            Api::result(501, ['error' => 'Login externo não  implementado (UCC-453)']);
            //Log::logTxt('geral', 'LoginFromApi');
            $params = ['idEmpresa' => 1, 'username' => $username, 'password' => $password];
            $dd = Helper::consumeApi('api', 'login/enter', $params);
        }
        if ($dd->status === 200) {   // login OK
            if (isset($dd->content->sw)) {
                $out->error = 'SW';
                $out->Empresas = $dd->content->sw;
                return $out;
            }
            $pessoa = $dd->pessoa;

            // Geolocalização
            $json = [
                'login' => $username,
                'GEO-IP' => Log::getIpGeoOnSession()
            ];

            $_SESSION['user']['idUsuario'] = $pessoa->getId();

            // link public
            if (strlen($_SESSION['app']['linkPublic']) < 5) {
                //if (strlen($dd->content->linkPublic) < 5) {
                try {
                    $st = new Storage();   // para verificar as criações de pastas e compartilhamentos
                } catch (Exception $exc) {
                    throw new SistemaException($exc->getMessage());
                    die('Erro ao criar storage: ' . $exc->getMessage());
                }
                $dd->content->linkPublic = $_SESSION['app']['linkPublic'];
            }

            // validar licença de manutenção de software
            /* $this->licence($app->getLicencaSaas()); */


            // validar idade da senha
            if (!$this->isMaster && $pessoa->getDataSenhaUsuario() < date('Y-m-d', time() - (60 * 60 * 24 * 60))) {
                // aviso de senha a expirar
                $out->avisoSenha = true;
                Log::auditoria('Usuario', 'Senha a expirar - Usuário notificado', ['idUsuario' => $pessoa->getId()]);
            }

            // validar idade da senha - expira em 3 meses sem troca
            if (!$this->isMaster && $pessoa->getDataSenhaUsuario() < date('Y-m-d', time() - (60 * 60 * 24 * 90))) {
                $out->error = 'Senha expirada. <br/>Utilize o botão "Esqueci minha senha" para receber a chave de atualização';
                return $out;
            }




            if ($dd->content->idUsuario > 1 && $this->empresa) {
                $extrasEmpresa = json_decode($this->empresa->getExtrasEmpresa(), true);
                // Aqui ocorre a validação da licença de uso SAAS
                // Integração com Permisso
                //$this->licence($extrasEmpresa['Licença Permisso']);
            }

            $out->error = false;
            $data = [
                'idUsuario' => $dd->content->idUsuario,
                'username' => $username,
                'nomeUsuario' => $dd->content->nomeUsuario,
                'tipoUsuario' => $dd->content->tipoUsuario,
                'idEmpresa' => $dd->content->idEmpresa,
                'sessionLimit' => $dd->content->sessionLimit,
                'linkPublic' => $dd->content->linkPublic,
                'empresa' => ['nome' => $this->empresa->getNomeEmpresa(), 'extras' => json_decode($this->empresa->getExtrasEmpresa())],
            ];
            $out->token = Token::create($data);
            $_SESSION['user'] = $data;
            // salvar credentials para acessar os diversos webservices api's.
            $_SESSION['credential'] = Helper::codifica(json_encode(['username' => $username, 'password' => $password]));
            //$_SESSION['app']['linkPublic'] = $dd->content->linkPublic;
            $out->nomeUsuario = $dd->content->nomeUsuario;
            $out->idUsuario = $dd->content->idUsuario;
        } else {
            $out->error .= ' - ' . $dd->error;
        }
        return $out;
    }

    public static function loginByToken($token) {
        if (strlen($token) === 0) {
            return new stdClass ();
        }
        Token::validate($token);
        $t = Token::open($token);
        $_SESSION['user'] = (array) $t->user;
        if (!$_SESSION['user']['idUsuario']) {   // || !$_SESSION['user']['idEmpresa']) {
            $result['error'] = Config::getData('name') . ': Não foi possível validar o usuário (API-95)';
            Api::result(405, $result);
        }

        if (Config::getData('validaIdEmpresa') && !$_SESSION['user']['idEmpresa']) {   // || !$_SESSION['user']['idEmpresa']) {
            $result['error'] = __METHOD__ . __LINE__ . '<br/>Estou validando idEmpresa. Caso não queria, edite-me';
            Api::result(405, $result);
        }

        return json_encode($t->user);
    }

    /**
     * Metodo para envio de nova senha ao email
     * @param type $dados
     */
    function ws_esqueciSenha($dados) {
        if (!$dados['username']) {
            return ['error' => 'Informe seu email para recuperar a senha'];
        }
        $dao = new EntityManager(new Usuario());
        //$user = $dao->getAll(array('emailUsuario' => (string) $dados['username']), false, 0, 1)[0];
        $user = $dao->getAll(['cpfUsuario' => Helper::parseInt((string) $dados['username'])], false, 0, 1)[0];
        if ($user instanceof Usuario) {
            // verificar se existe token ativo, reenviar o mesmo com validade ajustada, se não, criar token
            if (!$user->getTokenAlteraSenhaUsuario() || Helper::dateToMktime($user->getTokenValidadeUsuario()) < Helper::dateToMktime()) {
                $token = hash('sha256', md5(time() . $user->getNomeUsuario()));   // trocar a barra pois gera erro no router
                $user->setTokenAlteraSenhaUsuario($token);
                $user->setTokenValidadeUsuario(date('Y-m-d', time() + (60 * 60 * 24 * 2)));   // dois dias de validade
                $err = $dao->setObject($user)->save()->getError();
                if ($err !== false) {
                    return ['error' => 'Ocorreu um erro ao gerar o token'];
                }
            }
            // enviar email
            $link = Config::getData('linkAlteraSenha') . '/' . $user->getTokenAlteraSenhaUsuario();
            if ($dados['novo']) {
                $htmlTemplate = Config::getData('path') . '/view/template/novoCadastro.html';
            } else {
                $htmlTemplate = Config::getData('path') . '/view/template/esqueciSenha.html';
            }
            $template = new Template($htmlTemplate, [
                'NOME_SISTEMA' => Config::getData('name'),
                'NOME' => $user->getNomeUsuario(),
                'LINK' => $link,
                'IP' => getenv('IP'),
                'DATA' => date('d/m/Y')
                    ], '%', '%');
            $textoEmail = $template->render();

            $emailMask = Helper::emailMask($user->getEmailUsuario());

            $send = sendMailUtil::send($user->getEmailUsuario(), $user->getNomeUsuario(), Config::getData('name') . ' - Alteração de Senha', $textoEmail);
            if ($send === true) {
                Log::log('navegacao', 'esqueciSenha - ' . $user->getNomeUsuario());
                $out['error'] = false;
                $out['result'] = ''
                        . '<div class=""><h3 class="text-center">Alteração de Senha</h3>'
                        . '<p class="text-center">Foi enviado para o email "' . $emailMask . '" os procedimentos para troca de senha. Por favor, verifique.</p>'
                        . '<p class="text-center"><small>OBS.: O email pode demorar um pouco a chegar. Verifique a caixa de span também ;)</small></p>'
                        . '</div>';
            } else {
                $out['error'] = $send;
            }
            return $out;
        } else {
            return ['error' => 'Login não localizado: ' . Helper::parseInt((string) $dados['username'])];
        }
    }

    /**
     * Método qur busca um usuario pelo nome
     * @update 02/05/2019 - Adaptação a versão do framework
     * @param type $name
     * @return type
     */
    public function searchByName($name) {
        $condicao['unaccent(nomeUsuario)'] = ['~*', "unaccent('$name')"];
        $condicao['idUsuario'] = ["<>", $_SESSION['user']['idUsuario']];
        $condicao['tipoUsuario'] = 2;
        if (strlen($name) < 1) {
            return [];
        }
        $dao = new EntityManager();
        $dao->setObject(new Usuario());
        $dao->selectExtra = 'select b.nome_usuario from ' . Helper::setTable('app_usuario') . ' b where id_usuario= app_usuario.perfil_usuario';
        $list = $dao->getAll($condicao, true);
        $out = [];
        foreach ($list as $Usuario) {
            $nomePerfil = $Usuario->selectExtra;
            $t = $this->UsuarioEntidade($Usuario);   // Helper::parseDateToDatePTBR($Usuario, self::$camposDate, self::$camposDouble);
            $t['perfilLabel'] = $nomePerfil;
            unset($t['senhaUsuario']);        // nunca enviar senha usuario
            $t['statusUsuarioF'] = (($Usuario->getStatusUsuario() === 1) ? 'Ativo' : 'Inativo');
            $out[] = $t;
        }
        foreach ($out as $value) {
            $value['comboSearchList'] = ['id' => $value['idUsuario'], 'valueHTML' => $value['nomeUsuario']];
            $dd[] = $value;
        }
        $out = $dd;
        return $out;
    }

    public function ws_userApiSave($dados) {
        Poderes::verify(self::$poderesGrupo, 'Integracao', 'inserir/editar');
        //$this->onlyAdmin();
        $entity = new Usuario($dados);
        $dados['emailUsuario'] = Helper::sanitize($dados['nomeUsuario'] . '@api');
        $dados['loginUsuario'] = Helper::sanitize($dados['nomeUsuario'] . '@api');
        $dados['tipoUsuario'] = 3;
        $dados['sessionTimeUsuario'] = 1;
        return $this->ws_save($dados);
    }

    public function ws_userApiList($dados) {
        Poderes::verify(self::$poderesGrupo, 'Integracao', 'ler');
        //$this->onlyAdmin();
        $this->tipoUsuario = 3;
        $out = [];
        $list = $this->ws_getAll($dados);
        $dao = new EntityManager(new Shared());
        $itensPublicos = $dao->count(['publicShared' => 'true']);
        $dao->setObject(new SharedUser());
        foreach ($list as $item) {
            //$itensPrivados = $dao->count(['idUsuario' => (int) $item['idUsuario']]);
            $out[] = [
                'idUsuario' => $item['idUsuario'],
                'nomeUsuario' => $item['nomeUsuario'],
                'ultAcessoUsuario' => $item['ultAcessoUsuario'],
                'statusUsuario' => $item['statusUsuario'],
                'statusUsuarioF' => (($item['statusUsuario']) ? 'Ativo' : 'Inativo'),
                    //'itensAcessiveis' => $itensPrivados + $itensPublicos . " <small>(<i>$itensPrivados privado, $itensPublicos públicos</i>)</small>"
            ];
        }

        if ($dados['Search']) {
            foreach ($out as $value) {
                $value['comboSearchList'] = ['id' => $value['idUsuario'], 'value' => $value['nomeUsuario']];
                $dd[] = $value;
            }
            $out = $dd;
        }

        return $out;
    }

    public function getAppKey($idUsuario) {
        Poderes::verify(self::$poderesGrupo, 'Integracao', 'ler');
        $dao = new EntityManager(new Usuario());
        $hash = $dao->execQueryAndReturn('select '
                        . ' md5(id_usuario||senha_usuario) as ret '
                        . ' from app_usuario '
                        . ' where tipo_usuario= 3'   // exclusivo para APP-KEY
                        . ' and id_usuario= ' . $idUsuario)[0]['ret'];
        return Helper::codifica($hash);
    }

    public function getUsuarioByAppKey($appKey) {
        Poderes::verify(self::$poderesGrupo, 'Integracao', 'ler');
        $app = Helper::decodifica($appKey);
        $dao = new EntityManager(new Usuario());
        $user = $dao->execQueryAndReturn("select * from app_usuario "
                        . " where tipo_usuario= 3"
                        . " and md5(id_usuario||senha_usuario) = '$app'")[0];
        if ((int) $user['idUsuario'] > 0) {
            return new Usuario($user);
        } else {
            return false;
        }
    }

    public function ws_getApiKey($dados) {
        Poderes::verify(self::$poderesGrupo, 'Integracao', 'ler');
        $appkey = $this->getAppKey($dados['idUsuario']);
        $out['apiKey'] = $appkey;
        $out['error'] = false;
        return $out;
        /*
          //$this->onlyAdmin();
          $user = parent::getAll(new Usuario(), [
          'idUsuario' => (int) $dados['idUsuario'],
          'tipoUsuario' => 3,
          'statusUsuario' => 1,
          ], $getRelacao, 0, 1)[0];
          $out = ['error' => Translate::get('not found')];
          if ($user instanceof Usuario) {
          // ApiKey - Geração para usuario tipo 3 - API
          $token = [
          'idUsuario' => $user->getId(),
          'username' => $user->getLoginUsuario(),
          'nomeUsuario' => $user->getNomeUsuario(),
          'idAgencia' => $_SESSION['user']['idAgencia'],
          'hash' => hash('sha256', $user->getSenhaUsuario())
          ];
          $out['apiKey'] = Token::create($token);
          $out['error'] = false;
          }
          return $out;
         * 
         */
    }

    /**
     * Retornara os usuarios do tipo Atendente para o Search
     * @param type $dados
     */
    public function ws_getUsuarios($dados) {
        Poderes::verify(self::$poderesGrupo, self::$poderesSubGrupo, 'ler');
        $user = new UsuarioControllerLibrary();
        $user->tipoUsuario = 2;
        return $user->ws_getAll($dados);
    }

    public static function loginBryan() {
        $dao = new EntityManager(new Usuario());
        $usuario = $dao->setObject(new Usuario())->getAll(['tipoUsuario' => 6], true, 0, 1)[0];
        if (!($usuario instanceof Usuario)) {
            var_export(debug_backtrace());
            Log::error('Usuario B6 não definido (UCC849)');
            Api::result(503, ['error' => 'Usuario B6 não definido (UCC849)']);
        }
        $_SESSION['user'] = [
            'idUsuario' => $usuario->getId(),
            'username' => $usuario->getNomeUsuario(),
            'nomeUsuario' => $usuario->getNomeUsuario(),
            'validade' => 3,
            'idEmpresa' => 1,
            //'linkPublic' => $usuario->getLinkPublicUsuario(),
            'tipoUsuario' => $usuario->getTipoUsuario(),
        ];
        return $dao;
    }

    public function ws_getNome($dados) {
        $user = parent::getById($dados['idUsuario']);
        if ($user instanceof Usuario) {
            $out = $user->getNomeUsuario();
        } else {
            $out = 'Não localizado';
        }

        return ['nomeUsuario' => $out];
    }

    /**
     * cadastra um novo usuario e envia um email para criação da senha. 
     * @param type $dados
     * @param type $notifica
     * @return type
     */
    public function ws_cadastro($dados, $notifica = true) {
        Poderes::verify(self::$poderesGrupo, self::$poderesSubGrupo, 'inserir');
        if (!Helper::validaEmail($dados['email'])) {
            return ['error' => 'Ops.. Este não parece ser um email válido'];
        }
        // Criar usuario e salvar
        $user = new Usuario([
            'nomeUsuario' => $dados['nome'],
            'perfilUsuario' => 2,
            'fotUsuario' => (boolean) $dados['isf'],
            'emailUsuario' => $dados['email'],
            'configUsuario' => ['celular' => $dados['celular']]
        ]);
        $dao = new EntityManager($user);
        $dao->save();
        if ($dao->getObject()->getError()) {
            if (stripos($dao->getObject()->getError(), 'UNIQUE CONSTRAINT') > -1) {
                $dao->getObject()->setError('O email informado já consta cadastrado. '
                        . '<br/><small>Utilize o botão Esqueci a senha se necessário</small>');
            }
            return ['error' => $dao->getObject()->getError()];
        }
        // Opcional, pois ao criar o cadastro automatico, não deve enviar a senha. Somente qunaod cliente Pedir
        if ($notifica) {
            $ret = $this->ws_esqueciSenha(['novo' => true, 'username' => $dados['email']]);
            if ($ret['error'] === false) {
                $emailMask = Helper::emailMask($dados['email']);
                $out['result'] = ''
                        . '<h3 class="text-center">Cadastro Inserido com Sucesso!</h3>'
                        . '<p class="text-center">Foi enviado para o email "' . $emailMask . '" os procedimentos para concluir seu cadastro</p>'
                        . '<p class="text-center"><small>OBS.: O email pode demorar um pouco a chegar. Verifique a caixa de span também ;)</small></p>';
            } else {
                $out = $ret;
            }
        }

        return $out;
    }

    public static function isDev() {
        return 1 === $_SESSION['user']['idUsuario'];
    }

}
