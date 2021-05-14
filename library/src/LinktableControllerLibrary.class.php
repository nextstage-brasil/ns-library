<?php

if (!defined("SISTEMA_LIBRARY")) {
    die("Acesso direto não permitido");
}

/**
 * 
 * @date 30/05/18 04:33:31
 */
class LinktableControllerLibrary extends AbstractController {

    private $dao, $relacao;
    public static $vinculos = ['escola', 'empresa', 'processo', 'pessoa', 'fato', 'comunidade', 'federacao', 'clube', 'municipio'];

    // retirei enquadramento pois estava contando nos vinculospublic static $vinculos = ['escola', 'empresa', 'processo', 'pessoa', 'fato', 'comunidade', 'federacao', 'clube', 'municipio', 'enquadramento'];

    /**
     * @create 30/05/2018
     */
    public function __construct() {
        $this->dao = new EntityManager();

        $this->camposDate = ['createtimeLinktable'];      // relacionar os campos do tipo date que precisam ser tratados antes de enviar a resposta
        $this->camposDouble = [];      // relacionar os campos do tipo double que precisam ser tratados antes de enviar a resposta
    }

    /**
     * @date 13/02/2019
     * Método para atender diversas chamadas de linktable numa unica requisição
     * @param type $dados
     */
    public function ws_carga($dados) {

        //@rever removido pois nao vi sentido Poderes::verify('Sistema', 'linktable', 'listar');
        $time = new Eficiencia(__METHOD__);
        $time->setLimits(1, 10);

        /*
          $dados['entidade'] = 'pessoa';
          $dados['id'] = 325;
         * 
         */
        $out = [];
        foreach (self::$vinculos as $item) {
            $t = [];
            $t['relacao'] = $dados['entidade'] . '|' . $item;
            $t['entidade'] = ucwords($item);
            $t['div'] = 'vinculo' . ucwords($item);
            $t['title'] = "{{'$item' | tr}}"; // 2019-08-29: renderizado na view Config::getData('titlePagesAliases', $item);

            if ($item === 'pessoa') {
                //return __METHOD__.__LINE__;
            }

            // somente para contar o total de registros
            $t['count'] = $this->ws_getAll([
                'relacaoLinktable' => trim($dados['entidade']) . '|' . $item,
                'idLeftLinktable' => $dados['id'],
                'count' => true
                    ], false);

            $t['dados'] = $this->ws_getAll([
                'relacaoLinktable' => trim($dados['entidade']) . '|' . $item,
                'idLeftLinktable' => $dados['id']
                    ], false);
            $l = [];

            foreach ($t['dados'] as $v) {
                $l[] = @$v['nome' . ucwords($item)];
            }
            $t['string'] = implode(', ', $l);

            //$t['xabagaia'] = AppController::xabagaia($item, $this->dao, false);



            $out[] = $t;
        }

        $time->end();
        return $out;
    }

    public static function toEntitie(Linktable $Linktable) {
        $ctr = new LinktableController();
        return $ctr->LinktableEntidade($Linktable);
    }

    private function LinktableEntidade(Linktable $Linktable) {
        //$dao = new EntityManager();
        //$id = $Linktable->getId();
        $out = Helper::parseDateToDatePTBR($Linktable, $this->camposDate, $this->camposDouble);

        // extras
        $out['extraLinktable'] = Helper::extrasJson($this->getExtras($Linktable), $out['extraLinktable']);

        $out['referencia'] = 'TESTE DE REFRENCIA'; // $out['nome'];

        /*
          $out += [
          'Files' => UploadfileController::getFiles(['entidade' => 'Linktable', 'valorid' => $id], $dao),
          ];
         * 
         */

        return $out;
    }

## Metodos padrão para WebService (ws)

    /**
     * @create 30/05/2018
     * Método responsavel por devolver uma entidade nova e vazia
     */
    public function ws_getNew() {
        $out = $this->LinktableEntidade(new Linktable());
        $out['idLinktable'] = 0;
    }

    /**
     * @create 30/05/2018
     * Método responsavel por devolver uma entidade nova e vazia
     */
    public function ws_getById($dados, $registraLog = true) {

        //$this->getKeyRelacao($dados);
        //Poderes::verify($dados['relacaoLeft'], 'Vinculos', 'listar ' . Config::getData('titlePagesAliases', $dados['relacaoRight']));
        Poderes::verify('LOGOS', 'linktable', 'listar');

        $dao = new EntityManager(new Linktable());
        $Linktable = $dao->getById($dados['idLinktable']);
        if ($Linktable instanceof Linktable) {
            if ($registraLog && strlen($Linktable->getExtraLinktable()) > 0) {

                $rel = explode("|", $Linktable->getLtRel()->getNomeLtRel());
                $rel[0] = Config::getEntidadeName($rel[0]);
                $rel[1] = Config::getEntidadeName($rel[1]);
                $entA = new $rel[0]();
                $entB = new $rel[1]();
                $left = $dao->setObject($entA)->getById($Linktable->getIdLeftLinktable());
                $right = $dao->setObject($entB)->getById($Linktable->getIdRightLinktable());
                $fnA = 'getNome' . $rel[0];
                $fnB = 'getNome' . $rel[1];
                if (method_exists($entA, $fnA)) {
                    $nomeA = $left->$fnA();
                }
                if (method_exists($entB, $fnB)) {
                    $nomeB = $right->$fnB();
                }


                // log
                $descricao = "Relação entre "
                        . Config::getAliasesTable($rel[0])
                        . " ($nomeA) e "
                        . Config::getAliasesTable($rel[1])
                        . "($nomeB)";
                //$json = TlController::getJsonEntidade('Linktable', $Linktable->getId(), $descricao, $link, $icone);
                //TlController::add($rel[0], $Linktable->getIdLeftLinktable(), 'Leitura detalhada de vínculo', $json);
            }

            $Linktable->setError(false);
            return $this->LinktableEntidade($Linktable);
        } else {
            return ['error' => 'Vinculo não localizado'];
        }
    }

    /**
     * @create 30/05/2018
     * Metodo responsavel por gerar relação de dados da entidade. Acesso via JSON.
     */
    public function ws_getAll($dados, $verificaPoderes = false) {

        //$eficiencia = new Eficiencia(__METHOD__ . $dados['relacaoLinktable'], true);

        $this->getKeyRelacao($dados);

        $this->dao->setObject(new App());
        //Poderes::verify($dados['relacaoLeft'], 'Vinculos', 'listar ' . Config::getData('titlePagesAliases', $dados['relacaoRight']));
        if ($verificaPoderes) {      // pode ser anulada pois foi verificada antes
            Poderes::verify('LOGOS', 'linktable', 'listar');
        }

        $relacao = $this->relacao->nome;
        $idRelacao = $this->relacao->id;


        if ($relacao === '' || !$relacao) {
            return ['error' => 'Relação não informada - ' . $relacao];
        }


        $r = explode('|', $relacao);
        // caso exista idLeft, significa que devo listar as entidades a direita
        $entidadeRelacionada = (($dados['idLeftLinktable'] > 0) ? $r[1] : $r[0]); // entidade para efetuar o search.
        $entidadeOrigem = (($dados['idLeftLinktable'] > 0) ? $r[0] : $r[1]); // entidade que esta pedindo
        $campoIdSelect = (($dados['idLeftLinktable'] > 0) ? 'id_right_linktable' : 'id_left_linktable');
        $campoIdWhere = (($dados['idLeftLinktable'] > 0) ? 'id_left_linktable' : 'id_right_linktable');
        $valorIdWhere = (($dados['idLeftLinktable'] > 0) ? $dados['idLeftLinktable'] : $dados['idRightLinktable']);

        // dados obrigatórios para continuar
        if ($relacao === '' || !$relacao) {
            return ['error' => 'Relação não informada - ' . $relacao];
        }

        //return ['error' => $entidadeRelacionada];

        if (!$entidadeRelacionada || $entidadeRelacionada === '') {
            return ['error' => 'Relação não identificada - ' . $entidadeRelacionada];
        }

        if ($dados['idLeftLinktable'] <= 0 && $dados['idRightLinktable'] <= 0) {      // um dos dois precisa vir
            return ['error' => 'ID Relação não informada ' . $relacao];
        }

        // Selecionar na entidade relacionada os dados. Buscar em Controller pois devem vir como entidades
        $entidadeRelacionadaCamel = ucwords(Helper::name2CamelCase(mb_strtolower($entidadeRelacionada)));
        $entity = new $entidadeRelacionadaCamel();

        // Relacionamento entre entidades do mesmo nome. Devo buscar todas as relações, de a para b e de b para a, tanto faz.
        if (Helper::compareString($r[0], $r[1])) {
            $cpoId = 'id_' . mb_strtolower($r[0]);


            /*
              $query = "select id_left_linktable
              from " . Helper::setTable('app_linktable')
              . " where id_lt_rel = $idRelacao and (id_left_linktable= $valorIdWhere or id_right_linktable= $valorIdWhere) and id_left_linktable <> $valorIdWhere)"
              . " or "
              . "$cpoId in (select id_right_linktable from " . Helper::setTable('app_linktable') . " where id_lt_rel = $idRelacao and (id_left_linktable= $valorIdWhere or id_right_linktable= $valorIdWhere) and id_right_linktable <> $valorIdWhere";
             */

            // @2019-06-19 - query para obtenção de autorelacionamentos
            $query = "
                select t.$cpoId from (
                    select $cpoId from " . Helper::setTable($entity->getTable()) . "
                    where
                        -- relacionamento left
                        $cpoId in(
                                select id_left_linktable 
                                from " . Helper::setTable('app_linktable') . "
                                where 
                                    id_lt_rel = $idRelacao 
                                    and (id_left_linktable= $valorIdWhere or id_right_linktable= $valorIdWhere) and id_left_linktable <> $valorIdWhere
                        )
                        --- relacionaemnto right
                        or $cpoId in(
                                select id_right_linktable 
                                from " . Helper::setTable('app_linktable') . " 
                                where 
                                    id_lt_rel = $idRelacao 
                                    and (id_left_linktable= $valorIdWhere or id_right_linktable= $valorIdWhere) and id_right_linktable <> $valorIdWhere
                        ) 
                ) t
            ";




            $this->dao->selectExtra = "select row_to_json(d) from ("
                    . "select id_linktable, extra_linktable "
                    . "from " . Helper::setTable("app_linktable")
                    . " where id_lt_rel = $idRelacao and "
                    . " (id_right_linktable= $valorIdWhere or id_right_linktable= " . Helper::setTable($entity->getTable()) . "." . Helper::reverteName2CamelCase($entity->getCpoId()) . ")
                        and 
                        (id_left_linktable= $valorIdWhere or id_left_linktable= " . Helper::setTable($entity->getTable()) . "." . Helper::reverteName2CamelCase($entity->getCpoId()) . ")"
                    . ") d
                ";
        } else {      // relacionamento demais
            $query = "select "
                    . $campoIdSelect
                    . " from " . Helper::setTable('app_linktable')
                    //. " where relacao_linktable= '$relacao' "
                    . " where id_lt_rel = $idRelacao "
                    . " and $campoIdWhere= $valorIdWhere";

            $this->dao->selectExtra = "select row_to_json(d) from ( "
                    . "select id_linktable, extra_linktable "
                    . "from " . Helper::setTable('app_linktable')
                    //. " where relacao_linktable= '$relacao' "
                    . " where id_lt_rel = $idRelacao"
                    . " and $campoIdWhere= $valorIdWhere "
                    . " and $campoIdSelect= " . Helper::setTable($entity->getTable()) . "." . Helper::reverteName2CamelCase($entity->getCpoId())
                    . ") d";
        }





        $condicao[$entity->getCpoId()] = ['in', "($query)"];

        parent::setSearch($entidadeRelacionadaCamel, $condicao, $dados);

        // Logar os termos utilizados para search em vinculos
        if (strlen($dados['Search']) > 1) {
            $ent = Config::getAliasesTable($entidadeRelacionada);
            $text = $dados['Search'];
            $titulo = "Vinculo: $ent, Texto: $text";
            //TlController::add($entidadeOrigem, $valorIdWhere, 'Search em Vínculos', //TlController::getJsonEntidade($entidadeRelacionada, '', $titulo));
        }


        $this->dao->setObject($entity)->setOrder('nome_' . mb_strtolower($entidadeRelacionada));

        // @2019-07-10 regra para visualizar somente pessoas confomre permissão de leitura
        if ($entity instanceof Pessoa) {
            $condicao['idPessoaTipo'] = PessoaController::setTipoPessoaByPoderes();
        }
        // @2019-10-28 regra para visualizar somente fatos confomre permissão de leitura
        if ($entity instanceof Fato) {
            FatoController::setTipoByPoderes($condicao);
        }

        // somente registros vivos
        //Log::logTxt('debug', "Teste ". $entidadeRelacionadaCamel);
        if (method_exists($entity, 'isAlive' . $entidadeRelacionadaCamel)) {
            $condicao['isAlive' . $entidadeRelacionadaCamel] = 'true';
        }



        if ($dados['count']) {
            return (int) $this->dao->count($condicao);
        }

        // @07/05/2019 ver paginação dos linktable, e count ao carregar...

        $pagina = (int) $dados['pagina']; //0;
        $limite = 30;

        if ($dados['condicao']) {
            $json = Helper::jsonToArrayFromView($dados['condicao']);
            foreach ($json as $item => $value) {
                if (is_array($value)) {
                    $condicao[$item] = ["->>'$value[0]' = ", "'$value[1]'"];
                } else {
                    $condicao[$item] = $value;
                }
            }
        }
        $list = $this->dao->getAll($condicao, true, $pagina, $limite);      // obter as relações, gera um custo maior de query. avaliar a necessidade em produção
        // aplicar uploadfiles
        $out = [];
        $ctr = new UploadfileController();
        $isPessoa = Helper::compareString('pessoa|pessoa', $relacao);
        foreach ($list as $item) {
            $idLinktable = $item->selectExtra;
            $ret = parent::objectToArray($item);
            if ($isPessoa || $ret['extrasPessoa']) {
                $ret['cardColor'] = json_decode($ret['Situacao']['extrasSituacao'])->color;
                $ret['extrasPessoa'] = json_decode($ret['extrasPessoa'], true);
                if ((int) $ret['extrasPessoa']['idSituacaopenal'] > 0) {
                    $st = $this->dao->setObject(new Situacaopenal())->getById($ret['extrasPessoa']['idSituacaopenal']);
                    $ret['Situacaopenal'] = parent::objectToArray($st);
                    $ret['Situacaopenal']['extrasSituacaopenal'] = json_decode($ret['Situacaopenal']['extrasSituacaopenal'], true);
                    unset($ret['Situacaopenal']['Agencia']);
                }
            }

            if (method_exists(get_class($item), 'getUploadfile')) {
                $ret['Uploadfile'] = UploadfileController::toEntitie($item->getUploadfile());
            }
            $ret['linktable'] = json_decode($idLinktable, true);
            //$ret['linktable']['htCount'] = $item->selectExtra2;
            // trocar idENTIDADE pelo value setado

            if (is_array($ret['linktable']['extra_linktable'])) {
                foreach ($ret['linktable']['extra_linktable'] as $key => $id) {
                    if (mb_substr($key, 0, 2) === 'id') {
                        $ent = Config::getEntidadeName(mb_substr($key, 2));
                        $file = Config::getData('path') . '/auto/entidades/' . $ent . '.class.php';
                        Helper::directorySeparator($file);
                        if (file_exists($file) && (int) $id > 0) {
                            $c = $this->dao->setObject(new $ent())->getById($id);
                            if ($c instanceof $ent) {
                                $fn = 'getNome' . $ent;
                                $ret['linktable']['extra_linktable'][$key] = $c->$fn();

                                // especifico para Galeria, obter ala
                                if ($ent === 'Galeria') {
                                    $ret['linktable']['extra_linktable']['idGaleria'] .= ' (' . json_decode($c->getExtrasGaleria())->ala . ')';
                                }
                            } else {
                                Log::logTxt('nãoachou-identidade', $ent);
                                $ret['linktable']['extra_linktable'][$key] = 'link ' . $id . ' nao existe';
                            }
                        } else {
                            Log::logTxt('naoachouentidade', 'Entidade:' . $file . ', ID: ' . $id);
                        }
                    }
                }
            }


            /*

              // Nome do cargo
              if ((int) $ret['linktable']['extra_linktable']['idCargo'] > 0) {
              $this->dao->setObject($cargo);
              $c = $this->dao->getAll(['idCargo' => (int) $ret['linktable']['extra_linktable']['idCargo']])[0];
              if ($c instanceof Cargo) {
              $ret['nomeCargo'] = $c->getNomeCargo();
              }
              }

              // Nome da função
              if ((int) $ret['linktable']['extra_linktable']['idFuncao'] > 0) {
              $this->dao->setObject($funcao);
              $c = $this->dao->getAll(['idFuncao' => (int) $ret['linktable']['extra_linktable']['idFuncao']], false)[0];
              if ($c instanceof Funcao) {
              $ret['nomeFuncao'] = $c->getNomeFuncao();
              }
              }
             * 
             */

            // Avatar do usuario
            if ($relacao === 'USUARIOGRUPO|USUARIO') {
                $this->dao->setObject(new Uploadfile());
                $ret['avatar'] = $ctr->uploadEntidade($this->dao->getAll(['idUploadfile' => $ret['avatarUsuario']], false, 0, 1)[0]);
            }

            // relação com arquivo, criar entidade correta
            if (stripos($relacao, '|UPLOADFILE') > 0) {
                $ret = $ctr->toEntitie($item);
                $ret['linktable'] = json_decode($idLinktable, true);
            }

            //Api::result(200, $this->dao->getQuery());


            $ret['ent'] = ucwords($entidadeRelacionada);
            $out[] = $ret;
        }
        //$eficiencia->end();
        return $out;
    }

    public static function getList($id, &$dao) {
        $t = new LinktableController();
        $dao->setObject(new Linktable());
        return $t->ws_getAll(['idLinktable' => $id], $dao);
    }

    /**
     * @create 30/05/2018
     * Metodo responsavel por salvar uma entidade
     */
    public function ws_save($dados) {
        $ltrel = Helper::jsonToArrayFromView($dados['LtRel']);
        if ($ltrel['nomeLtRel']) {
            $dados['relacaoLinktable'] = $ltrel['nomeLtRel'];
        }


        $ent = explode('|', $dados['relacaoLinktable']);
        $naoValidarPoderes = ['CASO', 'PROCEDIMENTO', 'TAREFA'];

        // pra enviar arquivos, não deve ser validados poderes pq isso é feito na entidade principal, não no linktable
        if (Helper::compareString('file', $ent[1]) === false && array_search(Helper::upper($ent[0]), $naoValidarPoderes) === false) {
            Poderes::verify($ent[0], $ent[0], 'editar');
        }
        /* se noa deve validar poderes, pra q esse else?
          else {
          return ['error' => 'ent' . $ent];
          }
         */
        $this->getKeyRelacao($dados);
        $dao = new EntityManager($entity);

        // autorelacionamento - nao permitir
        if (($dados['idLeftLinktable'] === $dados['idRightLinktable']) && ($dados['relacaoLeft'] === $dados['relacaoRight'])) {
            return ['error' => 'Não registrado por ser auto-vinculo' . $dados['idLeftLinktable'] . ' === ' . $dados['idRightLinktable']];
        }

        // vinculo de mesma entidade: verificar se já existe, com inversão de valores
        // Caso ocorra inserção de uma mesma relação com os idright e left invertido, causará um erro ao buscar linktable
        // trata-se de um hash para corrigir a chave unica do banco, que não interpreta a posição left and right
        if ((int) $dados['idLinktable'] === 0 && $dados['relacaoLeft'] === $dados['relacaoRight']) {
            $query = 'select count(id_linktable) as qtde from ' . Helper::setTable('app_linktable')
                    . ' where id_lt_rel = ' . $dados['idLtRel']
                    . ' and ('
                    . ' (id_left_linktable= ' . $dados['idLeftLinktable'] . ' and id_right_linktable= ' . $dados['idRightLinktable'] . ')'
                    . ' or (id_left_linktable= ' . $dados['idRightLinktable'] . ' and id_right_linktable= ' . $dados['idLeftLinktable'] . ')'
                    . ' )';
            $qtde = $dao->execQueryAndReturn($query)[0]['qtde'];
            if ($qtde > 0) {
                return ['error' => 'Já existe relação'];
            }
        }


        //$dados['relacaoLinktable'] = Helper::upper($dados['relacaoLinktable']);
        $dados['idUsuario'] = $_SESSION['user']['idUsuario'];
        $json = Helper::jsonToArrayFromView($dados['extraLinktable']);
        //Log::logTxt('debug', $json);
        $dados['extraLinktable'] = json_encode($json);
        $entity = new Linktable($dados);


        //$dao->onConflict = 'on conflict on constraint linktable_id_left_linktable_id_right_linktable_id_lt_rel_key do nothing';
        $dao->onConflict = ' on conflict'
                . ' on constraint linktable_un'
                . ' do nothing';
        $link = $dao->setObject($entity)->save(false);

        Log::logTxt('debug', json_encode($dao->saveDiff));


        // Processo de registro da timeline e LOG de auditorias
        $termo = ( ((int) $dados['idLinktable'] > 0) ? 'Editado' : 'Adicionado');
        if ($link->getError() === false && $link->getId() > 0) {      // commit, and return
            $dao2 = new EntityManager();

            switch (true) {
                case (stripos($dados['relacaoLinktable'], '|FILE') > -1): // linktable com arquivos
                    $up = parent::getById(new Uploadfile(), $dados['idRightLinktable']);
                    if (stripos($up->getMimeUploadfile(), 'image') > -1 || stripos($up->getMimeUploadfile(), 'video') > -1 || stripos($up->getMimeUploadfile(), 'audio') > -1) {
                        $icone = $up->getFilenameUploadfile();
                    } else {
                        $icone = 'nao_disponviel';
                    }
                    $dados['relacaoLeft'] = str_replace('_FOTOS', '', $dados['relacaoLeft']);
                    //TlController::add($dados['relacaoLeft'], $dados['idLeftLinktable'], 'Arquivo adicionado', //TlController::getJsonEntidade('Uploadfile', $dados['idRightLinktable'], '', '', $icone));
                    break;
                case (stripos($dados['relacaoLinktable'], '|RISCO') > -1): // linktable com arquivos
                    $up = parent::getById(new Risco(), $dados['idRightLinktable']);
                    //TlController::add($dados['relacaoLeft'], $dados['idLeftLinktable'], 'Risco mapeado', //TlController::getJsonEntidade('Risco', $dados['idRightLinktable'], $up->getNomeRisco(), '', $icone));
                    break;
                case (stripos($dados['relacaoLinktable'], '|ENQUADRAMENTO') > -1):
                    $n = Config::getEntidadeName($dados['relacaoRight']);
                    $up = $dao2->setObject(new $n())->getById($dados['idRightLinktable']);
                    //TlController::add($dados['relacaoLeft'], $dados['idLeftLinktable'], 'Definido tag. ' . $up->getEnquadramentoArea()->getNomeEnquadramentoArea(), //TlController::getJsonEntidade($dados['relacaoRight'], $dados['idRightLinktable'], $up->getNomeEnquadramento()));
                    break;
                case (stripos($dados['relacaoLinktable'], '|PROFISSAO') > -1):
                    //TlController::add($dados['relacaoLeft'], $dados['idLeftLinktable'], 'Inserido profissão', //TlController::getJsonEntidade($dados['relacaoRight'], $dados['idRightLinktable']));
                    break;
                default:
                    //TlController::add($dados['relacaoLeft'], $dados['idLeftLinktable'], $termo . ' vinculo com ' . Config::getAliasesTable($dados['relacaoRight']), //TlController::getJsonEntidade($dados['relacaoRight'], $dados['idRightLinktable']));
                    //TlController::add($dados['relacaoRight'], $dados['idRightLinktable'], $termo . ' vinculo com ' . Config::getAliasesTable($dados['relacaoLeft']), //TlController::getJsonEntidade($dados['relacaoLeft'], $dados['idLeftLinktable']));
                    break;
            }
            return $this->LinktableEntidade($dao->getObject());
        } else {      //return error
            if (stripos($link->getErrorToString(), '23505') > -1) {
                $link->setError(false);
            }
            return parent::objectToArray($link);
        }
    }

    /**
     * @create 30/05/2018
     * Metodo responsavel por remover uma entidade
     */
    public function ws_remove($dados, $validaPoderes = true) {

        $dao = new EntityManager(new Linktable());
        $lt = $dao->getById($dados['idLinktable'], true);
        if ($validaPoderes) {
            $ent = $lt->getLtRel()->getNomeLtRel(); //
            $entidade = explode('|', $ent)[0];
            Poderes::verify($entidade, $entidade, 'editar');
        }

        if ($lt instanceof Linktable) {
            $dao->beginTransaction();
            $ret = $dao->setObject($lt)->remove(false);


            if ($ret === true) {
                $rel = explode('|', $lt->getLtRel()->getNomeLtRel());
                UploadfileController::removeByEntidadeId('Linktable', $lt->getId());

                // timeline
                switch (true) {
                    case (stripos($rel, '|FILE') > -1):
                        //TlController::add($rel[0], $lt->getIdLeftLinktable(), 'Arquivo removido', //TlController::getJsonEntidade('Uploadfile', $lt->getIdRightLinktable()));
                        break;
                    case (stripos($rel, '|RISCO') > -1):
                        $up = parent::getById(new Risco(), $lt->getIdRightLinktable());
                        //TlController::add($rel[0], $lt->getIdLeftLinktable(), 'Mapeamento de risco removido', //TlController::getJsonEntidade('Risco', $lt->getIdRightLinktable(), $up->getNomeRisco(), '', $icone));
                        break;
                    case (stripos($rel, '|ENQUADRAMENTO') > -1):
                        $up = parent::getById(new Enquadramento(), $lt->getIdRightLinktable());
                        //TlController::add($rel[0], $lt->getIdLeftLinktable(), 'Removido tag. ' . $up->getEnquadramentoArea()->getNomeEnquadramentoArea(), //TlController::getJsonEntidade('Risco', $lt->getIdRightLinktable(), $up->getNomeEnquadramento()));
                        break;
                    case (stripos($rel, '|PROFISSAO') > -1):
                        $text = parent::getById(new Profissao(), $lt->getIdRightLinktable())->getNomeProfissao();
                        //TlController::add($rel[0], $lt->getIdLeftLinktable(), 'Removido profissão', //TlController::getJsonEntidade('Risco', $lt->getIdRightLinktable(), $text));
                        break;

                    default:
                        //TlController::add($rel[0], $lt->getIdLeftLinktable(), 'Removido vinculo com ' . Config::getAliasesTable($rel[1]), //TlController::getJsonEntidade($rel[1], $lt->getIdRightLinktable()));
                        //TlController::add($rel[1], $lt->getIdRightLinktable(), 'Removido vinculo com ' . Config::getAliasesTable($rel[0]), //TlController::getJsonEntidade($rel[0], $lt->getIdLeftLinktable()));
                        break;
                }
                $dao->commit();
            } else {
                $dao->rollback();
                Log::error('Erro ao remover linktable: ' . json_encode($ret), $dados);
                return ['error' => 'Erro ao remover vinculo (LTT 334)'];
            }

            return ['error' => false];
        } else {
            return ['error' => 'Vinculo não localizado'];
        }
        /*

          $this->getKeyRelacao($dados);
          //Poderes::verify($dados['relacaoLeft'], 'Vinculos', 'remover');



          $entity = new Linktable($dados);


          $ret = parent::remove($entity);
          if ($ret === true) {      // tudo certo, removido com segurança
          UploadfileController::removeByEntidadeId('Linktable', $entity->getId());
          // somente se não for file, jogar na timeline
          if (stripos($dados['relacaoLinktable'], '|FILE') === false) {
          TlController::add($dados['relacaoLeft'], $dados['idLeftLinktable'], 'Removido vinculo com' . Config::getAliasesTable($dados['relacaoRight']), TlController::getJsonEntidade($dados['relacaoRight'], $dados['idRightLinktable']));
          TlController::add($dados['relacaoRight'], $dados['idRightLinktable'], 'Removido vinculo com ' . Config::getAliasesTable($dados['relacaoLeft']), TlController::getJsonEntidade($dados['relacaoLeft'], $dados['idLeftLinktable']));
          }

          $out = ['error' => false];
          } else {
          $out = ['error' => '<br/> ' . $ret];
          }
          return $out;
         * 
         */
    }

    /**
     * Gera os cards que serão exibidos na lista de vinculos
     * @param type $dados
     * @return type
     */
    public function ws_getCard($dados) {
        $this->getKeyRelacao($dados);
        $relacao = $this->getRelacao($dados);
        // preparar o CARD conforme o model a ser listado
        $objectAngular = 'Linktable';
        $height = false;
        $menuContexto = 'LinktableContextItens';
        $ngRepeatList = 'Linktables';      //|filter:filtro';
        $msgLengthZero = 'Nenhum vinculo localizado';
        $classColunas = 'coluna-card';
        $nomeCampo = 'nome' . ucwords(strtolower($relacao));
        $labelRelacao = Config::getData('titlePagesAliases', $relacao);
        switch ($relacao) {
            case 'MODULO':
                $array = [
                    Card::getModelBasic('Nome', 'nomeModulo'),
                ];
                break;
            case 'CURSO':
                $array = [
                    Card::getModelBasic('Nome', 'nomeCurso'),
                    Card::getModelBasic('Nome', 'nomeCurso'),
                ];
                break;
            default:
                $array = [
                    Card::getModelBasic('Nome', $nomeCampo, 'card-danger'),
                    Card::getModelBasic('*****CARD PENDENTE DE TABULAÇÃO*****', 'CARDsegundalinha', 'text-center'),
                    Card::getModelBasic('CARD: ' . $relacao, 'dd', 'text-center'),
                ];
                $view = 'Não definido';
                break;
        }

        // extras, conforme relação
        switch ($dados['relacaoLinktable']) {
            case 'POLO|CURSO':
                $array[] = Card::getModelBasic('Disponível', 'linktable.extra_linktable.disponivel');
                break;
            default:
                break;
        }


        $tableLine = [];
        $head = [];
        foreach ($array as $value) {
            if ($value['label'] === 'html-table') {
                continue;
            } else if ($value['label'] === 'img') {
                $head[] = '';
                $tableLine[] = '<img class="img img-rounded", style="max-width:60px;" ng-src="{{' . $objectAngular . '.' . $value['atributo'] . '}}" />';
            } else {
                $head[] = $value['label'];
                $tableLine[] = '{{' . $objectAngular . '.' . $value['atributo'] . '}}';
            }
        }

        // table editável
        $table = new Table($head);
        $table->setCss('text-dark')
                ->setForeach($ngRepeatList, $objectAngular)
                ->setMenuContexto($menuContexto)
                ->setOnClick('LinktableOnClick(' . $objectAngular . ')')
                ->addLinha($tableLine);


        //$array[0]['class'] .= 'card-white';
        return [
            'error' => false,
            'relacao' => $relacao,
            'card' => Card::basic($array, $objectAngular, $height, $menuContexto, $ngRepeatList, $msgLengthZero, $classColunas),
            'table' => '<span ng-show="' . $ngRepeatList . '.length">' . $table->printTable() . '</span>',
            'view' => (($view) ? $view : '<span ng-show="' . $ngRepeatList . '.length">' . $table
                    ->setCss("{{xabagaia.ns100r && 'mouseover-hand' || ''}}")
                    ->printTable() . '</span>')
        ];
    }

    /**
     * Retorna um JSON com os dados para exibição do vinculo e Modal
     * @param type $dados
     */
    public function ws_getModalData($dados) {
        $relacao = $this->getRelacao($dados);
        switch ($relacao) {
            case 'PESSOA':
                $modalData = [
                    ['label' => 'Nome', 'field' => 'nomePessoa', 'grid' => 'col-sm-6', 'class' => 'text-left'],
                    ['label' => 'Situação', 'field' => 'Situacao.nomeSituacao', 'grid' => 'col-sm-6', 'class' => 'text-left'],
                ];
                break;
            case 'COMUNIDADE':
                $modalData = [
                    ['label' => 'Nome comunidade', 'field' => 'nomeComunidade', 'grid' => 'col-sm-6', 'class' => 'text-left'],
                ];
                break;
            case 'CLUBE':
                $modalData = [
                    ['label' => 'Nome clube', 'field' => 'nomeClube', 'grid' => 'col-sm-6', 'class' => 'text-left'],
                    ['label' => 'Municipio', 'field' => 'Municipio.nomeMunicipio', 'grid' => 'col-sm-4', 'class' => 'text-left'],
                    ['label' => 'UF', 'field' => 'Municipio.Uf.siglaUf', 'grid' => 'col-sm-2', 'class' => 'text-left'],
                    ['label' => 'Tipo', 'field' => 'ClubeTipo.nomeClubeTipo', 'grid' => 'col-sm-5', 'class' => 'text-left'],
                    ['label' => 'Capacidade', 'field' => 'capacidadeClube', 'grid' => 'col-sm-2', 'class' => 'text-left'],
                    ['label' => 'Diretor', 'field' => 'diretorClube', 'grid' => 'col-sm-5', 'class' => 'text-left'],
                    ['label' => 'MAPA', 'field' => 'Municipio.Uf.nomeUf', 'grid' => 'col-sm-12', 'class' => 'text-left'],
                ];
                break;
            case 'EMPRESA':
                $modalData = [
                    ['label' => 'Nome empresa', 'field' => 'nomeEmpresa', 'grid' => 'col-sm-6', 'class' => 'text-left']
                ];
                break;
            case 'FEDERACAO':
                $modalData = [
                    ['label' => 'Nome', 'field' => 'nomeFederacao', 'grid' => 'col-sm-6', 'class' => 'text-left'],
                    ['label' => 'Municipio', 'field' => 'Municipio.nomeMunicipio', 'grid' => 'col-sm-4', 'class' => 'text-left'],
                    ['label' => 'UF', 'field' => 'Municipio.Uf.siglaUf', 'grid' => 'col-sm-2', 'class' => 'text-left'],
                    ['label' => 'Fundação', 'field' => 'dataFundacaoFederacao|date', 'grid' => 'col-sm-12', 'class' => 'text-left'],
                    ['label' => '', 'field' => 'detalheFederacao', 'grid' => 'col-sm-12', 'class' => 'text-left'],
                ];
                break;
            case 'FATO':
                $modalData = [
                    ['label' => 'Data', 'field' => "dataFato|date", 'grid' => 'col-sm-2', 'class' => 'text-left'],
                    ['label' => 'Titulo', 'field' => 'nomeFato', 'grid' => 'col-sm-10', 'class' => 'text-left'],
                    ['label' => 'Descrição', 'field' => 'descricaoFato', 'grid' => 'col-sm-12', 'class' => 'text-left'],
                    ['label' => 'Link', 'field' => 'linkFato', 'grid' => 'col-sm-12', 'class' => 'text-left'],
                    ['label' => 'Categoria', 'field' => 'FatoCategoria.nomeFatoCategoria', 'grid' => 'col-sm-6', 'class' => 'text-left'],
                    ['label' => 'Fonte', 'field' => 'FatoFonte.nomeFatoFonte', 'grid' => 'col-sm-4', 'class' => 'text-left'],
                    ['label' => 'Horário', 'field' => 'horarioFato', 'grid' => 'col-sm-2', 'class' => 'text-left'],
                    ['label' => 'Municipio', 'field' => 'Municipio.nomeMunicipio', 'grid' => 'col-sm-4', 'class' => 'text-left'],
                    ['label' => 'UF', 'field' => 'Municipio.Uf.siglaUf', 'grid' => 'col-sm-2', 'class' => 'text-left'],
                ];
                break;
            case 'FUNCAO':
                $modalData = [
                    ['label' => 'Nome função', 'field' => 'nomeFuncao', 'grid' => 'col-sm-12', 'class' => 'text-left'],
                ];
                break;
            case 'ESCOLA':
                $modalData = [
                    ['label' => 'Nome escola', 'field' => 'nomeEscola', 'grid' => 'col-sm-12', 'class' => 'text-left'],
                ];
                break;
            case 'MUNICIPIO':
                $modalData = [
                    ['label' => 'Nome Municipio', 'field' => 'nomeMunicipio', 'grid' => 'col-sm-8', 'class' => 'text-left'],
                    ['label' => 'UF', 'field' => 'Uf.siglaUf', 'grid' => 'col-sm-4', 'class' => 'text-left'],
                ];
                break;
            case 'USUARIO':
                $modalData = [
                    ['label' => 'Nome', 'field' => 'nomeUsuario', 'grid' => 'col-sm-6', 'class' => 'text-left'],
                ];
                break;
            case 'PROFISSAO':
                $modalData = [
                    ['label' => 'Nome / Titulo', 'field' => 'nome' . ucwords(strtolower($relacao)), 'grid' => 'col-sm-12', 'class' => 'text-left'],
                ];

                break;



            default:
                $modalData = [
                    ['label' => 'NOME ' . $relacao, 'field' => 'nome' . ucwords(strtolower($relacao)), 'grid' => 'col-sm-12', 'class' => 'text-left'],
                    ['label' => '!!! VIEW PENDENTE DE TABULAÇÃO !!! (LTC-230)', 'field' => 'nomePessoa', 'grid' => 'col-sm-12', 'class' => 'text-center text-danger'],
                    ['label' => 'RELAÇÃO: ' . $relacao, 'field' => 'nomePessoa', 'grid' => 'col-sm-12', 'class' => 'text-center text-danger'],
                ];
                break;
        }
        /*
          $modalData = [
          'json' => json_encode($modalData)
          ];
         * 
         */
        return $modalData;
    }

    private function getRelacao($dados) {
        Helper::upperByReference($dados['relacaoLinktable']);
        $r = explode('|', $dados['relacaoLinktable']);

        // caso exista idLeft, significa que devo listar as entidades a direita
        $relacao = (((int) $dados['idLeftLinktable'] > 0) ? $r[1] : $r[0]);
        return $relacao;
    }

    public function ws_getRelacao($dados) {
        return ['error' => false, 'relacao' => ucwords(Helper::name2CamelCase($this->getRelacao($dados)))];
    }

    public static function vinculoSetTabs($vinculos = false, $entidade, $fileJS = false) {
        /*
          if (!is_array($vinculos)) {
          return [];
          }
         */

        // 13/02/2019 - configurado na mão devido ao load por carga
        $vinculos = self::$vinculos;
        //$fileJS = Config::getData('pathView') . '/view/' . $entidade . '/' . $entidade . '-script.js';
        //     //     //     //     //     //     //     //     //     //     //     //     //     ///


        $labelCampoNeeded = "$entidade.id$entidade>0";
        $js = [];
        $out = [];
        $js[] = '$scope.setVinculosOnEdit = function (id) {';
        $js[] = '$scope.setAbas(id);';

        asort($vinculos);
        foreach ($vinculos as $vinculo) {
            if ($vinculo === 'enquadramento') { // não mostrar nas abas
                continue;
            }
            /*
              if ($vinculo === 'arquivos') {
              $out[] = array_merge(Tab::getModel('vinculoFiles', 'Arquivos <span class="badge badge-light">{{' . $entidade . '.Files.length}}</span>', Html::uploadFile($entidade)), ['ng-if' => $labelCampoNeeded]);
              //$js[] = '$("#UFDCont_'.$entidade.$entidade.'_Files").html($compile(\'<upload-file entidade="\'+$scope.entidadeName+\'" valorid="\' + id + \'" btn-text="Novo" btn-icon="fa-plus" max-size="1600" thumbs="false"></upload-file>\')($scope));';
              } else {
             * 
             */
            $label = "{{'$vinculo' | tr}}"; // Config::getData('titlePagesAliases', ucwords($vinculo));
            $idTab = 'vinculo_' . $vinculo;
            $idDiv = 'vinculo' . ucwords($vinculo);
            $label_badge = ' <span class="badge badge-light badge-vinculo ' . $idTab . '">0</span>';
            $out[] = array_merge(Tab::getModel($idTab, $label . $label_badge, '<div id = "' . $idDiv . '"></div>'), ['ng-if' => $labelCampoNeeded]);
            /* 13/02/2019 - retirei pois nao mais vira vinculos de risco e endereco aqui
             * nem mesmo os vinculos, pq serão obtidos por carga total
              switch ($vinculo) {
              case 'risco':
              $js[] = '$("#vinculoRisco").html($compile(\'<risco badge-id="vinculoRisco" entidade="' . Helper::upper($entidade) . '" id="\' + id+ \'"></risco>\')($scope));';
              break;
              case 'endereco':
              $js[] = '$("#vinculoEndereco").html($compile(\'<ns-address entidade="' . Helper::upper($entidade) . '" valorid="\' + id+ \'" limite="\'+$scope.AddressLimit+\'"></ns-address>\')($scope));';
              break;
              default:
              //$js[] = '$("#' . $idDiv . '").html($compile(\'<linktable title="' . $label . '" view="false" grid-cards="col-sm-4" text-search="O que deseja buscar?" relacao="' . $entidade . '|' . $vinculo . '" id-left="\' + id + \'"/>\')($scope));';
              break;
              }
             * 
             */

            /*
              }
             * */
        }
        $js[] = '};';
        if ($fileJS) {
            $txt = file_get_contents($fileJS);
            $string = explode('$scope.setVinculosOnEdit = function (id) {', $txt);
            if (count($string) > 1) {
                $inicio = $string[0];
                $string = explode('};', $string[1]);
                unset($string[0]);
                $fim = implode("};  ", $string);
            } else {
                $inicio = 'NÃO LOCALIZADO FUNCAO PARA ATUALIZAR.';
                $fim = '$scope.setVinculosOnEdit = function (id) {};';
                $fim .= '$scope.setVinculosOnEdit(v.id' . $entidade . ');';
                $js = [];
            }
            $jsTxt = $inicio . implode("\n", $js) . $fim;

            /*
              $lines = explode("\n", $jsTxt);
              $newJS = [];
              for ($i=0; $i<count($lines); $i++) {
              if (strlen($lines[$i]) > 1)   {
              //echo strlen($lines[$i]).'-'.$lines[$i].'<br/>';
              $newJS[] = $lines[$i];
              }
              }
              $jsTxt = implode("\n", $newJS);
             */

            Helper::saveFile($fileJS, '', $jsTxt, 'SOBREPOR');
            //Log::ver('Novo JS Salvo com ' . ((count($js) > 0) ? 'sucesso!' : 'ERRO. Verifique o arquivo'));
        }
        return $out;
    }

    public function ws_getKeyRelacao($dados) {
        $this->getKeyRelacao($dados);
        return $dados;
    }

    /**
     * Administra a criação de novas chaves de relação
     * @param type $dados
     */
    private function getKeyRelacao(&$dados) {
        $this->getIdRelacao($dados['relacaoLinktable']);
        $idLeft = (int) $dados['idLeftLinktable'];
        $idRight = (int) $dados['idRightLinktable'];
        if ($this->relacao->revertido) {
            $idRight = (int) $dados['idLeftLinktable'];
            $idLeft = (int) $dados['idRightLinktable'];
        }

        // Obter ID da relação
        $rel = explode("|", $this->relacao->nome);
        $dados['relacaoLeft'] = $rel[0];
        $dados['relacaoRight'] = $rel[1];
        $dados['relacaoLinktable'] = $this->relacao->nome;
        $dados['idLeftLinktable'] = $idLeft;
        $dados['idRightLinktable'] = $idRight;
        $dados['idLtRel'] = $this->relacao->id;

        $this->relacao->entidadeRelacionada = (($dados['idLeftLinktable'] > 0) ? $rel[1] : $rel[0]); // entidade para efetuar o search.
        $this->entidadeOrigem = (($dados['idLeftLinktable'] > 0) ? $rel[0] : $rel[1]); // entidade que esta pedindo
        $this->campoIdSelect = (($dados['idLeftLinktable'] > 0) ? 'id_right_linktable' : 'id_left_linktable');
        $this->campoIdWhere = (($dados['idLeftLinktable'] > 0) ? 'id_left_linktable' : 'id_right_linktable');
        $this->valorIdWhere = (($dados['idLeftLinktable'] > 0) ? $dados['idLeftLinktable'] : $dados['idRightLinktable']);
    }

    /**
     * Ira buscar o ID da relação na tabela de relações. Para idnexação em inteiros no tipo de relação
     * @param type $relacao
     * @return type
     */
    public function getIdRelacao($relacao) {
        Helper::upperByReference($relacao);
        if (strlen($relacao) < 2) {
            Api::result(403, ['error' => '(LTT 896) Relação nao informada: ' . $relacao]);
        }
        $t = explode('|', $relacao);
        $reverso = $t[1] . '|' . $t[0];
        $item = $this->dao->setObject(new LtRel())
                        ->getAll([
                            'nomeLtRel' => ['in', "('$relacao', '$reverso')"]
                                ], false, 0, 1)[0];
        //$id = (int) $this->dao->execQueryAndReturn("select id_lt_rel as xid from app_lt_rel where nome_lt_rel in ('$relacao', '$reverso') limit 1")[0]['xid'];
        //$item = $this->dao->execQueryAndReturn("select id_lt_rel as xid from app_lt_rel where nome_lt_rel in ('$relacao', '$reverso') limit 1")[0];
        if (!($item instanceof LtRel)) {
            Log::log('debug', 'Criação de nova relação linktable: ' . $relacao);
            $n = new LtRel();
            $n->setNomeLtRel($relacao);
            $this->dao->setObject($n)->save();
            if ($this->dao->getObject()->getError() === false) {
                return $this->getIdRelacao($relacao);
            }
        }
        $this->relacao = (object) [
                    'id' => (int) $item->getId(),
                    'nome' => $item->getNomeLtRel(),
                    'revertido' => !Helper::compareString($relacao, $item->getNomeLtRel())
        ];
        return $item->getId();
    }

    /**
     * Retorna array com os camps extras da relação setada
     */
    public function getExtras($linktable) {
        $dao = new EntityManager();
        switch ($linktable->getIdLtRel()) {

            case 2:
                $out = [
                    'Data inicio' => Helper::getExtrasConfig('Data inicio', 'col-3', 'date', 'Data de inicio do curso')
                ];
                break;
            default:
                $out = [];
        }

        return $out;
    }

    /**
     * Método retorna um array com o modelo a ser preenchido para a relação, a ser salvo em extras
     * @param type $relacao
     */
    private function getModelJson($relacao, $json, &$dao) {
        $out = [
            'valid' => true,
            'model' => []
        ];
        //$dao = new EntityManager();
        $linktable = $dao->getObject();
        switch ($relacao) {
            case 'PESSOA|EMPRESA':
                $out['model'] = ['idCargo' => 0];
                if ((int) $json['idCargo'] <= 0) {
                    $out['valid'] = false;
                    // lista de opções: todos os cargos desta empresa
                    $dao->setObject(new Cargo());
                    $out['Aux']['Cargo'] = parent::objectToArray($dao->getAll(['idEmpresa' => $linktable->getIdRightLinktable()], false));
                    $out['args'] = [
                        'title' => 'Necessário escolher um cargo nesta empresa',
                        'body' => Html::inputSelectNgRepeat('Args.extraLinktable.idCargo', 'Cargo', 'false', 'Aux.Cargo'),
                        'btnOk' => 'Continuar'
                    ];
                }
                break;
            case 'PESSOA|FEDERACAO':
                $out['model'] = ['Função' => 0];
                if ((int) $json['Função'] <= 0) {
                    $out['valid'] = false;
                    // lista de opções: todos os cargos desta empresa
                    $dao->setObject(new Funcao());
                    $out['Aux']['Funcao'] = parent::objectToArray($dao->getAll(['idFederacao' => $linktable->getIdRightLinktable()], false));
                    $out['args'] = [
                        'title' => 'Necessário escolher uma função',
                        'body' => Html::inputSelectNgRepeat('Args.extraLinktable[\'Função\']', 'Função', 'false', 'Aux.Funcao'),
                        'btnOk' => 'Continuar'
                    ];
                }
                break;

            default:
                break;
        }

        return $out;
    }

    public function ws_tagList($dados) {
        // vinculos
        $vinculos = $this->ws_getAll($dados);
        $entidade = ucwords(mb_strtolower($this->relacao->entidadeRelacionada));

        // relacao total
        $condicao = [];
        $controller = $entidade . 'Controller';
        $ctr = new $controller();
        if ($dados['condicao']) {
            $json = Helper::jsonToArrayFromView($dados['condicao']);
            foreach ($json as $item => $value) {
                if (is_array($value)) {
                    $condicao[$item] = ["->>'$value[0]' = ", "'$value[1]'"];
                } else {
                    $condicao[$item] = $value;
                }
            }
            // Casos especificos
            if (Helper::compareString($dados['relacaoLinktable'], 'polo|usuario')) { // somente professores habilitados para os modulos deste campos
                /* @05/01/2021: removi pois em polo nao existe id_usuario e estava gerando erro.
                  $condicao['idUsuario_2'] = ['in', "(
                  select distinct a.id_usuario
                  from app_usuario a
                  inner join app_linktable b on a.id_usuario= b.id_right_linktable
                  inner join app_lt_rel c on c.id_lt_rel = b.id_lt_rel
                  where c.nome_lt_rel= 'MODULO|USUARIO' and b.id_left_linktable in (
                  select id_modulo from modulo  a
                  inner join app_linktable b on a.id_modulo= b.id_right_linktable
                  inner join app_lt_rel c on c.id_lt_rel = b.id_lt_rel
                  where c.nome_lt_rel= 'CURSO|MODULO' and b.id_left_linktable in (
                  select id_curso from curso  a
                  inner join app_linktable b on a.id_curso= b.id_right_linktable
                  inner join app_lt_rel c on c.id_lt_rel = b.id_lt_rel
                  where c.nome_lt_rel= 'POLO|CURSO' and b.id_left_linktable = 10
                  )
                  )
                  )
                  "]; //fecha usuario
                 * 
                 */
            }
            $ctr->setCondicaoManual($condicao);
        }
        $list = $ctr->ws_getAll([]);

        // juncao
        $out = [
            'itens' => []
        ];
        foreach ($list as $item) {
            $ret = [];
            $ret['checked'] = false;
            $ret['sort'] = $item['extraLinktable']['sort'];
            foreach ($vinculos as $v) {
                if ($v['id' . $entidade] === $item['id' . $entidade]) {
                    $ret['checked'] = true;
                    $ret['idLinktable'] = $v['linktable']['id_linktable'];
                    $ret['sort'] = $v['linktable']['extra_linktable']['sort'];
                    continue;
                }
            }
            $ret['label'] = \NsUtil\Helper::formatTextAllLowerFirstUpper($item['nome' . $entidade]);
            $ret['id'] = $item['id' . $entidade];
            $out['itens'][] = $ret;
        }
        // ordenar array em ordem alfabética
        usort($out['itens'], function($a, $b) {
            return $a['label'] > $b['label'];
        });
        $out['title'] = Config::getAliasesTable($entidade);
        return $out;
    }

    public function ws_setSort($dados) {
        $dao = new EntityManager(new Linktable());
        $itens = Helper::jsonToArrayFromView($dados['list']);
        foreach ($itens as $item) {
            $lt = $dao->getById($item['idLinktable']);
            if (!($lt instanceof Linktable)) {
                continue;
            }
            $extras = json_decode($lt->getExtraLinktable(), true);
            $extras['sort'] = (int) $item['sort'];
            $lt->setExtraLinktable($extras);
            $dao->setObject($lt)->save();
        }
        return ['error' => $dao->getObject()->getErrorToString()];
    }

    public function ws_addExtras($dados) {
        $dao = new EntityManager(new Linktable());
        $lt = $dao->getById($dados['id']);
        if (!($lt instanceof Linktable)) {
            return ['error' => 'Não localizada'];
        }
        $extras = json_decode($lt->getExtraLinktable(), true);
        $extras[$dados['key']] = $dados['value'];
        $lt->setExtraLinktable($extras);
        $dao->setObject($lt)->save();
        return ['error' => $dao->getObject()->getErrorToString()];
    }

}
