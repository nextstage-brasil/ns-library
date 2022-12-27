<?php

namespace NsLibrary\Builder;

use NsLibrary\Config;
use NsLibrary\Controller\ModelSetterDefault;
use NsUtil\Helper;
use NsUtil\Template;

class EntidadesCreate {

    private static $namespace;

    public static function save($dados, $entidade) {
        ### Criação de entidade
        $template = self::get($dados);
        $file = Config::getData('pathEntidades')
            . DIRECTORY_SEPARATOR
            . ((self::$namespace) ? self::$namespace . DIRECTORY_SEPARATOR : '')
            . $entidade
            . '.php';
        Helper::saveFile($file, false, $template, 'SOBREPOR');
        return true;
    }

    public static function get($dados) {
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
private $relacoes = [' . implode(", ", $dados['relacionamentos']) . '];';

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
            /*
              if (stripos($val['coments'], '[noupper]') > 0) {
              $val['coments'] = strtolower(str_replace('[noupper]', '', $val['coments']));
              $val['upper'] = '';
              } else {
              $val['upper'] = 'Helper::upperByReference($' . $val['nome'] . ');';
              $val['coments'] = strtolower($val['coments']);
              }
             */
            $val['coments'] = ucfirst($val['coments']);
            $val['notnull'] = $val['notnull'] ?? false;
            $val['relacionamentos'] = $val['relacionamentos'] ?? null;

            $val['upper'] = ''; /// retirei pois o upper deixa o layout horrivel
            $val['USER'] = ((Helper::compareString('idusuario', $val['nome']) && !Helper::compareString('usuario', $dados['tabela'])) ? '$idUsuario = (($idUsuario) ? $idUsuario : $_SESSION[\'user\'][\'id_pessoa\']);' : ''); // protegendo para que todos aparceeam clean, somente user
            switch ($val['tipo']) {
                case 'OBJECT':
                    $template = ModelSetterDefault::getTemplateObject();
                    // self::$templateObject;
                    $val['nome'] = ucwords($val['nome']);
                    $val['valorPadrao'] = '$dd';
                    break;
                case 'EXTERNA':
                    $template = ModelSetterDefault::getTemplateExterna();
                    // self::$templateExterna;
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
                    $template = ModelSetterDefault::getTemplate($val['tipo']);
                    break;
                default:
                    $template = ModelSetterDefault::getTemplate('default');



                    // case 'timestamp':
                    // case 'datetime':
                    //     $template = ((($val['notnull'] === true)) ? self::$templateDateTimeObrigatorio : self::$templateDateTime);
                    //     break;
                    // case 'date':
                    //     $template = ((($val['notnull'] === true)) ? self::$templateDateObrigatorio : self::$templateDate);
                    //     break;
                    // case 'double':
                    //     $template = ((($val['notnull'] === true)) ? self::$templateDoubleObrigatorio : self::$templateDouble);
                    //     break;
                    // case 'json':
                    // case 'jsonb':
                    //     $template = ((($val['notnull'] === true)) ? self::$templateJsonObrigatorio : self::$templateJson);
                    //     break;
                    // case 'boolean':
                    //     $template = self::$templateBool;
                    //     break;
                    // case 'bytea':
                    //     $template = ((($val['notnull'] === true)) ? self::$templateByteaObrigatorio : self::$templateBytea);
                    //     break;

                    // (($val['notnull'] === true && !$val['key']) ? self::$templateObrigatorio : self::$template);
            }
            $val['notnull'] = (($val['notnull'] === true) ? "true" : "false");
            // propriedades
            $propriedades[] = 'private $' . $val['nome'] . ';';
            //$propriedades[] = 'private $' . $val['nome'] . 'Detalhes;';
            $template = utf8_encode($template);

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

public function responseIfHasError($code = 200) {
    if ($this->getError() !== false) {
        \NsUtil\Api::result($code, [\'error\' => $this->getError()]);
    }
}


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
* @return $this
 */
public function read($id) {
    $ret = $this->list([$this->cpoId => (int) $id])[0];
    if ($ret instanceof $this)  {
        $dd = (new Controller())->objectToArray($ret);
        $this->populate($dd);
    } else {
        $this->error = "ID not found \'$id\'";
    }
    return $this;
}

/**
    * Obtém a lista de entidades. 
     * @param array $filters Array contendo chave=>valor para filtro no banco. Utilizar camelcase para nome dos campos, ex.: nomeUsuario=>"Teste"
     * @param int $page Paginação
     * @param int $limit Limite de resultados por busca
     * @param array $order Array contendo dois campos: 0: chave para ordenar, 1 : sort. Ex.: ["nomePessoa", "asc"] 
     * @return type array    
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

public function save($onConflict=\'\') {
    $this->setDao();
    $updateName = \'setUpdatedAt\' . array_pop(explode(\'\\\\\', get_class($this)));
    if (method_exists($this, $updateName))   {
        $this->$updateName(\'NOW\');
    }
    $ret = $this->dao->setObject($this)->save($onConflict);
    if ($ret->getError() !== false)   {
        $this->setError = $ret->getError();
    }
    return $this;
}

public function count(array $filters=[]) : int   {
    $this->setDao();    
    return (int) $this->dao->count($filters);
}

public function remove() {
    $this->setDao();
    $ret = $this->dao->setObject($this)->remove();
    if ($ret === true)   {
        $this->init([]);
    }
    return $ret;
}

public function toArray() {
    return (new Controller())->objectToArray($this);
}
               
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
       
        public function addRelacionamento($tabela, $campoNaTabelaReferenciada=\'\', $campoNestaEntidade=\'\') {
            if (!is_array($tabela))   {
                $array = [\'tabela\' => $tabela, \'cpoRelacao\' =>$campoNaTabelaReferenciada , \'cpoOrigem\' => $campoNestaEntidade];
            } else {
                $array = $tabela;
            }
            $this->relacoes[] = $array;
        }';

        $out = $out . implode("", $propriedades) . $construct . implode("", $getSet) . $rel . '}';

        return $out;
    }

    //     public static $templateObrigatorio = '
    //         // obrigatório
    //         public function set%nomeFunction%($%nome%) {
    //         %USER%
    //                 if (is_array($%nome%))   {
    //             $%nome% = $%nome%[\'%nome%\'];
    //         }

    //         $%nome% =  Helper::getValByType($%nome%, \'%tipo%\');

    //         if (strlen((string)$%nome%) <= 0) {
    //             $this->error[\'%nome%\'] = \'%coments%\';
    //         } else {
    //             unset($this->error[\'%nome%\']);
    //             $this->%nome% =  Helper::getValByType($%nome%, \'%tipo%\');
    //         }
    //         return $this;
    //     }

    //     public function get%nomeFunction%() {
    //         return $this->%nome%;
    //     }
    // ';
    //     public static $template = 'public function set%nomeFunction%($%nome%) {
    //         // Não obrigatorio
    //         if (is_array($%nome%))   {
    //             $%nome% = $%nome%[\'%nome%\'];
    //         }
    //         $this->%nome% =  Helper::getValByType($%nome%, \'%tipo%\');
    //         return $this;
    //     }

    //     public function get%nomeFunction%() {
    //         return $this->%nome%;
    //     }
    // ';

    //     public static $templateDoubleObrigatorio = '
    //         // double obrigatorio
    //         public function set%nomeFunction%($%nome%) {
    //         if (is_array($%nome%))   {
    //             $%nome% = $%nome%[\'%nome%\'];
    //         }

    //         $%nome% =  Helper::getValByType($%nome%, \'%tipo%\');

    //         if (strlen((string)$%nome%) <= 0) {
    //             $this->error[\'%nome%\'] = \'%coments%\';
    //         } else {
    //             $%nome% = (double) Helper::decimalFormat($%nome%);
    //             if ($%nome% > 0) {
    //                 unset($this->error[\'%nome%\']);
    //                 $this->%nome% =  Helper::getValByType($%nome%, \'double\');
    //             } else {
    //                 $this->error[\'%nome%\'] = \'%coments%\';
    //             }
    //         }
    //         return $this;
    //     }

    //     public function get%nomeFunction%() {
    //         return $this->%nome%;
    //     }
    // ';
    //     public static $templateDouble = 'public function set%nomeFunction%($%nome%) {
    //         // double simples
    //         if (is_array($%nome%))   {
    //             $%nome% = $%nome%[\'%nome%\'];
    //         }

    //         //$%nome% = (double) Helper::decimalFormat($%nome%);

    //         $%nome% = Helper::decimalFormat($%nome%);
    //         $this->%nome% = Helper::getValByType($%nome%, \'double\');
    //         unset($this->error[\'%nome%\']);
    //         return $this;
    //     }

    //         public function get%nomeFunction%() {
    //             return $this->%nome%;
    //         }
    // ';




    //     public static $templateObrigatorioString = '
    //         // Obrigatorio - String
    //         public function set%nomeFunction%($%nome%) {
    //         if (is_array($%nome%))   {
    //             $%nome% = $%nome%[\'%nome%\'];
    //         }

    // //        $%nome% = Helper::getValByType($%nome%, \'string\');
    //         $%nome% =  Helper::getValByType($%nome%, \'%tipo%\');

    //         if (strlen((string)$%nome%) <= 0) {
    //             $this->error[\'%nome%\'] = \'%coments%\';
    //         } else {
    //             unset($this->error[\'%nome%\']);
    //             %upper%
    //             $this->%nome% = (%tipo%) mb_substr((string)$%nome%, 0, %maxsize%);

    //         }
    //         return $this;
    //     }

    //     public function get%nomeFunction%() {
    //         return $this->%nome%;
    //     }
    // ';
    //     public static $templateString = 'public function set%nomeFunction%($%nome%) {
    //         if (is_array($%nome%))   {
    //             $%nome% = $%nome%[\'%nome%\'];
    //         }
    //         $%nome% = Helper::getValByType($%nome%, \'%tipo%\');

    //         %upper%
    //         $%nome% = Helper::getValByType($%nome%, \'string\');
    //         $this->%nome% = (%tipo%) mb_substr((string)$%nome%, 0, %maxsize%);
    //         return $this;
    //     }

    //     public function get%nomeFunction%() {
    //         return $this->%nome%;
    //     }
    // ';



    //     public static $templateObject = 'public function set%nomeFunction%($%nome%) {
    //         $this->%nome% = (($%nome% instanceof %nome%)? $%nome% : new %nome%($%nome%));
    //         return $this;
    //     }

    //     public function get%nomeFunction%() {
    //         return $this->%nome%;
    //     }
    // ';


    //     public static $templateExterna = 'public function set%nomeFunction%($%nome%) {
    //         $this->%nome% = (object) $%nome%;
    //         return $this;
    //     }

    //     public function get%nomeFunction%() {
    //         return $this->%nome%;
    //     }
    // ';


    // public static $templateDate = 'public function set%nomeFunction%($%nome%) {
    //     if (is_array($%nome%))   {
    //         $%nome% = $%nome%[\'%nome%\'];
    //     }
    //     $%nome% = Helper::getValByType($%nome%, \'string\');
    //     $date = Helper::formatDate($%nome%);
    //     if ($date)   {
    //             $this->%nome% = (string) $date;
    //         } else   {
    //             $this->%nome% = null;
    //         }
    //         return $this;
    // }
    // public function get%nomeFunction%() {
    //     return $this->%nome%;
    // }';
    // public static $templateDateObrigatorio = 'public function set%nomeFunction%($%nome%) {
    //     if (is_array($%nome%))   {
    //         $%nome% = $%nome%[\'%nome%\'];
    //     }
    //     $%nome% = Helper::getValByType($%nome%, \'string\');

    //     if (strlen((string)$%nome%) < 8) { // menor que 8 não é data ddmmyyyy
    //         $this->error[\'%nome%\'] = \'%coments%\';
    //     } else {
    //         unset($this->error[\'%nome%\']);
    //         $date = Helper::formatDate($%nome%);
    //         if ($date)   {
    //             $this->%nome% = (string) $date;
    //         } else   {
    //             $this->error[\'%nome%\'] = "%coments% - Data Inválida";
    //         }
    //     } 
    //     return $this;
    // }
    // public function get%nomeFunction%() {
    //     return $this->%nome%;
    // }';


    // public static $templateDateTime = 'public function set%nomeFunction%($%nome%) {
    //             if (is_array($%nome%))   {
    //         $%nome% = $%nome%[\'%nome%\'];
    //     }

    //     $date = Helper::formatDate($%nome%, \'c\', true);
    //     if ($date)   {
    //             $this->%nome% = (string) $date;
    //         } else   {
    //             $this->%nome% = null;
    //         }
    //         return $this;
    // }
    // public function get%nomeFunction%() {
    //     return $this->%nome%;
    // }';
    // public static $templateDateTimeObrigatorio = 'public function set%nomeFunction%($%nome%) {
    //     $%nome% = Helper::getValByType($%nome%, \'string\');

    //     if (strlen((string)$%nome%) <= 8) {
    //         $this->error[\'%nome%\'] = \'%coments%\';
    //     } else {
    //         unset($this->error[\'%nome%\']);
    //         $date = Helper::formatDate($%nome%, \'c\', true);
    //         if ($date)   {
    //             $this->%nome% = (string) $date;
    //         } else   {
    //             $this->error[\'%nome%\'] = "%coments% - Invalid date";
    //         }
    //     }
    //     return $this;
    // }
    // public function get%nomeFunction%() {
    //     return $this->%nome%;
    // }';



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

    //     public static $templateJsonObrigatorio = 'public function set%nomeFunction%($%nome%) {
    //         if (!is_array($%nome%) && !is_object($%nome%)) {
    //             $%nome% = json_decode($%nome%, true);
    //         }
    //         $%nome% = json_encode($%nome%, JSON_HEX_QUOT | JSON_HEX_APOS);
    //         $%nome% = str_replace(\'&#34;\', \'\\u0022\', $%nome%);
    //         if (!$%nome% || json_last_error() > 0 || $%nome%===null || $%nome%===\'null\' ) {
    //             $this->error[\'%nome%\'] = \'%coments%\';
    //         }  else {
    //             unset($this->error[\'%nome%\']);
    //             $this->%nome% = $%nome%;
    //         }  

    //         return $this;

    //     }

    //     public function get%nomeFunction%() {
    //         return $this->%nome%;
    //     }
    // ';
    //     public static $templateJson = 'public function set%nomeFunction%($%nome%) {
    //         if (!is_array($%nome%) && !is_object($%nome%)) {
    //             $%nome% = json_decode($%nome%, true);
    //         }
    //         $%nome% = json_encode($%nome%, JSON_HEX_QUOT | JSON_HEX_APOS);
    //         $%nome% = str_replace(\'&#34;\', \'\\u0022\', $%nome%);
    //         if (json_last_error() > 0) {
    //             $%nome% = json_encode([]);
    //         }        
    //         $this->%nome% = $%nome%;

    //         return $this;
    //     }

    //     public function get%nomeFunction%() {
    //         return $this->%nome%;
    //     }
    // ';
    // public static $templateBool = '
    //     public function set%nomeFunction%($%nome%) {
    //                 if (is_array($%nome%))   {
    //                     $%nome% = $%nome%[\'%nome%\'];
    //                 }
    //                 if (gettype($%nome%) === \'boolean\')   {
    //                     $this->%nome% = (string) (($%nome%) ?\'true\' :\'false\' );
    //                 } else {
    //                     $this->%nome% = (string) ((Helper::compareString(\'true\', (string) $%nome%)) ?\'true\' :\'false\' );
    //                 }
    //                 return $this;
    //             }

    //             public function get%nomeFunction%() {
    //                 return $this->%nome%;
    //             }
    // ';
}
