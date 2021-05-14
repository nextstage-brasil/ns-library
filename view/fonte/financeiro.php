<?php

// Automação de Criação de Sistema - 06/01/2021 07:41:13
if (!defined("SISTEMA_LIBRARY")) {
    die("Acesso direto não permitido");
}

include Config::getData('path') . '/view/template/template1.php';

// nome da entidade JS
$entidade = 'Financeiro';
$onclick = $entidade . 'OnEdit(' . $entidade . ')';

//$viewHTML = file_get_contents(Config::getData('url') . "/_build/_sourceView/$entidade/view.php");
// titulo apresentado ao usuário. Caso não tenha sido configurado aliase, será exibido o nome da entidade
$title = Config::getData('titlePagesAliases', $entidade);

// filtros na exibição da lista
$filtros = [
    ['grid' => 'col-6 col-sm-4', 'entidade' => 'Matricula'],
    ['grid' => 'col-6 col-sm-4', 'entidade' => 'Status'],
    ['grid' => 'col-6 col-sm-4', 'entidade' => 'Auxiliar'],
    ['grid' => 'col-6 col-sm-4', 'entidade' => 'FormaPgto'],
    ['grid' => 'col-6 col-sm-4', 'entidade' => 'Conta']
];


//  cards com totais
$filtros = [
    '<div class="col-12 mb-2 border-bottom"><div class="row">'
    . '<div class="col-12 col-lg-3"><ns-card-home card-class="success" title="Recebido" valor="{{Total.recebido|currency}}" descricao="Total de valores recebidos"></ns-card-home></div>'
    . '<div class="col-12 col-lg-3"><ns-card-home card-class="info" title="A receber" valor="{{Total.receber|currency}}" descricao="Total de valores a receber"></ns-card-home></div>'
    . '<div class="col-12 col-lg-3"><ns-card-home card-class="danger" title="Inadimplentes" valor="{{Total.inadimplentes}}" descricao="Total de alunos inadimplentes"></ns-card-home></div>'
    . '<div class="col-12 col-lg-3"><ns-card-home card-class="warning" title="Outros" valor="{{Total.outros}}" descricao="Total de a A DEFINIR"></ns-card-home></div>'
    . '</div></div>',
    //'<div class="col-4 col-lg-2">' . $periodo->left . '</div>',
    //'<div class="col-4 col-lg-2">' . $periodo->right . '</div>',
    '<div class="col-8 col-lg-4">' . Html::input(['type' => 'daterange', 'ng-model' => 'Args._periodoRange', 'ng-change' => $onChange], 'Periodo') . '</div>',
    ['grid' => 'col-6 col-sm-4', 'entidade' => 'Matricula'],
    ['grid' => 'col-6 col-sm-4', 'entidade' => 'Status'],
    ['grid' => 'col-6 col-sm-4', 'entidade' => 'Auxiliar'],
    ['grid' => 'col-6 col-sm-4', 'entidade' => 'FormaPgto'],
    ['grid' => 'col-6 col-sm-4', 'entidade' => 'Conta']
];

// Campos apresentados na lista
$Financeiros = [
    ['label' => 'Data recebimento', 'atributo' => 'none + (Financeiro.pagamentoFinanceiro|date:\'dd/MM/yyyy\')', 'class' => 'text-left'],
    ['label' => 'Tipo', 'atributo' => 'Auxiliar.nomeAuxiliar', 'class' => 'text-left'],
    //['label' => Config::getAliasesField('idStatusFinanceiro'), 'atributo' => 'Status.nomeStatus', 'class' => 'text-left'],
    ['label' => Config::getAliasesField('idMatricula'), 'atributo' => 'idMatricula', 'class' => 'text-left'],
    ['label' => 'Vencimento', 'atributo' => 'vencimentoFinanceiro', 'class' => 'text-left'],
    ['label' => Config::getAliasesField('valorFinanceiro'), 'atributo' => 'none + (Financeiro.valorFinanceiro|currency)', 'class' => 'text-left'],
    ['label' => Config::getAliasesField('descricaoFinanceiro'), 'atributo' => 'descricaoFinanceiro', 'class' => 'text-left'],
    /* ['label' => Config::getAliasesField('extrasFinanceiro'), 'atributo' => 'extrasFinanceiro', 'class' => 'text-left'], */
    ['label' => 'Forma de pagamento', 'atributo' => 'FormaPgto.nomeFormaPgto', 'class' => 'text-left'],
    ['label' => 'Conta', 'atributo' => 'Conta.nomeConta', 'class' => 'text-left']
];
$Financeiros[0]['ngclick'] = $onclick;

// cards
$card = Card::basic($Financeiros, $entidade, false, $entidade . 'ContextItens');

// table
foreach ($Financeiros as $campo) {
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
    Form::getModel(Html::inputSelectNgRepeat('Financeiro.idMatricula', Config::getAliasesField('idMatricula'), 'Financeiro.idMatricula_ro', 'Aux.Matricula', $ngClick, $ngChange), 'col-sm-4'),
    Form::getModel(Html::input(['ng-model' => 'Financeiro.valorFinanceiro', 'type' => 'number', 'class' => '', 'required' => 'required ', 'ng-change' => $ngChange], Config::getAliasesField('valorFinanceiro')), 'col-sm-4'),
    Form::getModel(Html::inputDatePicker(Config::getAliasesField('vencimentoFinanceiro'), 'Financeiro.vencimentoFinanceiro', $minDate, $maxDate, $ngChange), 'col-sm-4'),
    Form::getModel(Html::input(['ng-model' => 'Financeiro.descricaoFinanceiro', 'type' => 'text', 'class' => '', 'required' => 'not-required', 'ng-change' => $ngChange], Config::getAliasesField('descricaoFinanceiro')), 'col-sm-4'),
    Form::getModel('<config-json title="' . Config::getAliasesField('extrasFinanceiro') . '" model="Financeiro.extrasFinanceiro" grid="col-sm-6"></config-json>', 'col-sm-12'),
    Form::getModel(Html::inputSelectNgRepeat('Financeiro.idStatusFinanceiro', Config::getAliasesField('idStatusFinanceiro'), 'Financeiro.idStatusFinanceiro_ro', 'Aux.StatusFinanceiro', $ngClick, $ngChange), 'col-sm-4'),
    Form::getModel(Html::inputSelectNgRepeat('Financeiro.idAuxiliar', Config::getAliasesField('idAuxiliar'), 'Financeiro.idAuxiliar_ro', 'Aux.Auxiliar', $ngClick, $ngChange), 'col-sm-4'),
    Form::getModel(Html::inputSelectNgRepeat('Financeiro.idFormaPgto', Config::getAliasesField('idFormaPgto'), 'Financeiro.idFormaPgto_ro', 'Aux.FormaPgto', $ngClick, $ngChange), 'col-sm-4'),
    Form::getModel(Html::inputDatePicker(Config::getAliasesField('pagamentoFinanceiro'), 'Financeiro.pagamentoFinanceiro', $minDate, $maxDate, $ngChange), 'col-sm-4'),
    Form::getModel(Html::inputSelectNgRepeat('Financeiro.idConta', Config::getAliasesField('idConta'), 'Financeiro.idConta_ro', 'Aux.Conta', $ngClick, $ngChange), 'col-sm-4')
];

// Head de impressão dos filtros utilizados
$tableFiltros = new Table(['', '', '', '', '', ''], false, false, 'table-bordered', false);
$tableFiltros->setExplode(false);
$tableFiltros->addLinha([
    '<p class="text-strong">Matricula</p>
                  <p ng-repeat="filter in Aux.Matricula | filter: {idMatricula:Args.idMatricula}:true">{{filter.nomeMatricula}}</p>',
    '<p class="text-strong">StatusFinanceiro</p>
                  <p ng-repeat="filter in Aux.StatusFinanceiro | filter: {idStatusFinanceiro:Args.idStatusFinanceiro}:true">{{filter.nomeStatusFinanceiro}}</p>',
    '<p class="text-strong">Auxiliar</p>
                  <p ng-repeat="filter in Aux.Auxiliar | filter: {idAuxiliar:Args.idAuxiliar}:true">{{filter.nomeAuxiliar}}</p>',
    '<p class="text-strong">FormaPgto</p>
                  <p ng-repeat="filter in Aux.FormaPgto | filter: {idFormaPgto:Args.idFormaPgto}:true">{{filter.nomeFormaPgto}}</p>',
    '<p class="text-strong">Conta</p>
                  <p ng-repeat="filter in Aux.Conta | filter: {idConta:Args.idConta}:true">{{filter.nomeConta}}</p>',
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

$js = ""
        . "var _initDate = '" . date('Y') . "-01-01';"
        . "var _endDate = '" . date('Y') . "-12-31';";
//$js = "var _encontrosHTML='Cristofer';";
echo \NsUtil\Packer::jsPack($js);

Component::init($entidade . '-script.js');

include Config::getData('path') . '/view/template/template2.php';
