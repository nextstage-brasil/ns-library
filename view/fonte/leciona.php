<?php
// Automação de Criação de Sistema - 13/01/2020 07:34:45
if (!defined("SISTEMA_LIBRARY")) {die("Acesso direto não permitido");}               
AppController::naoDisponivel();
include Config::getData('path') . '/view/template/template1.php';

// nome da entidade JS
$entidade = 'Leciona';
$onclick = $entidade . 'OnEdit(' . $entidade . ')';

//$viewHTML = file_get_contents(Config::getData('url') . "/_build/_sourceView/$entidade/view.php");

// titulo apresentado ao usuário. Caso não tenha sido configurado aliase, será exibido o nome da entidade
$title = Config::getData('titlePagesAliases', $entidade); 

// filtros na exibição da lista
$filtros = [['grid' => 'col-6 col-sm-4', 'entidade' => 'Modulo'],
['grid' => 'col-6 col-sm-4', 'entidade' => 'Usuario']];

// Campos apresentados na lista
$Lecionas = [
            ['label' => Config::getAliasesField('nomeLeciona'), 'atributo' => 'nomeLeciona', 'class'=>'text-left'],

            ['label' => Config::getAliasesField('idModulo'), 'atributo' => 'Modulo.nomeModulo', 'class'=>'text-left'],

            ['label' => Config::getAliasesField('isResponsavelLeciona'), 'atributo' => 'isResponsavelLecionaF', 'class'=>'text-left'],

            ['label' => Config::getAliasesField('idUsuario'), 'atributo' => 'Usuario.nomeUsuario', 'class'=>'text-left']
    ];
$Lecionas[0]['ngclick'] = $onclick;

// cards
$card = Card::basic($Lecionas, $entidade, false, $entidade.'ContextItens');

// table
foreach ($Lecionas as $campo) {
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
Form::getModel(Html::input(['ng-model' => 'Leciona.nomeLeciona', 'type'=>'text', 'class'=>'', 'required'=>'required ', 'ng-change' => $ngChange], Config::getAliasesField('nomeLeciona')), 'col-sm-4'), 
Form::getModel(Html::inputSelectNgRepeat('Leciona.idModulo', Config::getAliasesField('idModulo'), 'Leciona.idModulo_ro', 'Aux.Modulo', $ngClick, $ngChange), 'col-sm-4'), 
Form::getModel(Html::inputSelectNgRepeat('Leciona.isResponsavelLeciona', Config::getAliasesField('isResponsavelLeciona'), 'Leciona.isResponsavelLeciona_ro', 'Aux.Boolean', $ngClick, $ngChange, 'Boolean'), 'col-sm-4'), 
Form::getModel(Html::inputSelectNgRepeat('Leciona.idUsuario', Config::getAliasesField('idUsuario'), 'Leciona.idUsuario_ro', 'Aux.Usuario', $ngClick, $ngChange), 'col-sm-4')
];

// Head de impressão dos filtros utilizados
$tableFiltros = new Table(['','',''], false, false, 'table-bordered', false);$tableFiltros->setExplode(false);$tableFiltros->addLinha([
                  '<p class="text-strong">Modulo</p>
                  <p ng-repeat="filter in Aux.Modulo | filter: {idModulo:Args.idModulo}:true">{{filter.nomeModulo}}</p>',
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