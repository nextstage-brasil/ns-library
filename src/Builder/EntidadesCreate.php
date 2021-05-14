<?php

namespace NsLibrary\Builder;

use NsLibrary\Config;
use NsUtil\Helper;
use NsUtil\Template;
use function mb_substr;
use function utf8_encode;

class EntidadesCreate {

    public static function save($dados, $entidade) {
        ### Criação de entidade
        $template = self::get($dados);
        $file = Config::getData('pathEntidades') . DIRECTORY_SEPARATOR . $entidade . '.php';
        Helper::saveFile($file, false, $template, 'SOBREPOR');
        return true;
    }

    public static function get($dados) {
        $out = '<?php
            
            namespace ' . Config::getData('psr4Name') . '\NsLibrary\Entidades;
            use NsUtil\Helper;
            use function mb_substr;
            
            /** CREATE AT ' . date('d/m/Y H:i:s') . ' BY NsLibrary Framework **/
if (!defined("SISTEMA_LIBRARY")) {die("Acesso direto não permitido");}               
class ' . $dados['entidade'] . '{

private $error; // armazena possiveis erros, inclusive, obrigatoriedades.
private $table = "' . $dados['schemaTable'] . '";
private $cpoId = "' . $dados['cpoID'] . '";
private $dao = null;';


        $getSet[] = (new Template(self::$getterSetterPadrao, array('cpoID' => $dados['cpoID']), '%', '%'))->render();
        foreach ($dados['atributos'] as $val) {
            $val['valorPadrao'] = str_replace("::date", "", $val['valorPadrao']);
            $val['valorPadrao'] = str_replace('::timestamp without time zone', '', $val['valorPadrao']);
            $val['nomeFunction'] = ucwords($val['nome']);

            // tratamento para CE - alterar o nome da function para id ao inves de ce
            $terceiraLetra = mb_substr($val['nome'], 2, 1);
            if (mb_substr($val['nome'], 0, 2) === 'ce' && Helper::compareString(strtoupper($terceiraLetra), $terceiraLetra, true)) {
                $val['nomeFunction'] = 'Id' . mb_substr($val['nome'], 2);
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




            $val['upper'] = ''; /// retirei pois o upper deixa o layout horrivel
            $val['USER'] = ((Helper::compareString('idusuario', $val['nome']) && !Helper::compareString('usuario', $dados['tabela'])) ? '$idUsuario = (($idUsuario) ? $idUsuario : $_SESSION[\'user\'][\'id_pessoa\']);' : ''); // protegendo para que todos aparceeam clean, somente user
            switch ($val['tipo']) {
                case 'OBJECT':
                    $template = self::$templateObject;
                    $val['nome'] = ucwords($val['nome']);
                    $val['valorPadrao'] = '$dd';
                    break;
                case 'EXTERNA':
                    $template = self::$templateExterna;
                    $val['nome'] = mb_substr($val['nome'], 2);
                    $val['nomeFunction'] = ucwords($val['nome']);
                    $val['valorPadrao'] = '$dd';
                    break;

                case 'timestamp':
                case 'datetime':
                    $template = ((($val['notnull'] === true)) ? self::$templateDateTimeObrigatorio : self::$templateDateTime);
                    break;
                case 'date':
                    $template = ((($val['notnull'] === true)) ? self::$templateDateObrigatorio : self::$templateDate);
                    break;
                case 'string':
                case 'text':
                    $val['tipo'] = 'string';
                    $template = ((($val['notnull'] === true)) ? self::$templateObrigatorioString : self::$templateString);
                    break;
                case 'double':
                    $template = ((($val['notnull'] === true)) ? self::$templateDoubleObrigatorio : self::$templateDouble);
                    break;
                case 'json':
                case 'jsonb':
                    $template = ((($val['notnull'] === true)) ? self::$templateJsonObrigatorio : self::$templateJson);
                    break;
                case 'boolean' :
                    $template = self::$templateBool;
                    break;

                default:
                    $template = (($val['notnull'] === true && !$val['key']) ? self::$templateObrigatorio : self::$template);
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
               //$t = new Eficiencia(\'ConstructEntitie:\'.get_class($this));
                    $this->error = false;
                    ' . implode($constructSet) . '
                    $this->populate($dd);

            
        //$t->end(0.1);
            


        
               }

private function setDao() {
    if ($this->dao === null)  {
        $this->dao = new \NsLibrary\Controller\EntityManager($this);
    }
}

/**
 * Executa a busca de um item pelo ID da tabela 
 */
public function read($id) {
    $ret = $this->list(["' . $dados['cpoID'] . '" => $id])[0];
    if ($ret instanceof ' . $dados['entidade'] . ')  {
        $dd = (new \NsLibrary\Controller\Controller())->objectToArray($ret);
        $this->populate($dd);
    } else {
        $this->error = "Not found ID " . $id;
    }
    return $this;
}

/**
    * Obtém a lista de entidades. 
*/
public function list(array $filter=[], $inicio=0, $fim=1000, $order=false)   {
    $this->setDao();
    return $this->dao->getAll($filter, true, $inicio, $fim);
}

public function persist() {
    $this->setDao();
    $ret = $this->dao->setObject($this)->save();
    if ($ret->getError() !== false)   {
        $this->setError = $ret->getError();
    }
    return $this;
}
               
public function populate($dd)  {
                    $this->error = false;
        if ($dd) {
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
                if (mb_substr($set, 0, 3) === "set") {
                    $file = lcfirst(mb_substr($set, 3, 300));
                    $dd[$file] = ((!isset($dd[$file])) ? $dd[Helper::reverteName2CamelCase($file)] : $dd[$file]);
                    if (isset($dd[$file])) {
                        $this->$set($dd[$file]);
                    }
                }
            }
        }
}';
        if (is_array($dados['relacionamentos'])) {
            $rel = '
                // metodo para retornar os campos de relacionamento entre as entidades
                public function getRelacionamentos()   {
                    return self::getRelacionamentosStatic();
                    //return array(' . implode(", ", $dados['relacionamentos']) . ');
                }
            public static function getRelacionamentosStatic()   {
                     return array(' . implode(", ", $dados['relacionamentos']) . ');
                }';
        }

        $out = $out . implode("", $propriedades) . $construct . implode("", $getSet) . $rel . '}';

        return $out;
    }

    public static $templateObrigatorio = '
        // obrigatório
        public function set%nomeFunction%($%nome%) {
        %USER%
                if (is_array($%nome%))   {
            $%nome% = $%nome%[\'%nome%\'];
        }

        if (strlen($%nome%) <= 0) {
            $this->error[\'%nome%\'] = \'%coments%\';
        } else {
            unset($this->error[\'%nome%\']);
            $this->%nome% =  Helper::getValByType($%nome%, \'%tipo%\');
        }
    }

    public function get%nomeFunction%() {
        return $this->%nome%;
    }
';
    public static $template = 'public function set%nomeFunction%($%nome%) {
        // Não obrigatorio
        if (is_array($%nome%))   {
            $%nome% = $%nome%[\'%nome%\'];
        }
        $this->%nome% =  Helper::getValByType($%nome%, \'%tipo%\');
    }

    public function get%nomeFunction%() {
        return $this->%nome%;
    }
';
    public static $templateDoubleObrigatorio = '
        // double obrigatorio
        public function set%nomeFunction%($%nome%) {
        if (is_array($%nome%))   {
            $%nome% = $%nome%[\'%nome%\'];
        }

        if (strlen($%nome%) <= 0) {
            $this->error[\'%nome%\'] = \'%coments%\';
        } else {
            $%nome% = (double) Helper::decimalFormat($%nome%);
            if ($%nome% > 0) {
                unset($this->error[\'%nome%\']);
                $this->%nome% =  Helper::getValByType($%nome%, \'double\');
            } else {
                $this->error[\'%nome%\'] = \'%coments%\';
            }
        }
    }

    public function get%nomeFunction%() {
        return $this->%nome%;
    }
';
    public static $templateDouble = 'public function set%nomeFunction%($%nome%) {
        // double simples
        if (is_array($%nome%))   {
            $%nome% = $%nome%[\'%nome%\'];
        }

        //$%nome% = (double) Helper::decimalFormat($%nome%);
        
        $%nome% = Helper::decimalFormat($%nome%);
        $this->%nome% = Helper::getValByType($%nome%, \'double\');
        unset($this->error[\'%nome%\']);
    }

        public function get%nomeFunction%() {
            return $this->%nome%;
        }
';
    public static $templateObrigatorioString = '
        // Obrigatório - String
        public function set%nomeFunction%($%nome%) {
        if (is_array($%nome%))   {
            $%nome% = $%nome%[\'%nome%\'];
        }
        
        $%nome% = Helper::getValByType($%nome%, \'string\');

        if (strlen($%nome%) <= 0) {
            $this->error[\'%nome%\'] = \'%coments%\';
        } else {
            unset($this->error[\'%nome%\']);
            %upper%
            $this->%nome% = (%tipo%) mb_substr($%nome%, 0, %maxsize%);
            
        }
    }

    public function get%nomeFunction%() {
        return $this->%nome%;
    }
';
    public static $templateString = 'public function set%nomeFunction%($%nome%) {
        if (is_array($%nome%))   {
            $%nome% = $%nome%[\'%nome%\'];
        }
        $%nome% = Helper::getValByType($%nome%, \'%tipo%\');

        %upper%
        $%nome% = Helper::getValByType($%nome%, \'string\');
        $this->%nome% = (%tipo%) mb_substr($%nome%, 0, %maxsize%);
    }

    public function get%nomeFunction%() {
        return $this->%nome%;
    }
';
    public static $templateObject = 'public function set%nomeFunction%($%nome%) {
        $this->%nome% = (($%nome% instanceof %nome%)? $%nome% : new %nome%($%nome%));
    }

    public function get%nomeFunction%() {
        return $this->%nome%;
    }
';
    public static $templateExterna = 'public function set%nomeFunction%($%nome%) {
        $this->%nome% = (object) $%nome%;
    }

    public function get%nomeFunction%() {
        return $this->%nome%;
    }
';
    public static $templateDate = 'public function set%nomeFunction%($%nome%) {
        if (is_array($%nome%))   {
            $%nome% = $%nome%[\'%nome%\'];
        }
        $%nome% = Helper::getValByType($%nome%, \'string\');
        $date = Helper::formatDate($%nome%);
        if ($date)   {
                $this->%nome% = (string) $date;
            } else   {
                $this->%nome% = null;
            }
    }
    public function get%nomeFunction%() {
        return $this->%nome%;
    }';
    public static $templateDateObrigatorio = 'public function set%nomeFunction%($%nome%) {
        if (is_array($%nome%))   {
            $%nome% = $%nome%[\'%nome%\'];
        }
        $%nome% = Helper::getValByType($%nome%, \'string\');

        if (strlen($%nome%) < 8) { // menor que 8 não é data ddmmyyyy
            $this->error[\'%nome%\'] = \'%coments%\';
        } else {
            unset($this->error[\'%nome%\']);
            $date = Helper::formatDate($%nome%);
            if ($date)   {
                $this->%nome% = (string) $date;
            } else   {
                $this->error[\'%nome%\'] = "%coments% - Data Inválida";
            }
        }        
    }
    public function get%nomeFunction%() {
        return $this->%nome%;
    }';
    public static $templateDateTime = 'public function set%nomeFunction%($%nome%) {
                if (is_array($%nome%))   {
            $%nome% = $%nome%[\'%nome%\'];
        }

        $date = Helper::formatDate($%nome%, \'arrumar\', true);
        if ($date)   {
                $this->%nome% = (string) $date;
            } else   {
                $this->%nome% = null;
            }
    }
    public function get%nomeFunction%() {
        return $this->%nome%;
    }';
    public static $templateDateTimeObrigatorio = 'public function set%nomeFunction%($%nome%) {
        if (strlen($%nome%) <= 8) {
            $this->error[\'%nome%\'] = \'%coments%\';
        } else {
            unset($this->error[\'%nome%\']);
            $date = Helper::formatDate($%nome%);
            if ($date)   {
                $this->%nome% = (string) $date;
            } else   {
                $this->error[\'%nome%\'] = "%coments% - Data Inválida";
            }
        }
    }
    public function get%nomeFunction%() {
        return $this->%nome%;
    }';
    public static $getterSetterPadrao = '
/** 
Metodos obrigatório pois EntityManager depende deles 
*/
    public function getId() {
        return $this->%cpoID%;
    }

    public function setId($id) {
        $this->%cpoID% = (int) $id;
    }

    public function setError($error) {
        Helper::upperByReference($error);
        $this->error = $error;
    }

    public function getError() {
        if (is_array($this->error)) {
            if (count($this->error) === 0) {
                $this->error = false;
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
    
/**
Demais métodos getters e setters
**/
            ';
    public static $setterConstruct = '$this->set%nomeFunction%(%valorPadrao%);';
    public static $templateJsonObrigatorio = '
        public function set%nomeFunction%($%nome%) {
        if (is_array($%nome%)) {
            $%nome% = json_encode($%nome%, true);
        } else {
            $%nome% = str_replace(\'&#34;\', \'"\', $%nome%);
        }
        json_decode($%nome%);
        if (json_last_error() > 0) {
            $this->error[\'%nome%\'] = \'%coments%\';
        } else {
            unset($this->error[\'%nome%\']);
            $this->%nome% = $%nome%;
        }
    }

    public function get%nomeFunction%() {
        return $this->%nome%;
    }
';
    public static $templateJson = 'public function set%nomeFunction%($%nome%) {
        if (is_array($%nome%)) {
            $%nome% = json_encode($%nome%);
        }
        if (strlen($%nome%)<=0)   {
            $%nome% = \'{}\';
        }
        $this->%nome% = $%nome%;
    }

    public function get%nomeFunction%() {
        return $this->%nome%;
    }
';
    public static $templateBool = '
        public function set%nomeFunction%($%nome%) {
                    if (is_array($%nome%))   {
                        $%nome% = $%nome%[\'%nome%\'];
                    }
                    if (gettype($%nome%) === \'boolean\')   {
                        $this->%nome% = (string) (($%nome%) ?\'true\' :\'false\' );
                    } else {
                        $this->%nome% = (string) ((Helper::compareString(\'true\', (string) $%nome%)) ?\'true\' :\'false\' );
                    }
                }

                public function get%nomeFunction%() {
                    return $this->%nome%;
                }
    ';

}
