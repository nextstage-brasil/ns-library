<?php

namespace NsLibrary\Entities;

use NsUtil\Helper;

class Field {

    private $description, $type, $default, $name, $maxsize, $notnull;

    public function __construct(string $name, string $type = 'string', $default = '', string $description = '', bool $notnull = false) {
        $this->name = Helper::sanitize($name);
        $this->type = $type;
        $this->description = $description;
        if ($default === '' && ($type === 'jsonb' || $type === 'json')) {
            $default = "'{}'";
        }
        $this->default = $default;
        $this->notnull = $notnull;
    }

    function setMaxsize(int $maxsize): Field {
        $this->maxsize = $maxsize;
        return $this;
    }

    function getDescription() {
        return $this->description;
    }

    function getType() {
        return $this->type;
    }

    function getDefault() {
        return $this->default;
    }

    function getName() {
        return $this->name;
    }

    function getMaxsize() {
        return $this->maxsize;
    }
    
    public function getNotnull() {
        return $this->notnull;
    }



}
