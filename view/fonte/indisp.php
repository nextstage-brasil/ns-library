<?php

// Automação de Criação de Sistema - 18/05/2020 04:29:48
if (!defined("SISTEMA_LIBRARY")) {
    die("Acesso direto não permitido");
}

include Config::getData('path') . '/view/template/template1.php';

// nome da entidade JS
$entidade = 'Indisp';
$onclick = $entidade . 'OnEdit(' . $entidade . ')';

//$viewHTML = file_get_contents(Config::getData('url') . "/_build/_sourceView/$entidade/view.php");
// titulo apresentado ao usuário. Caso não tenha sido configurado aliase, será exibido o nome da entidade
$title = Config::getData('titlePagesAliases', $entidade);

// filtros na exibição da lista
$filtros = [['grid' => 'col-6 col-sm-4', 'entidade' => 'Usuario']];

// Campos apresentados na lista
$Indisps = [
    ['label' => Config::getAliasesField('idUsuario'), 'atributo' => 'Usuario.nomeUsuario', 'class' => 'text-left'],
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
    Form::getModel(Html::inputSelectNgRepeat('Indisp.idUsuario', Config::getAliasesField('idUsuario'), 'Indisp.idUsuario_ro', 'Aux.Usuario', $ngClick, $ngChange), 'col-sm-4'),
    Form::getModel(Html::inputDatePickerDependente(Config::getAliasesField('inicioIndisp'), 'Indisp.inicioIndisp', 'IndispMinDate', 'Indisp.fimIndisp', 'init()'), 'col-md-4'),
    Form::getModel(Html::inputDatePickerDependente(Config::getAliasesField('fimIndisp'), 'Indisp.fimIndisp', 'Indisp.inicioIndisp', 'Hoje', 'init()'), 'col-sm-2'),
    Form::getModel(Html::input(['ng-model' => 'Indisp.nomeIndisp', 'required' => 'not-required', 'type' => 'text', 'rows' => '5', 'ng-change' => $ngChange], Config::getAliasesField('nomeIndisp')), 'col-sm-12'),
//    Form::getModel(Html::inputDatePicker(Config::getAliasesField('inicioIndisp'), 'Indisp.inicioIndisp', $minDate, $maxDate, $ngChange), 'col-sm-4'),
        //Form::getModel(Html::inputDatePicker(Config::getAliasesField('fimIndisp'), 'Indisp.fimIndisp', $minDate, $maxDate, $ngChange), 'col-sm-4'),
        //Form::getModel('<config-json title="' . Config::getAliasesField('extrasIndisp') . '" model="Indisp.extrasIndisp" grid="col-sm-6"></config-json>', 'col-sm-12')
];

// Head de impressão dos filtros utilizados
$tableFiltros = new Table(['', ''], false, false, 'table-bordered', false);
$tableFiltros->setExplode(false);
$tableFiltros->addLinha([
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
