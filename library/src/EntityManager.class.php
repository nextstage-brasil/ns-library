<?php

/**
 * Description of EntityManager
 *
 * @author NextStage
 */
class EntityManager implements DAOInterface {

    private $object;
    private $message;
    public $con;
    private $order;
    private $countUploadfile;
    private $innerOrLeftJoin; // para definir se a consulta será por left ou inner join
    public $selectExtra, $selectExtraB; // serve para injetar um select extra. será colocado entre parenteses e zerado a cada chamada;
    private $groupBy;
    private $query;

    public function __construct($object = false) {
        if (!$object) {
            $this->object = new App();
        } else {
            $this->object = $object;
        }
        $this->order = false;
        $this->message = '';
        $this->con = Connection::getConnection();
        $this->setInnerOrLeftJoin();
    }

    function setOrder($orderBy) {
        if ($orderBy && stripos($orderBy, '.') === false) { // não veio tabela principal
            $orderBy = $this->object->getTable() . '.' . $orderBy;
        }
        $this->order = $orderBy;
        return $this;
    }

    function setGroupBy($campos) {
        $out = [];
        foreach ($campos as $campo) {
            $f = Helper::reverteName2CamelCase($campo);
            $out[] = $f;
        }
        $this->groupBy = implode(', ', $out);
        return $this;
    }

    function setInnerOrLeftJoin($innerOrLeftJoin = 'left') {
        $this->innerOrLeftJoin = (($innerOrLeftJoin === 'left') ? 'left' : 'inner');
        return $this;
    }

    public function setCountUploadfile($switch) {
        $this->countUploadfile = (boolean) $switch;
        return $this;
    }

    public function setObject($object) {
        $this->object = $object;
        $this->order = false;
        return $this;
    }

    public function beginTransaction() {

        $this->con->begin_transaction();
        return $this;
    }

    public function commit() {
        $this->con->commit();
        return $this;
    }

    public function rollback() {
        $this->con->rollback();
        return $this;
    }

    /**
     * Usado para registrar inserts e updates
     * Retorna Array:
     * 0 - True or False
     * 1 - Mensagem de confirma��o
     * 2 - Array com campos obrigat�rios
     * 3 - ID da opera��o
     * 4 - Link para retorno completo
     */
    public function save($onConflict = '') {
        if ($this->object->getError() !== false) {
            //Log::log('entityManager-error', $this->object->getTable() . ': ' . json_encode($this->object->getError()));
            return $this->object;
        }
        $tabela = $this->object->getTable();
        try {
            $app = new AppLibraryController();
            $api = new ReflectionClass(get_class($this->object));

            //$objectOld = $this->getAll([$this->object->getCpoId() => $this->object->getId()], false, 0, 1)[0];

            $subs = ['Property [', 'private $', ']', ' ', '<default>'];
            foreach ($api->getProperties() as $atributoOriginal) {

                $atributo = (string) trim(str_replace($subs, '', $atributoOriginal));

                // nao salvar createtime
                if (strpos(strtolower($atributo), 'createtime') !== false) {
                    continue;
                }
                // nao salvar campo id
                if ($this->object->getCpoId() === $atributo && $this->object->getId() === 0) {
                    continue;
                }

                $functionGet = 'get' . Helper::name2CamelCase(ucwords($atributo));
                if (substr($atributo, -8) != "Detalhes" && $atributo != "error" && $atributo != 'table' && $atributo != 'cpoId' && $atributo != $this->object->getCpoId()) {
                    $val = $this->object->$functionGet();
                    $type = gettype($val);
                    if ($type === 'object') {
                        continue;
                    }
                    $atributo = Helper::reverteName2CamelCase($atributo);
                    /*
                      switch ($type) {
                      case 'object':
                      continue;
                      break;
                      case 'integer':
                      case 'double':
                      break;
                      default:
                      $val = str_replace("'", '"', $val);
                      break;
                      }

                      if ($val != NULL) {
                      if (gettype($val) === 'integer' || gettype($val) === 'double') {
                      } else {
                      $val = str_replace("'", '"', $val);
                      //$val = stripslashes($val);
                      $val = "'$val'";
                      }
                      } else {
                      $val = "NULL"; //((gettype($val) === 'integer' || gettype($val) === 'double') ? $val : "NULL");
                      }
                     */

                    // alteração para prepare query
                    $preparedValues[$atributo] = $val;
                    //
                    //$queryUpdate[] = "$atributo= $val";
                    //$queryInsertKeys[] = "$atributo";
                    //$queryInsertVals[] = $val;
                }
            }
            /**
              //$queryUpdate = "UPDATE $tabela SET " . implode(", ", $queryUpdate) . " WHERE " . Helper::reverteName2CamelCase($this->object->getCpoId()) . "= " . $this->object->getId();
              $queryInsert = "INSERT INTO $tabela (" . implode(", ", $queryInsertKeys) . ") VALUES (" . implode(", ", $queryInsertVals) . ")";
              if (Config::getData('database', 'type') === 'postgres') {
              $queryInsert .= " returning " . Helper::reverteName2CamelCase($this->object->getCpoId()) . " as nsnovoid";
              }
             * 
             */
            try {
                // auditoria onsave
                //$this->auditoria();

                if ($this->object->getId() > 0) {
                    //$this->con->executeQuery($queryUpdate);
                    $preparedValues[Helper::reverteName2CamelCase($this->object->getCpoId())] = $this->object->getId();
                    $this->con->update($tabela, $preparedValues, Helper::reverteName2CamelCase($this->object->getCpoId()));
                    $auditoria = 'Atualizar';
                } else {
                    $auditoria = 'Inserir';
                    $this->con->insert($tabela, $preparedValues, Helper::reverteName2CamelCase($this->object->getCpoId()), $onConflict);

                    //$this->con->executeQuery($queryInsert);
                    if (Config::getData('database', 'type') === 'postgres') {
                        $dd = $this->con->next();
                        $this->object->setId($dd['nsnovoid']);
                    } else {
                        $this->object->setId($this->con->lastInsertId);
                    }
                }

                // auditoria
                // Ver a necessidade pois ira atuar com trigger
                /**


                  if ($audit) { // para controle de loopes
                  // auditoria
                  $objectNew = $this->getAll([$this->object->getCpoId() => $this->object->getId()], false, 0, 1)[0];
                  $d = Helper::arrayDiff($app->objectToArray($objectNew), $app->objectToArray($objectOld));
                  if (count($d) > 0) {
                  $this->diff = true;
                  $diff[$this->object->getCpoId()] = $objectNew->getId();
                  $diff['alteracoes'] = $d;
                  Log::auditoria(get_class($this->object), $auditoria, $diff);
                  }
                  }
                 * 
                 * 
                 */
            } catch (Exception $exc) {
                foreach (Config::getData('errors') as $chave => $value) {
                    if (stripos(strtolower($exc->getMessage()), strtolower($chave)) > -1) {
                        $error[] = $value;
                    }
                }
                $error = (is_array($error) ? $error[0] : $exc->getMessage()) . ((Config::getData('dev')) ? $exc->getMessage() . $this->con->query : '');
                $this->object->setError($error);
            }
        } catch (Exception $e) {
            // traduzir erros conhecidos
            $erros = array(
                'Undefined column' => 'Erro no sistema. (ABS104)'
            );
            foreach ($erros as $chave => $value) {
                if (stripos(strtolower($e->getMessage()), strtolower($chave)) > -1) {
                    $error[] = $value;
                }
            }
            $error = (is_array($error) ? $error : $e->getMessage());
            $this->object->setError($error);
            return $this->object;
        }
        return $this->object;
    }

    /**
      public function update(array $update, array $condicao) {
      $tabelaPrincipal = $this->object->getTable();
      $cond = $this->trataCondicao($condicao, $tabelaPrincipal);
      $up = [];
      foreach ($update as $key => $val) {
      $key = Helper::reverteName2CamelCase($key);
      if (gettype($val) === 'integer') {
      $up[] = "$key = $val";
      } else {
      $up[] = "$key = '$val'";
      }
      }
      $query = "update $tabelaPrincipal set " . implode(',', $up) . " " . $cond;
      return $this->con->executeQuery($query);
      }
     * 
     */

    /**
     * Ira gravar log com as diferencas entre arrays
     */
    private function auditoria() {
        if (array_search(get_class($this->object), ['ApiLog']) === false) {
            $app = new AppController();
            $new = $app->objectToArray($this->object);
            if ($this->object->getId() > 0) {
                $tipo = 'update';
                $oldObject = $this->getAll([$this->object->getCpoId() => $this->object->getId()], false, 0, 1)[0];
                $old = $app->objectToArray($oldObject);
            } else {
                $tipo = 'insert';
                $old = [];
            }
            $diff = Helper::arrayDiff($new, $old);
            Log::auditoria(get_class($this->object), $tipo, $diff, $this->object->getId());
            //Log::log('auditoria', $tipo, $this->object->getId(), get_class($this->object), $diff);
        }
    }

    public function remove($audit = true) {
        try {
            $app = new AppLibraryController();
            $oldObject = $this->getById($this->object->getId(), false);
            $diff['removed'] = $app->objectToArray($oldObject);
            $diff[$this->object->getCpoId()] = $this->object->getId();
            if ($diff['removed'][$this->object->getCpoId()] > 0) {
                $this->con->executeQuery("DELETE FROM "
                        . $this->object->getTable()
                        . " WHERE " . Helper::reverteName2CamelCase($this->object->getCpoId()) . "= " . $this->object->getId()
                        . " RETURNING " . Helper::reverteName2CamelCase($this->object->getCpoId()));
                $res = $this->con->next();
                $result = (boolean) $res[Helper::reverteName2CamelCase($this->object->getCpoId())];
                if ($audit) {
                    Log::auditoria(get_class($this->object), 'Remover', $diff);
                    //TlController::addFromAuditoria(get_class($this->object), $this->object->getId(), 'Remover', $diff);
                }

                return $result;
            }
        } catch (Exception $e) {
            Log::error("Erro ao remover: " . $e->getMessage());
            return self::getErrorByConfig($e->getMessage());
        }
    }

    // Faz  aleitura em config de erros conhecidos e traduz para o ambiente
    public static function getErrorByConfig($msg) {
        foreach (Config::getData('errors') as $chave => $value) {
            if (stripos($msg, $chave) > -1) {
                return $value;
            }
        }
        return $msg;
    }

    public function getMessage() {
        return $this->message;
    }

    public function getObject() {
        return $this->object;
    }

    public function getByCondition($condicao, $getRelacoes = false) {
        if (is_array($condicao)) {
            $entities = $this->getAll($condicao, $getRelacoes);
            return $entities;
        }
    }

    /* falta pensar melhor nisso
      public function getAllNoSql($entidades, $condicao, $inicio = 0, $limit = 1000) {
      $tabelaPrincipal = $this->object->getTable();
      $entidadePrincipal = get_class($this->object);
      $condicaoTratada = self::trataCondicao($condicao, $tabelaPrincipal);
      $order = $tabelaPrincipal . '.' . (($this->order) ? $this->order : Helper::reverteName2CamelCase($this->object->getCpoId()) . ' ASC');
      $select = [];
      $select[$tabelaPrincipal] = $this->object->getTable() . '.*';
      $innerJoin = [];
      $app = new AppController();

      // relacionamentos da tabela atual
      if (method_exists(get_class($this->object), 'getRelacionamentos')) {
      $relacoes = $this->object->getRelacionamentos();
      foreach ($relacoes as $relacao) {
      $select[$relacao['tabela']] = $relacao['tabela'] . '.*';
      $innerJoin[$relacao['tabela']] = "left JOIN $relacao[tabela] ON $relacao[tabela].$relacao[cpoOrigem] = $tabelaPrincipal.$relacao[cpoRelacao]";
      }
      }

      // varrer array de entidades, criar query
      foreach ($entidades as $entidade) {
      $class = new $entidade();
      $tabela = Helper::reverteName2CamelCase($entidade);
      $select[$tabela] = $tabela . '.*';
      $innerJoin[$tabela] = "left JOIN $tabela ON $tabela." . Helper::reverteName2CamelCase($this->object->getCpoId()) . " = $tabelaPrincipal." . Helper::reverteName2CamelCase($this->object->getCpoId());
      if (method_exists($entidade, 'getRelacionamentos')) {
      $relacoes = $class->getRelacionamentos();
      foreach ($relacoes as $relacao) {
      if ($relacao['tabela'] !== $tabelaPrincipal) {
      $select[$relacao['tabela']] = $relacao['tabela'] . '.*';
      $innerJoin[$relacao['tabela']] = "left JOIN $relacao[tabela] ON $relacao[tabela].$relacao[cpoOrigem] = $tabela.$relacao[cpoRelacao]";
      }
      }
      }
      }

      // executar query
      $query = 'SELECT ' . implode(', ', $select) . ' FROM ' . $tabelaPrincipal . ' ' . implode(' ', $innerJoin);
      $query .= $condicaoTratada . " ORDER BY " . $order . " LIMIT " . $limit . " OFFSET " . $inicio * $limit;
      Log::logTxt('debug', $query);
      $this->con->executeQuery($query);

      // criar objetos
      $out = [];
      while ($dd = $this->con->next())   {
      $o = $app->objectToArray(new $entidadePrincipal($dd));
      foreach ($entidades as $entidade) {
      $t = new $entidade($dd);
      $o[$entidade] = $app->objectToArray(new $entidade($dd));
      }
      $out[] = $o;
      }
      return $out;
      }
     * 
     */

    public function getAll($condicao, $getRelacoes = true, $inicio = 0, $limit = 1000, $relacaoExceto = array()) {
        //$relacaoExceto = array_merge(array('usuario'), $relacaoExceto);
        $tabelaPrincipal = $this->object->getTable();
        $condicao = self::trataCondicao($condicao, $tabelaPrincipal);
        //$order = $tabelaPrincipal . '.' . (($this->order) ? $this->order : Helper::reverteName2CamelCase($this->object->getCpoId()) . ' ASC');
        $order = (($this->order) ? $this->order : $tabelaPrincipal . '.' . Helper::reverteName2CamelCase($this->object->getCpoId()) . ' ASC');
        //Log::logTxt('query', $condicao);
        // select extra, definido ou não
        if ($this->countUploadfile) {
            $select[] = '(select count(id_uploadfile) from uploadfile '
                    . 'where entidade_uploadfile= \'' . Helper::upper($tabelaPrincipal) . '\' '
                    . 'and valorid_uploadfile= ' . $tabelaPrincipal . '.id_' . $tabelaPrincipal . ') as countuploadfile';
            $this->countUploadfile = false;
        }

        // groupBy
        if (strlen($this->groupBy) > 2) {
            $condicao .= ' group by ' . $this->groupBy;
            $select[] = $this->groupBy;
            $this->groupBy = false;
        }



        // relacionamentos        
        $select[] = $this->object->getTable() . '.*'
                . (($this->selectExtra) ? ', (' . $this->selectExtra . ') as selectExtra' : '')
                . (($this->selectExtraB) ? ', (' . $this->selectExtraB . ') as selectExtraB' : '');
        $innerJoin = array();
        $relacoes = array();
        if ($getRelacoes && method_exists(get_class($this->object), 'getRelacionamentos')) {
            $relacoes = $this->object->getRelacionamentos();
            //Log::logTxt('query-debug', $relacoes);
            foreach ($relacoes as $relacao) {
                if (array_search($relacao['tabela'], $relacaoExceto) === false) {
                    $select[] = $relacao['tabela'] . '.*';
                    $innerJoin[] = $this->innerOrLeftJoin . " JOIN $relacao[tabela] ON $relacao[tabela].$relacao[cpoRelacao] = $tabelaPrincipal.$relacao[cpoOrigem]";
                }
            }
        }
        //$query = 'SELECT distinct(' . $tabelaPrincipal . '.' . Helper::reverteName2CamelCase($this->object->getCpoId()) . '), ' . implode(', ', $select) . ' FROM ' . $tabelaPrincipal . ' ' . implode(' ', $innerJoin);

        $query = 'SELECT ' . implode(', ', $select) . ' FROM ' . $tabelaPrincipal . ' ' . implode(' ', $innerJoin);

        $query .= $condicao
                . " ORDER BY " . $order . " LIMIT " . $limit . " OFFSET " . $inicio * $limit;
        //Log::ver($query); 
        $this->query = $query;


        $this->con->executeQuery($query);

        if ($this->con->numRows === 0) {
            return [];
        }
        $objetoAtual = get_class($this->object);
        $con = Connection::getConnection();
        $nsEnt = new $objetoAtual();
        while ($dd = $this->con->next()) {
            $entitie = clone($nsEnt);
            $entitie->populate($dd);
            //new $objetoAtual($dd);
            // relacionamnetos
            foreach ($relacoes as $relacao) {
                $entidade = ucwords(Helper::name2CamelCase($relacao['tabela']));
                if (!$$entidade) {
                    $$entidade = new $entidade();
                }
                $newEntitie = clone($$entidade);
                $newEntitie->populate($dd);
                // especifico para municipio, mostrar a UF. 3 nivel de relacionamento
                if ($entidade === 'Municipio' && (int) $dd['id_uf'] > 0) {
                    //$con = Connection::getConnection();
                    $con->executeQuery('select * from app_uf where id_uf= ' . $dd['id_uf']);
                    $uf = new Uf($con->next());
                    $newEntitie->setUf($uf);
                }
                // Especifico para Pessoa, mostrar o avatar
                if ($entidade === 'Pessoa' && (int) $dd['idUploadfile'] > 0) {
                    //$con = Connection::getConnection();
                    $con->executeQuery('select * from uploadfile where id_uploadfile= ' . $dd['idUploadfile']);
                    $up = new Uploadfile($con->next());
                    $newEntitie->setUploadfile($up);
                }
                $set = 'set' . $entidade;
                $entitie->$set($newEntitie);
            }
            // contador de arquivos em uploadfile
            $entitie->countUploadfile = (int) (($dd['countuploadfile']) ? $dd['countuploadfile'] : 0);
            $entitie->selectExtra = $dd['selectextra'];
            $entitie->selectExtraB = $dd['selectextrab'];

            //Log::logTxt('debug', "$objetoAtual =  VALOR DE UPLOADFILE CONTAR: " . $entitie->countUploadfile);

            $entities[] = $entitie;
        }
        $this->setInnerOrLeftJoin(); // reset para manter o padrão a cada consulta
        $this->selectExtra = false; // para manter reset a cada consulta
        $this->selectExtraB = false; // para manter reset a cada consulta
        return ((is_array($entities)) ? $entities : array());
    }

    /**
     * Alterado em 30/05/2018, por eficiencia e manutenção facilitada
     * @param array $condicao
     * @param string $tabelaPrincipal
     * @return string
     */
    public static function trataCondicao($condicao, $tabelaPrincipal) {
        if (!$condicao) {
            return '';
        }
        $where = [];
        if (!is_array($condicao)) {
            $where[] = $condicao;
            $condicao = [];
        }
        foreach ($condicao as $key => $val) {
            $key = explode('_', $key)[0];
            // tratamento da key: caso não venha palavras, tratar com revertCamelCase
            $unaccent = ((stripos($key, 'unaccent') === false) ? false : 'unaccent');
            $upper = ((stripos($key, 'upper') === false) ? false : 'upper');
            $entidadeDefinida = ((stripos($key, '.') === false) ? false : 'upper');
            $f = '%s'; // funcao a ser aplicada na var
            if (!$unaccent && !$upper && !$entidadeDefinida) { // se não vier funcao nenhuma, apenas manter o padrão
                $key = $tabelaPrincipal . '.' . Helper::reverteName2CamelCase($key);
            } else {
                // upper(unaccent(loginUsuario))
                // Obter o nome do campo dentro de parentes, reveter e alterar na string
                $fn = explode('(', $key);
                $field = str_replace(')', '', $fn[count($fn) - 1]);
                $key = str_replace($field, Helper::reverteName2CamelCase($field), $key);
                unset($fn[count($fn) - 1]);
                // funcoes enviadas em key
                foreach ($fn as $value) {
                    $fecha .= ')';
                    $abre .= $value . '(';
                }
                $f = $abre . '%s' . $fecha;
            }

            // configurações para tipo de banco de dados (operadores)
            if (is_array($val) && Config::getData('database', 'type') === 'mysql') {
                $val[0] = str_replace('~*', 'regexp', $val[0]);
            }

            // tratamento da val: 1:
            $where[] = ((is_array($val)) ? "$key $val[0] $val[1]" : $key . '=' . ((gettype($val) === 'integer') ? $val : sprintf($f, "'$val'")));
        }
        return " WHERE " . implode(" AND ", $where);
    }

    public function getMaxId($condicao = false, $opcao = 'MAX') {
        $nomeEntidade = get_class($this->object);
        if ($condicao) {
            $where = ' WHERE ' . $condicao;
        }
        $this->con->executeQuery("SELECT " . $opcao . "(" . Helper::reverteName2CamelCase($this->object->getCpoId()) . ") FROM " . $this->object->getTable() . $where);
        if ($this->con->numRows == 0) {
            return false;
        }
        while ($dd = $this->con->next()) {
            $entitie = $this->getById($dd[Helper::reverteName2CamelCase($this->object->getCpoId())]);
        }
        return $entitie;
    }

    public function getMinId($condicao = false) {
        return $this->getMaxId($condicao, 'MIN');
    }

    public static function getByIdStatic($objeto, $pk) {
        $temp = new EntityManager($objeto);
        return $temp->getById($pk, false);
    }

    public function getById($pk, $relacao = true, $dd = false) {
        return $this->getAll([$this->object->getCpoId() => (int) $pk], $relacao)[0];

        /*

          $nomeEntidade = get_class($this->object);
          if (!$dd) {
          $this->con->executeQuery("SELECT * FROM " . $this->object->getTable() . " WHERE " . Helper::reverteName2CamelCase($this->object->getCpoId()) . "= " . $pk);
          if ($this->con->numRows == 0) {
          return false;
          }
          while ($dd = $this->con->next()) {
          $entitie = new $nomeEntidade();
          foreach ($dd as $key => $val) {
          $field = "set" . ucwords(Helper::name2CamelCase($key));
          $entitie->$field($val);
          }
          }
          } else {
          $entitie = new $nomeEntidade();
          foreach ($dd as $key => $val) {
          $field = "set" . ucwords(Helper::name2CamelCase($key));
          if (method_exists(get_class($this->object), $field)) {
          $entitie->$field($val);
          }
          }
          }

          // Relacionamnetos
          if ($relacao === true && method_exists(get_class($this->object), 'getRelacionamentos')) {
          Log::logTxt('debug', 'Obter relacionamentos');
          $relacoes = $this->object->getRelacionamentos();
          if (is_array($relacoes)) {
          foreach ($relacoes as $relacao) {
          foreach ($relacao as $key => $val)
          $$key = $val;

          // obter nome da entidade
          $table = str_replace("sis_", "", str_replace("mem_", "", $tabela));
          $tabela = ucwords(Helper::name2CamelCase($table));

          // criar objeto relacionado
          //$objetoRelacao = new $tabela();
          // popular objeto relacionado
          $t = $tabela . 'Controller';
          $em = new $t;
          $functionGetCampoOrigem = 'get' . Helper::name2CamelCase($cpoOrigem);
          Log::logTxt('debug', $functionGetCampoOrigem);
          $pk = $entitie->$functionGetCampoOrigem();
          $objetoRelacao = (($pk > 0) ? $em->getById($objetoRelacao, $pk, true) : new $tabela());

          // setar propriedade da entidade pesquisada
          $functionSetPropriedade = 'set' . $tabela;
          $entitie->$functionSetPropriedade($objetoRelacao);
          }
          }
          }
          return $entitie;
         * 
         */
    }

    public function execQueryAndReturn($query, $log = true) {
        $out = [];
        $this->con->executeQuery($query, $log);
        while ($dd = $this->con->next()) {
            $out[] = Helper::name2CamelCase($dd);
        }
        return $out;
    }

    function getQuery() {
        return $this->query;
    }

    public function count($condicao) {
        $tabela = $this->object->getTable();
        $query = 'select count(' . Helper::reverteName2CamelCase($this->object->getCpoId()) . ') as qtde '
                . ' from ' . $tabela . ' ' . $this->trataCondicao($condicao, $tabela);
        return (int) $this->execQueryAndReturn($query)[0]['qtde'];
    }

}
