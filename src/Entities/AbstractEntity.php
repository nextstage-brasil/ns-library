<?php

namespace NsLibrary\Entities;

abstract class AbstractEntity
{
    protected $error; // armazena possiveis erros, inclusive, obrigatoriedades.
    protected $table;
    protected $cpoId;
    protected $dao = null;
    protected array $relacoes;
    public $selectExtra = null;

    public function __construct(string $table, string $cpoId, array $relacionamentos)
    {
        $this->table = $table;
        $this->cpoId = $cpoId;
        $this->relacoes = [implode(",", $relacionamentos)];
    }
}
