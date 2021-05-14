<?php

interface ConnectionInterface {   
    public function __construct($type);
    public function open();
    public function close();
    public function autocommit($var);
    public function commit();
    public function rollback();
    public function executeQuery($query, $gravarLog=true);
    public function next();
    //public function read($table, $id, $nameFieldId);
}