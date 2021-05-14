<?php
// Automação de Criação de Sistema - 13/01/2020 07:34:44
if (!defined("SISTEMA_LIBRARY")) {die("Acesso direto não permitido");}               
AppController::naoDisponivel();
include Config::getData('path') . '/view/template/template1.php';

// nome da entidade JS
$entidade = 'SistemaLog';
$onclick = $entidade . 'OnEdit(' . $entidade . ')';

//$viewHTML = file_get_contents(Config::getData('url') . "/_build/_sourceView/$entidade/view.php");

// titulo apresentado ao usuário. Caso não tenha sido configurado aliase, será exibido o nome da entidade
$title = Config::getData('titlePagesAliases', $entidade); 

// filtros na exibição da lista
$filtros = [];

// Campos apresentados na lista
$SistemaLogs = [
            ['label' => Config::getAliasesField('ipLog'), 'atributo' => 'ipLog', 'class'=>'text-left'],

            ['label' => Config::getAliasesField('userLog'), 'atributo' => 'userLog', 'class'=>'text-left'],

            ['label' => Config::getAliasesField('tipoLog'), 'atributo' => 'tipoLog', 'class'=>'text-left'],

            ['label' => Config::getAliasesField('textoLog'), 'atributo' => 'textoLog', 'class'=>'text-left'],

            ['label' => Config::getAliasesField('entidadeLog'), 'atributo' => 'entidadeLog', 'class'=>'text-left'],

            ['label' => Config::getAliasesField('valoridLog'), 'atributo' => 'valoridLog', 'class'=>'text-left'],

            ['label' => Config::getAliasesField('datasendLog'), 'atributo' => 'datasendLog', 'class'=>'text-left'],

            ['label' => Config::getAliasesField('usuarioId'), 'atributo' => 'usuarioId', 'class'=>'text-left']
    ];
$SistemaLogs[0]['ngclick'] = $onclick;

// cards
$card = Card::basic($SistemaLogs, $entidade, false, $entidade.'ContextItens');

// table
foreach ($SistemaLogs as $campo) {
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
Form::getModel(Html::input(['ng-model' => 'SistemaLog.ipLog', 'type'=>'text', 'class'=>'', 'required'=>'required ', 'ng-change' => $ngChange], Config::getAliasesField('ipLog')), 'col-sm-4'), 
Form::getModel(Html::input(['ng-model' => 'SistemaLog.userLog', 'type'=>'text', 'class'=>'', 'required'=>'required ', 'ng-change' => $ngChange], Config::getAliasesField('userLog')), 'col-sm-4'), 
Form::getModel(Html::input(['ng-model' => 'SistemaLog.tipoLog', 'type'=>'text', 'class'=>'', 'required'=>'required ', 'ng-change' => $ngChange], Config::getAliasesField('tipoLog')), 'col-sm-4'), 
Form::getModel(Html::input(['ng-model' => 'SistemaLog.textoLog', 'type'=>'text', 'class'=>'', 'required'=>'not-required', 'ng-change' => $ngChange], Config::getAliasesField('textoLog')), 'col-sm-4'), 
Form::getModel(Html::input(['ng-model' => 'SistemaLog.entidadeLog', 'type'=>'text', 'class'=>'', 'required'=>'not-required', 'ng-change' => $ngChange], Config::getAliasesField('entidadeLog')), 'col-sm-4'), 
Form::getModel(Html::input(['ng-model' => 'SistemaLog.valoridLog', 'type'=>'number', 'class'=>'', 'required'=>'not-required', 'ng-change' => $ngChange], Config::getAliasesField('valoridLog')), 'col-sm-4'), 
Form::getModel('<config-json title="'.Config::getAliasesField('datasendLog').'" model="SistemaLog.datasendLog" grid="col-sm-6"></config-json>', 'col-sm-12'), 
Form::getModel(Html::input(['ng-model' => 'SistemaLog.usuarioId', 'type'=>'number', 'class'=>'', 'required'=>'not-required', 'ng-change' => $ngChange], Config::getAliasesField('usuarioId')), 'col-sm-4')
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