<?php

use NsStorageLibrary\Storage\Storage;

/**
 * @date 29/06/2018
 * @author Nextstage
 * @description Este Controller não executara persistencia, apenas centraliza os dados a serem exibidos no App
 */
if (!defined("SISTEMA_LIBRARY")) {
    die("Acesso direto não permitido");
}

class AppLibraryController extends AbstractController {

    private $empresa;
    private static $dao;

    public function __construct() {
        
    }

    public static function getCredentials() {
        $v = Helper::decodifica($_SESSION['credential']);
        return json_decode($v);
    }

    /** re4torna as conexoe spara filerun via client * */
    public static function getTokenFileserver() {
        $storage = new Storage();
        $out = [
            'token' => $storage->adapter->getAccessToken(),
            'host' => Config::getData('fileserver', $storage->adapterName)['url'],
            'bucket' => Config::getData('fileserver', $storage->adapterName)['bucket'],
            'prefix' => '', //((Config::getData('dev')) ? 'dev/' : '') . $_SESSION['user']['idUsuario'],
        ];
        return $out;
    }

    public static function uploadFile($dados) {
        $t = new AppController();
        return $t->ws_uploadFile($dados);
    }

    /**
     * Trata os envios de uploadfile da view
     * 
     * @param type $dados
     * @return type
     */
    public function ws_uploadFile($dados) {
        $t = explode("_", $dados['ParamsRouter'][4]);
        $dados['thumbs'] = $t[0];
        $dados['entidade'] = filter_var($dados['entidade'], FILTER_SANITIZE_STRING);
        $dados['valorid'] = (int) filter_var($dados['valorid'], FILTER_VALIDATE_INT);

        $_SESSION['upload_prefix'] = md5($dados[entidade] . $dados[valorid]); // garante um nome unico por entidade/id
        $dir = Config::getData('path') . '/ns-app/tmp/'; // . Config::getData('filesPrefix');
        Helper::saveFile($dir . '/index.php', '', '<?php http_response_code(404);');
        $maxsize = (($dados['maxsize']) ? $dados['maxsize'] : 3000);

        foreach ($_FILES as $arquivo) {


            // tamanho máximo permitido por foto
            $arquivo['maxFilesize'] = 20 * 1024 * 1024;
            $upload = new Upload($arquivo, $dir, $maxsize, $thumbs);
            $result = $upload->execute();

            // Recognise Search
            if (Helper::compareString($dados['entidade'], 'SearchRecognise')) {
                return ['error' => false, 'file' => $upload->getNome()];
            }
            //$dados['atleta'] = [];

            if ($result === true) { // avaliar se esta salvo com sucesso no disco
                $dados['nomeOriginal'] = $upload->getNomeOriginal();
                $dados['idUsuario'] = $_SESSION['user']['idUsuario'];

                // Tratamento de envio de fotos de eventos
                if ($isEvento) { // foto de evento
                    $dados['descricao'] = $upload->getNomeOriginal();
                    $dados['nomeOriginal'] = Helper::sanitize($dados['nomeOriginal']);
                    $dados['idUsuario'] = (int) $dados['idFotografo'];
                    $dados['fonte'] = '';
                }
                return $this->uploadFileSetStorage($upload, $dir, $dados); // upload comum
            } else {
                return array('error' => $result);
            }
        }
    }

    /**
     * Salva o arquivo recem enviado no storage adequado.
     * 
     * 01/07/2019: criei o campo is_on_fs para definir se o arquivo já esta em fileserver. Portanto, não será necessário o upload imediato aqui
     * @param Upload $upload
     * @param type $dir
     * @param type $dados
     * @return type
     */
    public function uploadFileSetStorage($upload, $dir, $dados) {
        if (!($upload instanceof Upload)) {
            return ['error' => 'Erro (APP-249)'];
        }

        $entidade = Config::getData('path') . '/auto/entidades/' . ucwords(Helper::name2CamelCase(mb_strtolower($dados['entidade']))) . '.class.php';

        if ((int) $dados['valorid'] <= 0 || !file_exists($entidade)) { // qq situação dessas vai pra classificar depois
            Log::logTxt('upload-data', "classificar: $entidade | $dados[valorid]");
            $dados['valorid'] = 1;
            $dados['entidade'] = 'CLASSIFICAR';
        }

        $fileTmp = $dir . DIRECTORY_SEPARATOR . $upload->getNome();
        $thumbs = Helper::thumbsOnName($fileTmp);
        $filename = Config::getData('filesPrefix') . '/' . $upload->getNome();
        $storageInCrontab = Helper::compareString($dados['entidade'], 'EVENTO'); /// se for evento, adiar o storage pq se dara em crontab. os demais ja sobe
        // preparar o arquivo na pasta correta para o CRONTAB poder identificar
        // file save
        $stUploadfile = 'Local';
        if (!$storageInCrontab) {
            $stFsUploadfile = 3;
            $storage = new Storage();
            $stUploadfile = $storage->adapterName;
            $storage->loadFile($fileTmp, true)->setPath($filename)->upload();
            //$storage->save($filename, fopen($fileTmp, 'r'), false, true);
            unlink($fileTmp);
        } else {
            $stFsUploadfile = 1;
        }
        $save = true;


        //thumbs
        if ($save) {
            $dao = new EntityManager();
            if (!$storageInCrontab && file_exists($thumbs)) {
                $storage->loadFile($thumbs, true)->setPath($filename)->upload(true); // thumbs
                unlink($thumbs);
            }
            // agora sim, salvar a entidade no indice uploadfile
            $file = new Uploadfile();
            $file->setCreatetimeUploadfile('NOW');
            $file->setFilenameUploadfile($filename);
            $file->setNomeUploadfile($dados['nomeOriginal']);
            $file->setEntidadeUploadfile(Helper::upper($dados['entidade']));
            $file->setValoridUploadfile($dados['valorid']);
            $file->setIdUsuario($_SESSION['user']['idUsuario']);
            $file->setMimeUploadfile($upload->getMime());
            $file->setExtensaoUploadfile($upload->getTipo());
            $file->setDescricaoUploadfile($dados['descricao']);
            $file->setRecognitionId($dados['recognitionId']);
            $file->setFonteUploadfile($dados['fonte']);
            $file->setStFsUploadfile($stFsUploadfile);
            $file->setStUploadfile($stUploadfile);
            //$file->setAtletaUploadfile($dados['atleta']);
            $dao->setObject($file)->save('on conflict on constraint app_uploadfile_un do update set st_fs_uploadfile='.$stFsUploadfile);
            $out = UploadfileController::toEntitie($dao->getObject());

            // log
            $dados['id' . $dados['entidade']] = $dados['valorid'];
            Log::navegacaoApi($dados['entidade'] . "/Adicionar arquivo: " . $upload->getNomeOriginal() . '.' . $upload->getTipo(), $dados, true);


            return $out;
        } else {
            return ['error' => 'Erro ao salvar arquivo no Storage: ' . $storage->error];
        }
    }

    /**
     * Ira definir na tabela a entidade que pertence o arquivo enviado
     * 
     * @update 11/12/2018 - acrescentando o linktable ao salvar a entidade. Não verdade, não mais será necessário entidade aqui. alterar numa próxima versão
     * @param type $json
     * @param type $entidade
     * @param type $id
     * @param type $dao
     * @return type
     */
    public static function trataUploadFile($json, $entidade, $id, &$dao) {
        //Log::logTxt('trataUploadFile-JSON', $json);
        Helper::upperByReference($entidade);
        $atualObject = $dao->getObject();
        $out = [];
        $data = Helper::jsonToArrayFromView($json);
        $error = false;
        foreach ($data as $upItem) {
            $upload = new Uploadfile($upItem);
            if ($upload->getId() > 0 && Helper::compareString($upload->getEntidadeUploadfile(), 'classificar')) {
                $upload->setEntidadeUploadfile($entidade);
                $upload->setValoridUploadfile($id);
                $err = $dao->setObject($upload)->save()->getError();
                if ($err !== false) {
                    $error[] = 'Erro ao atualizar o indice do arquivo (APP-260)';
                }
            }
        }
        $dao->setObject($atualObject);
        return array('error' => $error, 'files' => $out);
    }

    private function clearDir($dir, $qtdeDias, $loglabel, $trash = true) {
        $handle = opendir($dir);
        if ($handle) {
            while (false !== ($file = readdir($handle))) {
                $filename = $dir . DIRECTORY_SEPARATOR . $file;
                $dtCriacao = filemtime($filename);
                $dtRemover = Helper::dateToMktime() - (60 * 60 * 24 * $qtdeDias);
                if ($dtCriacao < $dtRemover) { // mover para lixeira
                    $newname = Config::getData('local', 'path') . '/sistema/.trash/' . $file;
                    $log[] = $file;
                    if ($trash) {
                        rename($filename, $newname);
                    } else {
                        
                    }
                }
            }
            $nomeatual = $_SESSION['user']['nome'];
            $_SESSION['user']['nome'] = 'SISTEMA';
            //Log::log($logLabel, $log);
            $_SESSION['user']['nome'] = $nomeatual;
            closedir($handle);
        }
    }

    public static function clearUploadFile() {
        $dirs[] = Config::getData('path') . '/ns-app/tmp';
        $dirs[] = Config::getData('path') . '/ns-app/tmp/thumbs';
        foreach ($dirs as $dir) {
            $handle = opendir($dir);
            if ($handle) {
                while (false !== ($file = readdir($handle))) {
                    $filename = $filename = $dir . DIRECTORY_SEPARATOR . $file;
                    $dtCriacao = filemtime($filename);
                    $dtRemover = Helper::dateToMktime() - (60 * 60 * 24 * 5); // 5 dias atras;
                    if ($dtCriacao < $dtRemover) { // mover para lixeira
                        $nomeatual = $_SESSION['user']['nome'];
                        $_SESSION['user']['nome'] = 'SISTEMA';
                        Helper::deleteFile($filename);
                        $_SESSION['user']['nome'] = $nomeatual;
                    }
                }
                closedir($handle);
            }
        }
    }

    public static function clearTrash($days = 31) {
        $dir = Config::getData('path') . '/.trash';
        $handle = opendir($dir);
        if ($handle) {
            while (false !== ($file = readdir($handle))) {
                $filename = $dir . DIRECTORY_SEPARATOR . $file;
                $dtCriacao = filemtime($filename);
                $dtRemover = Helper::dateToMktime() - (60 * 60 * 24 * $days); // 31 dias de lixeira
                if ($dtCriacao < $dtRemover) { // mover para lixeira
                    $nomeatual = $_SESSION['user']['nome'];
                    $_SESSION['user']['nome'] = 'SISTEMA';
                    Helper::deleteFile($filename, false, false); // retirar da lixeira
                    $_SESSION['user']['nome'] = $nomeatual;
                }
            }
            closedir($handle);
        }
    }

    public static function getAux($extras, $entidade) {
        $out = [];
        $out += [
            'Ativo' => [
                ['idAtivo' => '1', 'nomeAtivo' => 'Ativo'],
                ['idAtivo' => '2', 'nomeAtivo' => 'Inativo']
            ],
            'Boolean' => [
                ['idBoolean' => 'true', 'nomeBoolean' => 'Sim'],
                ['idBoolean' => 'false', 'nomeBoolean' => 'Não'],
            ],
            'unidadeMedidas' => [
                ['idUnidadeMedidas' => 'KG', 'nomeUnidadeMedidas' => 'KG'],
                ['idUnidadeMedidas' => 'UNIDADE', 'nomeUnidadeMedidas' => 'UNIDADE'],
                ['idUnidadeMedidas' => 'METROS', 'nomeUnidadeMedidas' => 'METROS'],
                ['idUnidadeMedidas' => 'BRL', 'nomeUnidadeMedidas' => 'BRL'],
                ['idUnidadeMedidas' => 'USD', 'nomeUnidadeMedidas' => 'USD'],
                ['idUnidadeMedidas' => 'EUR', 'nomeUnidadeMedidas' => 'EUR'],
            ],
            'StatusFinanceiro' => [
                ['idStatusFinanceiro' => '', 'nomeStatusFinanceiro' => 'Todos'],
                ['idStatusFinanceiro' => '1', 'nomeStatusFinanceiro' => 'Com pendência'],
                ['idStatusFinanceiro' => '2', 'nomeStatusFinanceiro' => 'Sem pendência']
            ],
            'TipoMatricula' => [
                ['idTipoMatricula' => '', 'nomeTipoMatricula' => 'Todos'],
                ['idTipoMatricula' => '1', 'nomeTipoMatricula' => 'Somente inscrição'],
                ['idTipoMatricula' => '2', 'nomeTipoMatricula' => 'Somente matrícula']
            ],
        ];

        if (count($extras) > 0) {
            $dao = new EntityManager();
            $app = new AppLibraryController();
            foreach ($extras as $item) {
                $item = ucwords(Helper::name2CamelCase($item));
                if (file_exists(Config::getData('path') . "/auto/entidades/$item.class.php")) {
                    if (Helper::compareString($item, 'Status')) {
                        $st = new StatusController();
                        $out[$item] = $st->getToAux($entidade);
                    } else {
                        $object = new $item();
                        $condicao = [];

                        if (method_exists($object, 'setIdUsuario') && !Helper::compareString($item, 'usuario')) {
                            $object->setIdUsuario($_SESSION['user']['idUsuario']);
                            $condicao['idUsuario'] = $_SESSION['user']['idUsuario'];
                        }
                        if (method_exists($object, 'setIdEmpresa') && !Helper::compareString($item, 'empresa')) {
                            $object->setIdEmpresa($_SESSION['user']['idEmpresa']);
                            $condicao['idEmpresa'] = $_SESSION['user']['idEmpresa'];
                        }
                        if (method_exists($object, 'setIsAlive' . $item)) {
                            $condicao['isAlive' . $item] = 'true'; // somente deve mostrar tuplas vivas. Deletadas devem ser obtidas explicitamente.
                        }




                        $l = $dao->setObject($object)->getAll($condicao, false);
                        $list = $app->objectToArray($l, false);
                        $out[$item] = $list;
                    }
                }
            }
        }

        return $out;
    }

    private function licence($licenca) {
        return true; //desativado nesta aplicação
        /*
          if (Config::getData('dev')) {
          //return true;
          }

          // fazer acesso ao nsPermisso para validação de licenca - mensalidade
          $url = 'https://www.nextstage.com.br/produtos/permisso/api/licenca/status/';
          // /3: Licenca SAAS, /6: ID do Produto
          $url .= trim(strtolower($licenca)) . '/3/6';
          $ret1 = Helper::curlCall($url);

          //Log::ver($ret1->content);
          //die();
          $ret = json_decode($ret1->content);
          if ($ret->status === 200) { // caso ocorra erro na comunicação de validação, não interromper uso, mas me avisar por log de error
          $_SESSION['licenceValid'] = $ret->content->is_valid;
          $this->licenceData = $ret->content;
          if ($ret->content->is_valid !== true) {
          Api::result(200, ['error' => $ret->error]);
          //die('<h3>Licença de uso expirada. Contate o administrador.</h3>' . $ret->content->link);
          }
          } else {
          Log::error('Erro na validação de licença: ' . $ret1->content);
          }
         */
    }

    public static function init() {
        if (!self::$dao) {
            self::$dao = new EntityManager();
        }
        if (!$_SESSION['idUserMaster'] || !$_SESSION['idBryan']) {
            $list = self::$dao->setObject(new Usuario())->getAll(['tipoUsuario' => ['in', '(4,6)']], false);
            foreach ($list as $item) {
                if ($item->getTipoUsuario() === 4) { // master
                    $_SESSION['idUserMaster'] = $item->getId();
                }
                if ($item->getTipoUsuario() === 6) { // bryan
                    $_SESSION['idBryan'] = $item->getId();
                }
            }
        }
    }

    /**
     * Atendenrá as demandas de notificação do sistema. Elaborar como será  asaida disso
     * @date 09/01/2019
     * @update 04/06/2019
     * @param type $data
     */
    public static function notify($idUsuario, $text, $type = 'messenger', $subject = '', $args = []) {
        $args['idUsuario'] = $idUsuario;
        $args['text'] = $text;
        if (!$idUsuario || !$text) {
            Log::log('debug', "Notififcar envolvido: Notificação não criada por falta de parametros", false, false, $args);
            return false;
        }

        self::init();
        if (is_array($idUsuario)) {
            foreach ($idUsuario as $item) {
                self::notify($item, $text, $type, $subject, $args);
            }
            return true;
        }

        $user = self::$dao->setObject(new Usuario())->getById($idUsuario);
        if (!($user instanceof Usuario)) {
            Log::log('debug', "Notififcar envolvido: Notificação não criada. Usuário não existe", false, false, $args);
            return false;
        }

        // Notificação através do comunicador
        $m = new Mensagem([
            'idUsuario' => $_SESSION['idBryan'],
            'destinoIdMensagem' => $user->getId(),
            'textoMensagem' => $text//Helper::codifica($text)
        ]);

        $texto = (new Template(false, [
                    'TITLE' => $subject,
                    'CONTENT' => $text
                        ]))->render();

        switch ($type) {
            case 'all':
                self::$dao->setObject($m)->save();
                sendMailUtil::send($user->getEmailUsuario(), $user->getNomeUsuario(), $subject, $texto);
                break;
            case 'email':
                sendMailUtil::send($user->getEmailUsuario(), $user->getNomeUsuario(), $subject, $texto);
                break;
            default: // onlyMessenger
                self::$dao->setObject($m)->save();
        }


        return true;
    }

    public static function notificationHtml() {
        return '
            <!-- notification sound -->
            <audio id="mnr-sound-player" style="display:none;">
                <source src="' . Config::getData('url') . '/view/audio/notification.mp3" />
                <embed src="' . Config::getData('url') . '/view/audio/notification.mp3" hidden="true" autostart="false" loop="false"/>
            </audio>
            ';
    }

}
