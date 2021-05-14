<?php
// Automação de Criação de Sistema - 13/01/2020 07:34:44
if (!defined("SISTEMA_LIBRARY")) {die("Acesso direto não permitido");}               
AppController::naoDisponivel();
include Config::getData('path') . '/view/template/template1.php';

// nome da entidade JS
$entidade = 'LoginAttempts';
$onclick = $entidade . 'OnEdit(' . $entidade . ')';

//$viewHTML = file_get_contents(Config::getData('url') . "/_build/_sourceView/$entidade/view.php");

// titulo apresentado ao usuário. Caso não tenha sido configurado aliase, será exibido o nome da entidade
$title = Config::getData('titlePagesAliases', $entidade); 

// filtros na exibição da lista
$filtros = [];

// Campos apresentados na lista
$LoginAttemptss = [
            ['label' => Config::getAliasesField('ipLoginAttempts'), 'atributo' => 'ipLoginAttempts', 'class'=>'text-left'],

            ['label' => Config::getAliasesField('userLoginAttempts'), 'atributo' => 'userLoginAttempts', 'class'=>'text-left'],

            ['label' => Config::getAliasesField('passLoginAttempts'), 'atributo' => 'passLoginAttempts', 'class'=>'text-left'],

            ['label' => Config::getAliasesField('motivoLoginAttempts'), 'atributo' => 'motivoLoginAttempts', 'class'=>'text-left'],

            ['label' => Config::getAliasesField('geoLoginAttempts'), 'atributo' => 'geoLoginAttempts', 'class'=>'text-left']
    ];
$LoginAttemptss[0]['ngclick'] = $onclick;

// cards
$card = Card::basic($LoginAttemptss, $entidade, false, $entidade.'ContextItens');

// table
foreach ($LoginAttemptss as $campo) {
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
Form::getModel(Html::input(['ng-model' => 'LoginAttempts.ipLoginAttempts', 'type'=>'text', 'class'=>'', 'required'=>'required ', 'ng-change' => $ngChange], Config::getAliasesField('ipLoginAttempts')), 'col-sm-4'), 
Form::getModel(Html::input(['ng-model' => 'LoginAttempts.userLoginAttempts', 'type'=>'text', 'class'=>'', 'required'=>'required ', 'ng-change' => $ngChange], Config::getAliasesField('userLoginAttempts')), 'col-sm-4'), 
Form::getModel(Html::input(['ng-model' => 'LoginAttempts.passLoginAttempts', 'type'=>'text', 'class'=>'', 'required'=>'required ', 'ng-change' => $ngChange], Config::getAliasesField('passLoginAttempts')), 'col-sm-4'), 
Form::getModel(Html::input(['ng-model' => 'LoginAttempts.motivoLoginAttempts', 'type'=>'text', 'class'=>'', 'required'=>'not-required', 'ng-change' => $ngChange], Config::getAliasesField('motivoLoginAttempts')), 'col-sm-4'), 
Form::getModel('<config-json title="'.Config::getAliasesField('geoLoginAttempts').'" model="LoginAttempts.geoLoginAttempts" grid="col-sm-6"></config-json>', 'col-sm-12')
];

// Head de impressão dos filtros utilizados


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