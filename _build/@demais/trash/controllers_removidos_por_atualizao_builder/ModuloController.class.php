<?php

if (!defined("SISTEMA_LIBRARY")) {
    die("Acesso direto não permitido");
}

/**
 * 
 * @date 2020-01-13T16:34:50-02:00
 */
class ModuloController extends AbstractController {

    private static $poderesGrupo = 'Modulo';
    private static $poderesSubGrupo = 'Modulo';

    /**
     * @create 13/01/2020
     */
    public function __construct() {
        $this->ent = 'Modulo';
        $this->camposDate = ['createtimeModulo'];
        $this->camposDouble = [];
        $this->camposJson = ['extrasModulo'];

        $this->condicao = [];
        $this->object = new $this->ent();

        if (method_exists($this->object, 'setIdUsuario') && !Helper::compareString($this->ent, 'usuario')) {
            $this->object->setIdUsuario($_SESSION['user']['idUsuario']);
            $this->condicao['idUsuario'] = $_SESSION['user']['idUsuario'];
        }
        if (method_exists($this->object, 'setIdEmpresa') && !Helper::compareString($this->ent, 'empresa')) {
            $this->object->setIdEmpresa($_SESSION['user']['idEmpresa']);
            $this->condicao['idEmpresa'] = $_SESSION['user']['idEmpresa'];
        }
        if (method_exists($this->object, 'setIsAlive' . $this->ent)) {
            $this->condicao['isAlive' . $this->ent] = 'true'; // somente deve mostrar tuplas vivas. Deletadas devem ser obtidas explicitamente.
        }

        /**
          // Models json a ser configurado:

         * */
    }

    /**
     * @create 13/01/2020
     * Chama o método em parent e retorna. Caso seja necessário alguma intervenção nesta classe
     */
    public function toView($obj) {
        return parent::toView($obj);
    }

    ## Metodos padrão para WebService (ws)
    /**
     * @create 13/01/2020
     * Método responsavel por devolver uma entidade nova e vazia
     */

    public function ws_getNew() {
        Poderes::verify(self::$poderesGrupo, self::$poderesSubGrupo, 'inserir');
        return $this->toView($this->object);
    }

    /**
     * @create 13/01/2020
     * Método responsavel por devolver uma entidade nova e vazia
     */
    public function ws_getById($dados) {
        Poderes::verify(self::$poderesGrupo, self::$poderesSubGrupo, 'ler');
        $ent = parent::getById($dados['id'], true);
        return $this->toView($ent);
    }

    /**
     * @create 13/01/2020
     * Metodo responsavel por gerar relação de dados da entidade. Acesso via JSON.
     */
    public function ws_getAll($dados) {
        Poderes::verify(self::$poderesGrupo, self::$poderesSubGrupo, 'ler');

        // IDs esperados
        foreach (['idModulo', 'idEmpresa'] as $v) {
            if ((int) $dados[$v] > 0) {
                $this->condicao[$v] = (int) $dados[$v];
            }
        }

        if ($dados['count']) {
            return parent::count();
        }


        $inicio = (int) $dados['pagina'];
        $fim = 30; // paginação obrigatória
        $getRelacao = ((isset($dados['getRelacao'])) ? $dados['getRelacao'] : true);

        // set search padrão - ira procura por nomeENTIDADE
        $this->setSearch($dados);
        $this->extrasA = "select count(a.id_usuario) as qtde from app_usuario a"
                . " inner join app_linktable b on a.id_usuario= b.id_right_linktable"
                . " inner join app_lt_rel c on c.id_lt_rel = b.id_lt_rel"
                . " where c.nome_lt_rel= 'MODULO|USUARIO' and b.id_left_linktable= modulo.id_modulo";

        $entities = parent::getAll($dados, $getRelacao, $inicio, $fim, $order);
        $out = Helper::parseDateToDatePTBR($entities, $this->camposDate, $this->camposDouble, $this->camposJson);
        $this->setDadosComboSearch($dados, $out, $this->ent);
        for ($i = 0; $i < count($out); $i++) {
            // calculo da carga horaria
            $dur_minutos = explode(':', $out[$i]['extrasModulo']['dur']);
            $minutos_totais = ($dur_minutos[0] * 60 + $dur_minutos[1]) * (int) $out[$i]['extrasModulo']['enc'];
            $horas = floor($minutos_totais / 60);
            $minutos = $minutos_totais % 60;

            $out[$i]['ch'] = "$horas hrs" . (($minutos > 0) ? " e $minutos min" : '');
            $out[$i]['ch_min'] = $minutos_totais;
            $out[$i]['reoc'] = (($out[$i]['extrasModulo']['reoc'] === 'S') ? 'Semanal' : 'Mensal');
            $out[$i]['dur'] = "$dur_minutos[0] hrs e $dur_minutos[1] min";
        }

        return $out;
    }

    /**
     * @create 13/01/2020
     * Metodo responsavel por salvar uma entidade
     */
    public function ws_save($dados) {
        $action = ( ((int) $dados['id' . $this->ent] > 0) ? 'Editar' : 'Inserir');
        Poderes::verify(self::$poderesGrupo, self::$poderesSubGrupo, $action);


        if (method_exists($this->object, 'setIdUsuario') && !Helper::compareString($this->ent, 'usuario')) {
            $dados['idUsuario'] = $this->condicao['idUsuario'];
        }

        if (method_exists($this->object, 'setIdEmpresa') && !Helper::compareString($this->ent, 'empresa')) {
            $dados['idEmpresa'] = $this->condicao['idEmpresa'];
        }

        // Caso utilize o avatar no uploadfile
        //$dados['idUploadfile'] = Helper::jsonToArrayFromView($dados['Uploadfile'])['idUploadfile'];// para controle via avatar

        $id = parent::save($dados);

        // Retornar o objeto persisitido
        $t = $this->ws_getById(['id' => $id]);
        $t['result'] = Translate::get('Salvo com sucesso');
        return $t;
    }

    /**
     * @create 13/01/2020
     * Metodo responsavel por remover uma entidade
     */
    public function ws_remove($dados) {
        Poderes::verify(self::$poderesGrupo, self::$poderesSubGrupo, 'remover');
        return parent::remove($dados['id']);
    }

    /**
     * Calculo da carga horaria de um ou mais modulos
     * @param array $idModulos
     * @return type
     */
    public function calculaCargaHoraria(array $idModulos) {
        $this->condicao['idModulo'] = ['in', '(' . implode(',', $idModulos) . ')'];
        $out = $this->ws_getAll([]);
        $minutos_totais = 0;
        for ($i = 0; $i < count($out); $i++) {
            $minutos_totais += (int) $out[$i]['ch_min'];
        }

        $horas = floor($minutos_totais / 60);
        $minutos = $minutos_totais % 60;
        return [
            'minutos' => $minutos_totais,
            'horas' => "$horas hrs" . (($minutos > 0) ? " e $minutos min" : ''),
        ];
    }

}
