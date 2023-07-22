<?php

namespace NsLibrary\Builder;

use NsLibrary\Config;
use NsLibrary\Controller\ModelSetterDefault;
use NsUtil\Helper;
use NsUtil\Template;

class EntidadesCreate
{

    private static $namespace;
    public static bool $ignoreNamespace = false;

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
        // self::$namespace = (($schema === 'public') ? null : ucwords($schema));
        self::$namespace = self::$ignoreNamespace || $schema === 'public'
            ? null
            : ucwords($schema);
        $out = '<?php
            ' .
            (self::$ignoreNamespace
                ? ''
                : 'namespace ' . Config::getData('psr4Name') . '\NsLibrary\Entities' . ((self::$namespace) ? '\\' . self::$namespace : '') . ';'
            )
            . '

            // use NsUtil\Helper;
            // use NsLibrary\Controller\Controller;
            // use NsLibrary\Controller\EntityManager;
            use NsLibrary\Controller\ModelSetterDefault;
            use NsLibrary\Entities\EntityManagerInterface;
            // use function NsUtil\json_decode;

/** Created by NsLibrary Framework **/
if (!defined("SISTEMA_LIBRARY")) {die("' . $dados['entidade'] . ': Direct access not allowed. Define the SISTEMA_LIBRARY contant to use this class.");}               

class ' . $dados['entidade'] . ' extends \NsLibrary\Entities\AbstractEntity {

// private $error; // armazena possiveis erros, inclusive, obrigatoriedades.
// private $table = "' . ($dados['schemaTable'] ?? 'var schemaTable is not defined!!') . '";
// private $cpoId = "' . $dados['cpoID'] . '";
// private $dao = null;
// private $relacoes = [' . implode(", ", $dados['relacionamentos']) . '];
// public $selectExtra = null;
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
                        // $val['valorPadrao'] = 'isset($dd["' . $val['nome'] . '"]) && is_array($dd["' . $val['nome'] . '"]) ? $dd["' . $val['nome'] . '"] : $dd';
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
                        throw new \Exception("Entities Create: Invalid Template Type: " . var_export($val, true));
                        $template = ModelSetterDefault::getTemplate('NOT_IMPLEMENTED: ' . $val['tipo']);
                }
            }


            $val['notnull'] = (($val['notnull'] === true) ? "true" : "false");

            // propriedades
            $propriedades[] = 'protected $' . $val['nome'] . ';';

            // $template = utf8_encode($template);
            $getSet[] = (new Template($template, $val, '%', '%'))->render();

            $constructSet[] = (new Template(self::$setterConstruct, $val, '%', '%'))->render();
        }

        $construct = '

        /**
         * Contruct of model
         *
         * @param array|null $dd
         */    

         public function __construct(?array $dd=[], ?EntityManagerInterface $dao = null)  {

            parent::__construct(
                "' . ($dados['schemaTable'] ?? 'var schemaTable is not defined!!') . '", 
                "' . $dados['cpoID'] . '", 
                [ 
                    ' . implode(",\n", $dados['relacionamentos']) . '
                ], 
                $dao
            );

            $this->init($dd);
        }
               
/**
 * Reconstruct de data of model
 *
 * @param array|null $dd
 * @return self
 */
public function init($dd = [])
{
$this->error = [];
' . implode('  ', $constructSet) . '
$this->populate($dd);
return $this;
}

public static function getRelacionamentosStatic()   {
    return (new self())->getRelacionamentos();
}
';


        $out = $out . implode("", $propriedades) . $construct . implode("", $getSet) . '}';

        return $out;
    }

    public static $getterSetterPadrao = '//';

    public static $setterConstruct = '$this->set%nomeFunction%(%valorPadrao%);';
}
