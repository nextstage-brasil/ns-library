<?php
$file = '../../../library/SistemaLibrary.php';
require_once $file;

//echo "Indisponibilidade de usuários em desenvolvimento";
// nome da entidade JS
$entidade = 'Indisp';
$onclick = $entidade . 'OnEdit(' . $entidade . ')';

//$viewHTML = file_get_contents(Config::getData('url') . "/_build/_sourceView/$entidade/view.php");
// titulo apresentado ao usuário. Caso não tenha sido configurado aliase, será exibido o nome da entidade
$title = Config::getData('titlePagesAliases', $entidade);

// filtros na exibição da lista
//$filtros = [['grid' => 'col-6 col-sm-4', 'entidade' => 'Usuario']];
// Campos apresentados na lista
$Indisps = [
//    ['label' => Config::getAliasesField('idUsuario'), 'atributo' => 'Usuario.nomeUsuario', 'class' => 'text-left'],
    ['label' => Config::getAliasesField('inicioIndisp'), 'atributo' => 'inicioIndisp', 'class' => 'text-left'],
    ['label' => Config::getAliasesField('fimIndisp'), 'atributo' => 'fimIndisp', 'class' => 'text-left'],
    ['label' => Config::getAliasesField('nomeIndisp'), 'atributo' => 'nomeIndisp', 'class' => 'text-left'],
        //['label' => Config::getAliasesField('extrasIndisp'), 'atributo' => 'extrasIndisp', 'class' => 'text-left']
];
$Indisps[0]['ngclick'] = $onclick;

// cards
$card = Card::basic($Indisps, $entidade, false, $entidade . 'ContextItens');

// table
foreach ($Indisps as $campo) {
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
    //Form::getModel(Html::inputSelectNgRepeat('Indisp.idUsuario', Config::getAliasesField('idUsuario'), 'Indisp.idUsuario_ro', 'Aux.Usuario', $ngClick, $ngChange), 'col-sm-4'),
//    Form::getModel(Html::inputDatePicker(Config::getAliasesField('inicioIndisp'), 'Indisp.inicioIndisp', $minDate, $maxDate, $ngChange), 'col-sm-4'),
    //Form::getModel(Html::inputDatePicker(Config::getAliasesField('fimIndisp'), 'Indisp.fimIndisp', $minDate, $maxDate, $ngChange), 'col-sm-4'),
    Form::getModel(Html::inputDatePickerDependente(Config::getAliasesField('inicioIndisp'), 'Indisp.inicioIndisp', 'IndispMinDate', 'Indisp.fimIndisp', 'init()'), 'col-sm-6'),
    Form::getModel(Html::inputDatePickerDependente(Config::getAliasesField('fimIndisp'), 'Indisp.fimIndisp', 'Indisp.inicioIndisp', 'Hoje', 'init()'), 'col-sm-6'),
    //Form::getModel('<config-json title="' . Config::getAliasesField('extrasIndisp') . '" model="Indisp.extrasIndisp" grid="col-sm-6"></config-json>', 'col-sm-12')
    Form::getModel(Html::input(['ng-model' => 'Indisp.nomeIndisp', 'required' => 'not-required', 'type' => 'text', 'rows' => '5', 'ng-change' => $ngChange], Config::getAliasesField('nomeIndisp')), 'col-sm-12'),
];
// Criação do objeto Template. Retorna Head, List e Print. 
$template = new AdminTemplate($entidade, $title, $tableFiltros, $filtros, $card, $table);
$template->setForm($form);
?>
<h3>Indisponibilidade de usuário</h3>
<div ng-show="!Indisp">
    <div class="row mb-4">
        <div class="col-md-4">
            <button class="btn btn-primary" ng-click="IndispNew()"><?= Html::iconFafa('plus') ?> Adicionar indisponibilidade</button>
        </div>
        <div class="col-md-4"></div>
        <div class="col-md-4"></div>
    </div>
    <?= $table->printTable() ?>
</div>
<div ng-show="Indisp" id="IndiponibilidadeUsuarioVisible">
    <?php
    echo (($tabs) ? Tab::printTab($tabs) : $template->printForm());
    echo str_replace('btnsAdmin', '', AdminTemplate::getButtonsStatic($entidade, '', true, ['save' => 'Salvar indiponibilidade']));
    ?>
</div>