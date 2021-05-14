<?php
// Automação de Criação de Sistema - 13/01/2020 07:34:45
if (!defined("SISTEMA_LIBRARY")) {die("Acesso direto não permitido");}               
AppController::naoDisponivel();
include Config::getData('path') . '/view/template/template1.php';

// nome da entidade JS
$entidade = 'Uploadfile';
$onclick = $entidade . 'OnEdit(' . $entidade . ')';

//$viewHTML = file_get_contents(Config::getData('url') . "/_build/_sourceView/$entidade/view.php");

// titulo apresentado ao usuário. Caso não tenha sido configurado aliase, será exibido o nome da entidade
$title = Config::getData('titlePagesAliases', $entidade); 

// filtros na exibição da lista
$filtros = [['grid' => 'col-6 col-sm-4', 'entidade' => 'Usuario']];

// Campos apresentados na lista
$Uploadfiles = [
            ['label' => Config::getAliasesField('filenameUploadfile'), 'atributo' => 'filenameUploadfile', 'class'=>'text-left'],

            ['label' => Config::getAliasesField('entidadeUploadfile'), 'atributo' => 'entidadeUploadfile', 'class'=>'text-left'],

            ['label' => Config::getAliasesField('valoridUploadfile'), 'atributo' => 'valoridUploadfile', 'class'=>'text-left'],

            ['label' => Config::getAliasesField('nomeUploadfile'), 'atributo' => 'nomeUploadfile', 'class'=>'text-left'],

            ['label' => Config::getAliasesField('descricaoUploadfile'), 'atributo' => 'descricaoUploadfile', 'class'=>'text-left'],

            ['label' => Config::getAliasesField('fonteUploadfile'), 'atributo' => 'fonteUploadfile', 'class'=>'text-left'],

            ['label' => Config::getAliasesField('extensaoUploadfile'), 'atributo' => 'extensaoUploadfile', 'class'=>'text-left'],

            ['label' => Config::getAliasesField('mimeUploadfile'), 'atributo' => 'mimeUploadfile', 'class'=>'text-left'],

            ['label' => Config::getAliasesField('classificacaoUploadfile'), 'atributo' => 'classificacaoUploadfile', 'class'=>'text-left'],

            ['label' => Config::getAliasesField('recognitionId'), 'atributo' => 'recognitionId', 'class'=>'text-left'],

            ['label' => Config::getAliasesField('idUsuario'), 'atributo' => 'Usuario.nomeUsuario', 'class'=>'text-left'],

            ['label' => Config::getAliasesField('conteudoUplodfile'), 'atributo' => 'conteudoUplodfile', 'class'=>'text-left'],

            ['label' => Config::getAliasesField('stFsUploadfile'), 'atributo' => 'stFsUploadfile', 'class'=>'text-left']
    ];
$Uploadfiles[0]['ngclick'] = $onclick;

// cards
$card = Card::basic($Uploadfiles, $entidade, false, $entidade.'ContextItens');

// table
foreach ($Uploadfiles as $campo) {
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
Form::getModel(Html::input(['ng-model' => 'Uploadfile.filenameUploadfile', 'type'=>'text', 'class'=>'', 'required'=>'required ', 'ng-change' => $ngChange], Config::getAliasesField('filenameUploadfile')), 'col-sm-4'), 
Form::getModel(Html::input(['ng-model' => 'Uploadfile.entidadeUploadfile', 'type'=>'text', 'class'=>'', 'required'=>'required ', 'ng-change' => $ngChange], Config::getAliasesField('entidadeUploadfile')), 'col-sm-4'), 
Form::getModel(Html::input(['ng-model' => 'Uploadfile.valoridUploadfile', 'type'=>'number', 'class'=>'', 'required'=>'required ', 'ng-change' => $ngChange], Config::getAliasesField('valoridUploadfile')), 'col-sm-4'), 
Form::getModel(Html::input(['ng-model' => 'Uploadfile.nomeUploadfile', 'type'=>'text', 'class'=>'', 'required'=>'not-required', 'ng-change' => $ngChange], Config::getAliasesField('nomeUploadfile')), 'col-sm-4'), 
Form::getModel(Html::input(['ng-model' => 'Uploadfile.descricaoUploadfile', 'type'=>'text', 'class'=>'', 'required'=>'not-required', 'ng-change' => $ngChange], Config::getAliasesField('descricaoUploadfile')), 'col-sm-4'), 
Form::getModel(Html::input(['ng-model' => 'Uploadfile.fonteUploadfile', 'type'=>'text', 'class'=>'', 'required'=>'not-required', 'ng-change' => $ngChange], Config::getAliasesField('fonteUploadfile')), 'col-sm-4'), 
Form::getModel(Html::input(['ng-model' => 'Uploadfile.extensaoUploadfile', 'type'=>'text', 'class'=>'', 'required'=>'not-required', 'ng-change' => $ngChange], Config::getAliasesField('extensaoUploadfile')), 'col-sm-4'), 
Form::getModel(Html::input(['ng-model' => 'Uploadfile.mimeUploadfile', 'type'=>'text', 'class'=>'', 'required'=>'not-required', 'ng-change' => $ngChange], Config::getAliasesField('mimeUploadfile')), 'col-sm-4'), 
Form::getModel(Html::input(['ng-model' => 'Uploadfile.classificacaoUploadfile', 'type'=>'number', 'class'=>'', 'required'=>'required ', 'ng-change' => $ngChange], Config::getAliasesField('classificacaoUploadfile')), 'col-sm-4'), 
Form::getModel(Html::input(['ng-model' => 'Uploadfile.recognitionId', 'type'=>'text', 'class'=>'', 'required'=>'not-required', 'ng-change' => $ngChange], Config::getAliasesField('recognitionId')), 'col-sm-4'), 
Form::getModel(Html::inputSelectNgRepeat('Uploadfile.idUsuario', Config::getAliasesField('idUsuario'), 'Uploadfile.idUsuario_ro', 'Aux.Usuario', $ngClick, $ngChange), 'col-sm-4'), 
Form::getModel(Html::input(['ng-model' => 'Uploadfile.conteudoUplodfile', 'required'=>'not-required', 'type'=>'textarea', 'rows'=>'5', 'ng-change' => $ngChange], Config::getAliasesField('conteudoUplodfile')), 'col-sm-12'), 
Form::getModel(Html::input(['ng-model' => 'Uploadfile.stFsUploadfile', 'type'=>'number', 'class'=>'', 'required'=>'not-required', 'ng-change' => $ngChange], Config::getAliasesField('stFsUploadfile')), 'col-sm-4')
];

// Head de impressão dos filtros utilizados
$tableFiltros = new Table(['',''], false, false, 'table-bordered', false);$tableFiltros->setExplode(false);$tableFiltros->addLinha([
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