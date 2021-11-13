<?php

namespace NsLibrary\Controller;

/**
 * TODO Auto-generated comment.
 */
abstract class AbstractModel {

    protected $dao, $error, $table, $cpoId;

    public function __construct() {
        
    }

    protected function setDao() {
        if ($this->dao === null) {
            $this->dao = new \NsLibrary\Controller\EntityManager($this);
        }
    }

    public function setSchema($schema) {
        $t = explode(".", $this->table);
        $table = array_pop($t);
        $this->table = "$schema.$table";
        //echo $this->table;
        return $this;
    }

}
