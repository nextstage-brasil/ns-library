<?php

// Automação de Criação de Sistema - 20/08/2020 06:25:29
if (!defined("SISTEMA_LIBRARY")) {
    die("Acesso direto não permitido");
}

include Config::getData('path') . '/view/template/template1.php';

// nome da entidade JS
$entidade = 'GradePolo';
$onclick = $entidade . 'OnEdit(' . $entidade . ')';

//$viewHTML = file_get_contents(Config::getData('url') . "/_build/_sourceView/$entidade/view.php");
// titulo apresentado ao usuário. Caso não tenha sido configurado aliase, será exibido o nome da entidade
$title = Config::getData('titlePagesAliases', $entidade);

// filtros na exibição da lista
$onChange = "GradePoloGetAll('', true)";
$periodo = Html::inputDatePickersGetLeftAndRight('Data inicial', 'Data final', 'Args.dataInicial', 'Args.dataFinal', '', '', $onChange);
$filtros = [
    ['grid' => 'col-6 col-sm-3', 'entidade' => 'Curso', 'label' => 'Curso'],
    ['grid' => 'col-6 col-sm-3', 'entidade' => 'Polo', 'label' => 'Polo'],
    '<div class="col-8 col-lg-4">' . Html::input(['type'=>'daterange',  'ng-model' => 'Args._periodoRange', 'ng-change' => $onChange], 'Periodo') . '</div>',
    ['grid' => 'col-6 col-sm-3', 'entidade' => 'Usuario', 'label' => 'Professor'],
    ['grid' => 'col-6 col-sm-3', 'entidade' => 'Modulo', 'label' => 'Módulo'],
];

// Campos apresentados na lista
$GradePolos = [
    ['label' => Config::getAliasesField('idPolo'), 'atributo' => 'Polo.nomePolo', 'class' => 'text-left'],
    ['label' => Config::getAliasesField('idCurso'), 'atributo' => 'Curso.nomeCurso', 'class' => 'text-left'],
    ['label' => Config::getAliasesField('dtHrGradePolo'), 'atributo' => 'dtHrGradePolo', 'class' => 'text-left', 'order' => 'dataOrder'],
    ['label' => Config::getAliasesField('encontroGradePolo'), 'atributo' => 'encontroGradePolo', 'class' => 'text-left', 'order' => 'encontroGradePolo'],
    ['label' => Config::getAliasesField('Professor'), 'atributo' => 'Usuario.nomeUsuario', 'class' => 'text-left'],
    ['label' => Config::getAliasesField('idModulo'), 'atributo' => 'Modulo.nomeModulo', 'class' => 'text-left'],
    ['label' => Config::getAliasesField('hrainicioGradePolo'), 'atributo' => 'hrainicioGradePolo', 'class' => 'text-left'],
    ['label' => Config::getAliasesField('durGradePolo'), 'atributo' => 'durGradePolo', 'class' => 'text-left'],
    ['label' => Config::getAliasesField('isPresencialGradePolo'), 'atributo' => 'isPresencialGradePoloF', 'class' => 'text-left'],
        //['label' => Config::getAliasesField('extrasGradePolo'), 'atributo' => 'extrasGradePolo', 'class' => 'text-left'],
];
$GradePolos[0]['ngclick'] = $onclick;

// cards
$card = Card::basic($GradePolos, $entidade, false, $entidade . 'ContextItens');

// table
foreach ($GradePolos as $campo) {
    $order = (($campo['order']) ? $campo['order'] : $campo['atributo']);
    $th[$order] = (($campo['label']) ? str_replace(':', '', $campo['label']) : '') . '|' . $campo['class'];
    $td[] = '{{' . $entidade . '.' . $campo['atributo'] . '}}' . '|' . str_replace('text-strong', '', $campo['class']);
}
$table = new Table($th, false, true, '', true);
$table->setForeach($entidade . "Filtradas = (" . $entidade . "s| filter : filtro | orderBy: 'nome" . $entidade . "')", $entidade);
$table->setOnClick($entidade . 'OnEdit(' . $entidade . ')');
$table->setMenuContexto($entidade . 'ContextItens');
$table->addLinha($td);

// Form
$professorList = "(Professores | orderBy: 'nome')"; // "(Lists.AllProfs | filter: {idPolo:GradePolo.idPolo,idModulo: GradePolo.idModulo} | orderBy: 'nome')";
$modulosList = " (Modulos | orderBy: 'nomeModulo')";
$form = [
    Form::getModel(Html::inputSelectNgRepeat('GradePolo.idCurso', Config::getAliasesField('idCurso'), 'GradePolo.idCurso_ro', "Aux.Curso|orderBy:'nomeCurso'", $ngClick, $ngChange), 'col-sm-3'),
    Form::getModel(Html::inputSelectNgRepeat('GradePolo.idPolo', Config::getAliasesField('idPolo'), 'GradePolo.idPolo_ro', "Aux.Polo|orderBy:'nomePolo'", $ngClick, $ngChange), 'col-sm-3'),
    /// Modulos com base no polo
    Form::getModel(Html::inputSelectNgRepeat('GradePolo.idModulo', Config::getAliasesField('idModulo'), 'GradePolo.idModulo_ro', $modulosList, $ngClick, $ngChange), 'col-sm-3'),
    // Aqui, obter conforme os itens selecionados..
    Form::getModel(Html::inputSelectNgRepeat('GradePolo.idUsuario', Config::getAliasesField('professor'), 'GradePolo.idUsuario_ro', $professorList, $ngClick, $ngChange, ''), 'col-sm-3'),
    Form::getModel(Html::inputDatePicker(Config::getAliasesField('dtHrGradePolo'), 'GradePolo.dtHrGradePolo', $minDate, '2050-01-01', $ngChange), 'col-sm-3'),
    Form::getModel(Html::input(['ng-model' => 'GradePolo.hrainicioGradePolo', 'type' => 'text', 'class' => 'hora', 'required' => 'not-required', 'ng-change' => $ngChange], Config::getAliasesField('hrainicioGradePolo')), 'col-sm-2'),
    Form::getModel(Html::input(['ng-model' => 'GradePolo.durGradePolo', 'type' => 'text', 'class' => 'duracao', 'required' => 'not-required', 'ng-change' => $ngChange], Config::getAliasesField('durGradePolo')), 'col-sm-4'),
    //Form::getModel(Html::input(['ng-model' => 'GradePolo.isPresencialGradePolo', 'type' => 'text', 'class' => '', 'required' => 'required ', 'ng-change' => $ngChange], Config::getAliasesField('isPresencialGradePolo')), 'col-sm-4'),
    Form::getModel(Html::inputSelectNgRepeat("GradePolo.isPresencialGradePolo", 'Presencial?', 'NS21', "Aux.Boolean", false, $ngChange, 'Boolean'), 'col-sm-3'),
        //Form::getModel('<config-json title="' . Config::getAliasesField('extrasGradePolo') . '" model="GradePolo.extrasGradePolo" grid="col-sm-6"></config-json>', 'col-sm-12'),
];

// Head de impressão dos filtros utilizados
$tableFiltros = new Table(['', '', '', '', ''], false, false, 'table-bordered', false);
$tableFiltros->setExplode(false);
$tableFiltros->addLinha([
    '<p class="text-strong">Polo</p>
                  <p ng-repeat="filter in Aux.Polo | filter: {idPolo:Args.idPolo}:true">{{filter.nomePolo}}</p>',
    '<p class="text-strong">Usuario</p>
                  <p ng-repeat="filter in Aux.Usuario | filter: {idUsuario:Args.idUsuario}:true">{{filter.nomeUsuario}}</p>',
    '<p class="text-strong">Curso</p>
                  <p ng-repeat="filter in Aux.Curso | filter: {idCurso:Args.idCurso}:true">{{filter.nomeCurso}}</p>',
    '<p class="text-strong">Modulo</p>
                  <p ng-repeat="filter in Aux.Modulo | filter: {idModulo:Args.idModulo}:true">{{filter.nomeModulo}}</p>',
    '<p class="text-strong">Texto Pesquisa</p>
          <p class="text-upper">{{Args.Search}}</p>']);

// Criação do objeto Template. Retorna Head, List e Print. 
$template = new AdminTemplate($entidade, $title, $tableFiltros, $filtros, $card, $table);
$template->setForm($form);
$template->setViewHTML($viewHTML);

/* Tabs, caso seja necessário
  $tabs = [
  Tab::getModel('identificacao', 'Cadastro', $template->printForm()),
  array_merge(Tab::getModel('arquivos', 'Arquivos <span id="'.$entidade.'Files" class="badge badge-info">', Html::uploadFile($entidade)), ['ng-if' => "$entidade.id$entidade>0"])
  ];
 */


$html = $template->printTemplate()
        . '<div id="formEdit' . $entidade . '" class="controleShow' . $entidade . ' d-print-none">'
        . (($tabs) ? Tab::printTab($tabs) : $template->printForm())
        . AdminTemplate::getButtonsStatic($entidade)
        . '</div>';


echo '<div ng-controller="' . $entidade . 'Controller" id="controllerContent" class="d-none">'
 . $html
 . '</div>';


/* caso necessario injetar JS antes do controller
  $packer = new Packer($js, 'Normal', true, false, true);
  $packed_js = $packer->pack();
  echo "<script>$packed_js</script>";
 */

Component::init($entidade . '-script.js');

include Config::getData('path') . '/view/template/template2.php';
