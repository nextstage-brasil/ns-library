<?php

// Automação de Criação de Sistema - 05/01/2021 06:51:42
if (!defined("SISTEMA_LIBRARY")) {
    die("Acesso direto não permitido");
}

include Config::getData('path') . '/view/template/template1.php';

// nome da entidade JS
$entidade = 'Usuario';
$onclick = $entidade . 'OnEdit(' . $entidade . ')';

//$viewHTML = file_get_contents(Config::getData('url') . "/_build/_sourceView/$entidade/view.php");
// titulo apresentado ao usuário. Caso não tenha sido configurado aliase, será exibido o nome da entidade
$title = Config::getData('titlePagesAliases', $entidade);

// filtros na exibição da lista
$filtros = [];

// Campos apresentados na lista
$Usuarios = [
    ['label' => Config::getAliasesField('nomeUsuario'), 'atributo' => 'nomeUsuario', 'class' => 'text-left'],
    ['label' => Config::getAliasesField('tipoUsuario'), 'atributo' => 'tipoUsuario', 'class' => 'text-left'],
    ['label' => Config::getAliasesField('perfilUsuario'), 'atributo' => 'perfilUsuario', 'class' => 'text-left'],
    ['label' => Config::getAliasesField('emailUsuario'), 'atributo' => 'emailUsuario', 'class' => 'text-left'],
    ['label' => Config::getAliasesField('senhaUsuario'), 'atributo' => 'senhaUsuario', 'class' => 'text-left'],
    ['label' => Config::getAliasesField('dataNascimentoUsuario'), 'atributo' => 'dataNascimentoUsuario', 'class' => 'text-left'],
    ['label' => Config::getAliasesField('sexoUsuario'), 'atributo' => 'sexoUsuario', 'class' => 'text-left'],
    ['label' => Config::getAliasesField('rgUsuario'), 'atributo' => 'rgUsuario', 'class' => 'text-left'],
    ['label' => Config::getAliasesField('cpfUsuario'), 'atributo' => 'cpfUsuario', 'class' => 'text-left'],
    ['label' => Config::getAliasesField('tokenAlteraSenhaUsuario'), 'atributo' => 'tokenAlteraSenhaUsuario', 'class' => 'text-left'],
    ['label' => Config::getAliasesField('cepUsuario'), 'atributo' => 'cepUsuario', 'class' => 'text-left'],
    ['label' => Config::getAliasesField('complementoCepUsuario'), 'atributo' => 'complementoCepUsuario', 'class' => 'text-left'],
    ['label' => Config::getAliasesField('tokenValidadeUsuario'), 'atributo' => 'tokenValidadeUsuario', 'class' => 'text-left'],
    ['label' => Config::getAliasesField('ultAcessoUsuario'), 'atributo' => 'ultAcessoUsuario', 'class' => 'text-left'],
    ['label' => Config::getAliasesField('dataSenhaUsuario'), 'atributo' => 'dataSenhaUsuario', 'class' => 'text-left'],
    ['label' => Config::getAliasesField('sessionTimeUsuario'), 'atributo' => 'sessionTimeUsuario', 'class' => 'text-left'],
    ['label' => Config::getAliasesField('avatarUsuario'), 'atributo' => 'avatarUsuario', 'class' => 'text-left'],
    ['label' => Config::getAliasesField('extrasUsuario'), 'atributo' => 'extrasUsuario', 'class' => 'text-left'],
    ['label' => Config::getAliasesField('linkPublicUsuario'), 'atributo' => 'linkPublicUsuario', 'class' => 'text-left'],
    ['label' => Config::getAliasesField('statusUsuario'), 'atributo' => 'statusUsuario', 'class' => 'text-left']
];
$Usuarios[0]['ngclick'] = $onclick;

// cards
$card = Card::basic($Usuarios, $entidade, false, $entidade . 'ContextItens');

// table
foreach ($Usuarios as $campo) {
    $th[$campo['atributo']] = (($campo['label']) ? str_replace(':', '', $campo['label']) : '') . '|' . $campo['class'];
    $td[] = '{{' . $entidade . '.' . $campo['atributo'] . '}}' . '|' . str_replace('text-strong', '', $campo['class']);
}
$table = new Table($th, false, true, '', true);
$table->setForeach($entidade . "Filtradas = (" . $entidade . "s| filter : filtro | orderBy: 'nome" . $entidade . "')", $entidade);
$table->setOnClick($entidade . 'OnEdit(' . $entidade . ')');
$table->setMenuContexto($entidade . 'ContextItens');
$table->addLinha($td);

// Form
$form = [
    Form::getModel(Html::input(['ng-model' => 'Usuario.nomeUsuario', 'type' => 'text', 'class' => '', 'required' => 'required ', 'ng-change' => $ngChange], Config::getAliasesField('nomeUsuario')), 'col-sm-4'),
    Form::getModel(Html::input(['ng-model' => 'Usuario.tipoUsuario', 'type' => 'number', 'class' => '', 'required' => 'required ', 'ng-change' => $ngChange], Config::getAliasesField('tipoUsuario')), 'col-sm-4'),
    Form::getModel(Html::input(['ng-model' => 'Usuario.perfilUsuario', 'type' => 'number', 'class' => '', 'required' => 'required ', 'ng-change' => $ngChange], Config::getAliasesField('perfilUsuario')), 'col-sm-4'),
    Form::getModel(Html::input(['ng-model' => 'Usuario.emailUsuario', 'type' => 'text', 'class' => '', 'required' => 'required ', 'ng-change' => $ngChange], Config::getAliasesField('emailUsuario')), 'col-sm-4'),
    Form::getModel(Html::input(['ng-model' => 'Usuario.senhaUsuario', 'type' => 'text', 'class' => '', 'required' => 'not-required', 'ng-change' => $ngChange], Config::getAliasesField('senhaUsuario')), 'col-sm-4'),
    Form::getModel(Html::inputDatePicker(Config::getAliasesField('dataNascimentoUsuario'), 'Usuario.dataNascimentoUsuario', $minDate, $maxDate, $ngChange), 'col-sm-4'),
    Form::getModel(Html::input(['ng-model' => 'Usuario.sexoUsuario', 'type' => 'text', 'class' => '', 'required' => 'not-required', 'ng-change' => $ngChange], Config::getAliasesField('sexoUsuario')), 'col-sm-4'),
    Form::getModel(Html::input(['ng-model' => 'Usuario.rgUsuario', 'type' => 'text', 'class' => '', 'required' => 'not-required', 'ng-change' => $ngChange], Config::getAliasesField('rgUsuario')), 'col-sm-4'),
    Form::getModel(Html::input(['ng-model' => 'Usuario.cpfUsuario', 'type' => 'text', 'class' => '', 'required' => 'not-required', 'ng-change' => $ngChange], Config::getAliasesField('cpfUsuario')), 'col-sm-4'),
    Form::getModel(Html::input(['ng-model' => 'Usuario.tokenAlteraSenhaUsuario', 'type' => 'text', 'class' => '', 'required' => 'not-required', 'ng-change' => $ngChange], Config::getAliasesField('tokenAlteraSenhaUsuario')), 'col-sm-4'),
    Form::getModel(Html::input(['ng-model' => 'Usuario.cepUsuario', 'type' => 'text', 'class' => '', 'required' => 'not-required', 'ng-change' => $ngChange], Config::getAliasesField('cepUsuario')), 'col-sm-4'),
    Form::getModel(Html::input(['ng-model' => 'Usuario.complementoCepUsuario', 'type' => 'text', 'class' => '', 'required' => 'not-required', 'ng-change' => $ngChange], Config::getAliasesField('complementoCepUsuario')), 'col-sm-4'),
    Form::getModel(Html::inputDatePicker(Config::getAliasesField('tokenValidadeUsuario'), 'Usuario.tokenValidadeUsuario', $minDate, $maxDate, $ngChange), 'col-sm-4'),
    Form::getModel(Html::inputDatePicker(Config::getAliasesField('ultAcessoUsuario'), 'Usuario.ultAcessoUsuario', $minDate, $maxDate, $ngChange), 'col-sm-4'),
    Form::getModel(Html::inputDatePicker(Config::getAliasesField('dataSenhaUsuario'), 'Usuario.dataSenhaUsuario', $minDate, $maxDate, $ngChange), 'col-sm-4'),
    Form::getModel(Html::input(['ng-model' => 'Usuario.sessionTimeUsuario', 'type' => 'number', 'class' => '', 'required' => 'required ', 'ng-change' => $ngChange], Config::getAliasesField('sessionTimeUsuario')), 'col-sm-4'),
    Form::getModel(Html::input(['ng-model' => 'Usuario.avatarUsuario', 'type' => 'number', 'class' => '', 'required' => 'not-required', 'ng-change' => $ngChange], Config::getAliasesField('avatarUsuario')), 'col-sm-4'),
    Form::getModel('<config-json title="' . Config::getAliasesField('extrasUsuario') . '" model="Usuario.extrasUsuario" grid="col-sm-6"></config-json>', 'col-sm-12'),
    Form::getModel(Html::input(['ng-model' => 'Usuario.linkPublicUsuario', 'type' => 'text', 'class' => '', 'required' => 'not-required', 'ng-change' => $ngChange], Config::getAliasesField('linkPublicUsuario')), 'col-sm-4'),
    Form::getModel(Html::input(['ng-model' => 'Usuario.statusUsuario', 'type' => 'number', 'class' => '', 'required' => 'required ', 'ng-change' => $ngChange], Config::getAliasesField('statusUsuario')), 'col-sm-4')
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
        . '<div id="formEdit' . $entidade . '" class="controleShow' . $entidade . ' d-print-none">'
        . (($tabs) ? Tab::printTab($tabs) : $template->printForm())
        . AdminTemplate::getButtonsStatic($entidade)
        . '</div>';


echo '<div ng-controller="' . $entidade . 'Controller" id="controllerContent" class="d-none">'
 . $html
 . '</div>';


/* caso necessario injetar JS antes do controller
  $packer = new Packer($js, 'Normal', true, false, true);
  $packed_js = $packer->pack();
  echo "<script>$packed_js</script>";
 */

Component::init($entidade . '-script.js');

include Config::getData('path') . '/view/template/template2.php';
