<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Html {

    public static $class = '';
    public static $uploadFileTitle = 'Arquivos';
    private static $btnNew;
    public static $ngDisabled;

    public static function input($atributos, $label = false) { //$nomeCampo, $label, $value = '', $placeholder, $maxChar = false, $size = false, $cpoEspecial = false, $class = 'cpoInput', $extras = false) {
        if (is_object($atributos)) {
            $atributos = (array) $atributos;
        }
        $atributos['type'] = ((strlen($atributos['type']) > 0) ? $atributos['type'] : 'text');
        $atributos['class'] .= ' form-control pb-0 pl-2 mb-1';
        $label = (($atributos['placeholder']) ? $atributos['placeholder'] : $label);
        unset($atributos['placeholder']);
        $required = $atributos['required'];
        $checked = $atributos['checked'];
        $readonly = $atributos['readonly']; //) ? $atributos['readonly'] : $atributos['ng-model'] . '_ro');
        //$atributos['required'] = (($atributos['required']) ? 'required' : 'not-required');
        $controlClear = ((isset($atributos['controlClear'])) ? true : false);
        $strControlClear = '<i ng-show="' . $atributos['ng-model'] . '" ng-click="filterClear(\'' . $atributos['ng-model'] . '\')" class="form-control-clear fa fa-times form-control-feedback hidden" aria-hidden="true"></i>';

        // Hints
        if (!($atributos['hint'])) { // o envio via construção sobrepõe o default da modelagem
            $atributos['hint'] = Config::getHint($atributos['ng-model']); // testar se existe um default guardado
        }
        $hint = (($atributos['hint']) ? Html::hint($atributos['hint']) : '');

        // daterange
        if (stripos($atributos['class'], 'daterange') !== false) {
            $labelClass = '-range';
        }



        unset($atributos['checked']);
        unset($atributos['readonly']);
        unset($atributos['controlClear']);
        unset($atributos['hint']);
        foreach ($atributos as $key => $val) {
            $att[] = "$key=\"$val\"";
            $$key = $val;
        }



        $template = '<div class="form-group" ' . $hint . '>'
                . (($readonly === 'true') ? ''
                . '<p class="border-bottom form-control pb-0 pl-2 mb-1" ng-bind-html="' . $atributos['ng-model'] . '"></p>'
                . '<span class="floating-label' . $labelClass . '"> ' . $label . '</span>' : ''
                //. '<p>{{' . $atributos['ng-model'] . '}}<i class="bar"></i></p>' : ''
                . '<input %1$s />'
                . (($label) ? '<span class="floating-label">' . $label . '</span>' : '')
                . ''
                )
                . '</div>';

        // tratamento especifico para campo de busca, pois o _ro causa erro de contexto no angularJS
        if (stripos($atributos['class'], 'Search') > -1) {
            $template = '<div class="form-group mb-0">'
                    . '<label class="control-label" for="input">%2$s</label>'
                    . '<input %1$s />'
                    . $strControlClear
                    . '</div>';
            $template = '<div class="form-group mb-0">'
                    . '<input %1$s />'
                    //. '<span class="floating-label" style="font-family:Arial, FontAwesome" /> OIE%2$s</span>'
                    . '<span class="floating-label" style="font-family:Arial, FontAwesome">%2$s</span>'
                    . $strControlClear
                    . '</div>'
                    //. '</div>
                    . '';
        }
        switch ($type) {
            case "daterange":

                $atributos['class'] .= ' daterange';
                $atributos['type'] = 'text';
                $att = [];
                foreach ($atributos as $key => $val) {
                    $att[] = "$key=\"$val\"";
                }
                $t = str_replace('floating-label', 'floating-label-range', sprintf($template, implode(" ", $att), $label));
                $out[] = $t;

                break;
            case 'checkbox':
            case'radio':
                unset($atributos['required']);
                $out[] = '<input ' . implode(" ", $att) . ' ' . $required . ' ' . $checked . ' /> ' . $label;
                break;
            case 'textarea':
                unset($att['type']);
                $out[] = '<div class="form-group mb-0" ' . $hint . '>'
                        . '<div ng-if="' . $readonly . '"><label>' . $label . '</label><p>{{' . $atributos['ng-model'] . '}}<i class="bar"></i></p></div>'
                        . '<label class="control-label" for="textareat">' . $label . '</label><textarea ' . implode(" ", $att) . '></textarea>'
                        . '</div>';
                break;
            case 'summernote':
                $options = (($atributos['options']) ? ($atributos['options']) : 'snOptions');
                $height = (($atributos['height']) ? $atributos['height'] : 700);
                $out[] = '<summernote ' . $hint . ' config="' . $options . '" height="' . $height . '" ng-model="' . $atributos['ng-model'] . '"></summernote>';
                break;

            default:
                $out[] = sprintf($template, implode(" ", $att), $label);
        }
        //$out[] = '<span id="_ ' . $name . '" class="notnull"></span>';
        return implode(" ", $out);
    }

    public static function inputCheck($nomeCampo, $value, $texto = false, $check = false, $onclick = false) {
        return ('<input class="' . self::$class . '" type="checkbox" name="' . $nomeCampo . '" id="' . $nomeCampo . '" value="' . $value . '" ' . (($check) ? "checked" : "") . ' ' . (($onclick) ? 'onclick="' . $onclick . '"' : '') . '>' . $texto);
    }

    public static function inputRadio($nomeCampo, $value, $texto = false, $check = false) {
        return ('<input class="' . self::$class . '" type="radio" name="' . $nomeCampo . '" id="' . $nomeCampo . '" value="' . $value . '" ' . (($check) ? "checked" : "") . '>' . $texto);
    }

    public static function inputHidden($nomeCampo, $valor) {
        return ('<input name="' . $nomeCampo . '" type="hidden" id="' . $nomeCampo . '" value="' . $valor . '">');
    }

    public static function inputSubmit($texto = 'Enviar', $class = "btn-primary") {
        return '<span id="submit"><input name="Enviar" class="btn ' . $class . '" type="submit" value="' . $texto . '"></span>';
    }

    public static function inputSelectNgRepeat($model, $label, $readonly = 'NS21', $ngRepeat = false, $ngClick = false, $ngChange = false, $campo = false, $idRetorno = false, $groupBy = '') { // NS21: var coringa qualquer
        $view = $model; // falta aprimorar
        $xyz = $idRetorno;
        //$xyz = "$modelPai.id$campo"; // falta aprimorar
        if ($campo === false) {
            $t = explode('.', $model);
            $inicio = 0;
            if (Helper::compareString(substr($t[count($t) - 1], 0, 2), 'id')) {
                $inicio = 2;
            }
            $campo = ucwords(substr($t[count($t) - 1], $inicio, 100));
            $modelPai = ucwords($t[count($t) - 2]);
            $view = "$modelPai.$campo.nome$campo";
        }
        $h = Config::getHint($model);
        $hint = (($h) ? 'data-toggle="tooltip" data-placement="top" data-html="true" title="' . $h . '"' : '');

        $ngRepeat = (($ngRepeat) ? $ngRepeat : 'Aux.' . $campo);
        //$label= '<label>' . $label . ' <a ng-if="!' . $readonly . '" href = "../' . $campo . '/?xyz=-1&r={{' . $xyz . '}}"><span class = "glyphicon glyphicon-plus"></span></a></label>';
        $out .= ''
                . '<div class="form-group mb-0" ' . $hint . '>'
                . '<div ng-if="' . $readonly . '">'
                . '<label>' . $label . '</label>'
                . '<p ng-bind-html="' . $view . '"></p>'
                . '</div>'
                . '<div ng-if="!' . $readonly . '">'
                . '<span class="floating-label-select">' . $label . '</span>'
                . '<select ng-model = "' . $model . '" class="form-control"'
                . (($ngChange) ? 'ng-change = "' . $ngChange . '"' : '')
                . (($ngClick) ? 'ng-click = "' . $ngClick . '"' : '')
                . ((self::$ngDisabled) ? 'ng-disabled = "' . self::$ngDisabled . '"' : "")
                . ' ng-options = "item.id' . $campo . ' as item.nome' . $campo . ' ' . $groupBy . '  for item in ' . $ngRepeat . '"></select>'
                . '</div>'
                . '</div>';
        self::$btnNew = false; // zerando a cada uso, pois btnNew ´estatico
        self::$ngDisabled = false;
        return $out;
    }

    public static function inputSelectFromArray($nome, $label, $arrayDados, $compare = false, $printEscolha = false, $ajax = false, $size = false, $extras = false, $diretiva = 'convert-to-number') {
        $cod_ajax = (($ajax) ? 'onChange = "javascript: ' . $ajax . '"' : "");
        $size = (($size) ? "size='$size'" : '');
        $codigo = '';
        $codigo = (($label) ? '<label>' . $label . '</label> ' : '');
        $codigo .= '<select ' . $diretiva . ' class="form-control" ng-model="' . $nome . '" name="' . $nome . '" id="' . $nome . '" ' . $cod_ajax . ' ' . $extras . '>';
        if ($printEscolha) {
            if ($printEscolha === true)
                $codigo .= "<option value=''>--Escolha--</option>";
            else
                $codigo .= "<option value=''>$printEscolha</option>";
        }
        if (is_array($arrayDados)) {
            foreach ($arrayDados as $value => $label) {
                $codigo .= "<option value='$value'" . (((string) $compare === (string) $value) ? ' selected' : '') . ">" . $label . "</option>";
            }
        } else {
            return '<i>Nenhum dado para montar seleção</i>'; //
        }
        $codigo .= "</select>";
        return $codigo;
    }

    /**
     * Dados: [title, content]
     * @param type $dados
     * @return type
     */
    public static function accordion($dados) {
        if (is_array($dados)) {
            $idAcordion = md5(time());
            $out[] = '<div id = "' . $idAcordion . '">';
            foreach ($dados as $dd) {
                $out[] = '<h3>' . $dd['title'] . '</h3><div><p>' . $dd['content'] . '</p></div>';
            }
            $out[] = '</div>';

            $out[] = '<script>$(function () {var icons = {header: "ui-icon-circle-arrow-e", activeHeader: "ui-icon-circle-arrow-s"};
            $("#' . $idAcordion . '").accordion({icons: icons});
            $("#toggle").button().on("click", function () {if ($("#accordion").accordion("option", "icons")) {$("#accordion").accordion("option", "icons", null);
            } else { $("#accordion").accordion("option", "icons", icons);
        }
        });
    });</script>';
            return implode(' ', $out);
        }
    }

    /**
     * Print a message for wait a var
     * @param type $wait
     * @param type $msg
     * @param type $clearfix
     * @param type $estiloGoogle
     * @return type
     */
    public static function msgWait($wait, $msg = false, $clearfix = false, $estiloGoogle = true) {
        $msg = (($msg) ? $msg : 'Carregando...');
        $class = (($estiloGoogle) ? 'infoOrange' : ' text-strong ');
        $out = '<div class="' . $class . ' text-center" ng-show="!' . $wait . '">' . Html::iconFafa('spinner fa-spin') . $msg . '</div>';
        $out = (($clearfix) ? '<div class="clearfix"></div>' . $out . '<div class="clearfix"></div>' : $out);
        return $out;
    }

    /**
     * Print a message info
     * @param type $msg
     * @param type $var
     * @param type $estiloGoogle
     * @param type $clearfix
     * @param string $class
     * @return string
     */
    public static function msgInfoTxt($msg, $var = false, $estiloGoogle = false, $clearfix = true, $class = '') {
        $var = (($var) ? 'ng-show="' . $var . '"' : '');
        $class = (($estiloGoogle) ? 'infoOrange ' : '') . $class;
        $out = (($clearfix) ? '<div class="clearfix" style="margin-top:20px;"></div>' : '')
                . '<div role="alert" class="alert alert-secondary' . $class . ' text-center" ' . $var . '><i class="fa fa-info-circle" aria-hidden="true"></i> ' . $msg . '</div>'
                . (($clearfix) ? '<div class="clearfix" style="margin-top:20px;"></div>' : '');
        return $out;
    }

    /**
     * Print a message like a info
     * @param type $msg
     * @param type $var
     * @param type $tag
     * @param type $class
     * @return type
     */
    public static function msgDicaTxt($msg, $var = false, $tag = 'span', $class = '') {
        $var = (($var) ? 'ng-show="' . $var . '"' : '');
        return '<' . $tag . ' ' . $var . ' class="' . $class . '"><i class="fa fa-lightbulb-o" aria-hidden="true"></i>' . $msg . '</' . $tag . '>';
    }

    public static function msgInfoTxtByTag($msg, $tag = 'p', $var = false) {
        $var = (($var) ? 'ng-show="' . $var . '"' : '');
        return '<' . $tag . ' ' . $var . '><i class="fa fa-info-circle" aria-hidden="true"></i>' . $msg . '</' . $tag . '>';
    }

    public static function msgInfoVar($var, $class = 'alert alert-info') {
        return '<div class="' . $class . ' msg-info-var text-center" ng-show="' . $var . '"><i class="fa fa-info-circle" aria-hidden="true"></i>{{' . $var . '}}</div>';
    }

    /**
     * 
     * @param type $entidade
     * @param type $varRepeat
     * @param type $ngClick
     * @param type $buttonText
     * @param type $thumbs
     * @param type $maxSize
     * @param type $avatar
     * @param type $multiple
     * @return string
     */
    public static function uploadFile($entidade, $varRepeat = false, $ngClick = false, $buttonText = 'Novo', $thumbs = 'false', $maxSize = 1600, $avatar = 'no', $multiple = 'yes') {


        return '<upload-file args="' . $entidade . 'UploadfileArgs" badge-id="' . $entidade . 'Files" valorid="{{' . $entidade . '.id' . $entidade . '}}" entidade="' . $entidade . '" btn-text="' . $buttonText . '" btn-icon="fa-plus" maxsize="' . (int) $maxSize . '" thumbs="' . (string) $thumbs . '" print-avatar="' . $avatar . '" multiple="' . $multiple . '"></upload-file>';

        $varRepeat = (($varRepeat) ? $varRepeat : $entidade . '.Files.files');

        $ngClick = (($ngClick) ? $ngClick : 'uploadFileOnClick($index, \'' . $entidade . '\')');

        /*
          // não exibir uploadfile nos módulos predefindos
          if (stripos(Config::getData('uploadNotShow'), Config::getData('rota')) > -1) {
          return '';
          }
         * 
         */

        $varTemp = 'c' . substr(md5(time()), 0, 10);
        $t = new Table(['Icone', 'Nome', 'Tipo', 'Data Envio|text-center', 'Proprietário|text-center', 'Perfil|text-center', 'Ações|text-center'], false, false, 'table-sm');
        $t->setForeach($varRepeat, 'arquivo');
        $t->addLinha(['<div style="position:relative;float:left; width:50px; height:50px; margin:5px;" class="text-center" ng-click="' . $ngClick . '">
            <span class="text-center img img-thumbnail" ng-if="!arquivo.thumbs" style="padding-left:2px;"><i class="fa fa-{{arquivo.icon}} fa-3x" aria-hidden="true"></i></span>
            <img ng-if="arquivo.thumbs" ng-src="{{arquivo.thumbs}}" class="img img-thumbnail"  />
            <!-- balao avatar -->
            <div style="position:absolute; top:0px; left:0px;" ng-if="arquivo.avatar"><i class="fa fa-id-badge fa-1x" aria-hidden="true"></i></div>
            </div>',
            self::input(['ng-model' => 'arquivo.nomeUploadfile', 'ng-blur' => 'uploadFileSaveName(arquivo)']),
            '{{arquivo.extensaoUploadfile}}|text-center align-bottom',
            '{{arquivo.createtimeUploadfile}}|text-center align-bottom',
            '{{arquivo.Usuario.nomeUsuario}}|text-center align-bottom',
            '<button class="btn btn-link btn-sm" ng-click="uploadFileAlteraPerfil(arquivo)"><i class="fa fa-{{arquivo.perfilIcon}}" aria-hidden="true"></i> {{arquivo.perfil}}</button>|text-center align-bottom',
            '<button class="btn btn-link btn-sm" ng-click="uploadFileShare(arquivo)"><i class="fa fa-share-alt" aria-hidden="true"></i></button>|text-center align-bottom'
        ]);


        $out = '<div class="clearfix"></div>
        <div class="uploadFileContainer mb-2 pt-2 border-top">
            <!-- Buttons -->
            <div class="text-left">
                <h5 class="text-left float-left mr-2">' . Html::iconFafa('file') . ' ' . self::$uploadFileTitle . '</h5>  
                <upload-file args="' . $entidade . 'UploadfileArgs" valorid="{{' . $entidade . '.id' . $entidade . '}}" entidade="' . $entidade . '" btn-text="' . $buttonText . '" btn-icon="fa-plus" maxsize="' . (int) $maxSize . '" thumbs="' . (string) $thumbs . '" print-avatar="' . $avatar . '" multiple="' . $multiple . '"></upload-file>
                    <!--
                <button class="btn btn-default btn-sm btnChangeUploadfile" ng-click="' . $varTemp . ' = !' . $varTemp . '">
                    <i class="fa {{' . $varTemp . ' && \'fa-list\' || \'fa-th-large\'}} fa-1x" aria-hidden="true"></i>
                </button> 
                -->
                <div class="clearfix"></div>
            </div>
        
            <!--
            <div ng-show="' . $varTemp . '">
                <div ng-repeat="arquivo in ' . $varRepeat . '" class="text-center float-left m-1" style="width:80px; height:80px;" ng-click="' . $ngClick . '">
                    <span class="text-center img img-thumbnail pl-1" ng-if="!arquivo.thumbs"><i class="fa fa-{{arquivo.icon}} fa-5x" aria-hidden="true"></i></span>
                    <img ng-if="arquivo.thumbs" ng-src="{{arquivo.thumbs}}" class="img img-thumbnail"  />
                    <div style="position:absolute; top:0px; left:0px;" ng-if="arquivo.avatar"><i class="fa fa-id-badge fa-1x" aria-hidden="true"></i></div>
                </div>
            </div>
            -->
        
            <!--
            <div ng-show="!' . $varTemp . '">' . $t->printTable() . '</div>
            -->

            <!--
            <div class="clearfix"></div>
            --> 
        </div>';
        self::$uploadFileTitle = 'Arquivos Vinculados'; // resetando a cada uso
        return $out;
        /*
          $pack = new Packer("document.write('".$out."');");
          return '<script>'.$pack->pack().'</script>';
         * 
         */
    }

    /**
     * Print a directive combo-search. Search an a entity
     * @param type $label
     * @param type $model
     * @param type $initial
     * @param type $type
     * @param type $action
     * @return type
     */
    public static function comboSearch($label, $model, $initial, $type = 'Municipio', $action = 'getAll', $hideBtnAdd = true) {
        $t = explode('.', $model);
        $campo = ucwords(substr($t[count($t) - 1], 2, 100));
        $modelPai = ucwords($t[count($t) - 2]);
        $view = "$modelPai.$campo.nome$campo";
        $out[] = '<div class="form-group" ng-if="' . $model . '_ro">'
                . '<label>' . $label . '</label>'
                . '<p>{{' . $view . '}}<i class="bar"></i></p>'
                . '</div>';
        $btn = (($hideBtnAdd) ? 'btn-add="false"' : '');
        $out[] = '<combo-search ' . $btn . ' ng-if="!' . $model . '_ro" label="' . $label . '" model="' . $model . '" initial="{{' . $initial . '}}" ws-type="' . $type . '" ws-action="' . $action . '"></combo-search>';
        return implode('', $out);
    }

    /**
     * Simple input search model
     * @param type $label
     * @param type $model
     * @param type $keyup
     * @param type $showContadorResultados
     * @return type
     */
    public static function search($label, $model = "Search", $keyup = "doSearch()", $showContadorResultados = true) {
        $out = self::input([
                    'ng-model' => $model,
                    'ng-keyup' => $keyup,
                    'placeholder' => '&#xF002; ' . $label,
                    'style' => 'font-family: Arial, FontAwesome',
                    'class' => 'Search'
        ]);
        $out .= (($showContadorResultados) ? '<div class="text-left text-small text-italic" ng-if="' . $model . ' && !working">Resultados encontrados para "{{' . $model . '}}"</div>' : '');
        return $out;
    }

    /**
     * Print a icon fafa model
     * @param string $name
     * @param type $tamanho
     * @return type
     */
    public static function iconFafa($name, $tamanho = 1, $text = '') {
        $tamanho = (($tamanho === 1) ? '1g' : $tamanho . 'x');
        $name = 'fa-' . $name . ' fa-' . $tamanho
                . (($text !== '') ? ' mr-1' : '');
        return '<i class="fa ' . $name . '" aria-hidden="true"></i>' . $text;
    }

    public static function printSignature() {
        return '<div class="clearfix" style="margin-top:35px;"></div>'
                . '<p class="alert alert-warning text-center d-none d-print-block">Data Impressão: ' . date('d/m/Y H:i:s') . ' por ' . $_SESSION['user']['nome'] . '</p>';
    }

    public function printHead() {
        return '<div class="clearfix"></div>'
                . '<p class="alert alert-warning text-center d-none d-print-block">Data Impressão: ' . date('d/m/Y H:i:s') . ' por ' . $_SESSION['user']['nome'] . '</p>';
    }

    /**
     * Date picker type
     * @param type $label
     * @param type $model
     * @param type $minDate
     * @param type $maxDate
     * @param type $ngBlur
     * @return type
     */
    public static function inputDatePicker($label, $model, $minDate = false, $maxDate = false, $ngBlur = false) {
        $maxDate = (($maxDate) ? Helper::formatDate($maxDate) : date('Y-m-d'));
        $minDate = (($minDate) ? Helper::formatDate($minDate) : '');
        return self::input([
                    'ng-if' => '!' . $model . '_ro',
                    'readonly' => $model . '_ro',
                    'name' => $model,
                    'datepicker' => '',
                    'ng-model' => $model,
                    'max-date' => $maxDate,
                    'min-date' => $minDate,
                    'ng-change' => $ngBlur,
                    'autocomplete' => 'off',
                        ], $label);
    }

    /**
     * Retorna dois elementos interligados de datapicker
     * @param type $labelA
     * @param type $labelB
     * @param type $modelA
     * @param type $modelB
     * @param type $minDate
     * @param type $maxDate
     * @param type $onChange
     * @return \stdClass
     */
    public static function inputDatePickersGetLeftAndRight($labelA, $labelB, $modelA, $modelB, $minDate, $maxDate, $onChange = '') {
        $out = new stdClass();
        $out->left = Html::inputDatePickerDependente($labelA, $modelA, $minDate, $modelB, $onChange);
        $out->right = Html::inputDatePickerDependente($labelB, $modelB, $modelA, $maxDate, $onChange);
        return $out;
    }

    /**
     * Retorna um elemento de datepicker com relação a outro
     * @param type $label
     * @param type $model
     * @param type $minDate
     * @param type $maxDate
     * @param type $ngChange
     * @param type $readonly
     * @return type
     */
    public static function inputDatePickerDependente($label, $model, $minDate = false, $maxDate = false, $ngChange = false, $readonly = true) {
        $readonly = (($readonly) ? ' readonly="true"' : '');
        return self::input([
                    'id' => md5($model),
                    'ng-if' => '!' . $model . '_ro',
                    'readonly' => $model . '_ro',
                    'name' => $model,
                    'datepickernew' => '',
                    'ng-model' => $model,
                    'max-date' => '{{' . $maxDate . '}}',
                    'min-date' => '{{' . $minDate . '}}',
                    'ng-change' => $ngChange,
                    'autocomplete' => 'off',
                        ], $label);
    }

    /**
     * UL List
     * @param array $dados
     * @param type $title
     * @param type $class
     * @return type
     */
    public static function ulList($dados, $title = '', $class = 'alert alert-info') {
        $out[] = '<div class="' . $class . '">';
        $out[] = (($title) ? '<h5>' . $title . '</h5>' : '');
        $out[] = '<ul style="list-style-type: square;list-style-position: inside;">';
        foreach ($dados as $dd) {
            $out[] = '<li>' . $dd . '</li>';
        }
        $out[] = '</ul></div>';
        return implode(' ', $out);
    }

    public static function divNgRepeat($conteudo, $list, $varName, $class = 'col-sm-4', $menuContexto = false, $msgLengthZero = false) {
        $context = (($menuContexto) ? ' on-long-press="" context-itens="{{' . $menuContexto . '}}" ' : '');
        $out = '<div class="clearfix"></div>'
                . '<div class="row">'
                . '<div ng-repeat="' . $varName . ' in ' . $list . '" class="mb-2 ' . $class . '" ' . $context . '>'
                . $conteudo
                . '</div>'
                . '</div>';
        $out .= '<div class="clearfix" style="margin-top:25px;"></div>';
        $out .= (($msgLengthZero) ? self::msgInfoTxt($msgLengthZero, "!$list.length") : '');
        return $out;
    }

    public static function searchbox($ngmodel, $ngclick) {
        $out = '<div class = "searchboxwrapper">%s %s</div>';
        $input = self::input(['class' => 'searchbox', 'name' => $ngmodel, 'ng-model' => $ngmodel]);
        $btn = self::input(['class' => 'searchsubmit', 'type' => 'submit', 'id' => 'searchsubmit', 'ng-click' => $ngclick]);
        return sprintf($out, $input, $btn);
    }

    public static function switchTableCard($controlVarName = 'viewTable') {
        $out = self::$btnNew
                . '<a class="btn btn-default" ng-click="' . $controlVarName . '=!' . $controlVarName . '" alt="teste">'
                . Html::iconFafa('{{' . $controlVarName . ' && \'id-card\' || \'list\'}}', 2)
                //. '{{' . $controlVarName . ' && \'Grade\' || \'Lista\'}}'
                . '</a>';
        self::$btnNew = false; // zerando a cada uso, pois btnNew ´estatico
        return $out;
    }

    public static function setBtnNew($ngClick, $text = 'Novo', $class = 'btn-default', $icon = 'plus') {
        self::$btnNew = '<a class="btn ' . $class . '" ng-click="' . $ngClick . '">' . Html::iconFafa($icon) . $text . '</a>   ';
    }

    public static function templateTableAndCard($table, $card, $controlVarName = 'viewTable') {
        $out = '<div>';
        $out .= self::switchTableCard($controlVarName);
        $out .= '<div ng-show="!' . $controlVarName . '">' . $card . '</div>';
        $out .= '<div ng-show="' . $controlVarName . '">' . $table . '</div>';
        $out .= '</div>';
        return $out;
    }

    /*
      public static function hint($text) {
      return ' data-toggle="tooltip" data-placement="top" data-html="true" title="' . $text . '" ';
      }
     */

    public static function hint($text, $position = "top") {
        return ' data-toggle="tooltip" data-placement="' . $position . '" data-html="true" title="' . $text . '" ';
    }

}
