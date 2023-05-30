<?php

namespace NsLibrary\Builder;

use NsLibrary\Config;
use NsUtil\Helper;

class RestControllerCreate
{

    private static $namespace;

    public static function save(array $dados, string $entidade, array $ignore = []): void
    {
        $ignoreDefault = [
            'Cep',
            'Linktable',
            'Trash',
            'Uploadfile',
            'Usuario',
            'Status',
            'UsuarioPermissao',
            'UsuarioTipo',
            'Shared',
            'Mensagem',
            'MensagemGrupo',
            'MensagemGrupoUsers',
            'ConhecimentoView',
            'Endereco',
            'JsonTable',
            'LtRel',
            'LoginAttempts',
            'SistemaFuncao',
            'Webhook'
        ];
        $controllersDefault = array_merge($ignore, $ignoreDefault);

        if (!Config::getData('pathRestControllers')) {
            die('pathRestControllers is not defined');
        }
        $template = self::get($dados);
        $prefix = ((array_search($entidade, $controllersDefault) === false) ? '' : '/ignoredByConfig/');
        $file = Config::getData('pathRestControllers')
            . DIRECTORY_SEPARATOR
            . ((self::$namespace) ? self::$namespace . DIRECTORY_SEPARATOR : '')
            . "{$entidade}.php";
        $fileWithPrefix = str_replace("{$entidade}.php", "{$prefix}{$entidade}.php", $file);

        if (file_exists($file) && \array_search($entidade, $controllersDefault) !== false && \array_search($entidade, $ignoreDefault) === false) {
            rename($file, $fileWithPrefix);
        }


        Helper::saveFile($fileWithPrefix, false, $template);

        //        // Não quero salvar esses controller, pq são padrão do framework
        //        if (array_search($entidade, $controllersDefault) === false) {
        //            $template = self::get($dados);
        //            Helper::saveFile($file, false, $template);
        //        } else {
        //            if (file_exists($file) && array_search($entidade, $ignoreDefault) === false) {
        //                rename($file, str_replace("{$entidade}.php", "__REMOVE__{$entidade}.old", $file));
        //            }
        //        }
    }

    public final static function get($dados): string
    {
        $schema = $dados['schema'];
        self::$namespace = (($schema === 'public') ? null : ucwords($schema));

        $dados['date'] = date('d/m/Y');
        $dados['datetime'] = date('c');
        $condicoes = [];
        foreach ($dados['atributos'] as $atributo) {
            if (strtolower(substr((string) $atributo['nome'], 0, 2)) === "id") {
                $tabelaRelacional = ucwords(substr((string) $atributo['nome'], 2, 150));
                $condicoes[] = "'id$tabelaRelacional'";
            }
        }
        $dados['condicoes'] = '// IDs esperados
                foreach ([' . implode(",", $condicoes) . '] as $v) {
            if ((int) $dados[$v] > 0) {
                $this->condicao[$v] = (int) $dados[$v];
            }
        }';

        // json config
        $jsonConfig = [];
        foreach ($dados['arrayCamposJson'] as $item) {
            //$jsonConfig[] = '$this->jsonDefault[' . $item . '] = [\'Campo a configurar\' => [\'default\' => \'\', \'grid\' => \'col-sm-6\', \'type\' => \'text\', \'class\' => \'\',\'ro\' => \'false\',\'tip\' => \'\', \'label\'=>\'\']];';
            $jsonConfig[] = "$item => \n'nome_variavel' =>[\n['default' => '', 'grid' => 'col-sm-4', 'type' => 'text', 'class' => '', 'ro' => 'false', 'tip' => '', 'label' => '']\n],";
        }
        $dados['jsonConfig'] = implode("\n", $jsonConfig);

        $template = '<?php
            namespace ' . Config::getData('psr4Name') . '\\' . str_replace([Config::getData('path') . '/src/', '/'], ['', '\\'], Config::getData('pathRestControllers')) . ((self::$namespace) ? '\\' . self::$namespace : '') . ';

use ' . Config::getData('psr4Name') . '\NsLibrary\Entities\\' . ((self::$namespace) ? self::$namespace . '\\' : '') . '%entidade% as Entitie;
use NsLibrary\Config;
use NsLibrary\Controller\ApiRest\AbstractApiRestController;
use NsUtil\Api;

/** Created by NsLibrary Framework **/
if (!defined("SISTEMA_LIBRARY")) {die("' . $dados['entidade'] . 'RestController: Direct access not allowed. Define the SISTEMA_LIBRARY contant to use this class.");}               


/**
* Rest Controller da rota
* Basta seguir o padrão ApiREST com os verbos HTTP para ação
* Caso seja uma ação especifica, ex.: /another, use a rota: 
*/

 class %entidade% extends AbstractApiRestController {
 
    private $entitieName=  \'%entidade%\';

    public function __construct(Api $api) {
        $this->init($api);
        $this->controllerInit(
                    $this->entitieName, 
                    new Entitie(), 
                     \'%entidade%\', 
                     \'%entidade%\', 
                    Config::getData(\'entitieConfig\')[$this->entitieName][\'camposDate\'],
                    Config::getData(\'entitieConfig\')[$this->entitieName][\'camposDouble\'],
                    Config::getData(\'entitieConfig\')[$this->entitieName][\'camposJson\'],
                );
    }

    public function list(): void {
        $out = $this->ws_getAll($this->dados);
        $this->response($out);
    }

    public function read(): void {
        $out = $this->ws_getById($this->dados);
        $this->response($out);
    }

    public function create(): void {
        $out = $this->ws_save($this->dados);
        $this->response($out);
    }

    public function update(): void {
        $this->create();
    }

    public function delete(): void {
        $out = $this->ws_remove($this->dados);
        $this->response($out);
    }
}';

        $out = (new \NsUtil\Template($template, $dados, '%', '%'))->render();
        return $out;
    }
}
