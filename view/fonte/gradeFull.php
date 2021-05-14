<?php

// Automação de Criação de Sistema - 13/01/2020 07:34:45
if (!defined("SISTEMA_LIBRARY")) {
    die("Acesso direto não permitido");
}

$FLUID = true;
include Config::getData('path') . '/view/template/template1.php';

// nome da entidade JS
$entidade = 'GradePolo';
$onclick = $entidade . 'OnEdit(' . $entidade . ')';

//$viewHTML = file_get_contents(Config::getData('url') . "/_build/_sourceView/$entidade/view.php");
// titulo apresentado ao usuário. Caso não tenha sido configurado aliase, será exibido o nome da entidade
$title = Config::getData('titlePagesAliases', $entidade);

// filtros na exibição da lista
$filtros = [['grid' => 'col-6 col-sm-4', 'entidade' => 'Polo'],
    ['grid' => 'col-6 col-sm-4', 'entidade' => 'Leciona'],
    ['grid' => 'col-6 col-sm-4', 'entidade' => 'Grade']];

// Campos apresentados na lista
$GradePolos = [
    ['label' => Config::getAliasesField('idPolo'), 'atributo' => 'Polo.nomePolo', 'class' => 'text-left'],
    ['label' => Config::getAliasesField('idLeciona'), 'atributo' => 'Leciona.nomeLeciona', 'class' => 'text-left'],
    ['label' => Config::getAliasesField('idGrade'), 'atributo' => 'Grade.nomeGrade', 'class' => 'text-left'],
    ['label' => Config::getAliasesField('dataGradePolo'), 'atributo' => 'dataGradePolo', 'class' => 'text-left'],
    ['label' => Config::getAliasesField('isPresencialGradePolo'), 'atributo' => 'isPresencialGradePolo', 'class' => 'text-left'],
    ['label' => Config::getAliasesField('horaInicioGradePolo'), 'atributo' => 'horaInicioGradePolo', 'class' => 'text-left'],
    ['label' => Config::getAliasesField('horaFimGradePolo'), 'atributo' => 'horaFimGradePolo', 'class' => 'text-left'],
    ['label' => Config::getAliasesField('extrasGradePolo'), 'atributo' => 'extrasGradePolo', 'class' => 'text-left']
];
$GradePolos[0]['ngclick'] = $onclick;

// cards
$card = ''; // Card::basic($GradePolos, $entidade, false, $entidade . 'ContextItens');

/*
  // table
  foreach ($GradePolos as $campo) {
  $th[$campo['atributo']] = (($campo['label']) ? str_replace(':', '', $campo['label']) : '') . '|' . $campo['class'];
  $td[] = '{{' . $entidade . '.' . $campo['atributo'] . '}}' . '|' . str_replace('text-strong', '', $campo['class']);
  }
  $table = new Table($th, false, true, '', true);
  $table->setForeach($entidade . "Filtradas = (" . $entidade . "s| filter : filtro | orderBy: 'nome" . $entidade . "')", $entidade);
  $table->setOnClick($entidade . 'OnEdit(' . $entidade . ')');
  $table->setMenuContexto($entidade . 'ContextItens');
  $table->addLinha($td);
 */




// @20/08/2020
$form = [
    Form::getModel(Html::inputSelectNgRepeat('GradePolo.idPolo', Config::getAliasesField('idPolo'), 'GradePolo.idPolo_ro', 'Aux.Polo', $ngClick, 'setIdPolo()'), 'col-sm-4'),
    Form::getModel(Html::inputSelectNgRepeat('GradePolo.idCurso', Config::getAliasesField('idCurso'), 'GradePolo.idCurso_ro', 'Aux.Curso|filter:filterCursoByPolo()', $ngClick, 'setIdPolo()'), 'col-sm-4'),
    Form::getModel(Html::inputSelectNgRepeat('GradePolo.idModulo', Config::getAliasesField('idModulo'), 'GradePolo.idModulo_ro', 'Aux.Modulo|filter:filterModulosByPolo()', $ngClick, 'setIdModulo()'), 'col-sm-4'),
    Form::getModel(Html::inputSelectNgRepeat('GradePolo.idUsuario', 'Professor', 'GradePolo.idUsuario_ro', 'Aux.Usuario|filter:filterProfessorByModulo()', $ngClick, $ngChange), 'col-sm-4'),
    Form::getModel(Html::inputDatePicker(Config::getAliasesField('dtHrGradePolo'), 'GradePolo.dtHrGradePolo', $minDate, $maxDate, $ngChange), 'col-sm-4'),
    Form::getModel(Html::inputSelectNgRepeat('GradePolo.isPresencialGradePolo', Config::getAliasesField('isPresencialGradePolo'), 'GradePolo.idCurso_ro', 'Aux.Boolean', $ngClick, $ngChange, 'Boolean'), 'col-sm-4'),
    //Form::getModel(Html::input(['ng-model' => 'GradePolo.isPresencialGradePolo', 'type' => 'text', 'class' => '', 'required' => 'required ', 'ng-change' => $ngChange], Config::getAliasesField('isPresencialGradePolo')), 'col-sm-4'),
//    Form::getModel('<config-json title="' . Config::getAliasesField('extrasGradePolo') . '" model="GradePolo.extrasGradePolo" grid="col-sm-6"></config-json>', 'col-sm-12'),
//    Form::getModel(Html::input(['ng-model' => 'GradePolo.durGradePolo', 'type' => 'number', 'class' => '', 'required' => 'not-required', 'ng-change' => $ngChange], Config::getAliasesField('durGradePolo')), 'col-sm-4'),
    Form::getModel(Html::input(['ng-model' => 'GradePolo.hrainicioGradePolo', 'type' => 'text', 'class' => 'hora', 'required' => 'not-required', 'ng-change' => $ngChange], Config::getAliasesField('hrainicioGradePolo')), 'col-sm-4')
];


// Obter os polos da entidade
$pl = new PoloController();
$polos = $pl->ws_getAll([]);

// Criar tabela
$th = ['Data do encontro'];
$td = ['<strong><span style=\"color:#666;\">Data do encontro</span></strong><br/>{{' . $entidade . '.dtHrGradePolo}}'
    . '<button ng-show="'.$entidade.'._isChanged" class="btn btn-block btn-sm btn-success" ng-click="saveByDate(GradePolo)">Salvar dados</button>'
];

// Esta tabela deve ser editável caso a data seja futura e usuario tenha permissão, então os campos colocados aqui devem ser inputs e um model de grade-polo
//  a funcao on-change deve enviar o md5 do id_polo e  item que esta sendo alterado
foreach ($polos as $polo) {
    $ngChange = 'setChange(GradePolo, GradePolo[' . $polo['idPolo'] . '])';    
    $ngChangeModulo = 'setChange(GradePolo, GradePolo[' . $polo['idPolo'] . '], \'modulo\')';    ;
    $idGradePoloFieldAngular = "GradePolo[" . $polo['idPolo'] . "]['idGradePolo']";
    $isSuggest = '!' . $idGradePoloFieldAngular . " && GradePolo[" . $polo['idPolo'] . "]['suggest']";
    // Modulo
    //Html::$ngDisabled = '!' . $idGradePoloFieldAngular;
    $modulo = "<strong><span style=\"color:#666;\">{{GradePolo[" . $polo['idPolo'] . "]['Polo']['nomePolo']}}</span></strong><br/>";
    $modulo .= Html::inputSelectNgRepeat("GradePolo[" . $polo['idPolo'] . "]['idModulo']", 'Módulo'
                    . '<span ng-show="' . $idGradePoloFieldAngular . '" class="{{workingSave[\'_' . $polo['idPolo'] . '\'] && \'text-dark\' || \'text-success\' }}">'
                    . ' <i style="z-index:9999;" class="fa {{workingSave[\'_' . $polo['idPolo'] . '\'] && \'fa-refresh fa-spin\' || \'fa-check\' }} ml-1" aria-hidden="true"></i>'
                    . '</span>'
                    . '<span ' . Html::hint('Criei isto com base nas configurações atuais. Para aceitar e editar conforme necessidade, clique aqui') . ' ng-show="' . $isSuggest . '" class="text-info float-right btn btn-link p-0">'
                    . ' <i style="z-index:9999;" class="fa fa-lightbulb-o ml-1" aria-hidden="true"></i> Sugerido!</span>'
                    , 'NS21', 'Data.modulos', false, $ngChangeModulo, 'Modulo'); // 'select com modulos disponiveis e o atual selecionado ou "NAO DEFINIDO"';
    //Professor
    //Html::$ngDisabled = '!' . $idGradePoloFieldAngular;
    //$professor = Html::inputSelectNgRepeat("GradePolo[" . $polo['idPolo'] . "]['idUsuario']", 'Professor', 'NS21', "Professores['modulo_{{GradePolo[" . $polo['idPolo'] . "]['idModulo']}}']", false, $ngChange, ''); // 'select com modulos disponiveis e o atual selecionado ou "NAO DEFINIDO"' . 'select com professores disponiveis para o modulo selecionado ou "NAO DEFINIDO": aqui deve haver uma barra vermelha / verde indicando empenho do recurso'
    //$professorList = "Professores['modulo_{{GradePolo[" . $polo['idPolo'] . "]['idModulo']}}']['polo_" . $polo['idPolo'] . "']";
    $professorList = "(Data.AllProfs | filter: {idPolo: $polo[idPolo],idModulo: GradePolo[" . $polo['idPolo'] . "]['idModulo']})" ;
    $professor = Html::inputSelectNgRepeat("GradePolo[" . $polo['idPolo'] . "]['idUsuario']", 'Professor', 'NS21',
                    //"Professores['modulo_{{GradePolo[" . $polo['idPolo'] . "]['idModulo']}}']", 
                    $professorList. "| filter: {idPolo: $polo[idPolo],idModulo: GradePolo[" . $polo['idPolo'] . "]['idModulo']}", 
                    false, $ngChange, ''); // 'select com modulos disponiveis e o atual selecionado ou "NAO DEFINIDO"' . 'select com professores disponiveis para o modulo selecionado ou "NAO DEFINIDO": aqui deve haver uma barra vermelha / verde indicando empenho do recurso'
    // Tipo
    Html::$ngDisabled = '!' . $idGradePoloFieldAngular;
    $tipo = Html::inputSelectNgRepeat("GradePolo[" . $polo['idPolo'] . "]['isPresencialGradePolo']", 'Presencial?', 'NS21', "Aux.Boolean", false, $ngChange, 'Boolean'); //'select com tipo de aula'
    // contadores
    $contadores = ''
            . '<button ng-show="' . $isSuggest . ' && '.$professorList.'.length > 0 " class="btn btn-sm m-0 p-0 mb-1 btn-block {{' . $idGradePoloFieldAngular . '>0 && \'btn-success\' || \'btn-info\'}}" ng-click="save(GradePolo[' . $polo['idPolo'] . '])">'
            . '{{' . $idGradePoloFieldAngular . '>0 && \'Salvar alterações\' || \'Iniciar edição\'}}'
            . '</button>'
            . '<p class="text-warning m-0" '.Html::hint('Nenhum professor localizado para ministrar este módulo neste polo').' ng-show="' . $isSuggest . ' && '.$professorList.'.length <= 0"><i class="fa fa-info mr-1"></i>Nenhum professor</p>'
            . '<span class="badge badge-secondary">' . rand(0, 40) . ' alunos</span>'
            . '';

    $th[] = $polo['nomePolo'];
    $td[] = ''
            . $modulo
            . $professor
            . $tipo
            // Linha Horário
            . '<span class="floating-label-select">Hora de início</span>'
            . Html::input(['ng-disabled' => '!' . $idGradePoloFieldAngular, 'ng-model' => "GradePolo[" . $polo['idPolo'] . "]['hrainicioGradePolo']", 'required' => 'true', 'class' => 'hora'])// 'input com hra inicio | input com hora de fim'
            . $contadores
            . '<p class="text-danger" ng-show="GradePolo[' . $polo['idPolo'] . '][\'error\']" ng-bind-html="GradePolo[' . $polo['idPolo'] . '][\'aviso\']"></p>'
            // Botões para salvar e remover
            //. '<div>Alteração detectada' . '</div>'
            . ''

    ;
}
$table = new Table($th, false, true, 'table-bordered table-sm table-fixed', true);
$isHtml = true;
$table->setForeach($entidade . "Filtradas = (GradePolos| filter : filtro | orderBy: 'order')", $entidade)
        ->setOnClick(false)
        ->setMenuContexto($entidade . 'ContextItens')
        ->setBindHTML(false)
        ->addLinha($td, '{{' . $entidade . '.idGradepolo}}', $isHtml);







// Head de impressão dos filtros utilizados
$tableFiltros = new Table(['', '', '', ''], false, false, 'table-bordered', false);
$tableFiltros->setExplode(false);
$tableFiltros->addLinha([
    '<p class="text-strong">Polo</p>
                  <p ng-repeat="filter in Aux.Polo | filter: {idPolo:Args.idPolo}:true">{{filter.nomePolo}}</p>',
    '<p class="text-strong">Leciona</p>
                  <p ng-repeat="filter in Aux.Leciona | filter: {idLeciona:Args.idLeciona}:true">{{filter.nomeLeciona}}</p>',
    '<p class="text-strong">Grade</p>
                  <p ng-repeat="filter in Aux.Grade | filter: {idGrade:Args.idGrade}:true">{{filter.nomeGrade}}</p>',
    '<p class="text-strong">Texto Pesquisa</p>
          <p class="text-upper">{{Args.Search}}</p>']);

// Criação do objeto Template. Retorna Head, List e Print. 
$template = new AdminTemplate($entidade, $title, $tableFiltros, $filtros, $card, $table);
$template->setForm($form);
$template->setViewHTML($viewHTML);

/* Tabs, caso seja necessário
  $tabs = [
  Tab::getModel('identificacao', 'Cadastro', $template->printForm()),
  array_merge(Tab::getModel('arquivos', 'Arquivos', Html::uploadFile($entidade)), ['ng-if' => "$entidade.id$entidade>0"])
  ];
 */


//$html = $template->printTemplate();

$html = $template->printTemplate()
        . '<div id="formEdit' . $entidade . '" class="controleShow' . $entidade . ' d-print-none" style="max-width:800px;margin:0px auto;">'
        . (($tabs) ? Tab::printTab($tabs) : $template->printForm())
        . AdminTemplate::getButtonsStatic($entidade)
        . '</div>';

/*
  . '<div id="formEdit' . $entidade . '" class="controleShow' . $entidade . ' d-print-none">'
  . (($tabs) ? Tab::printTab($tabs) : $template->printForm())
  . AdminTemplate::getButtonsStatic($entidade)
  . '</div>';
 */

echo '<div ng-controller="' . $entidade . 'Controller" id="controllerContent" class="d-none">'
 . $html
 . '</div>';


echo '<style>
table .floating-label-select, 
table p.form-control ~.floating-label, 
table input:disabled ~ .floating-label, 
table input:focus ~ .floating-label, 
table input:not(:focus):valid ~ .floating-label {
    font-size: 0.8em;
    font-weight: normal;
}
table select.form-control:not([size]):not([multiple]) {
    height: auto;
}
table .form-control {
    padding: 0;
    margin-bottom: 10px;

}
table textarea, 
table textarea.form-control, 
table input.form-control, 
table input[type=text], 
table input[type=password], 
table input[type=email], 
table input[type=number], 
table [type=text].form-control, 
table [type=password].form-control, 
table [type=email].form-control, 
table [type=tel].form-control, 
table [contenteditable].form-control {
    font-size: 0.9em;
}

</style>
';


/* caso necessario injetar JS antes do controller
  $packer = new Packer($js, 'Normal', true, false, true);
  $packed_js = $packer->pack();
  echo "<script>$packed_js</script>";
 */

Component::init('GradePoloGDF-script.js');

include Config::getData('path') . '/view/template/template2.php';
