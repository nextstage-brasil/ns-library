<?php

namespace NsLibrary\Entities;

use NsLibrary\Controller\Controller;
use NsLibrary\Controller\EntityManager;
use NsUtil\Api;
use NsUtil\Exceptions\ModelNotFoundException;
use NsUtil\Helper;

use function NsUtil\json_decode;

abstract class AbstractEntity
{
    protected $error; // armazena possiveis erros, inclusive, obrigatoriedades.
    // protected $schema;
    protected $table;
    protected $cpoId;
    protected $dao = null;
    protected array $relacoes = [];
    public $selectExtra = null;
    public static $externalDao = null;

    public function __construct(string $table, string $cpoId, array $relacionamentos, ?EntityManagerInterface $dao = null)
    {
        $this->table = $table;
        $this->cpoId = $cpoId;
        $this->relacoes = $relacionamentos;
        // $this->schema = explode(".", $this->table)[0];
        if (null !== $dao) {
            $this->setExternalDao($dao);
        }
    }

    public function init($dd = [])
    {
        return $this;
    }

    public function setExternalDao(EntityManagerInterface $dao)
    {
        $this->dao = $dao;
        $this->dao->setObject($this);
    }

    protected function setDao()
    {
        if ($this->dao === null) {
            $this->dao = null !== self::$externalDao
                ? new self::$externalDao($this)
                : new EntityManager($this);
        }
    }

    public function __destruct()
    {
        if ($this->dao) {
            unset($this->dao);
        }
    }

    /** 
     * Marca a proxima transação select para bloquear a linha até seu update
     * 
     */
    public function setLockedForUpdate()
    {
        $this->setDao();
        $this->dao->setLockForUpdate();
        $this->dao->setInnerOrLeftJoin("inner");
        return $this;
    }

    protected function getItem($key, $format)
    {
        if ($format === null) {
            return $this->{$key};
        } else if ($format === 'json') {
            return json_decode($this->{$key});
        } else if ($format === 'array') {
            return json_decode($this->{$key}, true);
        } else {
            throw new \Exception('Format "$format" is invalid');
        }
    }

    // Metodos obrigatório pois EntityManager depende deles 

    public function getId()
    {
        return $this->{$this->cpoId};
    }

    public function setId($id)
    {
        $this->{$this->cpoId} = (int) $id;
        return $this;
    }

    public function setError($error)
    {
        if ($error === false) {
            $this->error = [];
            return $this;
        }

        if (is_string($error)) {
            $error = [$error];
        }
        $this->error = (array) $error;
        return $this;
    }

    public function getError()
    {
        if (is_array($this->error)) {
            if (count($this->error) === 0) {
                return false;
            }
        }
        return $this->error;
    }

    public function getErrorToString()
    {
        if (is_array($this->getError())) {
            return implode(",", $this->getError());
        } else {
            return $this->getError();
        }
    }

    public function getTable()
    {
        return $this->table;
    }

    public function getCpoId()
    {
        return $this->cpoId;
    }


    /**
     *
     * @param int $code
     * @return void
     */
    public function responseIfHasError(int $code = 200)
    {
        if ($this->getError() !== false) {
            Api::result($code, ['error' => $this->getError()]);
        }
    }

    /**
     * Define o schema do objeto
     *
     * @param string $schema
     * @return void
     */
    public function setSchema($schema)
    {
        $t = explode(".", $this->table);
        $table = array_pop($t);
        $this->table = "$schema.$table";
        // $this->schema = $schema;
        return $this;
    }

    public function getSchema()
    {
        return explode(".", $this->table)[0] ?? 'public';
    }

    public function getTablenameWithoutSchema()
    {
        $ret = explode('.', $this->table);
        $table = array_pop($ret);
        return $table;
    }

    /**
     * List of entities
     *
     * @param array $filters
     * @param integer $page
     * @param integer $limit
     * @param mixed $order
     * @param boolean $returnObjects
     * @return array
     */
    public function list(array $filters = [], int $page = 0, int $limit = 1000, $order = false): array
    {
        $this->setDao();
        if ($order !== false) {
            if (is_array($order)) {
                $order = Helper::reverteName2CamelCase($order['0']) . ' ' . $order[1];
            }
            $this->dao->setOrder($order);
        }
        return (array) $this->dao->getAll($filters, true, $page, $limit);
    }

    /**
     * Executa a busca de um item pelo ID da tabela 
     *
     * @param int $id
     * @return static
     */
    public function read($id)
    {
        $ret = $this->list([$this->cpoId => (int) $id])[0] ?? null;
        if ($ret instanceof $this) {
            $dd = (new Controller())->objectToArray($ret);
            $this->init($dd);
        } else {
            $this->setError("ID not found '$id'");
        }
        return $this;
    }

    public function firstOrFail($param)
    {
        if (is_array($param)) {
            $item = $this->list($param)[0];
        } else {
            $item = $this->list([$this->cpoId => (int) $param])[0] ?? null;
        }

        if (!($item instanceof $this)) {
            throw new ModelNotFoundException("Not found", 404);
        }

        $dd = (new Controller())->objectToArray($item);
        $this->init($dd);

        return $this;
    }

    public function onError(\Closure $fn)
    {
        if ($this->getError() !== false) {
            return call_user_func($fn, $this->getError());
        }

        return $this;
    }

    /**
     * Persiste o objeto
     *
     * @param string $onConflict
     * @return static
     */
    public function save($onConflict = "")
    {
        $this->setDao();
        $parts = explode('\\', get_class($this));
        $updateName = 'setUpdatedAt' . array_pop($parts);
        if (method_exists($this, $updateName)) {
            $this->$updateName('NOW');
        }
        $ret = $this->dao->setObject($this)->save($onConflict);
        if ($ret->getError() !== false) {
            $this->setError($ret->getError());
        }
        return $this;
    }

    /**
     * Conta os itens conforme parametros
     *
     * @param array $filters
     * @return integer
     */
    public function count(array $filters = []): int
    {
        $this->setDao();
        return (int) $this->dao->count($filters);
    }

    /**
     * Retorna um objeto para ser anexado com padrões de paginacao
     *
     * @param array $filters
     * @return array
     */
    public function getPagination($atualPage, $limitPerPage,  $filters = []): array
    {
        return Helper::pagination(
            $atualPage,
            $limitPerPage,
            $this->count($filters)
        );
    }

    /**
     * Remove um objeto
     *
     * @return bool|string
     */
    public function remove()
    {
        $this->setDao();
        $ret = $this->dao->setObject($this)->remove();
        if ($ret === true) {
            $this->init([]);
        }
        return $ret;
    }

    /**
     * Undocumented function
     *
     * @return array
     */
    public function toArray($showRelations = true)
    {
        return (new Controller())->objectToArray($this, $showRelations);
    }

    /**
     * Popula o objeto com os dados em DD conforme campos
     *
     * @param ?array $dd Data to create model
     * @return void
     */
    public function populate($dd)
    {
        if (is_array($dd)) {
            $rel = ["setId", "setError"];
            $methods = get_class_methods($this);
            if (method_exists($this, "getRelacionamentos")) {
                $relacionamentos = $this->getRelacionamentos();
                foreach ($relacionamentos as $value) {
                    $entidade = ucwords(Helper::name2CamelCase($value["tabela"]));
                    $rel[] = "set$entidade";
                    unset($methods["set$entidade"]);
                }
            }
            foreach ($methods as $set) {
                if (array_search($set, $rel)) { // se encontrar, pular pq já foi setado anteriormente
                    continue;
                }
                if (mb_substr((string)$set, 0, 3) === "set") {
                    $file = lcfirst(mb_substr((string)$set, 3, 300));

                    // $dd[$file] = ((!isset($dd[$file])) ? $dd[Helper::reverteName2CamelCase($file)] : $dd[$file]);
                    if (!isset($dd[$file])) {
                        $dd[$file] = ((isset($dd[Helper::reverteName2CamelCase($file)])) ? $dd[Helper::reverteName2CamelCase($file)] : null);
                    }
                    if (isset($dd[$file])) {
                        $this->$set($dd[$file]);
                    }
                }
            }
        }
    }

    // metodo para retornar os campos de relacionamento entre as entidades
    public function getRelacionamentos()
    {
        return $this->relacoes;
    }

    public function addRelacionamento($tabela, $campoNaTabelaReferenciada = "", $campoNestaEntidade = "")
    {
        $schema = "public";;
        if (!is_array($tabela)) {
            if (strpos($tabela, ".") !== false) {
                $parts = explode(".", $tabela);
                $schema = $parts[0];
                $tabela = $parts[1];
            }
            $array = ["tabela" => $tabela, "schema" => $schema, "cpoRelacao" => $campoNaTabelaReferenciada, "cpoOrigem" => $campoNestaEntidade];
        } else {
            $array = $tabela;
        }
        $this->relacoes[] = $array;

        return $this;
    }
}
