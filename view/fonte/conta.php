<?php

// Automação de Criação de Sistema - 06/01/2021 06:57:15
if (!defined("SISTEMA_LIBRARY")) {
    die("Acesso direto não permitido");
}

include Config::getData('path') . '/view/template/template1.php';

// nome da entidade JS
$entidade = 'Conta';
$onclick = $entidade . 'OnEdit(' . $entidade . ')';

//$viewHTML = file_get_contents(Config::getData('url') . "/_build/_sourceView/$entidade/view.php");
// titulo apresentado ao usuário. Caso não tenha sido configurado aliase, será exibido o nome da entidade
$title = Config::getData('titlePagesAliases', $entidade);

// filtros na exibição da lista
$filtros = [['grid' => 'col-6 col-sm-4', 'entidade' => 'Banco'],
    ['grid' => 'col-6 col-sm-4', 'entidade' => 'Polo']];

// Campos apresentados na lista
$Contas = [
    ['label' => Config::getAliasesField('idPolo'), 'atributo' => 'Polo.nomePolo', 'class' => 'text-left'], 
    ['label' => Config::getAliasesField('tipoConta'), 'atributo' => 'tipoConta', 'class' => 'text-left'],
    ['label' => Config::getAliasesField('nomeConta'), 'atributo' => 'nomeConta', 'class' => 'text-left'],
    ['label' => Config::getAliasesField('bancoConta'), 'atributo' => 'bancoConta', 'class' => 'text-left'],
    ['label' => Config::getAliasesField('agenciaConta'), 'atributo' => 'agenciaConta', 'class' => 'text-left'],
    ['label' => Config::getAliasesField('numeroConta'), 'atributo' => 'numeroConta', 'class' => 'text-left'],
    ['label' => Config::getAliasesField('saldoatualConta'), 'atributo' => 'saldoatualConta', 'class' => 'text-left'],
    ['label' => Config::getAliasesField('datasaldoConta'), 'atributo' => 'datasaldoConta', 'class' => 'text-left'],
    ['label' => Config::getAliasesField('statusConta'), 'atributo' => 'statusConta', 'class' => 'text-left'],
    //['label' => Config::getAliasesField('extrasConta'), 'atributo' => 'extrasConta', 'class' => 'text-left'],
    ['label' => Config::getAliasesField('isContacaixaConta'), 'atributo' => 'isContacaixaContaF', 'class' => 'text-left'],
    ['label' => Config::getAliasesField('idBanco'), 'atributo' => 'Banco.nomeBanco', 'class' => 'text-left'],
    
];
$Contas[0]['ngclick'] = $onclick;

// cards
$card = Card::basic($Contas, $entidade, false, $entidade . 'ContextItens');

// table
foreach ($Contas as $campo) {
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
    Form::getModel(Html::inputSelectNgRepeat('Conta.idPolo', Config::getAliasesField('idPolo'), 'Conta.idPolo_ro', 'Aux.Polo', $ngClick, $ngChange), 'col-sm-4'), 
    
    Form::getModel(Html::input(['ng-model' => 'Conta.nomeConta', 'type' => 'text', 'class' => '', 'required' => 'not-required', 'ng-change' => $ngChange], Config::getAliasesField('nomeConta')), 'col-sm-8'),
    Form::getModel(Html::input(['ng-model' => 'Conta.tipoConta', 'type' => 'text', 'class' => '', 'required' => 'required ', 'ng-change' => $ngChange], Config::getAliasesField('tipoConta')), 'col-sm-2'),
    Form::getModel(Html::input(['ng-model' => 'Conta.bancoConta', 'type' => 'text', 'class' => '', 'required' => 'not-required', 'ng-change' => $ngChange], Config::getAliasesField('bancoConta')), 'col-sm-4'),
    Form::getModel(Html::input(['ng-model' => 'Conta.agenciaConta', 'type' => 'text', 'class' => '', 'required' => 'not-required', 'ng-change' => $ngChange], Config::getAliasesField('agenciaConta')), 'col-sm-4'),
    Form::getModel(Html::input(['ng-model' => 'Conta.numeroConta', 'type' => 'text', 'class' => '', 'required' => 'not-required', 'ng-change' => $ngChange], Config::getAliasesField('numeroConta')), 'col-sm-4'),
    //Form::getModel(Html::input(['ng-model' => 'Conta.saldoatualConta', 'type' => 'text', 'class' => 'decimal', 'required' => 'not-required', 'ng-change' => $ngChange], Config::getAliasesField('saldoatualConta')), 'col-sm-4'),
    //Form::getModel(Html::inputDatePicker(Config::getAliasesField('datasaldoConta'), 'Conta.datasaldoConta', $minDate, $maxDate, $ngChange), 'col-sm-4'),
    Form::getModel(Html::input(['ng-model' => 'Conta.statusConta', 'type' => 'number', 'class' => '', 'required' => 'required ', 'ng-change' => $ngChange], Config::getAliasesField('statusConta')), 'col-sm-4'),
    //Form::getModel('<config-json title="' . Config::getAliasesField('extrasConta') . '" model="Conta.extrasConta" grid="col-sm-6"></config-json>', 'col-sm-12'),
    Form::getModel(Html::inputSelectNgRepeat('Conta.isContacaixaConta', Config::getAliasesField('isContacaixaConta'), 'Conta.isContacaixaConta_ro', 'Aux.Boolean', $ngClick, $ngChange, 'Boolean'), 'col-sm-4'),
    Form::getModel(Html::inputSelectNgRepeat('Conta.idBanco', Config::getAliasesField('idBanco'), 'Conta.idBanco_ro', 'Aux.Banco', $ngClick, $ngChange), 'col-sm-4'),
    
];

// Head de impressão dos filtros utilizados
$tableFiltros = new Table(['', '', ''], false, false, 'table-bordered', false);
$tableFiltros->setExplode(false);
$tableFiltros->addLinha([
    '<p class="text-strong">Banco</p>
                  <p ng-repeat="filter in Aux.Banco | filter: {idBanco:Args.idBanco}:true">{{filter.nomeBanco}}</p>',
    '<p class="text-strong">Polo</p>
                  <p ng-repeat="filter in Aux.Polo | filter: {idPolo:Args.idPolo}:true">{{filter.nomePolo}}</p>',
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
