<?php

class DAO_UserPermissao extends EntityManager {

    public function __construct() {
        parent::__construct(new App());
    }

    /**
     * 
     * @create 28/12/2016
     * @update 28/12/2016
     * Metodo que retorna todas as permissões do usuario Pessoa informado
     */
    public function getPermissoesUser($idPessoa) {
        $out = [];
        $query = 'select a.* from app_usuario_permissao a 
                    inner join app_sistema_funcao b on b.id_sistema_funcao = a.id_sistema_funcao 
                    where a.id_usuario = '.$idPessoa;
        $list = $this->execQueryAndReturn($query);
        foreach ($list as $item) {
            $out[$item['idSistemaFuncao']] = true;
        }
        return $out;
    }

    public function allPoderes($idPessoa, $todas = false) {
        $this->con->executequery("DELETE FROM app_usuario_permissao WHERE id_usuario= $idPessoa"); 
        if ($todas) {
            $query = "INSERT INTO app_usuario_permissao (id_usuario, id_sistema_funcao) VALUES ";  
            $this->con->executeQuery("SELECT id_sistema_funcao FROM app_sistema_funcao");
            while ($sis = $this->con->next()) {
                $t[] = '(' . $idPessoa . ', ' . $sis['id_sistema_funcao'] . ')';
            }
            $this->con->executeQuery($query . implode(", ", $t));
        }
    }

}

// fecha classe
