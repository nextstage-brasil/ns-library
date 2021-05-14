<?php

// Automação de Criação de Sistema - 03/04/2020 04:46:45
if (!defined("SISTEMA_LIBRARY")) {
    die("Acesso direto não permitido");
}
AppController::naoDisponivel();
include Config::getData('path') . '/view/template/template1.php';

// nome da entidade JS
$entidade = 'XGrade';
$onclick = $entidade . 'OnEdit(' . $entidade . ')';

//$viewHTML = file_get_contents(Config::getData('url') . "/_build/_sourceView/$entidade/view.php");
// titulo apresentado ao usuário. Caso não tenha sido configurado aliase, será exibido o nome da entidade
$title = Config::getData('titlePagesAliases', $entidade);

// filtros na exibição da lista
$filtros = [['grid' => 'col-6 col-sm-4', 'entidade' => 'Modulo'],
    ['grid' => 'col-6 col-sm-4', 'entidade' => 'Curso']];

// Campos apresentados na lista
$XGrades = [
    ['label' => Config::getAliasesField('nomeGrade'), 'atributo' => 'nomeGrade', 'class' => 'text-left'],
    ['label' => Config::getAliasesField('idModulo'), 'atributo' => 'Modulo.nomeModulo', 'class' => 'text-left'],
    ['label' => Config::getAliasesField('idCurso'), 'atributo' => 'Curso.nomeCurso', 'class' => 'text-left'],
    ['label' => Config::getAliasesField('extrasGrade'), 'atributo' => 'extrasGrade', 'class' => 'text-left']
];
$XGrades[0]['ngclick'] = $onclick;

// cards
$card = Card::basic($XGrades, $entidade, false, $entidade . 'ContextItens');

// table
foreach ($XGrades as $campo) {
    $th[$campo['atributo']] = (($campo['label']) ? str_replace(':', '', $campo['label']) : '') . '|' . $campo['class'];
    $td[] = '{{' . $entidade . '.' . $campo['atributo'] . '}}' . '|' . str_replace('text-strong', '', $campo['class']);
}
$table = new Table($th, false, true, '', true);
$table->setForeach($entidade . "Filtradas = (" . $entidade . "s| filter : filtro | orderBy: 'nome" . $entidade . "')", $entidade);
$table->setOnClick($entidade . 'OnEdit(' . $entidade . ')');
$table->setMenuContexto($entidade . 'ContextItens');
$table->addLinha($td);

// Form
$form = [
    Form::getModel(Html::input(['ng-model' => 'XGrade.nomeGrade', 'type' => 'text', 'class' => '', 'required' => 'required ', 'ng-change' => $ngChange], Config::getAliasesField('nomeGrade')), 'col-sm-4'),
    Form::getModel(Html::inputSelectNgRepeat('XGrade.idModulo', Config::getAliasesField('idModulo'), 'XGrade.idModulo_ro', 'Aux.Modulo', $ngClick, $ngChange), 'col-sm-4'),
    Form::getModel(Html::inputSelectNgRepeat('XGrade.idCurso', Config::getAliasesField('idCurso'), 'XGrade.idCurso_ro', 'Aux.Curso', $ngClick, $ngChange), 'col-sm-4'),
    Form::getModel('<config-json title="' . Config::getAliasesField('extrasGrade') . '" model="XGrade.extrasGrade" grid="col-sm-6"></config-json>', 'col-sm-12')
];

// Head de impressão dos filtros utilizados
$tableFiltros = new Table(['', '', ''], false, false, 'table-bordered', false);
$tableFiltros->setExplode(false);
$tableFiltros->addLinha([
    '<p class="text-strong">Modulo</p>
                  <p ng-repeat="filter in Aux.Modulo | filter: {idModulo:Args.idModulo}:true">{{filter.nomeModulo}}</p>',
    '<p class="text-strong">Curso</p>
                  <p ng-repeat="filter in Aux.Curso | filter: {idCurso:Args.idCurso}:true">{{filter.nomeCurso}}</p>',
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
