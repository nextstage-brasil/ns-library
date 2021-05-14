<?php

// Automação de Criação de Sistema - 13/01/2020 07:34:44
if (!defined("SISTEMA_LIBRARY")) {
    die("Acesso direto não permitido");
}
AppController::naoDisponivel();
include Config::getData('path') . '/view/template/template1.php';

// nome da entidade JS
$entidade = 'Endereco';
$onclick = $entidade . 'OnEdit(' . $entidade . ')';

//$viewHTML = file_get_contents(Config::getData('url') . "/_build/_sourceView/$entidade/view.php");
// titulo apresentado ao usuário. Caso não tenha sido configurado aliase, será exibido o nome da entidade
$title = Config::getData('titlePagesAliases', $entidade);

// filtros na exibição da lista
$filtros = [];

// Campos apresentados na lista
$Enderecos = [
    ['label' => Config::getAliasesField('nomeEndereco'), 'atributo' => 'nomeEndereco', 'class' => 'text-left'],
    ['label' => Config::getAliasesField('cepEndereco'), 'atributo' => 'cepEndereco', 'class' => 'text-left'],
    ['label' => Config::getAliasesField('idMunicipio'), 'atributo' => 'Municipio.nomeMunicipio', 'class' => 'text-left'],
    ['label' => Config::getAliasesField('ruaEndereco'), 'atributo' => 'ruaEndereco', 'class' => 'text-left'],
    ['label' => Config::getAliasesField('numeroEndereco'), 'atributo' => 'numeroEndereco', 'class' => 'text-left'],
    ['label' => Config::getAliasesField('complementoEndereco'), 'atributo' => 'complementoEndereco', 'class' => 'text-left'],
    ['label' => Config::getAliasesField('bairroEndereco'), 'atributo' => 'bairroEndereco', 'class' => 'text-left'],
    ['label' => Config::getAliasesField('latitudeEndereco'), 'atributo' => 'latitudeEndereco', 'class' => 'text-left'],
    ['label' => Config::getAliasesField('longitudeEndereco'), 'atributo' => 'longitudeEndereco', 'class' => 'text-left'],
    ['label' => Config::getAliasesField('pontoReferenciaEndereco'), 'atributo' => 'pontoReferenciaEndereco', 'class' => 'text-left'],
    ['label' => Config::getAliasesField('entidadeEndereco'), 'atributo' => 'entidadeEndereco', 'class' => 'text-left'],
    ['label' => Config::getAliasesField('ativoEndereco'), 'atributo' => 'ativoEnderecoF', 'class' => 'text-left'],
    ['label' => Config::getAliasesField('valoridEndereco'), 'atributo' => 'valoridEndereco', 'class' => 'text-left'],
    ['label' => Config::getAliasesField('extrasEndereco'), 'atributo' => 'extrasEndereco', 'class' => 'text-left']
];
$Enderecos[0]['ngclick'] = $onclick;

// cards
$card = Card::basic($Enderecos, $entidade, false, $entidade . 'ContextItens');

// table
foreach ($Enderecos as $campo) {
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
    Form::getModel(Html::input(['ng-model' => 'Endereco.nomeEndereco', 'type' => 'text', 'class' => '', 'required' => 'not-required', 'ng-change' => $ngChange], Config::getAliasesField('nomeEndereco')), 'col-sm-4'),
    Form::getModel(Html::input(['ng-model' => 'Endereco.cepEndereco', 'type' => 'text', 'class' => '', 'required' => 'not-required', 'ng-change' => $ngChange], Config::getAliasesField('cepEndereco')), 'col-sm-4'),
    Form::getModel(Html::comboSearch(Config::getAliasesField('idMunicipio'), 'Endereco.idMunicipio', 'Endereco.Municipio.nomeMunicipio+\'/\'+Endereco.Municipio.Uf.siglaUf', 'Municipio', 'getAll'), 'col-sm-4'),
    Form::getModel(Html::input(['ng-model' => 'Endereco.ruaEndereco', 'type' => 'text', 'class' => '', 'required' => 'not-required', 'ng-change' => $ngChange], Config::getAliasesField('ruaEndereco')), 'col-sm-4'),
    Form::getModel(Html::input(['ng-model' => 'Endereco.numeroEndereco', 'type' => 'text', 'class' => '', 'required' => 'not-required', 'ng-change' => $ngChange], Config::getAliasesField('numeroEndereco')), 'col-sm-4'),
    Form::getModel(Html::input(['ng-model' => 'Endereco.complementoEndereco', 'type' => 'text', 'class' => '', 'required' => 'not-required', 'ng-change' => $ngChange], Config::getAliasesField('complementoEndereco')), 'col-sm-4'),
    Form::getModel(Html::input(['ng-model' => 'Endereco.bairroEndereco', 'type' => 'text', 'class' => '', 'required' => 'not-required', 'ng-change' => $ngChange], Config::getAliasesField('bairroEndereco')), 'col-sm-4'),
    Form::getModel(Html::input(['ng-model' => 'Endereco.latitudeEndereco', 'type' => 'text', 'class' => '', 'required' => 'not-required', 'ng-change' => $ngChange], Config::getAliasesField('latitudeEndereco')), 'col-sm-4'),
    Form::getModel(Html::input(['ng-model' => 'Endereco.longitudeEndereco', 'type' => 'text', 'class' => '', 'required' => 'not-required', 'ng-change' => $ngChange], Config::getAliasesField('longitudeEndereco')), 'col-sm-4'),
    Form::getModel(Html::input(['ng-model' => 'Endereco.pontoReferenciaEndereco', 'type' => 'text', 'class' => '', 'required' => 'not-required', 'ng-change' => $ngChange], Config::getAliasesField('pontoReferenciaEndereco')), 'col-sm-4'),
    Form::getModel(Html::input(['ng-model' => 'Endereco.entidadeEndereco', 'type' => 'text', 'class' => '', 'required' => 'required ', 'ng-change' => $ngChange], Config::getAliasesField('entidadeEndereco')), 'col-sm-4'),
    Form::getModel(Html::inputSelectNgRepeat('Endereco.ativoEndereco', Config::getAliasesField('ativoEndereco'), 'Endereco.ativoEndereco_ro', 'Aux.Boolean', $ngClick, $ngChange, 'Boolean'), 'col-sm-4'),
    Form::getModel(Html::input(['ng-model' => 'Endereco.valoridEndereco', 'type' => 'number', 'class' => '', 'required' => 'required ', 'ng-change' => $ngChange], Config::getAliasesField('valoridEndereco')), 'col-sm-4'),
    Form::getModel('<config-json title="' . Config::getAliasesField('extrasEndereco') . '" model="Endereco.extrasEndereco" grid="col-sm-6"></config-json>', 'col-sm-12')
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
