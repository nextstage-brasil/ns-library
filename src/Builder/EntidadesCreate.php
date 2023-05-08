<?php

namespace NsLibrary\Builder;

use NsLibrary\Config;
use NsLibrary\Controller\ModelSetterDefault;
use NsUtil\Helper;
use NsUtil\Template;

class EntidadesCreate
{

    private static $namespace;

    public static function save($dados, $entidade)
    {
        ### Criação de entidade
        $template = self::get($dados);
        $file = Config::getData('pathEntidades')
            . DIRECTORY_SEPARATOR
            . ((self::$namespace) ? self::$namespace . DIRECTORY_SEPARATOR : '')
            . $entidade
            . '.php';
        Helper::saveFile($file, false, $template, 'SOBREPOR');
        return $file;
    }

    public static function get($dados)
    {
        $dados['relacionamentos'] = ((isset($dados['relacionamentos']) && is_array($dados['relacionamentos'])) ? $dados['relacionamentos'] : []);
        $schema = $dados['schema'] ?? 'public';
        self::$namespace = (($schema === 'public') ? null : ucwords($schema));
        $out = '<?php
            
            namespace ' . Config::getData('psr4Name') . '\NsLibrary\Entities' . ((self::$namespace) ? '\\' . self::$namespace : '') . ';
            use NsUtil\Helper;
            use NsLibrary\Controller\Controller;
            use NsLibrary\Controller\EntityManager;
            use NsLibrary\Controller\ModelSetterDefault;

/** Created by NsLibrary Framework **/
if (!defined("SISTEMA_LIBRARY")) {die("' . $dados['entidade'] . ': Direct access not allowed. Define the SISTEMA_LIBRARY contant to use this class.");}               
class ' . $dados['entidade'] . '{

private $error; // armazena possiveis erros, inclusive, obrigatoriedades.
private $table = "' . ($dados['schemaTable'] ?? 'var schemaTable is not defined!!') . '";
private $cpoId = "' . $dados['cpoID'] . '";
private $dao = null;
private $relacoes = [' . implode(", ", $dados['relacionamentos']) . '];
public $selectExtra = null;
';

        // caso já exista um campo chamado ID, o setId e getId deve ser removido
        $getSetDefault = self::$getterSetterPadrao;
        foreach ($dados['atributos'] as $val) {
            if ($val['nome'] === 'id') {
                $getSetDefault = str_replace(['public function setId($id)', 'public function getId()'], ['private function LIBRARYsetId($id)', 'private function LIBRARYgetId()'], self::$getterSetterPadrao);
                break;
            }
        }

        $getSet[] = (new Template($getSetDefault, array('cpoID' => $dados['cpoID']), '%', '%'))->render();

        foreach ($dados['atributos'] as $val) {
            $val['valorPadrao'] = str_replace("::date", "", $val['valorPadrao']);
            $val['valorPadrao'] = str_replace('::timestamp without time zone', '', $val['valorPadrao']);
            $val['nomeFunction'] = ucwords($val['nome']);

            // tratamento para CE - alterar o nome da function para id ao inves de ce
            $terceiraLetra = mb_substr((string) $val['nome'], 2, 1);
            if (mb_substr((string) $val['nome'], 0, 2) === 'ce' && Helper::compareString(strtoupper($terceiraLetra), $terceiraLetra, true)) {
                $val['nomeFunction'] = 'Id' . mb_substr((string) $val['nome'], 2);
            }

            $val['coments'] = ucfirst($val['coments']);
            $val['notnull'] = $dados['cpoID'] === $val['nome']
                ? false
                : ($val['notnull'] ?? false);

            $val['relacionamentos'] = $val['relacionamentos'] ?? null;

            $val['upper'] = ''; /// retirei pois o upper deixa o layout horrivel
            $val['USER'] = ((Helper::compareString('idusuario', $val['nome']) && !Helper::compareString('usuario', $dados['tabela'])) ? '$idUsuario = (($idUsuario) ? $idUsuario : $_SESSION[\'user\'][\'id_pessoa\']);' : ''); // protegendo para que todos aparceeam clean, somente user

            // Tratamento especifico para campos tipo HTML:
            if (stripos($val['column_name'], '_html_') !== false) {
                $template = ModelSetterDefault::getTemplate('html');
            } else {

                switch ($val['tipo']) {
                    case 'OBJECT':
                        $template = ModelSetterDefault::getTemplateObject();
                        $val['nome'] = ucwords($val['nome']);
                        $val['valorPadrao'] = '$dd';
                        break;
                    case 'EXTERNA':
                        $template = ModelSetterDefault::getTemplateExterna();
                        $val['nome'] = mb_substr((string) $val['nome'], 2);
                        $val['nomeFunction'] = ucwords($val['nome']);
                        $val['valorPadrao'] = '$dd';
                        break;
                    case 'string':
                    case 'text':
                    case 'json':
                    case 'jsonb':
                    case 'boolean':
                    case 'timestamp':
                    case 'datetime':
                    case 'date':
                    case 'double':
                    case 'int':
                    case 'tsvector':
                        $template = ModelSetterDefault::getTemplate($val['tipo']);
                        break;
                    default:
                        $template = ModelSetterDefault::getTemplate('NOT_IMPLEMENTED: ' . $val['tipo']);
                }
            }


            $val['notnull'] = (($val['notnull'] === true) ? "true" : "false");

            // propriedades
            $propriedades[] = 'private $' . $val['nome'] . ';';

            // $template = utf8_encode($template);
            $getSet[] = (new Template($template, $val, '%', '%'))->render();
            $constructSet[] = (new Template(self::$setterConstruct, $val, '%', '%'))->render();
        }

        $construct = '
               public function __construct($dd=false)  {
                   $this->init($dd);
               }
               
private function init($dd)  {
 $this->error = [];
' . implode('  ', $constructSet) . '
$this->populate($dd);
}

private function setDao() {
    if ($this->dao === null)  {
        $this->dao = new EntityManager($this);
    }
}

public function __destruct() {
    if ($this->dao)  {
        unset($this->dao);
    }
}

/**
 *
 * @param int $code
 * @return void
 */
public function responseIfHasError($code = 200) {
    if ($this->getError() !== false) {
        \NsUtil\Api::result($code, [\'error\' => $this->getError()]);
    }
}

/**
 * Define o schema do objeto
 *
 * @param string $schema
 * @return void
 */
    public function setSchema($schema) {
        $t = explode(".", $this->table);
        $table = array_pop($t);
        $this->table = "$schema.$table";
        //echo $this->table;
        return $this;
    }

/**
 * Executa a busca de um item pelo ID da tabela 
 *
* @param type $id
* @return self
 */
public function read($id) {
    $ret = $this->list([$this->cpoId => (int) $id])[0];
    if ($ret instanceof $this)  {
        $dd = (new Controller())->objectToArray($ret);
        $this->populate($dd);
    } else {
        $this->setError("ID not found \'$id\'");
    }
    return $this;
}

/**
    * Obtém a lista de entidades. 
     * @param array $filters Array contendo chave=>valor para filtro no banco. Utilizar camelcase para nome dos campos, ex.: nomeUsuario=>"Teste"
     * @param int $page Paginação
     * @param int $limit Limite de resultados por busca
     * @param array $order Array contendo dois campos: 0: chave para ordenar, 1 : sort. Ex.: ["nomePessoa", "asc"] 
     * @return array    
*/
public function list(array $filters=[], int $page=0, int $limit=1000, $order=false) : array   {
        $this->setDao();    
        if ($order !== false) {
            if (is_array($order)) {
                $order = Helper::reverteName2CamelCase($order[\'0\']) . \' \' . $order[1];
            }
            $this->dao->setOrder($order);
        }
    return (array) $this->dao->getAll($filters, true, $page, $limit);
}

/**
 * Persiste o objeto
 *
 * @param string $onConflict
 * @return self
 */
public function save($onConflict = "") : self {
    $this->setDao();
    $parts = explode(\'\\\\\', get_class($this));
    $updateName = \'setUpdatedAt\' . array_pop($parts);
    if (method_exists($this, $updateName))   {
        $this->$updateName(\'NOW\');
    }
    $ret = $this->dao->setObject($this)->save($onConflict);
    if ($ret->getError() !== false)   {
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
public function count(array $filters=[]) : int   {
    $this->setDao();    
    return (int) $this->dao->count($filters);
}

/**
 * Retorna um objeto para ser anexado com padrões de paginacao
 *
 * @param array $filters
 * @return array
 */
public function getPagination($atualPage, $limitPerPage,  $filters = []): array {
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
public function remove() {
    $this->setDao();
    $ret = $this->dao->setObject($this)->remove();
    if ($ret === true)   {
        $this->init([]);
    }
    return $ret;
}

/**
 * Undocumented function
 *
 * @return array
 */
public function toArray() {
    return (new Controller())->objectToArray($this);
}
    
/**
 * Popula o objeto com os dados em DD conforme campos
 *
 * @param [type] $dd
 * @return void
 */
public function populate($dd)  {
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
                        $dd[$file] = ((isset($dd[Helper::reverteName2CamelCase($file)]))?$dd[Helper::reverteName2CamelCase($file)]:null);
                    }
                    if (isset($dd[$file])) {
                        $this->$set($dd[$file]);
                    }
                }
            }
        }
}';

        $rel = '
                // metodo para retornar os campos de relacionamento entre as entidades
        public function getRelacionamentos()   {
            return $this->relacoes;
        }
        public static function getRelacionamentosStatic()   {
            return (new ' . $dados['entidade'] . '())->getRelacionamentos();
        }
       
        public function addRelacionamento($tabela, $campoNaTabelaReferenciada = "", $campoNestaEntidade = "") : self {
            $schema = "public";;
            if (!is_array($tabela)) {
                if (strpos($tabela, ".") !== false)   {
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
    
    ';

        $out = $out . implode("", $propriedades) . $construct . implode("", $getSet) . $rel . '}';

        return $out;
    }



    public static $getterSetterPadrao = '

    // Metodos obrigatório pois EntityManager depende deles 

    public function getId() {
        return $this->%cpoID%;
    }

    public function setId($id) {
        $this->%cpoID% = (int) $id;
        return $this;
    }

    public function setError($error) {
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

    public function getError() {
        if (is_array($this->error)) {
            if (count($this->error) === 0) {
                return false;
            }
        }
        return $this->error;
    }

    public function getErrorToString() {
        if (is_array($this->getError())) {
            return implode(",", $this->getError());
        } else {
            return $this->getError();
        }
    }

    public function getTable() {
        return $this->table;
    }

    public function getCpoId() {
        return $this->cpoId;
    }            
    
    // Demais métodos getters e setters
            ';

    public static $setterConstruct = '$this->set%nomeFunction%(%valorPadrao%);';
}
