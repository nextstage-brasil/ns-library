<?php
// Automação de Criação de Sistema - 13/01/2020 07:34:46
if (!defined("SISTEMA_LIBRARY")) {die("Acesso direto não permitido");}               

include Config::getData('path') . '/view/template/template1.php';

// nome da entidade JS
$entidade = 'Reembolso';
$onclick = $entidade . 'OnEdit(' . $entidade . ')';

//$viewHTML = file_get_contents(Config::getData('url') . "/_build/_sourceView/$entidade/view.php");

// titulo apresentado ao usuário. Caso não tenha sido configurado aliase, será exibido o nome da entidade
$title = Config::getData('titlePagesAliases', $entidade); 

// filtros na exibição da lista
$filtros = [['grid' => 'col-6 col-sm-4', 'entidade' => 'Status'],
['grid' => 'col-6 col-sm-4', 'entidade' => 'ReembolsoMotivo'],
['grid' => 'col-6 col-sm-4', 'entidade' => 'Usuario']];

// Campos apresentados na lista
$Reembolsos = [
            ['label' => Config::getAliasesField('idStatus'), 'atributo' => 'Status.nomeStatus', 'class'=>'text-left'],

            ['label' => Config::getAliasesField('idReembolsoMotivo'), 'atributo' => 'ReembolsoMotivo.nomeReembolsoMotivo', 'class'=>'text-left'],

            ['label' => Config::getAliasesField('idUsuario'), 'atributo' => 'Usuario.nomeUsuario', 'class'=>'text-left'],

            ['label' => Config::getAliasesField('valorReembolso'), 'atributo' => 'valorReembolso', 'class'=>'text-left'],

            ['label' => Config::getAliasesField('dataPedidoReembolso'), 'atributo' => 'dataPedidoReembolso', 'class'=>'text-left'],

            ['label' => Config::getAliasesField('dataReferenciaReembolso'), 'atributo' => 'dataReferenciaReembolso', 'class'=>'text-left'],

            ['label' => Config::getAliasesField('dataDecisaoReembolso'), 'atributo' => 'dataDecisaoReembolso', 'class'=>'text-left'],

            ['label' => Config::getAliasesField('dataExecucaoReembolso'), 'atributo' => 'dataExecucaoReembolso', 'class'=>'text-left'],

            ['label' => Config::getAliasesField('observacoes'), 'atributo' => 'observacoes', 'class'=>'text-left'],

            ['label' => Config::getAliasesField('userIdDecisorReembolso'), 'atributo' => 'userIdDecisorReembolso', 'class'=>'text-left'],

            ['label' => Config::getAliasesField('extrasReembolso'), 'atributo' => 'extrasReembolso', 'class'=>'text-left']
    ];
$Reembolsos[0]['ngclick'] = $onclick;

// cards
$card = Card::basic($Reembolsos, $entidade, false, $entidade.'ContextItens');

// table
foreach ($Reembolsos as $campo) {
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
Form::getModel(Html::inputSelectNgRepeat('Reembolso.idStatus', Config::getAliasesField('idStatus'), 'Reembolso.idStatus_ro', 'Aux.Status', $ngClick, $ngChange), 'col-sm-4'), 
Form::getModel(Html::inputSelectNgRepeat('Reembolso.idReembolsoMotivo', Config::getAliasesField('idReembolsoMotivo'), 'Reembolso.idReembolsoMotivo_ro', 'Aux.ReembolsoMotivo', $ngClick, $ngChange), 'col-sm-4'), 
Form::getModel(Html::inputSelectNgRepeat('Reembolso.idUsuario', Config::getAliasesField('idUsuario'), 'Reembolso.idUsuario_ro', 'Aux.Usuario', $ngClick, $ngChange), 'col-sm-4'), 
Form::getModel(Html::input(['ng-model' => 'Reembolso.valorReembolso', 'type'=>'number', 'class'=>'', 'required'=>'required ', 'ng-change' => $ngChange], Config::getAliasesField('valorReembolso')), 'col-sm-4'), 
Form::getModel(Html::inputDatePicker(Config::getAliasesField('dataPedidoReembolso'), 'Reembolso.dataPedidoReembolso', $minDate, $maxDate, $ngChange), 'col-sm-4'), 
Form::getModel(Html::inputDatePicker(Config::getAliasesField('dataReferenciaReembolso'), 'Reembolso.dataReferenciaReembolso', $minDate, $maxDate, $ngChange), 'col-sm-4'), 
Form::getModel(Html::inputDatePicker(Config::getAliasesField('dataDecisaoReembolso'), 'Reembolso.dataDecisaoReembolso', $minDate, $maxDate, $ngChange), 'col-sm-4'), 
Form::getModel(Html::inputDatePicker(Config::getAliasesField('dataExecucaoReembolso'), 'Reembolso.dataExecucaoReembolso', $minDate, $maxDate, $ngChange), 'col-sm-4'), 
Form::getModel(Html::input(['ng-model' => 'Reembolso.observacoes', 'type'=>'text', 'class'=>'', 'required'=>'required ', 'ng-change' => $ngChange], Config::getAliasesField('observacoes')), 'col-sm-4'), 
Form::getModel(Html::input(['ng-model' => 'Reembolso.userIdDecisorReembolso', 'type'=>'text', 'class'=>'', 'required'=>'required ', 'ng-change' => $ngChange], Config::getAliasesField('userIdDecisorReembolso')), 'col-sm-4'), 
Form::getModel('<config-json title="'.Config::getAliasesField('extrasReembolso').'" model="Reembolso.extrasReembolso" grid="col-sm-6"></config-json>', 'col-sm-12')
];

// Head de impressão dos filtros utilizados
$tableFiltros = new Table(['','','',''], false, false, 'table-bordered', false);$tableFiltros->setExplode(false);$tableFiltros->addLinha([
                  '<p class="text-strong">Status</p>
                  <p ng-repeat="filter in Aux.Status | filter: {idStatus:Args.idStatus}:true">{{filter.nomeStatus}}</p>',
                  '<p class="text-strong">ReembolsoMotivo</p>
                  <p ng-repeat="filter in Aux.ReembolsoMotivo | filter: {idReembolsoMotivo:Args.idReembolsoMotivo}:true">{{filter.nomeReembolsoMotivo}}</p>',
                  '<p class="text-strong">Usuario</p>
                  <p ng-repeat="filter in Aux.Usuario | filter: {idUsuario:Args.idUsuario}:true">{{filter.nomeUsuario}}</p>',
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