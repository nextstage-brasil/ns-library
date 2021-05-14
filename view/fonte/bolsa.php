<?php
// Automação de Criação de Sistema - 13/01/2020 07:34:45
if (!defined("SISTEMA_LIBRARY")) {die("Acesso direto não permitido");}               

include Config::getData('path') . '/view/template/template1.php';

// nome da entidade JS
$entidade = 'Bolsa';
$onclick = $entidade . 'OnEdit(' . $entidade . ')';

//$viewHTML = file_get_contents(Config::getData('url') . "/_build/_sourceView/$entidade/view.php");

// titulo apresentado ao usuário. Caso não tenha sido configurado aliase, será exibido o nome da entidade
$title = Config::getData('titlePagesAliases', $entidade); 

// filtros na exibição da lista
$filtros = [['grid' => 'col-6 col-sm-4', 'entidade' => 'Matricula'],
['grid' => 'col-6 col-sm-4', 'entidade' => 'Status']];

// Campos apresentados na lista
$Bolsas = [
            ['label' => Config::getAliasesField('idMatricula'), 'atributo' => 'Matricula.nomeMatricula', 'class'=>'text-left'],

            ['label' => Config::getAliasesField('idStatus'), 'atributo' => 'Status.nomeStatus', 'class'=>'text-left'],

            ['label' => Config::getAliasesField('dataPedidoBolsa'), 'atributo' => 'dataPedidoBolsa', 'class'=>'text-left'],

            ['label' => Config::getAliasesField('dataDecisaoBolsa'), 'atributo' => 'dataDecisaoBolsa', 'class'=>'text-left'],

            ['label' => Config::getAliasesField('validadeBolsa'), 'atributo' => 'validadeBolsa', 'class'=>'text-left'],

            ['label' => Config::getAliasesField('extrasBolsa'), 'atributo' => 'extrasBolsa', 'class'=>'text-left'],

            ['label' => Config::getAliasesField('daquiprabaixotudojson'), 'atributo' => 'daquiprabaixotudojson', 'class'=>'text-left'],

            ['label' => Config::getAliasesField('salBolsa'), 'atributo' => 'salBolsa', 'class'=>'text-left'],

            ['label' => Config::getAliasesField('desempregado'), 'atributo' => 'desempregado', 'class'=>'text-left'],

            ['label' => Config::getAliasesField('dispostoSerbVoluntarios'), 'atributo' => 'dispostoSerbVoluntarios', 'class'=>'text-left'],

            ['label' => Config::getAliasesField('justificativaPedido'), 'atributo' => 'justificativaPedido', 'class'=>'text-left'],

            ['label' => Config::getAliasesField('decisao'), 'atributo' => 'decisao', 'class'=>'text-left']
    ];
$Bolsas[0]['ngclick'] = $onclick;

// cards
$card = Card::basic($Bolsas, $entidade, false, $entidade.'ContextItens');

// table
foreach ($Bolsas as $campo) {
$th[$campo['atributo']] = (($campo['label']) ? str_replace(':', '', $campo['label']) : '') . '|' . $campo['class'];
$td[] = '{{'.$entidade.'.'.$campo['atributo'] . '}}' . '|' . str_replace('text-strong', '', $campo['class']);
}
$table = new Table($th, false, true, '', true);
$table->setForeach($entidade."Filtradas = (".$entidade."s| filter : filtro | orderBy: 'nome".$entidade."')", $entidade);
$table->setOnClick($entidade.'OnEdit('.$entidade.')');
$table->setMenuContexto($entidade.'ContextItens');
$table->addLinha($td);

// Form
$form = [
Form::getModel(Html::inputSelectNgRepeat('Bolsa.idMatricula', Config::getAliasesField('idMatricula'), 'Bolsa.idMatricula_ro', 'Aux.Matricula', $ngClick, $ngChange), 'col-sm-4'), 
Form::getModel(Html::inputSelectNgRepeat('Bolsa.idStatus', Config::getAliasesField('idStatus'), 'Bolsa.idStatus_ro', 'Aux.Status', $ngClick, $ngChange), 'col-sm-4'), 
Form::getModel(Html::inputDatePicker(Config::getAliasesField('dataPedidoBolsa'), 'Bolsa.dataPedidoBolsa', $minDate, $maxDate, $ngChange), 'col-sm-4'), 
Form::getModel(Html::inputDatePicker(Config::getAliasesField('dataDecisaoBolsa'), 'Bolsa.dataDecisaoBolsa', $minDate, $maxDate, $ngChange), 'col-sm-4'), 
Form::getModel(Html::inputDatePicker(Config::getAliasesField('validadeBolsa'), 'Bolsa.validadeBolsa', $minDate, $maxDate, $ngChange), 'col-sm-4'), 
Form::getModel('<config-json title="'.Config::getAliasesField('extrasBolsa').'" model="Bolsa.extrasBolsa" grid="col-sm-6"></config-json>', 'col-sm-12'), 
Form::getModel(Html::input(['ng-model' => 'Bolsa.daquiprabaixotudojson', 'type'=>'text', 'class'=>'', 'required'=>'required ', 'ng-change' => $ngChange], Config::getAliasesField('daquiprabaixotudojson')), 'col-sm-4'), 
Form::getModel(Html::input(['ng-model' => 'Bolsa.salBolsa', 'type'=>'number', 'class'=>'', 'required'=>'required ', 'ng-change' => $ngChange], Config::getAliasesField('salBolsa')), 'col-sm-4'), 
Form::getModel(Html::input(['ng-model' => 'Bolsa.desempregado', 'type'=>'text', 'class'=>'', 'required'=>'required ', 'ng-change' => $ngChange], Config::getAliasesField('desempregado')), 'col-sm-4'), 
Form::getModel(Html::input(['ng-model' => 'Bolsa.dispostoSerbVoluntarios', 'type'=>'text', 'class'=>'', 'required'=>'required ', 'ng-change' => $ngChange], Config::getAliasesField('dispostoSerbVoluntarios')), 'col-sm-4'), 
Form::getModel(Html::input(['ng-model' => 'Bolsa.justificativaPedido', 'type'=>'text', 'class'=>'', 'required'=>'required ', 'ng-change' => $ngChange], Config::getAliasesField('justificativaPedido')), 'col-sm-4'), 
Form::getModel(Html::input(['ng-model' => 'Bolsa.decisao', 'type'=>'text', 'class'=>'', 'required'=>'required ', 'ng-change' => $ngChange], Config::getAliasesField('decisao')), 'col-sm-4')
];

// Head de impressão dos filtros utilizados
$tableFiltros = new Table(['','',''], false, false, 'table-bordered', false);$tableFiltros->setExplode(false);$tableFiltros->addLinha([
                  '<p class="text-strong">Matricula</p>
                  <p ng-repeat="filter in Aux.Matricula | filter: {idMatricula:Args.idMatricula}:true">{{filter.nomeMatricula}}</p>',
                  '<p class="text-strong">Status</p>
                  <p ng-repeat="filter in Aux.Status | filter: {idStatus:Args.idStatus}:true">{{filter.nomeStatus}}</p>',
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
. '<div id="formEdit'.$entidade.'" class="controleShow'.$entidade.' d-print-none">'
. (($tabs) ? Tab::printTab($tabs) : $template->printForm())
. AdminTemplate::getButtonsStatic($entidade)
.'</div>';


echo '<div ng-controller="' . $entidade . 'Controller" id="controllerContent" class="d-none">'
 . $html
 . '</div>';


/* caso necessario injetar JS antes do controller
$packer = new Packer($js, 'Normal', true, false, true);
$packed_js = $packer->pack();
echo "<script>$packed_js</script>"; 
*/

Component::init($entidade.'-script.js');

include Config::getData('path') . '/view/template/template2.php';