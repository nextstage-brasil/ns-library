<?php

namespace NsLibrary\Entities;

use NsUtil\Helper;

class Field {

    private $description, $type, $typeDB, $default, $name;
    private $maxsize = 1000000000;
    private $notnull = false;
    private $primaryKey = false;
    private static $types = [
        //numeros
        'serial' => 'int',
        'serial2' => 'int',
        'serial4' => 'int',
        'serial8' => 'int',
        'bigint' => 'int',
        'int2' => 'int',
        'int4' => 'int',
        'int8' => 'int',
        'integer' => 'int',
        'tinyint' => 'int',
        'smallint' => 'int',
        'mediumint' => 'int',
        'double' => 'double',
        'float' => 'double',
        'decimal' => 'double',
        'numeric' => 'double',
        //textos
        'text' => 'string',
        'varchar' => 'string',
        'char' => 'string',
        'blob' => 'string',
        'clob' => 'string',
        'bool' => 'string',
        'character' => 'string',
        'longvarchar' => 'string',
        'character varying' => 'string',
        'timestamp without time zone' => 'string',
        'time without time zone' => 'int',
        'enum' => 'string',
        'text' => 'string',
        'json' => 'json',
        'jsonb' => 'json',
        'object' => 'json',
        'array' => 'json',
        'date' => 'date',
        'timestamp' => 'date'
    ];

    public function __construct(string $name, string $type = 'varchar', $default = '', string $description = '', bool $notnull = false) {
        $this->setName($name);
        $this->setType($type);
        $this->setDefault($default);
        $this->setDescription($description);
        $this->setDescription($description);
        $this->setNotnull($notnull);
    }

    function setMaxsize(int $maxsize): Field {
        if ($this->type === 'string' && ($this->maxsize > 255 || $maxsize > 255)) {
            $this->typeDB = 'text';
        }
        if ($this->type === 'string' && ($this->maxsize <= 255 || $maxsize <= 255)) {
            $this->typeDB = 'varchar';
        }

        $this->maxsize = $maxsize;
        return $this;
    }

    public function setPrimaryKey(): Field {
        $this->primaryKey = true;
        return $this;
    }

    public function setDescription($description): Field {
        $this->description = $description;
        return $this;
    }

    public function setType(string $type): Field {
        $type = $type === 'string' ? 'varchar' : $type;
        $type = $type === 'int' ? 'int2' : $type;
        if (!isset(self::$types[$type])) {
            throw new \Exception("Type '$type' is not provisioned by NSLibrary");
        }
        $this->typeDB = $type;
        $this->type = self::$types[$type];
        $this->setDefault($this->default);
        $this->setMaxsize($this->maxsize);
        return $this;
    }

    public function setDefault($default): Field {
        if ($default === '' && ($this->type === 'jsonb' || $this->type === 'json')) {
            $default = "{}";
        }

        if (strlen($default) > 0 && ($this->type === 'jsonb' || $this->type === 'json')) {
            $default = str_replace(['::' . $this->type, "'", '&#39;'], [''], $default);
            $decode = json_decode((string) $default);
            if (null === $decode) {
                echo "\n";
                echo "\n";
                var_export($default);
                var_export($decode);
                echo "\n";
                echo "\n";
                throw new \Exception($this->name . " ERROR: Default value $default to field '$this->name' is invalid.");
            }
            $this->default = $default;
        } else {
            $this->default = Helper::getValByType($default, $this->type);
        }

        // if (strlen($this->default) > 0 && $this->type !== 'int' && $this->type !== 'double') {
        $this->default = "'" . (string)$this->default . "'";
        // }

        //        echo "##$this->name:  $type >>> $this->type >> $this->default" . PHP_EOL;

        return $this;
    }

    public function setName($name): Field {
        $this->name = Helper::sanitize($name);
        $this->name = Helper::name2CamelCase($this->name);
        return $this;
    }

    public function setNotnull($notnull = true): Field {
        $this->notnull = $notnull;
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

    public function getIsKey() {
        return $this->primaryKey === true;
    }

    public function getTypeDB() {
        return $this->typeDB;
    }
}
