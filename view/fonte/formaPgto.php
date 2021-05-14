<?php
// Automação de Criação de Sistema - 06/01/2021 03:34:49
if (!defined("SISTEMA_LIBRARY")) {die("Acesso direto não permitido");}               

include Config::getData('path') . '/view/template/template1.php';

// nome da entidade JS
$entidade = 'FormaPgto';
$onclick = $entidade . 'OnEdit(' . $entidade . ')';

//$viewHTML = file_get_contents(Config::getData('url') . "/_build/_sourceView/$entidade/view.php");

// titulo apresentado ao usuário. Caso não tenha sido configurado aliase, será exibido o nome da entidade
$title = Config::getData('titlePagesAliases', $entidade); 

// filtros na exibição da lista
$filtros = [];

// Campos apresentados na lista
$FormaPgtos = [
            ['label' => Config::getAliasesField('nomeFormaPgto'), 'atributo' => 'nomeFormaPgto', 'class'=>'text-left'],

            ['label' => Config::getAliasesField('tpFormaPgto'), 'atributo' => 'tpFormaPgto', 'class'=>'text-left']
    ];
$FormaPgtos[0]['ngclick'] = $onclick;

// cards
$card = Card::basic($FormaPgtos, $entidade, false, $entidade.'ContextItens');

// table
foreach ($FormaPgtos as $campo) {
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
Form::getModel(Html::input(['ng-model' => 'FormaPgto.nomeFormaPgto', 'type'=>'text', 'class'=>'', 'required'=>'required ', 'ng-change' => $ngChange], Config::getAliasesField('nomeFormaPgto')), 'col-sm-4'), 
Form::getModel(Html::input(['ng-model' => 'FormaPgto.tpFormaPgto', 'type'=>'number', 'class'=>'', 'required'=>'required ', 'ng-change' => $ngChange], Config::getAliasesField('tpFormaPgto')), 'col-sm-4')
];

// Head de impressão dos filtros utilizados


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