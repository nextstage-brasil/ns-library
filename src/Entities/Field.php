<?php

namespace NsLibrary\Entities;

class Field {

    private $description, $type, $default, $name, $maxsize;

    public function __construct($name, $type = 'string', $default = '', $description = '') {
        $this->name = \NsUtil\Helper::sanitize($name);
        $this->type = $type;
        $this->description = $description;
        $this->default = $default;
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

}
