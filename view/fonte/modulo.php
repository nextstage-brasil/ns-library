<?php

// Automação de Criação de Sistema - 13/01/2020 07:34:45
if (!defined("SISTEMA_LIBRARY")) {
    die("Acesso direto não permitido");
}

include Config::getData('path') . '/view/template/template1.php';

// nome da entidade JS
$entidade = 'Modulo';
$onclick = $entidade . 'OnEdit(' . $entidade . ')';

//$viewHTML = file_get_contents(Config::getData('url') . "/_build/_sourceView/$entidade/view.php");
// titulo apresentado ao usuário. Caso não tenha sido configurado aliase, será exibido o nome da entidade
$title = Config::getData('titlePagesAliases', $entidade);

// filtros na exibição da lista
$filtros = [];

// Campos apresentados na lista
$Modulos = [
    ['label' => Config::getAliasesField('nomeModulo'), 'atributo' => 'nomeModulo', 'class' => 'text-left'],
    ['label' => Config::getAliasesField('cargaHorariaModulo'), 'atributo' => 'ch', 'class' => 'text-left'],
    ['label' => 'Qtde encontros', 'atributo' => 'extrasModulo.enc', 'class' => 'text-left'],
    ['label' => 'Reocorrência', 'atributo' => 'reoc', 'class' => 'text-left'],
    ['label' => 'Duração encontro', 'atributo' => 'dur', 'class' => 'text-left'],
    ['label' => 'Qtde Avaliações', 'atributo' => 'extrasModulo.ava', 'class' => 'text-left'],
    ['label' => 'Professores', 'atributo' => 'ext_a', 'class' => 'text-left']
];
$Modulos[0]['ngclick'] = $onclick;

// cards
$card = Card::basic($Modulos, $entidade, false, $entidade . 'ContextItens');

// table
foreach ($Modulos as $campo) {
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
    Form::getModel(Html::input(['ng-model' => 'Modulo.nomeModulo', 'type' => 'text', 'class' => '', 'required' => 'required ', 'ng-change' => $ngChange], Config::getAliasesField('nomeModulo')), 'col-sm-4'),
//    Form::getModel(Html::input(['ng-model' => 'Modulo.cargaHorariaModulo', 'type' => 'text', 'class' => '', 'required' => 'required ', 'ng-change' => $ngChange], Config::getAliasesField('cargaHorariaModulo')), 'col-sm-4'),
//    Form::getModel(Html::input(['ng-model' => 'Modulo.recModulo', 'type' => 'text', 'class' => '', 'required' => 'required ', 'ng-change' => $ngChange], Config::getAliasesField('recModulo')), 'col-sm-4'),
//    Form::getModel(Html::input(['ng-model' => 'Modulo.durEncModulo', 'type' => 'text', 'class' => '', 'required' => 'required ', 'ng-change' => $ngChange], Config::getAliasesField('durEncModulo')), 'col-sm-4'),
//    Form::getModel(Html::input(['ng-model' => 'Modulo.qtdeAvalModulo', 'type' => 'number', 'class' => '', 'required' => 'required ', 'ng-change' => $ngChange], Config::getAliasesField('qtdeAvalModulo')), 'col-sm-4'),
    Form::getModel('<config-json title="" model="Modulo.extrasModulo" grid="col-sm-6"></config-json>', 'col-sm-12')
];

// Head de impressão dos filtros utilizados
// Criação do objeto Template. Retorna Head, List e Print. 
$template = new AdminTemplate($entidade, $title, $tableFiltros, $filtros, $card, $table);
$template->setForm($form);
$template->setViewHTML($viewHTML);

/* Tabs, caso seja necessário */
$tabs = [
    Tab::getModel('identificacao', 'Cadastro', $template->printForm()),
    //array_merge(Tab::getModel('arquivos', 'Arquivos relacionados', Html::uploadFile($entidade)), ['ng-if' => "$entidade.id$entidade>0"]),
    array_merge(Tab::getModel('arquivos', 'Arquivos <span id="' . $entidade . 'Files" class="badge badge-info"></span>', Html::uploadFile($entidade)), ['ng-if' => "$entidade.id$entidade>0"]),
];

if (Poderes::verify('usuario', 'usuario', 'ler', true)->getResult()) {
    $tabs[] = array_merge(Tab::getModel('usuario-tag', Config::getAliasesTable('professores'), '<ns-tag tipo="list" descricao="Professores habilitados para ministrar módulo. A ordem dos ativos será utilizado na criação da grade de polo"  titulo="Professores" relacao="modulo|usuario" id-left="{{' . $entidade . '.id' . $entidade . '}}" condicoes="condicaoUsuario"></ns-tag>'), ['ng-if' => "$entidade.id$entidade>0"]);
}



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
