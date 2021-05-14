<?php
// Automação de Criação de Sistema - 13/01/2020 07:34:44
if (!defined("SISTEMA_LIBRARY")) {die("Acesso direto não permitido");}               
AppController::naoDisponivel();
include Config::getData('path') . '/view/template/template1.php';

// nome da entidade JS
$entidade = 'Cep';
$onclick = $entidade . 'OnEdit(' . $entidade . ')';

//$viewHTML = file_get_contents(Config::getData('url') . "/_build/_sourceView/$entidade/view.php");

// titulo apresentado ao usuário. Caso não tenha sido configurado aliase, será exibido o nome da entidade
$title = Config::getData('titlePagesAliases', $entidade); 

// filtros na exibição da lista
$filtros = [];

// Campos apresentados na lista
$Ceps = [
            ['label' => Config::getAliasesField('logradouro'), 'atributo' => 'logradouro', 'class'=>'text-left'],

            ['label' => Config::getAliasesField('complemento'), 'atributo' => 'complemento', 'class'=>'text-left'],

            ['label' => Config::getAliasesField('bairro'), 'atributo' => 'bairro', 'class'=>'text-left'],

            ['label' => Config::getAliasesField('localidade'), 'atributo' => 'localidade', 'class'=>'text-left'],

            ['label' => Config::getAliasesField('uf'), 'atributo' => 'uf', 'class'=>'text-left'],

            ['label' => Config::getAliasesField('unidade'), 'atributo' => 'unidade', 'class'=>'text-left'],

            ['label' => Config::getAliasesField('ibge'), 'atributo' => 'ibge', 'class'=>'text-left'],

            ['label' => Config::getAliasesField('gia'), 'atributo' => 'gia', 'class'=>'text-left']
    ];
$Ceps[0]['ngclick'] = $onclick;

// cards
$card = Card::basic($Ceps, $entidade, false, $entidade.'ContextItens');

// table
foreach ($Ceps as $campo) {
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
Form::getModel(Html::input(['ng-model' => 'Cep.logradouro', 'type'=>'text', 'class'=>'', 'required'=>'not-required', 'ng-change' => $ngChange], Config::getAliasesField('logradouro')), 'col-sm-4'), 
Form::getModel(Html::input(['ng-model' => 'Cep.complemento', 'type'=>'text', 'class'=>'', 'required'=>'not-required', 'ng-change' => $ngChange], Config::getAliasesField('complemento')), 'col-sm-4'), 
Form::getModel(Html::input(['ng-model' => 'Cep.bairro', 'type'=>'text', 'class'=>'', 'required'=>'not-required', 'ng-change' => $ngChange], Config::getAliasesField('bairro')), 'col-sm-4'), 
Form::getModel(Html::input(['ng-model' => 'Cep.localidade', 'type'=>'text', 'class'=>'', 'required'=>'not-required', 'ng-change' => $ngChange], Config::getAliasesField('localidade')), 'col-sm-4'), 
Form::getModel(Html::input(['ng-model' => 'Cep.uf', 'type'=>'text', 'class'=>'', 'required'=>'not-required', 'ng-change' => $ngChange], Config::getAliasesField('uf')), 'col-sm-4'), 
Form::getModel(Html::input(['ng-model' => 'Cep.unidade', 'type'=>'text', 'class'=>'', 'required'=>'not-required', 'ng-change' => $ngChange], Config::getAliasesField('unidade')), 'col-sm-4'), 
Form::getModel(Html::input(['ng-model' => 'Cep.ibge', 'type'=>'text', 'class'=>'', 'required'=>'not-required', 'ng-change' => $ngChange], Config::getAliasesField('ibge')), 'col-sm-4'), 
Form::getModel(Html::input(['ng-model' => 'Cep.gia', 'type'=>'text', 'class'=>'', 'required'=>'not-required', 'ng-change' => $ngChange], Config::getAliasesField('gia')), 'col-sm-4')
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