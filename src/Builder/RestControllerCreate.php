<?php

namespace NsLibrary\Builder;

use NsLibrary\Config;
use NsUtil\Helper;

class RestControllerCreate {

    private static $namespace;

    public static function save(array $dados, string $entidade, array $ignore = []): void {
        $controllersDefault = (($ignore) ? $ignore : [
            'Linktable',
            'Trash',
            'Uploadfile',
            'Usuario',
            'UsuarioPermissao',
            'UsuarioTipo',
            'Mensagem',
            'Status'
        ]);

        if (!Config::getData('pathRestControllers')) {
            die('pathRestControllers is not defined');
        }

        // Não quero salvar esses controller, pq são padrão do framework
        if (array_search($entidade, $controllersDefault) === false) {
            $template = self::get($dados);

            $file = Config::getData('pathRestControllers')
                    . DIRECTORY_SEPARATOR
                    . ((self::$namespace) ? self::$namespace . DIRECTORY_SEPARATOR : '')
                    . $entidade
                    . 'Controller.php';
            Helper::saveFile($file, false, $template, 'SOBREPOR');
        }
    }

    public final static function get($dados) : string {
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
            namespace ' . Config::getData('psr4Name') . '\NsLibrary\RestControllers' . ((self::$namespace) ? '\\' . self::$namespace : '') . ';
use NsLibrary\Controller\ApiRest\AbstractApiRestController;
use NsUtil\Api;
use Poderes;

/** Created by NsLibrary Framework **/
if (!defined("SISTEMA_LIBRARY")) {die("' . $dados['entidade'] . 'RestController: Direct access not allowed. Define the SISTEMA_LIBRARY contant to use this class.");}               


/**
* Rest Controller da rota
* Basta seguir o padrão ApiREST com os verbos HTTP para ação
* Caso seja uma ação especifica, ex.: /another, use a rota: 
* @date %datetime%
*/

 class %entidade%Controller extends AbstractApiRestController {
 
    private static $poderesGrupo = \'%entidade%\';
    private static $poderesSubGrupo = \'%entidade%\';

    public function __construct(Api $api) {
        $this->init($api);
    }

    public function list(): void {
        Poderes::verify(self::$poderesGrupo, self::$poderesSubGrupo, \'ler\');
        $out = $this->ws_getAll($this->dados);
        $this->response($out);
    }

    public function read(): void {
        Poderes::verify(self::$poderesGrupo, self::$poderesSubGrupo, \'ler\');
        $out = $this->ws_getById($this->dados);
        $this->response($out);
    }

    public function create(): void {
        Poderes::verify(self::$poderesGrupo, self::$poderesSubGrupo, \'inserir\');
        $out = $this->ws_save($this->dados);
        $this->response($out);
    }

    public function update(): void {
        Poderes::verify(self::$poderesGrupo, self::$poderesSubGrupo, \'editar\');
        $this->create();
    }

    public function delete(): void {
        Poderes::verify(self::$poderesGrupo, self::$poderesSubGrupo, \'remover\');
        $out = $this->ws_remove($this->dados);
        $this->response($out);
    }
}';

        $out = (new \NsUtil\Template($template, $dados, '%', '%'))->render();
        return $out;
    }

}
