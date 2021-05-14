<?php
// Automação de Criação de Sistema - 13/01/2020 07:34:44
if (!defined("SISTEMA_LIBRARY")) {die("Acesso direto não permitido");}               
AppController::naoDisponivel();
include Config::getData('path') . '/view/template/template1.php';

// nome da entidade JS
$entidade = 'Post';
$onclick = $entidade . 'OnEdit(' . $entidade . ')';

//$viewHTML = file_get_contents(Config::getData('url') . "/_build/_sourceView/$entidade/view.php");

// titulo apresentado ao usuário. Caso não tenha sido configurado aliase, será exibido o nome da entidade
$title = Config::getData('titlePagesAliases', $entidade); 

// filtros na exibição da lista
$filtros = [['grid' => 'col-6 col-sm-4', 'entidade' => 'Usuario']];

// Campos apresentados na lista
$Posts = [
            ['label' => Config::getAliasesField('isPublic'), 'atributo' => 'isPublicF', 'class'=>'text-left'],

            ['label' => Config::getAliasesField('entidadePost'), 'atributo' => 'entidadePost', 'class'=>'text-left'],

            ['label' => Config::getAliasesField('valoridPost'), 'atributo' => 'valoridPost', 'class'=>'text-left'],

            ['label' => Config::getAliasesField('idUsuario'), 'atributo' => 'Usuario.nomeUsuario', 'class'=>'text-left'],

            ['label' => Config::getAliasesField('tituloPost'), 'atributo' => 'tituloPost', 'class'=>'text-left'],

            ['label' => Config::getAliasesField('tipoPost'), 'atributo' => 'tipoPost', 'class'=>'text-left'],

            ['label' => Config::getAliasesField('conteudoPost'), 'atributo' => 'conteudoPost', 'class'=>'text-left'],

            ['label' => Config::getAliasesField('extrasTexto'), 'atributo' => 'extrasTexto', 'class'=>'text-left'],

            ['label' => Config::getAliasesField('postIdPost'), 'atributo' => 'postIdPost', 'class'=>'text-left']
    ];
$Posts[0]['ngclick'] = $onclick;

// cards
$card = Card::basic($Posts, $entidade, false, $entidade.'ContextItens');

// table
foreach ($Posts as $campo) {
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
Form::getModel(Html::inputSelectNgRepeat('Post.isPublic', Config::getAliasesField('isPublic'), 'Post.isPublic_ro', 'Aux.Boolean', $ngClick, $ngChange, 'Boolean'), 'col-sm-4'), 
Form::getModel(Html::input(['ng-model' => 'Post.entidadePost', 'type'=>'text', 'class'=>'', 'required'=>'required ', 'ng-change' => $ngChange], Config::getAliasesField('entidadePost')), 'col-sm-4'), 
Form::getModel(Html::input(['ng-model' => 'Post.valoridPost', 'type'=>'number', 'class'=>'', 'required'=>'required ', 'ng-change' => $ngChange], Config::getAliasesField('valoridPost')), 'col-sm-4'), 
Form::getModel(Html::inputSelectNgRepeat('Post.idUsuario', Config::getAliasesField('idUsuario'), 'Post.idUsuario_ro', 'Aux.Usuario', $ngClick, $ngChange), 'col-sm-4'), 
Form::getModel(Html::input(['ng-model' => 'Post.tituloPost', 'type'=>'text', 'class'=>'', 'required'=>'required ', 'ng-change' => $ngChange], Config::getAliasesField('tituloPost')), 'col-sm-4'), 
Form::getModel(Html::input(['ng-model' => 'Post.tipoPost', 'type'=>'number', 'class'=>'', 'required'=>'required ', 'ng-change' => $ngChange], Config::getAliasesField('tipoPost')), 'col-sm-4'), 
Form::getModel(Html::input(['ng-model' => 'Post.conteudoPost', 'required'=>'required ', 'type'=>'textarea', 'rows'=>'5', 'ng-change' => $ngChange], Config::getAliasesField('conteudoPost')), 'col-sm-12'), 
Form::getModel('<config-json title="'.Config::getAliasesField('extrasTexto').'" model="Post.extrasTexto" grid="col-sm-6"></config-json>', 'col-sm-12'), 
Form::getModel(Html::input(['ng-model' => 'Post.postIdPost', 'type'=>'number', 'class'=>'', 'required'=>'not-required', 'ng-change' => $ngChange], Config::getAliasesField('postIdPost')), 'col-sm-4')
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