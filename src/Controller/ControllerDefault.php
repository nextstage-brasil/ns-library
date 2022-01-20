<?php

namespace NsLibrary\Controller;

/**
 * TODO Auto-generated comment.
 */
class ControllerDefault extends AbstractController {

    public function __construct($entidadeName, $entidadeObject, $poderesGrupo, $poderesSubGrupo, $camposDate = [], $camposDouble = [], $camposJson = []) {
        $this->ent = $entidadeName;
        $this->camposDate = $camposDate;
        $this->camposDouble = $camposDouble;
        $this->camposJson = $camposJson;
        $this->camposCrypto = \NsLibrary\Config::getData('fieldCrypto')[$entidadeName] ?? [];
        $this->poderesGrupo = $poderesGrupo;
        $this->poderesSubGrupo = $poderesSubGrupo;

        $this->condicao = [];
        $this->object = $entidadeObject;

        if (method_exists($this->object, 'setIdUsuario') && !Helper::compareString($this->ent, 'usuario')) {
            $this->object->setIdUsuario($_SESSION['user']['idUsuario']);
            $this->condicao['idUsuario'] = $_SESSION['user']['idUsuario'];
        }
        if (method_exists($this->object, 'setIdEmpresa') && !Helper::compareString($this->ent, 'empresa')) {
            $this->object->setIdEmpresa($_SESSION['user']['idEmpresa']);
            $this->condicao['idEmpresa'] = $_SESSION['user']['idEmpresa'];
        }
        if (method_exists($this->object, 'setIsAlive' . $this->ent)) {
            $this->condicao['isAlive' . $this->ent] = 'true';  // somente deve mostrar tuplas vivas. Deletadas devem ser obtidas explicitamente.
        }
    }

    public function toView($object) {
        $ret = $object->toArray();
        // Decrypto
        foreach ($this->camposCrypto as $val) {
            $ret[$val] = \NsLibrary\SistemaLibrary::decrypt($ret[$val], $this->object->getTable());
        }
        return $ret;
    }

    ## Metodos padrão para WebService (ws)

    public function ws_getNew() {
        return $this->toView($this->object);
    }

    public function ws_getById($dados) {
        $this->object->read($dados['id']);
        if ($this->object->getError()) {
            return ['error' => $this->object->getError()];
        }
        return $this->toView($this->object);
    }

    public function ws_getAll($dados) {

        // IDs esperados
        foreach (['id'] as $v) {
            if ((int) $dados[$v] > 0) {
                $this->condicao[$v] = (int) $dados[$v];
            }
        }

        if ($dados['count']) {
            return $this->object->count($this->condicao);
        }

        // Paginação
        $page = (int) $dados['pagina'] ?? (int) $dados['page'];
        $limit = 30;

        // Order
        $order = $dados['order'] ?? \NsUtil\Helper::reverteName2CamelCase($this->object->getCpoId()) . ' desc';

        // Search, caso exista
        parent::setSearch($dados);

        // Itens
        $list = $this->object->list($this->condicao, $page, $limit, $order);
        $out = [];
        foreach ($list as $object) {
            $item = $this->toView($object);
            unset($item['error']);
            $out[] = $item;
        }

        $this->setDadosComboSearch($dados, $out, $this->ent);

        return $out;
    }

    /**
     * @create 18/01/2022
     * Metodo responsavel por salvar uma entidade
     */
    public function ws_save($dados) {
        $action = ( ((int) $dados['id' . $this->ent] > 0) ? 'Editar' : 'Inserir');
        $isUpdate = $action === 'Editar';

        if (method_exists($this->object, 'setIdUsuario') && !Helper::compareString($this->ent, 'usuario')) {
            $dados['idUsuario'] = $this->condicao['idUsuario'];
        }

        if (method_exists($this->object, 'setIdEmpresa') && !Helper::compareString($this->ent, 'empresa')) {
            $dados['idEmpresa'] = $this->condicao['idEmpresa'];
        }

        // Se vier id, validar se existe
        if ($isUpdate) {
            $this->object->read($dados['id' . $this->ent]);
            if ($this->object->getError()) {
                return ['error' => $this->object->getError()];
            }
        }

        // Encryptar dados
        foreach ($this->camposCrypto as $val) {
            if ($dados[$val]) {
                $dados[$val] = \NsLibrary\SistemaLibrary::encrypt($dados[$val], $this->object->getTable());
            }
        }

        // Popular com os dados enviados
        $this->object->populate($dados);

        // Nem vai salvar com error
        if ($this->object->getError()) {
            return ['error' => $this->object->getError()];
        }

        $id = $this->object->save()->getId();

        // Retornar o objeto persisitido
        $t = $this->ws_getById(['id' => $id]);
        $t['result'] = [
            'message' => (($isUpdate) ? 'Atualizado' : 'Inserido') . ' com sucesso',
            'icon' => 'success'
        ];
        return $t;
    }

    /**
     * @create 18/01/2022
     * Metodo responsavel por remover uma entidade
     */
    public function ws_remove($dados) {
        $this->object->read($dados['id']);
        if ($this->object->getError()) {
            return ['error' => $this->object->getError()];
        }

        $res = $this->object->remove();
        $out['error'](($res === true) ? false : $res);

        $out['result'] = [
            'message' => (($res === true) ? 'Removido com sucesso' : $res),
            'icon' => 'success'
        ];
        return $out;
    }

}
