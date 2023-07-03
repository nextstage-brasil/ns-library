<?php

namespace NsLibrary\Entities;

interface EntityManagerInterface
{

    public function save($onConflict = '', $audit = true);
    public function remove($audit = true);
    public function getById($pk, $relacao = true);
    public function getAll($condicao, $getRelacoes = true, $inicio = 0, $limit = 1000, $relacaoExceto = []);
    public function setOrder($orderBy);
    public function setObject($object);
    public function count($condicao, $useQueryCount = true);
    public function setInnerOrLeftJoin($innerOrLeftJoin = 'left');
    public function setLockForUpdate();
}
