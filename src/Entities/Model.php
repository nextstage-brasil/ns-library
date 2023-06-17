<?php

namespace NsLibrary\Entities;

use NsLibrary\Builder\EntidadesCreate;
use NsLibrary\Config;
use NsUtil\Helper;

class Model {

    protected $entityName, $name, $fields, $schema, $ddl, $addTablenameOnFields, $prefix, $sufix;

    public function __construct($name = 'default') {
        $this->setName($name);
        $this->fields = [];
    }

    public function setName($name): self {
        $this->entityName = ucwords(Helper::name2CamelCase($name));
        $this->name = $name;
        return $this;
    }

    public function setAddTablenameOnFields(): self {
        $this->addTablenameOnFields = true;
        return $this;
    }

    public function setSchema($schema): self {
        $this->schema = $schema;
        return $this;
    }

    public function addField(Field $field) {
        $this->fields[] = $field;
        return $this;
    }

    private function prepare($prefix = '', $sufix = ''): array {
        $this->prefix = strlen($prefix) > 0 ? $prefix : $this->prefix;
        $this->sufix = strlen($sufix) > 0 ? $sufix : $this->sufix;

        $file = $this->prefix . $this->entityName . $sufix;

        $dados = [
            'schema' => $this->schema,
            'schemaTable' => $this->schema ? $this->schema  . '.' . $this->name : null, 
            'tabela' => $this->name,
            'cpoID' => 'id' . $this->entityName,
            'entidade' => $file,
            'atributos' => [],
            'camposDate' => [],
            'camposDouble' => [],
            'camposJson' => [],
            'arrayCamposJson' => [],
            'routeBackend' => Helper::name2CamelCase($file),
            'routeFrontend' => str_replace('_', '-', mb_strtolower($file))
        ];

        $dados['set'][] = '$obj = new ' . $file . '();' . PHP_EOL;

        // geração dos atributos
        foreach ($this->fields as $item) {
            $item instanceof Field;

            if ($this->addTablenameOnFields && strpos($item->getName(), $file) === false) {
                $item->setName($item->getName() . $file);
            }

            // Campo ID
            if ($item->getIsKey()) {
                $dados['cpoID'] = $item->getName();
            }

            // Definição dos array de tipos
            switch ($item->getType()) {
                case 'json':
                case 'jsonb':
                    $dados['camposJson'][] = $item->getName();
                    break;
                case 'date':
                case 'timestamp':
                case 'datetime':
                    $dados['camposDate'][] = $item->getName();
                    break;
                case 'double':
                case 'decimal':
                    $dados['camposDouble'][] = $item->getName();
                    break;
                default:
                    break;
            }

            // Criação dos atributos
            $dados ['atributos'][] = [
                'entidade' => $file,
                'key' => $item->getIsKey(),
                'nome' => $item->getName(),
                'column_name' => Helper::reverteName2CamelCase($item->getName()),
                'tipo' => $item->getType(),
                'typeDB' => $item->getTypeDB(),
                'maxsize' => (($item->getMaxsize()) ? $item->getMaxsize() : 1000000000),
                'valorPadrao' => $item->getDefault(),
                'coments' => $item->getDescription(),
                'notnull' => $item->getNotnull(),
                'hint' => $item->getDescription(),
                'relationship' => false
            ];
            $k = ucwords((string) Helper::name2CamelCase($item->getName()));
            $dados['example'][Helper::name2CamelCase($k)] = "";
            $dados['set'][] = '$obj->set' . ucwords(Helper::name2CamelCase($k)) . '($item->get' . ucwords(Helper::name2CamelCase($k)) . '());
        ';
        }

        return $dados;
    }

    public function generate($prefix = '', $sufix = ''): self {
        $dados = $this->prepare($prefix, $sufix);
        $template = EntidadesCreate::get($dados);
        Helper::saveFile(Config::getData('path') . '/src/NsLibrary/Entities/' . $dados['entidade'] . '.php', false, $template, 'SOBREPOR');
        return $this;
    }

    public function getDDL($drop=false) {
        $dados = $this->prepare();
        $fields = array_map(function ($item) use ($dados) {
            return implode(' ', [
        $item['column_name'],
        (($item['typeDB'] === 'varchar') ? $item['typeDB'] . ' (' . $item['maxsize'] . ')' : $item['typeDB']),
        (($item['notnull']) ? 'NOT NULL' : 'NULL'),
        (($item['nome'] !== $dados['cpoID'] && $item['valorPadrao'] !== '' && strlen($item['valorPadrao']) > 0) ? 'DEFAULT ' . $item['valorPadrao'] : '')
            ]);
        }, $dados['atributos']);
        
        return implode(' ' . PHP_EOL, [
            'CREATE TABLE IF NOT EXISTS ' . $dados['schemaTable'] . ' (',
            implode(',' . PHP_EOL, $fields) . ', ',
            'CONSTRAINT ' . $dados['tabela'] . '_pk PRIMARY KEY (' . Helper::reverteName2CamelCase($dados['cpoID']) . ') ',
            ');'
        ]);
    }

    public function createTableOnDB($drop=false) : self {
        $con = \NsLibrary\Connection::getConnection();
        $query = $this->getDDL();
        if ($drop)   {
            $con->executeQuery("DROP TABLE IF EXISTS $this->name;");
        }
        $con->executeQuery($query);
        return $this;
    }

}
