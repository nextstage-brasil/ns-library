<?php

if (!defined("SISTEMA_LIBRARY")) {
    die("Acesso direto não permitido");
}

class Poderes extends AbstractController {

    private $message; // ira armazenar possível mensagem de retorno
    private $error; // mensagens de erro
    private $result;
    public $user;

    public function __construct() {
        $this->setProperties();
        $this->poderes = array();
        $this->message = 'Usuário sem permissão';
    }

    /**
     * Metodo responsavel por validar poderes V2, bia BD
     */
    public static function verify($grupo, $subgrupo, $acao, $return = false) {

        //return true;


        global $dados;
        if ($_SESSION['user']['idUsuario'] > 1 && (int) $dados['idEmpresa'] > 0 && (int) $dados['idEmpresa'] !== $_SESSION['user']['idEmpresa']) {
            Log::error('Empresa não habilitada para o usuário');
            Api::result('403', ['error' => 'Empresa ' . $dados['idEmpresa'] . ' não habilitada para o usuário']);
        }

        $dao = new EntityManager();
        // Isso irá alterar o nome do grupo enviado, por isso, enviar sempre o nome da entidade conforme formatdo do sistema
        //Config::setDataByFile('aliases_table', Config::getData('path') . '/src/config/aliases_tables.php');
        //$subgrupo = (($grupo === $subgrupo) ? Config::getAliasesTable($grupo) : $subgrupo);
        //$grupo = Config::getAliasesTable($grupo);

        Helper::upperByReference($grupo);
        Helper::upperByReference($subgrupo);
        Helper::upperByReference($acao);
        $pp = new Poderes();
        $pp->setResult(false);
        $pp->setError('Sem permissão');
        $pp->setMessage('Sem permissão');
        $pp->user = $_SESSION['user']['idUsuario'];
        
        // Debug
        $back = debug_backtrace();
        $backtrace = ["$grupo | $subgrupo | $acao"];
        foreach ($back as $item) {
            $backtrace[] = [
                'file' => $item['file'],
                'line' => $item['line'],
                'function' => $item['function'],
                'class' => $item['class']
            ];
        }

        // bryan, master e dev não validam permissoes
        if ($_SESSION['user']['tipoUsuario'] === 6 || $_SESSION['user']['tipoUsuario'] === 4 || $pp->user === 1) {
            // criar a permissão caso não exista
            $var = new SistemaFuncao(['grupoFuncao' => $grupo, 'subgrupoFuncao' => $subgrupo, 'acaoFuncao' => $acao, 'extrasFuncao' => $backtrace]);
            $dao->setObject($var);
            $dao->save('on conflict do nothing');
            $pp->setResult(true);
            return $pp;
        }
        if (!$grupo || $grupo === '' || !$subgrupo || $subgrupo === '' || !$acao || $acao === '') {
            Log::error(__METHOD__ . " - Falha na inserção de propriedades: Grupo: '$grupo', Subgrupo: '$subgrupo' Ação: '$acao'");
            $pp->setResult(false);
            $pp->setError('Error (POC-51)');
            return $pp;
        }

        // Bloqueio para não operar sem login registrado
        if (!$pp->user) {
            Log::log('permissao-negada', 'Usuário não logado');

            Api::result('401', ['error' => 'Usuário não logado']);

            $pp->setMessage('Usuário não logado');
            return $pp;
        }

        /*
          $condicao = [
          'idUsuario' => $_SESSION['user']['idUsuario'],
          'appSistemaFuncao.grupoFuncao' => $grupo,
          'appSistemaFuncao.subgrupoFuncao' => $subgrupo,
          'appSistemaFuncao.acaoFuncao' => $acao
          ];
          $dao->setObject(new UsuarioPermissao());
          $dao->setInnerOrLeftJoin('inner');
          $ret = $dao->getAll($condicao, true, 0, 1)[0];
         * 
         */

        // Obtenção destes dados estão na SistemaLibrary, direto do perfil
        $perm = Config::getData('permissao');
        $chave = $grupo . $subgrupo . $acao;
        if ($perm[$chave] === true) {
            $pp->setResult(true);
            $pp->setMessage('Permissão por perfil');
            return $pp;
        } else {
            $condicao = [
                'grupoFuncao' => $grupo,
                'subgrupoFuncao' => $subgrupo,
                'acaoFuncao' => $acao
            ];
            $dao->setObject(new SistemaFuncao());
            $ret = $dao->getAll($condicao, false, 0, 1)[0];
            if ($ret instanceof SistemaFuncao) {
                Log::logTxt('permissao-recusada', var_export($backtrace, true));
                // existe e não tem permissão
                $pp->setMessage('<div class="text-center">'// . $grupo . $subgrupo . $acao
                        . '<i class="fa fa-lock fa-4x" aria-hidden="true"></i>'
                        . '<h3>Usuário sem permissão de acesso</h3>'
                        . '<p></p>'
                        . '<p>Caso entenda que necessite desde acesso, solicite autorização informando o código "PERM-' . $ret->getIdSistemaFuncao() . '"</p>'
                        . '</div>');
                if (!$return) {
                    Api::result(403, ['error' => $pp->getMessage()]);
                }
                return $pp;
            } else {

                // sistema função não existe. criar sem permissão
                $var = new SistemaFuncao(['grupoFuncao' => $grupo, 'subgrupoFuncao' => $subgrupo, 'acaoFuncao' => $acao, 'extrasFuncao' => $backtrace]);
                //$var = new SistemaFuncao($condicao);
                $dao->setObject($var);
                $dao->save('on conflict do nothing');
                $pp->setResult(false, false, false);
                return Poderes::verify($grupo, $subgrupo, $acao);
            }
        }
    }

    /** SETTERS AND GETTERS * */
    function getPoderes() {
        return self::PODERES;
    }

    function getResult() {
        if (!$this->result) {
            Log::log('permissao-recusada', $this->getMessage());
        }
        return $this->result;
    }

    function setResult($result, $textoMessage = false, $textoError = false) {
        $this->result = $result;
        $textoError = (($textoError) ? $textoError : $textoMessage);
        $this->message = (($this->result) ? $this->message : $textoMessage);
        $this->error = (($this->result) ? $this->error : $textoError);
    }

    function getCondicao() {
        return $this->condicao;
    }

    function getMessage() {
        return $this->message;
    }

    function getError() {
        return $this->error;
    }

    function setCondicao($condicao) {
        $this->condicao = $condicao;
    }

    function setMessage($message) {
        $this->message = $message;
    }

    function setError($error) {
        $this->error = $error;
    }

    // Set todas variaveis na inicialização para false;
    public function setProperties() {
        $propriedades = get_object_vars($this);
        foreach ($propriedades as $propriedade => $valor) {
            $this->$propriedade = false;
        }
    }

    public function toString() {
        $out = ''
                . '{User: ' . $this->user . '} '
                . ' {Result: ' . (($this->result) ? "TRUE" : "FALSE") . '} '
                . ' {Condicao: ' . var_export($this->condicao, true) . '}'
                . ' {Message: ' . $this->message . '}'
                . ' {Error: ' . $this->error . '}'
                . '';
        return $out;
    }

}

// fecha classe

