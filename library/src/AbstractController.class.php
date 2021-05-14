<?php

/**
 * TODO Auto-generated comment.
 */
abstract class AbstractController {

    protected $object, $condicao, $ent, $camposDouble, $camposDate, $camposJson, $jsonDefault, $lastObjectSave, $condicaoManual, $extrasA, $extrasB;

    public function save(&$dados) {
        Helper::jsonRecebeFromView($dados, $this->camposJson);
        $dao = new EntityManager();

        $this->setIds($dados);

        $ent = new $this->ent($dados);
        $dao->setObject($ent);
        if ($ent->getId()) {
            $actual = $dao->setObject($ent)->getById($ent->getId());
            if ($actual instanceof $this->ent) {
                $actual->populate($dados); // somente preencher os dados enviados, mantendo os atuais não editados no padrão já existente
                $dao->setObject($actual);
            }
        }

        $dao->beginTransaction();

        // Salvar entidade
        if ($dao->save()->getError()) {
            Api::result(200, ['error' => $dao->getObject()->getError()]);
        }
        //Log::logTxt('debug-dd', 'save-ok');

        $ID = $dao->getObject()->getId();

        // Tratamento de uploadfiles
        $error = AppLibraryController::trataUploadFile($dados['Files'], $this->ent, $ID, $dao)['error'];
        if ($error) {
            Api::result(200, ['error' => $error]);
        }

        // commit
        $dao->commit();

        $this->lastObjectSave = $dao->getObject();

        return $ID;
    }

    /**
     * @date 03/02/2019
     * @param type $object
     * @param type $condicao
     * @param type $getRelacao
     * @param type $inicio
     * @param type $limit
     * @param type $order
     * @param type $relacaoException
     * @return type
     */
    public function getAll(&$dados, $getRelacao = true, $inicio = 0, $fim = 1000, $order = false) {
        $this->setSearch($dados);
        $dao = new EntityManager($this->object);
        $dao->setOrder($order);
        $dao->selectExtra = $this->extrasA;
        $dao->selectExtraB = $this->extrasB;
        if ($this->condicaoManual) {
            $this->condicao = array_merge($this->condicao, $this->condicaoManual);
        }
        
        $out = $dao->getAll($this->condicao, $getRelacao, $inicio, $fim);
        return $out;
    }

    /**
     * @date 03/02/2019
     * @param type $id
     * @param type $relacao
     * @return type
     */
    public function getById($id, $relacao = true) {
        $this->condicao[$this->object->getCpoId()] = (int) $id;
        $dados = [];
        $object = $this->getAll($dados, $relacao, 0, 1, false)[0];
        return $object;
    }

    public function count() {
        $dao = new EntityManager($this->object);
        return ['count' => $dao->count($this->condicao)];
    }

    /**
     * @date 03/02/2019
     * @param type $id
     * @return type
     */
    public function remove($id) {
        $fn = 'setIsAlive' . $this->ent;
        $dao = new EntityManager($this->object);
        if (method_exists($this->object, $fn)) {
            // se existir, apenas sinalizar tupla como isAlive = false
            $ent = $dao->getById($id);
            if (!($ent instanceof $this->ent)) {
                Log::error('Registro não localizado para setar isAlive=false', ['entidade' => $this->ent, 'id' => $id]);
                return ['error' => false];
            }
            $ent->$fn('false');
            $dao->setObject($ent)->save();
            return ['error' => false];
        } else {
            $trash = TrashController::move($this->ent, $id);
            return ['error' => $trash->getError(), 'idTrash' => $trash->getId()];
        }
    }

    public function setIds(&$dados) {
        if ($dados['ignoreSetIdUser'] !== true && method_exists($this->object, 'setIdUsuario') && !Helper::compareString($this->ent, 'usuario')) {
            $dados['idUsuario'] = $_SESSION['user']['idUsuario'];
        }
        if (method_exists($this->object, 'setIdEmpresa') && !Helper::compareString($this->ent, 'empresa')) {
            $dados['idEmpresa'] = $_SESSION['user']['idEmpresa'];
        }
    }

    /**
     * Parse do objeto para array e entre no view
     * @param type $object
     * @return type
     */
    public function toView($object) {
        $out = $this->parseToView($object, $this->ent, $this->camposDate, $this->camposDouble, true);

        if (is_array($out['error'])) {
            if (count($out['error']) === 0) {
                $out['error'] = false;
            }
        }

        // tratamento dos campos JSON para sempre ter o padrão definido atribuido
        // jsonDefault esta definido no controller que chamou o abstract
        foreach ($this->camposJson as $item) {
            $out[$item] = Helper::extrasJson(Config::getModelJson($item), $out[$item]);
        }


        return $out;
    }

    public function ws_cep($dados) {
        $dd = BuscaCep::get($dados['cep']);
        if ($dd) {
            $rua = (($dados['numero']) ? "$dd[logradouro], $dados[numero]" : $dd['logradouro']);
            $dd['endereco'] = "$rua - $dd[bairro] - $dd[localidade]/$dd[uf]";
        } else {
            $dd['error'] = 'CEP Não localizado';
        }
        return $dd;
    }

    public static function _objectToArrayStatic($object, $detalhes = false) {
        return self::objectToArrayStatic($object, $detalhes);
    }

    public function objectToArray($object, $relacoes = true) {
        $array = array();
        if (is_object($object)) {
            $reflectionClass = new ReflectionClass(get_class($object));
            foreach ($reflectionClass->getProperties() as $property) {
                $property->setAccessible(true);
                // campo nao permitido, tabela logs, e detalhes
                if ((int) array_search($property->getName(), ['', 'table', 'cpoId', 'senha', 'password']) > 0) {
                    continue;
                }

                if (is_object($property->getValue($object))) { // caso object, repetir chamada
                    if ($relacoes) {
                        $array[$property->getName()] = $this->objectToArray($property->getValue($object));
                    } else {
                        unset($array[$property->getName()]);
                    }
                } else {
                    // core do parse
                    $VALOR = $property->getValue($object);
                    switch (true) {
                        case ($VALOR === 'true'):
                            $array[$property->getName() . 'F'] = 'Sim';
                            break;
                        case ($VALOR === 'false'):
                            $array[$property->getName() . 'F'] = 'Não';
                            break;
                        case (strpos($property->getName(), 'xtras') === 1): // campos extras, tipo JSON
                            $VALOR = json_decode($VALOR, true);
                            break;
                        default:
                    }
                    $array[$property->getName()] = $VALOR;
                }
                $property->setAccessible(false);
            }
        } else if (is_array($object)) {
            foreach ($object as $value) {
                $array[] = $this->objectToArray($value);
            }
        }
        return $array;
    }

    public static function nameTableCamelCase($val) {
        $val = str_replace(['sis_', 'mem_'], '', $val);
        $temp = explode("_", $val);
        if (is_array($temp)) {
            foreach ($temp as $val) {
                $entidade .= ucwords($val);
            }
        } else {
            $entidade = $temp;
        }
        return $entidade;
    }

    public static function arrayToObject($entity, $dados) {
        if (!is_array($dados)) {
            return $entity;
        }
        foreach ($dados as $key => $val) {
            $method_name = "set" . ucwords($key);
            $get = "get" . ucwords($key);
            if (method_exists($entity, $method_name) && $key != "error" && ucwords($key) != $key) {
                $val = (($val === 'null') ? '' : $val);
                $entity->$method_name($val);
            }
        }
        return $entity;
    }

    public function getMaxId($object, $condicao = false) {
        $em = new EntityManager($object);
        return $em->getMaxId($condicao);
    }

    public function getMinId($object, $condicao = false) {
        $em = new EntityManager($object);
        return $em->getMinId($condicao);
    }

    /**
     * Método para preparar buscar de geolocalização. 
     * @param type $dados
     * @return type
     */
    public function ws_getGeoByAddress($dados) {
        $ad[] = $dados['street'];
        $ad[] = $dados['number'];
        $ad[] = $dados['city'];
        $ad[] = $dados['state'];
        $ad[] = $dados['zipcode'];
        $ad[] = $dados['country'];
        $address = implode(', ', $ad);
        return GeoLocalizacao::getGeoByAddress($address);
    }

    protected function parseToView($object, $entidade, $campoDate, $campoDouble, $files = true) {
        $out = ['error' => Translate::get('NOT_FOUND')];
        $ent = $entidade;
        if ($object instanceof $entidade) {
            $dao = new EntityManager();
            $out = Helper::parseDateToDatePTBR($object, $campoDate, $campoDouble);
            if ($files) {
                $out += [
                    'Files' => UploadfileController::getFiles($ent, $object->getId(), $dao),
                ];
            }
        }
        $user = $out['Usuario'];
        unset($out['Usuario']); // nunca enviar usuario como relacionamento
        $out['Usuario'] = ['nomeUsuario' => $user['nomeUsuario']];

        return $out;
    }

    protected function setSearch(&$dados) {
        if (strlen($dados['Search']) > 1) {
            $dados['Search'] = urldecode($dados['Search']);
            $this->condicao['unaccent(nome' . $this->ent . ')'] = array('~*', "unaccent('" . $dados['Search'] . "')");
        }
    }

    protected function setDadosComboSearch(&$dados, &$out, $ent) {
        if (strlen($dados['Search']) > 1) {
            foreach ($out as $value) {
                $dd[] = ['id' => $value['id' . $ent], 'value' => $value['nome' . $ent]];
            }
            if ($dd) {
                $out['comboSearchList'] = $dd;
            }
        }
    }

    /*
     *  método que somente atualiza o token em operação
     */

    public function ws_sessionRenew($dados) {
        return ['error' => false, 'result' => 'Sessão renovada!'];
    }

    public function ws_validaLogin($dados) {
        return ['error' => false];
    }

    /*
      public function ws_getJsComponent($dados) {
      return [
      'error' => false, 'component' => Component::getContentJS('ns' . $dados['name'])
      ];
      }
     */

    /**
     * Métod sera chamado pelo uploadfile, quando estiver setado avatar
     * @date 2019-03-14
     * @param type $dados
     */
    public function ws_setAvatar($dados) {
        $entidade = ucwords(Helper::name2CamelCase($dados['entidade']));
        $id = $dados['valorid'];
        $idUploadfile = $dados['idUploadfile'];
        $filename = Config::getData('path') . '/auto/entidades/' . $entidade . '.class.php';
        if (file_exists($filename)) {
            $ent = new $entidade();
            $dao = new EntityManager($ent);
            $item = $dao->getById($id);
            if ($item instanceof $entidade) {
                // obter nome do campo relacionado
                foreach ($ent->getRelacionamentos() as $rel) {
                    if ($rel['tabela'] === 'app_uploadfile') {
                        $fn = 'set' . Helper::name2CamelCase($rel['cpoOrigem']);
                        $item->$fn($idUploadfile);
                        $dao->setObject($item)->save();
                        return $this->objectToArray($dao->getObject());
                    }
                }
            }
        }
    }

    public function setCondicaoManual(array $condicao) {
        $this->condicaoManual = $condicao;
    }

    public static function naoDisponivel() {
        header("Location:" . Config::getData('url') . '/home');
    }
    
    public function getObject()   {
        return $this->object;
    }

}
