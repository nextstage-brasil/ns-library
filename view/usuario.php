<?php
// Automação de Criação de Sistema - 04/05/2018 12:04:55
if (!defined("SISTEMA_LIBRARY")) {
    die("Acesso direto não permitido");
}
include Config::getData('pathView') . '/template/template1.php';

// nome da entidade JS
$prefixButton = $entidade = 'Usuario';

// titulo apresentado ao usuário. Caso não tenha sido configurado aliase, será exibido o nome da entidade
$title = '';

// filtros na exibição da lista
$filtros = [
    ['grid' => 'col-6 col-sm-4', 'entidade' => 'Status'],
    ['grid' => 'col-6 col-sm-4', 'entidade' => 'UsuarioTipo'],
    //sprintf(AdminTemplate::$filterTemplate, 'col-sm-4', $item['entidade'], Config::getAliasesTable($item['entidade']), $this->entidade);
    '<div class="custom-control custom-checkbox">
            <input type="checkbox" ng-click="filterOnlyProfessores()" class="custom-control-input" ng-checked="false" id="onlyProfessores">
            <label class="custom-control-label" for="onlyProfessores" ng-bind-html="\'Somente professores\'"></label>                        
     </div>'
];



// Campos apresentados na lista
$Usuarios = array(
    //array('label' => 'img', 'atributo' => 'avatar.thumbs', 'class' => 'text-left', 'linha' => ''),
    array('label' => 'Nome', 'atributo' => 'nomeUsuario', 'class' => 'text-left', 'linha' => ''),
    array('label' => 'Email', 'atributo' => 'emailUsuario', 'class' => 'text-left', 'linha' => '<br/>'),
    ['label' => Config::getAliasesField('dataNascimentoUsuario'), 'atributo' => 'dataNascimentoUsuario', 'class' => 'text-left'],
    ['label' => Config::getAliasesField('sexoUsuario'), 'atributo' => 'sexoUsuario', 'class' => 'text-left'],
    ['label' => Config::getAliasesField('rgUsuario'), 'atributo' => 'rgUsuario', 'class' => 'text-left'],
    ['label' => Config::getAliasesField('cpfUsuario'), 'atributo' => 'cpfUsuario', 'class' => 'text-left'],
    //array('label' => 'Perfil', 'atributo' => 'perfilLabel', 'class' => 'text-left', 'linha' => '<br/>'),/
    //array('label' => 'Unidade', 'atributo' => 'Unidade.nomeUnidade', 'class' => 'text-left', 'linha' => '<br/>'),
    array('label' => 'Último Acesso', 'atributo' => 'ultAcessoUsuario', 'class' => 'text-left', 'linha' => '<br/>'),
        //array('label' => 'Status', 'atributo' => 'statusUsuario', 'class' => 'text-left', 'linha' => '<br/>'),
        //array('label' => 'ApiKey', 'atributo' => 'apiKey', 'class' => 'text-left', 'linha' => '<br/>'),
);


//var_export(Config::getData('params'));
$tabsExtras = [];
switch (Config::getData('params')[1]) {
    case '1': // tela de perfil
        $title = 'Perfil de usuário';
        $form = [
            Form::getModel(Html::input(['ng-model' => 'Usuario.nomeUsuario', 'ng-change' => $ngChange, 'required' => 'required'], 'Nome do perfil'), 'col-sm-12'),
            Form::getModel('<config-json model="Usuario.extrasUsuario" grid="col-12"></config-json>', 'col-sm-12')
        ];
        $Usuarios = array(
            array('label' => 'NOME', 'atributo' => 'nomeUsuario', 'class' => 'text-left', 'linha' => ''),
        );
        $filtros = false;

        $tabsExtras = [
            array_merge(['ng-if' => 'Usuario.idUsuario'], Tab::getModel('permissoes', 'Permissões do sistema', \NsUtil\Helper::myFileGetContents(Config::getData('url') . '/view/template/modelos/usuario_permissoes.php'))),
        ];


        break;
    case '3': //  APIs  - Permissão de entradas
        $form = [
            Form::getModel(Html::input(['ng-model' => 'Usuario.nomeUsuario', 'ng-change' => $ngChange, 'required' => 'required'], 'Nome'), 'col-sm-12'),
            Form::getModel(Html::inputSelectNgRepeat('Usuario.statusUsuario', 'Status', 'Usuario.statusUsuario_ro', 'Aux.Status', $ngClick, $ngChange, 'Status'), 'col-sm-4'),
        ];
        $Usuarios = array(
            array('label' => 'NOME', 'atributo' => 'nomeUsuario', 'class' => 'text-left', 'linha' => ''),
            array('label' => 'Status', 'atributo' => 'statusUsuarioF', 'class' => 'text-left', 'linha' => ''),
            array('label' => 'Itens acessíveis', 'atributo' => 'itensAcessiveis', 'class' => 'text-left', 'linha' => ''),
        );
        $filtros = false;
        break;
    case '5': // Usuários solicitantes
        $form = [
            Form::getModel(Html::input(['ng-model' => 'Usuario.nomeUsuario', 'ng-change' => $ngChange, 'required' => 'required'], 'Nome'), 'col-sm-6'),
            Form::getModel(Html::input(['ng-model' => 'Usuario.emailUsuario', 'ng-change' => $ngChange, 'required' => 'required'], 'E-mail'), 'col-sm-6'),
            Form::getModel(Html::inputSelectNgRepeat('Usuario.statusUsuario', 'Status', 'Usuario.statusUsuario_ro', 'Aux.Status', $ngClick, $ngChange, 'Status'), 'col-sm-4'),
            Form::getModel(Html::input(['ng-model' => 'Usuario.loginUsuario', 'ng-change' => $ngChange, 'required' => 'required'], 'LOGIN <tip>Será utilizado para acessar o sistema</tip>'), 'col-sm-6'),
            Form::getModel(Html::input(['type' => 'number', 'min' => '5', 'max' => '120', 'ng-model' => 'Usuario.sessionTimeUsuario', 'required' => 'required', 'ng-change' => $ngChange], 'Tempo de sessão (minutos sem interação)'), 'col-sm-4'),
            Form::getModel('<config-json model="Usuario.extrasUsuario" grid="col-12"></config-json>', 'col-sm-12')
        ];
        $Usuarios = array(
            array('label' => 'NOME', 'atributo' => 'nomeUsuario', 'class' => 'text-left', 'linha' => ''),
            array('label' => 'Status', 'atributo' => 'statusUsuarioF', 'class' => 'text-left', 'linha' => ''),
                //array('label' => 'Itens acessíveis', 'atributo' => 'itensAcessiveis', 'class' => 'text-left', 'linha' => ''),
        );
        $filtros = false;
        break;
    default: // tela de usuario
        $title = 'Pessoas';
        // Form
        $form = [
            Form::getModel(Html::input(['ng-model' => 'Usuario.nomeUsuario', 'ng-change' => $ngChange, 'required' => 'required', 'hint' => 'Nome completo do usuário'], 'Nome do usuário'), 'col-sm-8'),
            Form::getModel(Html::inputSelectNgRepeat('Usuario.statusUsuario', 'Status', 'Usuario.statusUsuario_ro', 'Aux.Status', $ngClick, $ngChange, 'Status'), 'col-sm-4'),
            Form::getModel(Html::input(['ng-model' => 'Usuario.emailUsuario', 'ng-change' => $ngChange, 'required' => 'required'], 'E-mail'), 'col-sm-8'),
            Form::getModel(Html::inputSelectNgRepeat('Usuario.perfilUsuario', 'Perfil de permissões', 'Usuario.perfilUsuario_ro', 'Aux.UsuarioTipo', $ngClick, $ngChange, 'UsuarioTipo'), 'col-sm-4'),
            Form::getModel(Html::inputDatePicker(Config::getAliasesField('dataNascimentoUsuario'), 'Usuario.dataNascimentoUsuario', $minDate, $maxDate, $ngChange), 'col-sm-4'),
            Form::getModel(Html::input(['ng-model' => 'Usuario.rgUsuario', 'type' => 'text', 'class' => '', 'required' => 'not-required', 'ng-change' => $ngChange], Config::getAliasesField('rgUsuario')), 'col-sm-4'),
            Form::getModel(Html::input(['ng-model' => 'Usuario.cpfUsuario', 'type' => 'text', 'class' => 'cpf', 'required' => 'not-required', 'ng-change' => $ngChange], Config::getAliasesField('cpfUsuario')), 'col-sm-4'),
            Form::getModel(Html::input(['ng-model' => 'Usuario.cepUsuario', 'type' => 'text', 'class' => 'cep', 'required' => 'not-required', 'ng-change' => $ngChange], Config::getAliasesField('cepUsuario')), 'col-sm-3'),
            Form::getModel(Html::input(['ng-model' => 'Usuario.numeroCepUsuario', 'type' => 'text', 'class' => '', 'required' => 'not-required', 'ng-change' => $ngChange], Config::getAliasesField('numeroCepUsuario')), 'col-sm-4'),
            Form::getModel(Html::input(['ng-model' => 'Usuario.complementoCepUsuario', 'type' => 'text', 'class' => '', 'required' => 'not-required', 'ng-change' => $ngChange], Config::getAliasesField('complementoCepUsuario')), 'col-sm-5'),
            //Form::getModel(Html::inputSelectNgRepeat('Usuario.idUnidade', 'UNIDADE', 'Usuario.idUnidade_ro', 'Aux.Unidade', $ngClick, $ngChange), 'col-sm-4'),
            //Form::getModel(Html::input(['ng-model' => 'Usuario.loginUsuario', 'ng-change' => $ngChange, 'required' => 'required'], 'LOGIN'), 'col-sm-4'),
            //Form::getModel(Html::input(['type' => 'number', 'min' => '5', 'max' => '120', 'ng-model' => 'Usuario.sessionTimeUsuario', 'required' => 'required', 'ng-change' => $ngChange], 'Tempo de sessão <tip>minutos sem interação</tip>'), 'col-sm-4'),
            Form::getModel('<config-json model="Usuario.extrasUsuario" grid="col-6"></config-json>', 'col-sm-12')
        ];

        $tabsExtras = [
            Tab::getModel('modulo-tag', 'Modulos associados', '<ns-tag relacao="modulo|usuario" id-right="{{Usuario.idUsuario}}"></ns-tag>', 'Usuario.extrasUsuario.isProfessor===\'Sim\''),
            Tab::getModel('polo-tag', 'Polos associados', '<ns-tag relacao="polo|usuario" id-right="{{Usuario.idUsuario}}"></ns-tag>', 'Usuario.extrasUsuario.isProfessor===\'Sim\''),
            Tab::getModel('indisp', 'Indisponibilidades <span class="badge badge-info">{{Indisps.length}}</span>', \NsUtil\Helper::myFileGetContents(Config::getData('url') . '/view/template/modelos/usuario_indisponibilidade.php'), 'Usuario.extrasUsuario.isProfessor===\'Sim\''),
        ];
        
        

        break;
}


// table
foreach ($Usuarios as $campo) {
    $th[] = (($campo['label']) ? str_replace(':', '', $campo['label']) : '') . '|' . $campo['class'];
    $td[] = '{{Usuario.' . $campo['atributo'] . '}}' . '|' . str_replace('text-strong', '', $campo['class']);
}
$table = new Table($th, false, true, '', true);
$table->setForeach("UsuarioFiltradas | filter: myfilter", "Usuario");
$table->setOnClick('UsuarioOnEdit(Usuario)');
$table->setMenuContexto('UsuarioContextItens');
$table->addLinha($td);



// Head de impressão dos filtros utilizados
$tableFiltros = new Table(['', '', '', ''], false, false, 'table-bordered', false);
$tableFiltros->setExplode(false);
$tableFiltros->addLinha([
    '<p class="text-strong">UsuarioTipo</p>
                          <p ng-repeat="filter in Aux.UsuarioTipo | filter: {idUsuarioTipo:Args.idUsuarioTipo}:true">{{filter.nomeUsuarioTipo}}</p>',
    '<p class="text-strong">Unidade</p>
                          <p ng-repeat="filter in Aux.Unidade | filter: {idUnidade:Args.idUnidade}:true">{{filter.nomeUnidade}}</p>',
    '<p class="text-strong">MatriculaPromotor</p>
                          <p ng-repeat="filter in Aux.MatriculaPromotor | filter: {idMatriculaPromotor:Args.idMatriculaPromotor}:true">{{filter.nomeMatriculaPromotor}}</p>',
    '<p class="text-strong">Texto Pesquisa</p>
                          <p class="text-upper">{{Args.Search}}</p>']);

// Se houver necessidade, Tabs
// $tabs = array(array('id' => 'identificacao', 'label' => 'Identificação', 'conteudo' => file_get_contents(Config::getData('path') . '/sistema/view/Pessoa/form/identificacao.html')));
//$tabPrint = Html::geraTabs($tabs);
// Criação do objeto Template. Retorna Head, List e Print. 
// cards
// filtros na exibição da lista

$card = Card::basic($Usuarios, 'Usuario', false, 'UsuarioContextItens');
$template = new AdminTemplate($entidade, $title, $tableFiltros, $filtros, $card, $table, 'col-sm-4');
$template->setForm($form);
$tabs = array_merge([
    Tab::getModel('identificacao', 'Cadastro', $template->printForm()),
        ], $tabsExtras);
?>
<div ng-controller="UsuarioController">
    <?= $template->printTemplate() ?>

    <div id="formEditUsuario" class="controleShowUsuario d-print-none">
        <?= Tab::printTab($tabs) ?>
        <p class="text-center"><?= AdminTemplate::getButtonsStatic($prefixButton) ?></p>
    </div>
</div>

<?php
Component::init('UsuarioFramework-script.js');

include Config::getData('pathView') . '/template/template2.php';
