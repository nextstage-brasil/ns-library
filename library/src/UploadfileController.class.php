<?php

if (!defined("SISTEMA_LIBRARY")) {
    die("Acesso direto não permitido");
}

/**
 * 
 * @date 11/07/2018 12:21
 */
class UploadfileController extends AbstractController {

    /**
     * @create 08/07/2017
     */
    public function __construct() {
        $this->ent = 'Uploadfile';
        $this->camposDate = ['createtimeUploadfile'];
        $this->camposDouble = [];
        $this->camposJson = [];

        $this->condicao = [];
        $this->object = new $this->ent();
    }

    ## Metodos padrão para WebService (ws)
    ## Metodos padrão para WebService (ws)

    /**
     * @create 03/02/2019
     * Método responsavel por devolver uma entidade nova e vazia
     */
    public function ws_getNew() {
        $up = new Uploadfile();
        $up->setFilenameUploadfile('');
        return $this->uploadEntidade($up);
        return parent::toView($this->object);
    }

    /**
     * @create 04/03/2019
     * Leitra de objeto
     */
    public function ws_getById($dados) {
        $dao = new EntityManager(new Uploadfile());
        $upload = $dao->getById($dados['idUploadfile']);

        if ($upload instanceof Uploadfile) {
            $up = $this->uploadEntidade($upload);
            return $up;
        } else {
            return [];
        }
    }

    public static function toEntitie(Uploadfile $upload) {
        $ctr = new UploadfileController();
        return $ctr->uploadEntidade($upload);
    }

    // método que retorna uma entidade extra entidade registrada
    public function uploadEntidade($upload) {
        if ($upload instanceof Uploadfile) {
            $file = parent::objectToArray($upload, false);
            $file['icon'] = Helper::getThumbsByFilename($upload->getFilenameUploadfile());
            $file['filenameUrl'] = $this->getLinkToFile($upload->getFilenameUploadfile(), $upload->getId(), $upload->getStFsUploadfile());
            $file['perfil'] = (($upload->getClassificacaoUploadfile() === 1) ? 'Privado' : 'Público');
            $file['perfilIcon'] = (($upload->getClassificacaoUploadfile() === 1) ? 'lock' : 'globe');
            $file['nomeUploadfile'] = (($file['nomeUploadfile']) ? $file['nomeUploadfile'] : $file['filenameUploadfile']);
            $file['createtimeUploadfile'] = Helper::formatDate($upload->getCreatetimeUploadfile(), 'mostrar', true);
            $file['dateUploadfile'] = Helper::formatDate($upload->getCreatetimeUploadfile(), 'mostrar', false);
            $file['Usuario']['nomeUsuario'] = (($upload->getIdUsuario() === $_SESSION['user']['idUsuario']) ? 'Eu mesmo' : $file['Usuario']['nomeUsuario']);
            //if ($file['icon'] === 'file-image-o') {

            $ret = NsStorageLibrary\Config::init();
            $f = base64_encode(Helper::codifica($upload->getId()));
            //$file['filenameUrl'] = Config::getData('url') . '/fr?f=' . $f; //$upload->getId() . '_' . $file['extensaoUploadfile'];
            switch ($upload->getStUploadfile()) {
                case 'Filerun':
                    //$file['filenameUrl'] = Config::getData('url') . '/fr?f=' . $f;//$upload->getId() . '_' . $file['extensaoUploadfile'];
                    // trata de como pegar o linkpublic aqui
                    $url = $upload->getFilenameUploadfile();
                    break;
                default: // 28/10/2019: Caso não esteja definido, será tratado como um storage padrão
                    //$file['filenameUrl'] = Config::getData('url') . '/fr?f=' . $upload->getId() . '_' . $file['extensaoUploadfile'];
                    $url = $ret[$upload->getStUploadfile()]['linkPublic'] . '/' . $upload->getFilenameUploadfile();
                    break;
            }

            $file['thumbs'] = $url;// Helper::thumbsOnName($url); // URL pode mudar conforme storage
            $icon = Helper::getThumbsByFilename($file['thumbs']);
            if ($icon !== 'file-image-o') {
                $file['thumbs'] = Config::getData('urlView') . '/images/' . $icon . '.png';
            }

            //$file['thumbs'] = $this->getThumbs($url, $upload->getStFsUploadfile());
        } else {
            $file = [];
        }
        //}
        return $file;
    }

    /**
     * Método que retorna o thumbs do filename
     * @param type $filename
     * @param type $entidade
     * @return string
     */
    public function getThumbs($filename, $inTmpFolder = false) {
        return self::getThumbsStatic($filename, $inTmpFolder);
    }

    /**
     * 
     * @param type $filename
     * @param type $inTmpFolder: define se a foto ainda esta no diretorio temporario de upload
     * @return string
     */
    public static function getThumbsStatic($filename, $inFsServer = false) {

        //$filename = Helper::thumbsOnName($filename);
        // verificar se o arquivo ainda está em app
        if ($inFsServer < 3 && strlen($filename) > 0) {
            $file = Config::getData('url') . '/app/tmp/' . $filename;
            return Helper::thumbsOnName($file);
        }

        $f = $filename;
        if (Config::getData('fileserver', 'StoragePublic') === 'FileRun') {
            $f = urlencode($filename); // encode pois o arquivo vai como parametro
        }
        $out = $_SESSION['app']['linkPublic'] . $f; // . '&mode=default&download=1&path=' . urlencode($filename);

        $icon = Helper::getThumbsByFilename($filename);
        if ($icon !== 'file-image-o') {
            $out = Config::getData('urlView') . '/images/' . $icon . '.png';
        }

        if (!$filename || stripos($filename, '/') === false) {
            return Config::getData('urlView') . '/images/sem-imagem.png';
        }


        return $out;
    }

    public static function link($filename, $temporary = true, $inFsServer = false) {
        $up = new UploadfileController();
        return $up->getLinkToFile($filename, $temporary, $inFsServer);
    }

    /**
     * @param type $filename
     * @param type $temporary
     * @param type $inTmpFolder: define se a foto ainda esta na pasta tmp. Esta no registro de banco de dados
     * @return type
     */
    public function getLinkToFile($filename, $temporary = true, $inFsServer = false) {
        if (strlen($filename) === 0) {
            return Config::getData('url') . '/view/images/sem-imagem.png';
        }
        if ($inFsServer < 3) {
            return Config::getData('url') . '/app/tmp/' . $filename;
        } else {
            //Poderes::verify('Arquivos', 'Gerenciador', 'ver');
            $json = json_encode([
                'filename' => $filename,
                'datetime' => Helper::dateToMktime()
            ]);
            return Config::getData('url') . '/fr/0/' . Helper::codifica($json);
        }
    }

    /**
     * Método para adicionar o ID recebido do recognition face detection
     * @param type $idUploadfile
     * @param type $recognitionReturn
     * @param type $upload Object of uploadfile
     */
    public static function addRecognitionId($idUploadfile, $recognitionReturn, $upload = false) {
        $rec = (object) $recognitionReturn['content'];
        $out = (object) ['error' => false, 'status' => $recognitionReturn['httpStatus']];
        if ($recognitionReturn['httpStatus'] === 200 && $rec->personSamples > 0) {
            $ctr = new UploadfileController();
            if (!$upload) {
                $upload = $ctr->getAll(new Uploadfile(), ['idUploadfile' => $idUploadfile], false, 0, 1)[0];
            }
            if ($upload instanceof Uploadfile) {
                $upload->setRecognitionId($rec->trainId);
                $ctr->save($upload);
                Log::log('RECOGNITION', 'Modelo treinado:  ' . $rec->trainId);
            } else {
                $out->error = 'Arquivo inválido';
            }
        } else {
            $out->error = 'Erro no reconhecimento';
            Log::log('RECOGNITION', 'ERROR: httpStatus: ' . $recognitionReturn['httpStatus'] . ', personSamples: ' . $rec->personSamples . ' - Demais: ' . json_encode($recognitionReturn));
        }
        return $out;
    }

    public function ws_getValoridByFilename($dados) {
        Helper::upperByReference($dados[filename]);
        $this->condicao['filenameUploadfile'] = array('like', "'%$dados[filename]'");
        $dd = parent::getAll($dados)[0];
        $out[] = $this->uploadEntidade($dd);
        return $out;
    }

    public static function getFiles($entidade, $id, &$dao = false) {
        $t = new UploadfileController();
        return $t->ws_getFiles(['entidade' => (string) $entidade, 'valorid' => (int) $id], $dao);
    }

    public function ws_getFiles($dados, &$dao = false) {
        $dao = (($dao) ? $dao : new EntityManager());
        $out = [
            'files' => [],
            'count' => 0
        ];
        if (!$_SESSION['user']['idUsuario']) {
            return ['error' => 'Usuario não logado'];
        }
        if ((int) $dados['valorid'] > 0) {
            $dao->setObject(new Uploadfile());
            Helper::upperByReference($dados['entidade']);
            if ($dados['idFotografo']) {
                $idUsuario = (int) $dados['idFotografo'];
                $condicao = "entidade_uploadfile='$dados[entidade]'"
                        . " and valorid_uploadfile=$dados[valorid] "
                        . "and app_uploadfile.id_usuario= $idUsuario";
            } else {
                $idUsuario = $_SESSION['user']['idUsuario'];
                $condicao = "entidade_uploadfile='" . $dados['entidade'] . "' and valorid_uploadfile=$dados[valorid] and (app_uploadfile.id_usuario=" . $idUsuario . " or classificacao_uploadfile=2)";
            }

            if (strlen($dados['idAtleta']) > 0) {
                $condicao .= ' and fonte_uploadfile ~* \'' . $dados['idAtleta'] . '\' ';
            }
            // não obter arquivos marcados para remover
            $condicao .= ' and st_fs_uploadfile < 4';
            if ((int) $dados['pagina'] === 0) {
                $out['count'] = $dao->count($condicao);
            }
            $entities = $dao->getAll($condicao, true, (int) $dados['pagina'], 10);

            foreach ($entities as $upload) {
                $out['files'][] = $this->uploadEntidade($upload);
            }
        }
        return $out;
    }

    /**
     * @create 03/02/2019
     * Metodo responsavel por gerar relação de dados da entidade. Acesso via JSON.
     */
    public function ws_getAll($dados) {
        Poderes::verify('Uploadfile', 'Uploadfile', 'listar');

        $inicio = (int) $dados['pagina'];
        $fim = 100; // paginação obrigatória
        $getRelacao = ((isset($dados['getRelacao'])) ? $dados['getRelacao'] : true);

        // set search padrão - ira procura por nomeENTIDADE
        $this->setSearch($dados);

        $entities = parent::getAll($dados, $getRelacao, $inicio, $fim, $order);
        $out = Helper::parseDateToDatePTBR($entities, $this->camposDate, $this->camposDouble, $this->camposJson);
        $this->setDadosComboSearch($dados, $out, $this->ent);

        return $out;
    }

    /**
     * @create 08/07/2017
     * Metodo responsavel por salvar uma entidade
     */
    public function ws_save($dados) {
        $entity = new Uploadfile($dados);
        Poderes::verify('Uploadfile', 'Uploadfile', 'Alterar');
        if (method_exists($this->object, 'setIdUsuario')) {
            $dados['idUsuario'] = $this->condicao['idUsuario'];
        }
        if (method_exists($this->object, 'setIdEmpresa')) {
            $dados['idUsuario'] = $this->condicao['idEmpresa'];
        }

        $id = parent::save($dados);

        // Retornar o objeto persisitido
        $t = $this->ws_getById(['id' => $id]);
        $t['result'] = Translate::get('Salvo com sucesso');
        return $t;
    }

    /**
     * @create 17/08/2019
     * Método que marca um arquivo para remoção. Posteriormente, uma atividade ira tratar a remoção em storage
     */
    public function ws_remove($dados) {
        $dao = new EntityManager(new Uploadfile());
        $file = $dao->getById($dados['idUploadfile']);

        #### tratamento especifico para remoção de fotos
        // obter evento
        $idUsuarioEvento = -1;
        if ($file->getEntidadeUploadfile() === 'EVENTO') {
            $evento = $dao->setObject(new Evento())->getById($file->getValoridUploadfile());
            $idUsuarioEvento = $evento->getIdUsuario();
        }


        $dao->beginTransaction();
        // remover file
        if ($file instanceof Uploadfile) {
            // validar propriedade do arquivo: somente o proprietario ou dono do evento pode remover uma foto
            if ($file->getIdUsuario() !== $_SESSION['user']['idUsuario'] && $idUsuarioEvento !== $_SESSION['user']['idUsuario']) {
                return ['error' => 'Função exclusiva do proprietário'];
            }

            // remover do banco de dados: marcar com classificação 3, para posteriormente o CRON atuar
            $file->setStFsUploadfile(4); // marcado para remover
            $dao->setObject($file)->save();
            if ($dao->getObject()->getError() === false) {

                $dao->commit();

                $text = 'Arquivo removido: ' . $file->getNomeUploadfile() . '.' . $file->getMimeUploadfile();
                Log::log('NAVEGACAO', $text, $file->getValoridUploadfile(), $file->getEntidadeUploadfile());


                // remover do disco - remoção em disco se dará pelo CRON
                //$this->deleteFile($file);
                // remover do recognition face
                /*
                  $ret = Meerkart::remove($file->getRecognitionId());
                  if ($ret->httpStatus === 200) {
                  Log::log('Meerkart', "Removido TraindID. " . json_encode(['Entidade' => $file->getEntidadeUploadfile(), 'ID:' . $file->getValoridUploadfile()]));
                  }
                 * 
                 */
                $t = false;
            } else {
                $t = 'Ocorreu um erro ao remover: ' . $t;
            }
            return ['error' => $t];
        } else {
            return ['error' => 'Arquivo não localizado'];
        }
    }

    private function deleteFile(Uploadfile $file) {
        //@rever Storage esta dando error 
        /*
          $path = $file->getFilenameUploadfile();
          $storage = new Storage();
          $storage->delete($path);
          $storage->delete(Helper::thumbsOnName($path));
         */
        return true;
    }

    public static function removeByEntidadeId($entidade, $id) {
        $t = new UploadfileController();
        Helper::upperByReference($entidade);
        $dados = ['entidadeUploadfile' => $entidade, 'valoridUploadfile' => $id];
        $file = $t->getAll($dados)[0];
        if ($file instanceof Uploadfile) {
            // remover do banco de dados
            $t->remove($file);
            $t->deleteFile($file);
        }
        return ['error' => false];
    }

    public function ws_removeByEntidadeFilename($dados) {
        $poderes = Poderes::verify('Uploadfile', 'Uploadfile', 'excluir');
        if (!$poderes->getResult()) {
            return array('error' => $poderes->getMessage());
        }
        $dados = Helper::upper($dados);
        $file = parent::getAll(new Uploadfile(), [
                    'filenameUploadfile' => $dados['filename'],
                    'entidadeUploadFile' => $dados['entidade']
                        ], false)[0];
        if ($file instanceof Uploadfile) {
            // remover do banco de dados
            parent::remove($file);
            // enviar para lixeira arquivo fisico
            Helper::deleteFile(Config::getData('path') . "/files/$dados[entidade]/$dados[filename]");
        }
        return [];
    }

    // Método para alteração da classificação do arquivo em questão
    public function ws_alteraClassificacao($dados) {
        // Criar Objeto
        $upload = parent::getAll(new Uploadfile(), ['idUploadfile' => (int) $dados['idUploadfile']], false)[0];
        if ($upload instanceof Uploadfile) {
            // Verificar se usuário é proprietário
            if ($_SESSION['user']['idUsuario'] !== $upload->getIdUsuario()) {
                $out = $this->uploadEntidade($upload);
                $out['error'] = 'Somente o proprietário pode alterar esta informação';
                return $out;
            }
            // Alterar classificação
            $upload->setClassificacaoUploadfile((($upload->getClassificacaoUploadfile() === 2) ? 1 : 2));
            parent::save($upload);
            return $this->uploadEntidade($upload);
        } else {
            return ['error' => 'Arquivo não localizado (UFC-219)'];
        }
    }

    public function ws_saveName($dados) {
        if (strlen($dados['name']) < 3) {
            return ['error' => 'Nome inválido'];
        }
        $dao = new EntityManager(new Uploadfile());
        $upload = $dao->getAll(['idUploadfile' => (int) $dados['id']], false)[0];
        if ($upload instanceof Uploadfile) {
            // Verificar se usuário é proprietário
            if ($_SESSION['user']['idUsuario'] !== $upload->getIdUsuario() && $upload->getClassificacaoUploadfile() === 1) {
                $out = $this->uploadEntidade($upload);
                $out['error'] = 'Somente o proprietário pode alterar esta informação';
                return $out;
            }
            // Alterar nome
            $upload->setNomeUploadfile($dados['name']);
            $dao->setObject($upload)->save();
            return ['error' => false, 'result' => 'Nome atualizado'];
        } else {
            return ['error' => 'Arquivo não localizado (UFC-359)'];
        }
    }

    public function ws_saveDescription($dados) {
        $dao = new EntityManager(new Uploadfile());
        $upload = $dao->getAll(['idUploadfile' => (int) $dados['id']], false)[0];
        if ($upload instanceof Uploadfile) {
            // Verificar se usuário é proprietário
            if ($_SESSION['user']['idUsuario'] !== $upload->getIdUsuario() && $upload->getClassificacaoUploadfile() === 1) {
                $out = $this->uploadEntidade($upload);
                $out['error'] = 'Somente o proprietário pode alterar esta informação';
                return $out;
            }
            // Alterar nome
            $upload->setDescricaoUploadfile($dados['description']);
            $dao->setObject($upload)->save();
            return ['error' => false, 'result' => Translate::get(['descrição', 'atualizada com sucesso'])];
        } else {
            return ['error' => 'Arquivo não localizado (UFC-359)'];
        }
    }

    public function watermarker($pathImgOrign, $pathWatermarker, $pathSave, $position = []) {
        /*
          // marcadagua
          $img = WideImage::load($pathImgOrign);
          $watermark = WideImage::load($pathWatermarker);

          $new = $img->merge($watermark, $positiont['x'], position['y'], $position['opacity']);
          $new->saveToFile($pathSave, 80);
         * ********* */
    }

}
