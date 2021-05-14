<?php
// Automação de Criação de Sistema - 13/01/2020 07:34:44
if (!defined("SISTEMA_LIBRARY")) {die("Acesso direto não permitido");}               
AppController::naoDisponivel();
include Config::getData('path') . '/view/template/template1.php';

// nome da entidade JS
$entidade = 'SharedUser';
$onclick = $entidade . 'OnEdit(' . $entidade . ')';

//$viewHTML = file_get_contents(Config::getData('url') . "/_build/_sourceView/$entidade/view.php");

// titulo apresentado ao usuário. Caso não tenha sido configurado aliase, será exibido o nome da entidade
$title = Config::getData('titlePagesAliases', $entidade); 

// filtros na exibição da lista
$filtros = [['grid' => 'col-6 col-sm-4', 'entidade' => 'Shared'],
['grid' => 'col-6 col-sm-4', 'entidade' => 'Usuario']];

// Campos apresentados na lista
$SharedUsers = [
            ['label' => Config::getAliasesField('idShared'), 'atributo' => 'Shared.nomeShared', 'class'=>'text-left'],

            ['label' => Config::getAliasesField('idUsuario'), 'atributo' => 'Usuario.nomeUsuario', 'class'=>'text-left'],

            ['label' => Config::getAliasesField('writeSharedUser'), 'atributo' => 'writeSharedUserF', 'class'=>'text-left']
    ];
$SharedUsers[0]['ngclick'] = $onclick;

// cards
$card = Card::basic($SharedUsers, $entidade, false, $entidade.'ContextItens');

// table
foreach ($SharedUsers as $campo) {
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
Form::getModel(Html::inputSelectNgRepeat('SharedUser.idShared', Config::getAliasesField('idShared'), 'SharedUser.idShared_ro', 'Aux.Shared', $ngClick, $ngChange), 'col-sm-4'), 
Form::getModel(Html::inputSelectNgRepeat('SharedUser.idUsuario', Config::getAliasesField('idUsuario'), 'SharedUser.idUsuario_ro', 'Aux.Usuario', $ngClick, $ngChange), 'col-sm-4'), 
Form::getModel(Html::inputSelectNgRepeat('SharedUser.writeSharedUser', Config::getAliasesField('writeSharedUser'), 'SharedUser.writeSharedUser_ro', 'Aux.Boolean', $ngClick, $ngChange, 'Boolean'), 'col-sm-4')
];

// Head de impressão dos filtros utilizados
$tableFiltros = new Table(['','',''], false, false, 'table-bordered', false);$tableFiltros->setExplode(false);$tableFiltros->addLinha([
                  '<p class="text-strong">Shared</p>
                  <p ng-repeat="filter in Aux.Shared | filter: {idShared:Args.idShared}:true">{{filter.nomeShared}}</p>',
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