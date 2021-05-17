<?php

namespace NsLibrary\Builder;

class ControllerCreate {

    public static function save($dados, $entidade) {
                    // Não quero salvar esses controller, pq são padrão do framework
            if (array_search($entidade, [
                        'Linktable',
                        'Trash',
                        'Uploadfile',
                        'Usuario',
                        'UsuarioPermissao',
                        'UsuarioTipo',
                        'Mensagem',
                        'Status'
                    ]) === false) {
                $template = self::get($dados);
                $file = Config::getData('path') . '/src/controller/' . $entidade . 'Controller.class.php';
                Helper::saveFileBuild($file, $template);
            }
            
            
        ### Criação de entidade
        
        $file = Config::getData('pathEntidades') . DIRECTORY_SEPARATOR . $entidade . '.php';
        Helper::saveFile($file, false, $template, 'SOBREPOR');
        return true;
    }

    public final static function get($dados) {
        $dados['date'] = date('d/m/Y');
        $dados['datetime'] = date('c');
        $condicoes = [];
        foreach ($dados['atributos'] as $atributo) {
            if (strtolower(substr($atributo['nome'], 0, 2)) === "id") {
                $tabelaRelacional = ucwords(substr($atributo['nome'], 2, 150));
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
        foreach ($dados[arrayCamposJson] as $item) {
            //$jsonConfig[] = '$this->jsonDefault[' . $item . '] = [\'Campo a configurar\' => [\'default\' => \'\', \'grid\' => \'col-sm-6\', \'type\' => \'text\', \'class\' => \'\',\'ro\' => \'false\',\'tip\' => \'\', \'label\'=>\'\']];';
            $jsonConfig[] = "$item => \n'nome_variavel' =>[\n['default' => '', 'grid' => 'col-sm-4', 'type' => 'text', 'class' => '', 'ro' => 'false', 'tip' => '', 'label' => '']\n],";
        }
        $dados['jsonConfig'] = implode("\n", $jsonConfig);

        $out = Helper::escreveTemplate(self::$template, $dados);
        return $out;
    }

    public static $template = '<?php
if (!defined("SISTEMA_LIBRARY")) {die("Acesso direto não permitido");}               

/**
* Controlador da entidade
* Para liberar uma função ao /api, utilize o prefixo ws_, exemplo: public function ws_read($dados).
* Sempre será enviado o parametro array $dados nesses casos
* @date %datetime%
*/

class %entidade%Controller extends AbstractController {
    
    private static $poderesGrupo = \'%entidade%\';
    private static $poderesSubGrupo = \'%entidade%\';


    /**
     * Construtor
     * @create %date%
     */
    public function __construct() {
        $this->ent = \'%entidade%\';
        $this->camposDate = [%camposDate%];
        $this->camposDouble = [%camposDouble%];
        $this->camposJson = [%camposJson%];       
        
        $this->condicao = [];
        $this->object = new $this->ent();
        
        if (method_exists($this->object, \'setIdUsuario\') && !Helper::compareString($this->ent, \'usuario\'))   {
            $this->object->setIdUsuario($_SESSION[\'user\'][\'idUsuario\']);
            $this->condicao[\'idUsuario\'] = $_SESSION[\'user\'][\'idUsuario\'];
        }
        if (method_exists($this->object, \'setIdEmpresa\') && !Helper::compareString($this->ent, \'empresa\') )   {
                $this->object->setIdEmpresa($_SESSION[\'user\'][\'idEmpresa\']);
               $this->condicao[\'idEmpresa\'] = $_SESSION[\'user\'][\'idEmpresa\'];
        }
        if (method_exists($this->object, \'setIsAlive\'.$this->ent))   {
            $this->condicao[\'isAlive\'.$this->ent] = \'true\'; // somente deve mostrar tuplas vivas. Deletadas devem ser obtidas explicitamente.
        }  

        /**
        * Models json a ser configurado.
        * O array abaixo deve ser configurado no arquivo /src/config/model_json.php 
        %jsonConfig%
        */
    }
    
    /**
    * Chama o método em parent e retorna. Caso seja necessário alguma intervenção nesta classe    
    * @create %date%
    */
    public function toView($obj)   {
        if ($obj instanceof $this->ent)   {
            return parent::toView($obj);
        } else {
            return [\'error\' => \'Não localizado\'];
        }
    }

    /**
    * Cria um novo objeto da entidade e retorna em array
    * @create %date%
    * @return array
    */
    public function ws_getNew()   {
        Poderes::verify(self::$poderesGrupo, self::$poderesSubGrupo, \'inserir\');
        return $this->toView($this->object);
    }
    
    /**
    * Leitura de um item
    * @param array $dados Deve conter uma chave com id a ser obtido. Ex.: $dados[\'id\']
    * @create %date%
    * @return array
    */
    public function ws_getById($dados) {
        Poderes::verify(self::$poderesGrupo, self::$poderesSubGrupo, \'ler\');
        $ent = parent::getById($dados[\'id\'], true);
        return $this->toView($ent);
    }
    
    /**
    * Gera a relação de dados de uma entidade
    * @param array $dados Contem as chaves de busca definidas, além de \'count\' para apenas retornar a quantidade localizada deste search
    * @create %date%
    * @return array Retorna um array multidimensional
    */
    public function ws_getAll($dados) {
        Poderes::verify(self::$poderesGrupo, self::$poderesSubGrupo, \'ler\');
        
        %condicoes%
        
        if ($dados[\'count\'])   {
            return parent::count();
        }

        
        $inicio = (int)$dados[\'pagina\'];
        $fim = 30; // paginação obrigatória
        $getRelacao = ((isset($dados[\'getRelacao\']))?$dados[\'getRelacao\']:true);
        
        // set search padrão - ira procura por nomeENTIDADE
        $this->setSearch($dados);

        $entities = parent::getAll($dados, $getRelacao, $inicio, $fim, $order);
        $out = Helper::parseDateToDatePTBR($entities, $this->camposDate, $this->camposDouble, $this->camposJson);
        $this->setDadosComboSearch($dados, $out, $this->ent);
        
        return $out;

   }
    

    /**
    * Metodo responsavel por salvar uma entidade    
    * @create %date%
    * @param array $dados
    * @return array Retorna um array com o objeto salvo ou o erro
    */
    public function ws_save($dados) {
        $action = ( ((int)$dados[\'id\'.$this->ent] > 0) ? \'Editar\' : \'Inserir\');
        Poderes::verify(self::$poderesGrupo, self::$poderesSubGrupo, $action);
        
       
        if (method_exists($this->object, \'setIdUsuario\') && !Helper::compareString($this->ent, \'usuario\'))   {
            $dados[\'idUsuario\'] = $this->condicao[\'idUsuario\'];
        }
        
        if (method_exists($this->object, \'setIdEmpresa\') && !Helper::compareString($this->ent, \'empresa\') )   {
            $dados[\'idEmpresa\'] = $this->condicao[\'idEmpresa\'];
        }        
        
        // Caso utilize o avatar no uploadfile
        //$dados[\'idUploadfile\'] = Helper::jsonToArrayFromView($dados[\'Uploadfile\'])[\'idUploadfile\'];// para controle via avatar
        
        $id = parent::save($dados);
        
        // Retornar o objeto persisitido
        $t = $this->ws_getById([\'id\' => $id]);    
        $t[\'result\'] = Translate::get(\'Salvo com sucesso\');
        return $t;
    }

    /**
    * @param array $dados Deve conter uma chave com id a ser obtido. Ex.: $dados[\'id\']
    * @create %date%
    * @return bool
    */
    public function ws_remove($dados) {
        Poderes::verify(self::$poderesGrupo, self::$poderesSubGrupo, \'remover\');
        return parent::remove($dados[\'id\']);    
    }

}
';

}
