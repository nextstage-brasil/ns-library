<?php

namespace NsLibrary\Entities;

use NsLibrary\Builder\EntidadesCreate;
use NsUtil\Config;
use NsUtil\Helper;

class Model {

    protected $entityName, $name, $fields;

    public function __construct($name) {
        $this->entityName = ucwords(Helper::name2CamelCase($name));
        $this->name = $name;
        $this->fields = [];
    }

    public function addField(Field $field) {
        $this->fields[] = $field;
    }

    public function generate($prefix = '', $sufix = '') {
        $dados = [];
        $file = $prefix . $this->entityName . $sufix;
        $dados = [
            'entidade' => $file,
            'tabela' => $this->name,
            'cpoID' => 'id' . $this->entityName,
        ];
        $dados['set'][] = '$obj = new ' . $file . '();
        ';
        // geração dos atributos
        foreach ($this->fields as $item) {
            $item instanceof Field;
            $dados ['atributos'][] = [
                'nome' => Helper::name2CamelCase($item->getName()),
                'coments' => $item->getDescription(),
                'tipo' => $item->getType(),
                'valorPadrao' => ((strlen($item->getDefault() > 0)) ? $item->getDefault() : "''"),
                'maxsize' => (($item->getMaxsize()) ? $item->getMaxsize() : 1000),
            ];
            $dados['doc'][Helper::name2CamelCase($item->getName())] = "$item->v[1]: $v[0]";
            $dados['example'][Helper::name2CamelCase($k)] = "";
            $dados['set'][] = '$obj->set' . ucwords(Helper::name2CamelCase($k)) . '($item->get' . ucwords(Helper::name2CamelCase($k)) . '());
        ';
        }

        // Salvar entidade
        $template = EntidadesCreate::get($dados);
        Helper::saveFile(\NsLibrary\Config::getData('path') . '/src/NsLibrary/Entities/' . $file . '.php', false, $template, 'SOBREPOR');

        /*
          // Gerar documentação
          $doc[$this->name] = $dados['doc'];
          Helper::saveFile(\NsLibrary\Config::getData('path') . '/src/NsLibrary/Entities/doc/json/' . $this->name . '.json', '', json_encode($dados['example']), 'SOBREPOR');

          // setter facilitado
          $dados['set'][] = '$out[ ] = parent::objectToArray($obj);
          ';
          Helper::saveFile(\NsLibrary\Config::getData('path') . '/src/NsLibrary/Entities/doc/setter/' . $this->name . '.txt', '', implode("\n", $dados['set']), 'SOBREPOR');
         */
    }

}
