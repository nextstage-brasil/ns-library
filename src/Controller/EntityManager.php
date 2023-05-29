<?php

namespace NsLibrary\Controller;

use Exception;
use NsUtil\Log;
use NsUtil\Helper;
use ReflectionClass;
use NsLibrary\Config;
use NsLibrary\Connection;
use NsLibrary\Exceptions\ModelNotFoundException;

/**
 * Description of EntityManager
 *
 * @author NextStage
 */
class EntityManager
{

    private $object;
    private $message;
    public $con;
    private $order;
    private $countUploadfile;
    private $innerOrLeftJoin; // para definir se a consulta será por left ou inner join
    public $selectExtra, $selectExtraB; // serve para injetar um select extra. será colocado entre parenteses e zerado a cada chamada;
    private $groupBy;
    private $query;

    public function __construct($object)
    {
        $this->object = $object;
        $this->order = false;
        $this->message = '';
        $this->con = Connection::getConnection();
        $this->setInnerOrLeftJoin();
    }

    public function __destruct()
    {
        try {
            $this->con->close();
        } catch (Exception $exc) {
        }
    }

    function setOrder($orderBy)
    {
        if ($orderBy && stripos($orderBy, '.') === false) { // não veio tabela principal
            $orderBy = $this->object->getTable() . '.' . $orderBy;
        }
        $this->order = $orderBy;
        return $this;
    }

    function setGroupBy($campos)
    {
        $out = [];
        foreach ($campos as $campo) {
            $f = Helper::reverteName2CamelCase($campo);
            $out[] = $f;
        }
        $this->groupBy = implode(', ', $out);
        return $this;
    }

    function setInnerOrLeftJoin($innerOrLeftJoin = 'left')
    {
        $this->innerOrLeftJoin = (($innerOrLeftJoin === 'left') ? 'left' : 'inner');
        return $this;
    }

    public function setCountUploadfile($switch)
    {
        $this->countUploadfile = (bool) $switch;
        return $this;
    }

    public function setObject($object)
    {
        $this->object = $object;
        $this->order = false;
        return $this;
    }

    public function beginTransaction()
    {
        $this->con->begin_transaction();
        return $this;
    }

    public function commit()
    {
        $this->con->commit();
        return $this;
    }

    public function rollback()
    {
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
    public function save($onConflict = '')
    {
        if ($this->object->getError() !== false) {
            //Log::log('entityManager-error', $this->object->getTable() . ': ' . json_encode($this->object->getError()));
            return $this->object;
        }
        $tabela = $this->object->getTable();
        try {
            //$app = new AppLibraryController();
            $api = new ReflectionClass(get_class($this->object));

            //$objectOld = $this->getAll([$this->object->getCpoId() => $this->object->getId()], false, 0, 1)[0];

            $subs = ['Property [', 'private $', ']', ' ', '<default>'];
            foreach ($api->getProperties() as $atributoOriginal) {

                //                $atributo = (string) trim(str_replace($subs, '', $atributoOriginal));
                $atributo = $atributoOriginal->getName(); // (string) trim(str_replace($subs, '', $atributoOriginal));
                // nao salvar createtime
                if (strpos(strtolower($atributo), 'createtime') !== false) {
                    continue;
                }
                // nao salvar campo id
                if ($this->object->getCpoId() === $atributo && $this->object->getId() === 0) {
                    continue;
                }

                $functionGet = 'get' . Helper::name2CamelCase(ucwords($atributo));
                $atributosAIgnorar = ['dao', 'relacoes', 'error', 'table', 'cpoId', $this->object->getCpoId()];
                if (method_exists($this->object, $functionGet) && false === (bool) array_search($atributo, $atributosAIgnorar) && substr((string) $atributo, -8) != "Detalhes") {
                    $val = $this->object->$functionGet();
                    $type = gettype($val);
                    if ($type === 'object') {
                        continue;
                    }
                    $atributo = Helper::reverteName2CamelCase($atributo);

                    // alteração para prepare query
                    $preparedValues[$atributo] = $val;
                }
            }
            try {
                // auditoria onsave
                //$this->auditoria();

                if ($this->object->getId() > 0) {
                    $preparedValues[Helper::reverteName2CamelCase($this->object->getCpoId())] = $this->object->getId();
                    $this->con->update($tabela, $preparedValues, Helper::reverteName2CamelCase($this->object->getCpoId()));
                    $auditoria = 'Atualizar';
                } else {
                    $auditoria = 'Inserir';
                    $this->con->insert($tabela, $preparedValues, Helper::reverteName2CamelCase($this->object->getCpoId()), $onConflict);
                    $dd = $this->con->next();
                    $this->object->setId($dd['nsnovoid']);
                }
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
     * Ira gravar log com as diferencas entre arrays
     */
    private function auditoria()
    {
        if (array_search(get_class($this->object), ['ApiLog']) === false) {
            $app = new Controller();
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
            $file = Helper::getPathApp() . '/_NSUtilLogs/auditoria.log';
            Log::logTxt(
                'auditoria',
                json_encode([
                    'entity' => get_class($this->object),
                    'id' => $this->object->getId(),
                    'type' => $tipo,
                    'diff' => $diff,
                ])
            );
        }
    }



    /**
     * Ira aplicar a condição a chave "is_alive" para a entidade, caso esta condição exista
     *
     * @param array $condicao
     * @return object
     */
    public function setCondicaoIsAlive(&$condicao = []): object
    {
        // Tratamento da condição is_alive
        $isAliveMethod = 'getIsAlive' . get_class($this->object);
        $methodExists = method_exists($this->object, $isAliveMethod);
        if ($methodExists) {
            if (is_array($condicao)) {
                $condicao['isAlive' . get_class($this->object) . '_getall'] = 'true';
            } else {
                $condicao .= " and isAlive" . get_class($this->object) . " = 'true'";
            }
        }
        $out = (object) [
            'exists' => $methodExists,
            'get' => $isAliveMethod,
            'set' => 'setIsAlive' . get_class($this->object),
            'field' => 'is_alive_' . get_class($this->object),
            'fieldNome' => 'nome_' . get_class($this->object)
        ];
        if (method_exists($this->object, 'getNome' . get_class($this->object))) {
            $out->fieldName = 'nome_' . get_class($this->object);
        }
        return $out;
    }

    public function remove()
    {
        try {
            $alive = $this->setCondicaoIsAlive();
            if ($alive->exists) {
                $query = "UPDATE "
                    . $this->object->getTable()
                    . " SET " . $alive->field . "= 'false'"
                    . (($alive->fieldNome) ? ", $alive->fieldNome= $alive->fieldNome || ' (Removido)'" : "")
                    . " WHERE " . Helper::reverteName2CamelCase($this->object->getCpoId()) . "= " . $this->object->getId()
                    . " RETURNING " . Helper::reverteName2CamelCase($this->object->getCpoId());
            } else {
                $query = "DELETE FROM "
                    . $this->object->getTable()
                    . " WHERE " . Helper::reverteName2CamelCase($this->object->getCpoId()) . "= " . $this->object->getId()
                    . " RETURNING " . Helper::reverteName2CamelCase($this->object->getCpoId());
            }
            # ------------------------------------------------------------------------

            $this->con->executeQuery($query);
            $res = $this->con->next();
            $result = (bool) $res[Helper::reverteName2CamelCase($this->object->getCpoId())] > 0;
            return $result;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    // Faz  aleitura em config de erros conhecidos e traduz para o ambiente
    public static function getErrorByConfig($msg)
    {
        foreach (Config::getData('errors') as $chave => $value) {
            if (stripos($msg, $chave) > -1) {
                return $value;
            }
        }
        return $msg;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function getObject()
    {
        return $this->object;
    }

    public function getByCondition($condicao, $getRelacoes = false)
    {
        if (is_array($condicao)) {
            $entities = $this->getAll($condicao, $getRelacoes);
            return $entities;
        }
    }

    /**
     * Lista de itens no banco de dados
     * @param type $condicao
     * @param type $getRelacoes
     * @param type $inicio
     * @param type $limit
     * @param type $relacaoExceto
     * @return type
     */
    public function getAll($condicao, $getRelacoes = true, $inicio = 0, $limit = 1000, $relacaoExceto = array())
    {
        //$relacaoExceto = array_merge(array('usuario'), $relacaoExceto);
        $tabelaPrincipal = $this->object->getTable();
        $condicao = self::trataCondicao($condicao, $tabelaPrincipal);
        //$order = $tabelaPrincipal . '.' . (($this->order) ? $this->order : Helper::reverteName2CamelCase($this->object->getCpoId()) . ' ASC');
        $order = (($this->order) ? $this->order : $tabelaPrincipal . '.' . Helper::reverteName2CamelCase($this->object->getCpoId()) . ' ASC');

        // select extra, definido ou não
        if ($this->countUploadfile) {
            $select[] = '(select count(id_uploadfile) from app_uploadfile '
                . 'where entidade_uploadfile= \'' . mb_strtoupper($tabelaPrincipal) . '\' '
                . 'and valorid_uploadfile= ' . $tabelaPrincipal . '.id_' . $tabelaPrincipal . ') as countuploadfile';
            $this->countUploadfile = false;
        }

        // groupBy
        if (strlen((string) $this->groupBy) > 2) {
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
                    $select[] = "$relacao[schema].$relacao[tabela].*";
                    $innerJoin[] = $this->innerOrLeftJoin . " JOIN $relacao[schema].$relacao[tabela] ON $relacao[tabela].$relacao[cpoRelacao] = $tabelaPrincipal.$relacao[cpoOrigem]";
                }
            }
        }

        $query = 'SELECT ' . implode(', ', $select) . ' FROM ' . $tabelaPrincipal . ' ' . implode(' ', $innerJoin);

        $limitCleaned = (($limit > 0) ? (int) $limit : 'null');
        $query .= $condicao
            . ($limit > 0
                ? " ORDER BY " . $order . " LIMIT " . (int) $limit . " OFFSET " . $inicio * $limit
                : " ORDER BY " . $order);

        $this->query = $query;

        $this->con->executeQuery($query);

        if ($this->con->numRows === 0) {
            return [];
        }
        $objetoAtual = get_class($this->object);
        $con = Connection::getConnection();
        $nsEnt = new $objetoAtual();
        while ($dd = $this->con->next()) {
            $entitie = clone ($nsEnt);
            $entitie->populate($dd);
            //new $objetoAtual($dd);
            // relacionamnetos
            foreach ($relacoes as $relacao) {
                $entidade = ucwords(Helper::name2CamelCase($relacao['tabela']));
                if (!isset($$entidade)) {
                    if (class_exists($entidade)) {
                        $$entidade = new $entidade();
                    } else {
                        $namespace = Config::getData('psr4Name') . '\\NsLibrary\\Entities\\' . (($relacao['schema'] === 'public') ? '' : ucwords($relacao['schema']) . '\\') . $entidade;
                        $$entidade = new $namespace();
                    }
                }
                $newEntitie = clone ($$entidade);
                $newEntitie->populate($dd);

                // Caso a adição de relacionamento tenha sido feito manualmente, apenas setar o valor da propriedade
                $set = 'set' . $entidade;
                if (method_exists($entitie, $set)) {
                    $entitie->$set($newEntitie);
                } else {
                    $entitie->$entidade = $newEntitie;
                }
            }
            // contador de arquivos em uploadfile
            $entitie->countUploadfile = (int) ((isset($dd['countuploadfile'])) ? $dd['countuploadfile'] : 0);
            $entitie->selectExtra = ((isset($dd['selectextra'])) ? $dd['selectextra'] : null);
            $entitie->selectExtraB = ((isset($dd['selectextrab'])) ? $dd['selectextrab'] : null);

            //Log::logTxt('debug', "$objetoAtual =  VALOR DE UPLOADFILE CONTAR: " . $entitie->countUploadfile);

            $entities[] = $entitie;
        }
        $this->setInnerOrLeftJoin(); // reset para manter o padrão a cada consulta
        $this->selectExtra = false; // para manter reset a cada consulta
        $this->selectExtraB = false; // para manter reset a cada consulta
        $list = ((is_array($entities)) ? $entities : array());

        return $list;
    }

    /**
     * Alterado em 30/05/2018, por eficiencia e manutenção facilitada
     * @param array $condicao
     * @param string $tabelaPrincipal
     * @return string
     */
    public static function trataCondicao($condicao, $tabelaPrincipal)
    {
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

            switch (true) {
                case stripos($key, 'upper') !== false:
                    $upper = 'upper';
                    break;
                case stripos($key, 'lower') !== false:
                    $upper = 'lower';
                    break;
                default:
                    $upper = false;
                    break;
            }

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
                $abre = $fecha = '';
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

    public function getMaxId($condicao = false, $opcao = 'MAX')
    {
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

    public function getMinId($condicao = false)
    {
        return $this->getMaxId($condicao, 'MIN');
    }

    public static function getByIdStatic($objeto, $pk)
    {
        $temp = new EntityManager($objeto);
        return $temp->getById($pk, false);
    }

    public function getById($pk, $relacao = true, $dd = false)
    {
        return $this->getAll([$this->object->getCpoId() => $pk], $relacao)[0];
    }

    public function findOrFail($pk)
    {
        $item = $this->getById($pk);
        if ($item && get_class($item) === get_class($this->object)) {
            return $item;
        }

        throw new ModelNotFoundException("ID $pk not found");
    }

    public function execQueryAndReturn($query, $log = true)
    {
        $out = [];
        $this->con->executeQuery($query, $log);
        while ($dd = $this->con->next()) {
            $out[] = Helper::name2CamelCase($dd);
        }
        return $out;
    }

    function getQuery()
    {
        return $this->query;
    }

    public function count($condicao)
    {
        $tabela = $this->object->getTable();
        $query = 'select count(' . Helper::reverteName2CamelCase($this->object->getCpoId()) . ') as qtde '
            . ' from ' . $tabela . ' ' . $this->trataCondicao($condicao, $tabela);
        return (int) $this->execQueryAndReturn($query)[0]['qtde'];
    }
}
