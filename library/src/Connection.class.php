<?php

class Connection {

    public function __construct() {
        
    }

    public static function getConnection($type = false) {
        $type = (($type)?$type:Config::getData('database', 'type'));
        return new ConnectionPDO($type);
    }

}
