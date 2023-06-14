<?php

class SistemaCreate {

    public static $template = '<?php if (! defined ( "SISTEMA_LIBRARY" )) {	die ( "Acesso direto não permitido" ); }   ?>';

    /**
     * Metodo para criar os templates header e footer par auso no sistema
     */
    public static function init() {
        /*
          $filename = Config::getData('pathView') . "/view";
          if (!is_dir($filename)) {
          mkdir($filename, 0777);
          }
          $filename = $filename . "/index.php";
          if (!file_exists($filename)) {
          $template = '<?php include \'../template/template1.php\'; echo \'<h1>Página inicial</h1>\'; include \'../template/template2.php\';';
          file_put_contents($filename, $template);
          }
         * 
         */
    }

    public final static function getList($dados) {
        self::init();
        //$template_table = file_get_contents('./templates/template-list-table.html');
        $template_div = file_get_contents('./templates/template-list-div.html');
        //$campos = [];
        foreach ($dados['atributos'] as $atributo) {
            if ($atributo['key'] === true || stripos($atributo['nome'], 'createtime') !== false || stripos($atributo['nome'], 'idempresa') !== false || stripos($atributo['nome'], 'isalive') !== false) {
                continue;
            }
            if (strpos(strtoupper($atributo['coments']), '@JOINCOLUMN') !== false) {
                continue;
            }
            $label = strtoupper(Helper::reverteName2CamelCase($atributo['nome']));
            $label = str_replace('_' . strtoupper($atributo['entidade']), '', $label);
            $label = str_replace('_', ' ', $label);

            $label = $atributo['coments'];

            $atributo['nome'][0] = strtolower($atributo['nome'][0]);
            $headTable[] = strtoupper($label);
            if (substr((string) $atributo['nome'], 0, 2) === 'id') { // mostrar o resultado da relação
                $line[] = '
                <span ng-if="' . ucwords($dados['entidade']) . '.' . $atributo['nome'] . '"><br/><strong>' . $label . '</strong>: {{' . ucwords($dados['entidade']) . '.' . substr((string) $atributo['nome'], 2, 100) . '.label' . substr((string) $atributo['nome'], 2, 100) . '}}</span>';
                //<p>{{'.ucwords($dados['entidade']).'.'.$atributo['nome'].'}}</p>';//$registro[\''.$atributo['nome'].'\']';
                $lineTable[] = '
                <td>{{' . ucwords($dados['entidade']) . '.' . substr((string) $atributo['nome'], 2, 100) . '.label' . substr((string) $atributo['nome'], 2, 100) . '}}</td>';
                $attr = ucwords($dados['entidade']) . '.' . substr((string) $atributo['nome'], 2, 100) . '.nome' . substr((string) $atributo['nome'], 2, 100);
            } else {
                $line[] = '
                <span ng-if="' . ucwords($dados['entidade']) . '.' . $atributo['nome'] . '"><br/>{{' . ucwords($dados['entidade']) . '<span ng-bind-html="' . $atributo['nome'] . '"</span></span>';
                //<p>{{'.ucwords($dados['entidade']).'.'.$atributo['nome'].'}}</p>';//$registro[\''.$atributo['nome'].'\']';
                $lineTable[] = '
                <td>{{' . ucwords($dados['entidade']) . '.' . $atributo['nome'] . '}}</td>';
                $attr = $atributo['nome'];
            }
            //array('label' => '" . (($campos) ? str_replace("ID ", "", $label) . ': ' : '') . "', 'atributo' => '" . str_replace(ucwords($dados['entidade']) . '.', '', $attr) . "', 'class'=>'text-left " . (($campos) ? '' : 'text-strong') . "', 'linha' => '" . (($campos) ? '<br/>' : '') . "')";
            switch ($atributo['tipo']) {
                case 'boolean':
                    $f = 'F';
                    break;
                default:
                    $f = '';
            }
            $campos[] = "
            ['label' => Config::getAliasesField('" . $atributo['nome'] . "'), 'atributo' => '" . str_replace(ucwords($dados['entidade']) . '.', '', $attr) . $f . "', 'class'=>'text-left']";
            $modalData[] = "{label: '" . str_replace("ID ", "", $label) . "', text: v." . str_replace(ucwords($dados['entidade']) . '.', '', $attr) . ", grid: 'col-sm-12', classe: 'text-left'}";
        }
        if (!is_array($headTable)) {
            die('<p class="alert alert-danger">Erro ao criar o Head para table.' . Log::ver($dados) . '</p>');
        }
        $table = new Table($headTable);
        $form = self::getForm($dados);
        $campos = '$' . ucwords($dados['entidade']) . 's = [' . implode(",\n", $campos) . '
    ];';
        $modalData = implode(",\n", $modalData);

        $tabs = '// $tabs = ['
                . '[\'id\' => \'identificacao\', \'label\' => \'Identificação\', \'conteudo\' => file_get_contents(Config::getData(\'pathView\') . \'/view/Pessoa/form/identificacao.html\')]'
                . '];';

        $vars = array(
            'date' => date('d/m/Y h:i:s'),
            'entidade' => ucwords($dados['entidade']),
            'cpoID' => $dados['cpoID'],
            'table-head' => $table->head,
            'campos' => $campos,
            'tabs' => $tabs,
            'table-body' => implode('', $lineTable),
            'form' => $form['template'],
            'filtros' => $form['filtros'],
            'tableFiltros' => $form['tableFiltros'],
            'form_array' => $form['form_array'],
        );
        //$out['table'] = Helper::escreveTemplate($template_table, $vars);
        $out['MODAL_DATA'] = $modalData;
        $out['form'] = $form['template'];
        $out['div'] = Helper::escreveTemplate($template_div, $vars);
        $out['aux'] = $form['aux'];

        $out['setdata'] = $form['setdata'];
        return $out;
    }

    private static function getForm($dados) {
        $template = file_get_contents('./templates/template-edit.html');
        $INPUT = "Form::getModel(Html::input(['ng-model' => '%entidade%.%atributo%', 'type'=>'%tipo%', 'class'=>'%css%', 'required'=>'%required-tag%', 'ng-change' => \$ngChange], Config::getAliasesField('%atributo%')), 'col-sm-4')";
        $SELECT = "Form::getModel(Html::inputSelectNgRepeat('%entidade%.%atributo%', Config::getAliasesField('%atributo%'), '%entidade%.%atributo%_ro', 'Aux.%tabela-relacional%', \$ngClick, \$ngChange), 'col-sm-4')";
        $DATEPICKER = "Form::getModel(Html::inputDatePicker(Config::getAliasesField('%atributo%'), '%entidade%.%atributo%', \$minDate, \$maxDate, \$ngChange), 'col-sm-4')";
        $COMBOSEARCH = "Form::getModel(Html::comboSearch(Config::getAliasesField('%atributo%'), '%entidade%.idMunicipio', '%entidade%.Municipio.nomeMunicipio+\'/\'+%entidade%.Municipio.Uf.siglaUf', 'Municipio', 'getAll'), 'col-sm-4')";
        $SELECT_BOOLEAN = "Form::getModel(Html::inputSelectNgRepeat('%entidade%.%atributo%', Config::getAliasesField('%atributo%'), '%entidade%.%atributo%_ro', 'Aux.%tabela-relacional%', \$ngClick, \$ngChange, 'Boolean'), 'col-sm-4')";
        $JSON = "Form::getModel('<config-json title=\"'.Config::getAliasesField('%atributo%').'\" model=\"%entidade%.%atributo%\" grid=\"col-sm-6\"></config-json>', 'col-sm-12')";
        $TEXTAREA = "Form::getModel(Html::input(['ng-model' => '%entidade%.%atributo%', 'required'=>'%required-tag%', 'type'=>'textarea', 'rows'=>'5', 'ng-change' => \$ngChange], Config::getAliasesField('%atributo%')), 'col-sm-12')";

        // definição dos campos
        $form_array = $form = array();
        foreach ($dados['atributos'] as $atributo) {
            $combo = false;
            if ($atributo['key'] === true || stripos($atributo['nome'], 'createtime') !== false || stripos($atributo['nome'], 'isalive') !== false || stripos($atributo['nome'], 'idempresa') !== false || strpos(strtoupper($atributo['coments']), '@JOINCOLUMN') !== false) {
                continue;
            }


            $label = $atributo['coments'];

            $vars = array(
                'atributo' => $atributo['nome'],
                'label' => $label,
                'entidade' => $dados['entidade'],
                'entidadelower' => strtolower($dados['entidade']),
                'required-tag' => (($atributo['notnull']) ? 'required ' : 'not-required'),
                'tabela-relacional' => ucwords(substr((string) $atributo['nome'], 2, 150)),
                'diretiva' => ''
            );
            $elemento = $INPUT;
            switch ($atributo['tipo']) {
                case 'date':
                case'timestamp':
                    $vars['css'] = '';
                    $vars['diretiva'] = ' datepicker="" ';
                    $elemento = $DATEPICKER;
                    break;
                case 'int':
                    $vars['tipo'] = 'number';
                    $vars['css'] = '';
                    break;
                case 'boolean':
                    $elemento = $SELECT_BOOLEAN;
                    $vars['tabela-relacional'] = 'Boolean';
                    break;
                case 'double':
                    $vars['tipo'] = 'text';
                    $vars['css'] = 'decimal';
                    break;
                case 'json':
                case 'jsonb':
                    $elemento = $JSON;
                    break;
                case 'text':
                    $elemento = $TEXTAREA;
                    break;
                default:
                    $vars['tipo'] = 'text';
                    $vars['css'] = '';
            }

            if (strtolower(substr((string) $atributo['nome'], 0, 2)) === "id" && stripos($atributo['nome'], 'idempresa') === false) {
                // seleção para criação de combosearch de campos esdpecificos do sistema, definido nesse array em DEFINE, no index da build
                $elemento = $SELECT;
                foreach (['idMunicipio'] as $value) {
                    if (Helper::compareString($value, $atributo['nome'])) {
                        $elemento = $COMBOSEARCH;
                        $combo = true;
                    }
                }
                $vars['label'] = str_replace("ID ", "", $vars['label']);
                $vars['link'] = '<a href="../' . $vars['tabela-relacional'] . '/?xyz=-1&r={{' . $vars['entidade'] . '.id' . $vars['entidade'] . '}}"><span class="glyphicon glyphicon-plus"></span></a>';
                if (!$combo) { // nao fazer para combosearch
                    /*
                      $aux[] = '
                      DataLoadService.getContent("' . $vars['tabela-relacional'] . '", "getAll", {}, function (data) {$scope.Aux.' . $vars['tabela-relacional'] . '=data.content;
                      $scope.Args.id' . $vars['tabela-relacional'] . ' = false;
                      //$scope.Aux.' . $vars['tabela-relacional'] . 'Filter = [{id' . $vars['tabela-relacional'] . ':false, nome' . $vars['tabela-relacional'] . ':\'TODOS\'}];  angular.forEach(data.content, function(v,k){$scope.Aux.' . $vars['tabela-relacional'] . 'Filter.push(v);        });

                      });';
                     * 
                     */
                    $aux[] = "'" . $vars['tabela-relacional'] . "'";
                }
                $filtros[] = '[\'grid\' => \'col-6 col-sm-4\', \'entidade\' => \'' . $vars['tabela-relacional'] . '\']';
                /*
                  <div class="col-6 col-sm-4 col-md-3">'
                  . '<label><a class="btn btn-{{!Args.id' . $vars['tabela-relacional'] . ' && \\\'default\\\' || \\\'info\\\'}} btn-block" ng-click="filterClear(\\\'' . $vars['tabela-relacional'] . '\\\')">' . $vars['tabela-relacional'] . ' <span ng-show="Args.id' . $vars['tabela-relacional'] . '>0"><i class="fa fa-times" aria-hidden="true"></i></span></a></label>'
                  . '<select class="form-control" ng-change="' . $dados['entidade'] . 'GetAll(\\\'Atualizando Relação\\\', true)" ng-model="Args.id' . $vars['tabela-relacional'] . '" ng-options="item.id' . $vars['tabela-relacional'] . ' as item.nome' . $vars['tabela-relacional'] . ' for item in Aux.' . $vars['tabela-relacional'] . '"></select></div>';
                 * 
                 */
                $tableFiltros[] = '
                  \'<p class="text-strong">' . $vars['tabela-relacional'] . '</p>
                  <p ng-repeat="filter in Aux.' . $vars['tabela-relacional'] . ' | filter: {id' . $vars['tabela-relacional'] . ':Args.id' . $vars['tabela-relacional'] . '}:true">{{filter.nome' . $vars['tabela-relacional'] . '}}</p>\'';
            }
            /*
              if ($vars['tipo'] === 'date') {
              $data[] = ''
              . '$scope.' . $vars['entidade'] . '.' . $vars['atributo'] . ' = new Date();';
              }
             * 
             */
            $form[] = Helper::escreveTemplate($elemento, $vars);
            //$form_array[] = 'Form::getModel(\'' . $vars['label'] . '\', Html::input([\'ng-model\' => \'' . $vars['entidade'] . '.' . $vars['atributo'] . '\']), \'col-sm-6\')';
            $form_array[] = Helper::escreveTemplate($elemento, $vars);
        }
        $tableFiltros[] = '
          \'<p class="text-strong">Texto Pesquisa</p>
          <p class="text-upper">{{Args.Search}}</p>\'';
        if (is_array($aux)) {
            $out['aux'] = implode(', ', $aux);
            $out['filtros'] = implode(",\n", $filtros);
            foreach ($tableFiltros as $value) {
                $head[] = "''";
            }
            $temp = '$tableFiltros = new Table([' . implode(',', $head) . '], false, false, \'table-bordered\', false);';
            $temp .= '$tableFiltros->setExplode(false);';
            $temp .= '$tableFiltros->addLinha([' . implode(',', $tableFiltros) . ']);';
            $out['tableFiltros'] = $temp;
        }
        if (is_array($data)) {
            $out['setdata'] = implode(''
                    . '', $data);
        }
        $out['form_array'] = implode(", \n", $form_array);
        $out['template'] = Helper::escreveTemplate($template, array(
                    'arrayCampos' => null,
                    'entidade' => $dados['entidade'] ?? null,
                    'date' => date('d/m/Y'),
                    'datetime' => date('d/m/y h:i:s'),
                    'cpoId' => $dados['cpoID'] ?? null,
                    'form' => implode("", ($form ?? [])),
        ));

        return $out;
    }

    public final static function getIndex($dados) {
        $out = Helper::escreveTemplate(file_get_contents('./templates/template-index.html'), array('entidade' => $dados['entidade'], 'date' => date('d/m/Y'), 'datetime' => date('d/m/y h:i:s')));
        return $out;
    }

    public final static function getJs($dados) {
        $dados['date'] = date('d/m/Y');
        $dados['datetime'] = date('d/m/y h:i:s');
        $out = Helper::escreveTemplate(file_get_contents('./templates/template-script.js.txt'), $dados);
        return $out;
    }

}
