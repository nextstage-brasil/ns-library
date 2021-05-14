<?php

// Automação de Criação de Sistema - 13/01/2020 07:34:45
if (!defined("SISTEMA_LIBRARY")) {
    die("Acesso direto não permitido");
}

include Config::getData('path') . '/view/template/template1.php';

// nome da entidade JS
$entidade = 'Polo';
$onclick = $entidade . 'OnEdit(' . $entidade . ')';

//$viewHTML = file_get_contents(Config::getData('url') . "/_build/_sourceView/$entidade/view.php");
// titulo apresentado ao usuário. Caso não tenha sido configurado aliase, será exibido o nome da entidade
$title = Config::getData('titlePagesAliases', $entidade);

// filtros na exibição da lista
$filtros = [];

// Campos apresentados na lista
$Polos = [
    ['label' => Config::getAliasesField('nomePolo'), 'atributo' => 'nomePolo', 'class' => 'text-left'],
    ['label' => Config::getAliasesField('cepPolo'), 'atributo' => 'cepPolo|cep', 'class' => 'text-left'],
    ['label' => Config::getAliasesField('endPolo'), 'atributo' => 'endPolo', 'class' => 'text-left'],
    ['label' => Config::getAliasesField('secretariaId'), 'atributo' => "none + (Aux.Responsaveis|filter:{idUsuario:Polo.secretariaId})[0].nomeUsuario", 'class' => 'text-left'],
    ['label' => Config::getAliasesField('tesoureiroId'), 'atributo' => "none + (Aux.Responsaveis|filter:{idUsuario:Polo.tesoureiroId})[0].nomeUsuario", 'class' => 'text-left'],
    ['label' => Config::getAliasesField('responsavelId'), 'atributo' => "none + (Aux.Responsaveis|filter:{idUsuario:Polo.responsavelId})[0].nomeUsuario", 'class' => 'text-left'],
    ['label' => 'Tipo', 'atributo' => 'extrasPolo.tp', 'class' => 'text-left']
];
$Polos[0]['ngclick'] = $onclick;

// cards
$card = Card::basic($Polos, $entidade, false, $entidade . 'ContextItens');

// table
foreach ($Polos as $campo) {
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
    Form::getModel(Html::input(['ng-model' => 'Polo.nomePolo', 'type' => 'text', 'class' => '', 'required' => 'required ', 'ng-change' => $ngChange], Config::getAliasesField('nomePolo')), 'col-sm-4'),
    Form::getModel(Html::input(['ng-model' => 'Polo.cepPolo', 'type' => 'text', 'class' => '', 'required' => 'not-required', 'ng-change' => $ngChange], Config::getAliasesField('cepPolo')), 'col-sm-4'),
    Form::getModel(Html::input(['ng-model' => 'Polo.endPolo', 'type' => 'text', 'class' => '', 'required' => 'not-required', 'ng-change' => $ngChange], Config::getAliasesField('endPolo')), 'col-sm-4'),
    Form::getModel(Html::inputSelectNgRepeat('Polo.secretariaId', Config::getAliasesField('secretariaId'), 'Polo.secretariaId_ro', 'Aux.Secretarias', $ngClick, $ngChange, 'Usuario'), 'col-sm-4'),
    Form::getModel(Html::inputSelectNgRepeat('Polo.tesoureiroId', Config::getAliasesField('tesoureiroId'), 'Polo.tesoureiroId_ro', 'Aux.Secretarias', $ngClick, $ngChange, 'Usuario'), 'col-sm-4'),
    Form::getModel(Html::inputSelectNgRepeat('Polo.responsavelId', Config::getAliasesField('responsavelId'), 'Polo.responsavelId_ro', 'Aux.Responsaveis', $ngClick, $ngChange, 'Usuario'), 'col-sm-4'),
    Form::getModel('<config-json title="" model="Polo.extrasPolo" grid="col-sm-6"></config-json>', 'col-sm-12')
];

// Head de impressão dos filtros utilizados
// Criação do objeto Template. Retorna Head, List e Print. 
$template = new AdminTemplate($entidade, $title, $tableFiltros, $filtros, $card, $table);
$template->setForm($form);
$template->setViewHTML($viewHTML);

/* Tabs, caso seja necessário */
$tabs = [
    Tab::getModel('identificacao', 'Cadastro', $template->printForm()),
    array_merge(Tab::getModel('arquivos', 'Arquivos', Html::uploadFile($entidade)), ['ng-if' => "$entidade.id$entidade>0"]),
        //array_merge(Tab::getModel('curso-tag', Config::getAliasesTable('curso_disponivel'), '<linktable relacao="polo|curso" id-left="{{' . $entidade . '.id' . $entidade . '}}" title="' . Config::getAliasesTable('curso') . '" grid-cards="col-4"></linktable>'), ['ng-if' => "$entidade.id$entidade>0"]),
        //array_merge(Tab::getModel('curso-tag', 'Cursos', '<ns-tag relacao="polo|curso" id-left="{{' . $entidade . '.id' . $entidade . '}}" onchange="setCursosDisponiveis()"></ns-tag>'), ['ng-if' => "$entidade.id$entidade>0"]),
        //array_merge(Tab::getModel('grade-curricular', 'Grade curricular', 'Em desenvolvimento'), ['ng-if' => "$entidade.id$entidade>0"]),
];

if (Poderes::verify('curso', 'curso', 'ler', true)->getResult()) {
    $tabs[] = array_merge(Tab::getModel('curso-tag', 'Cursos', '<ns-tag relacao="polo|curso" id-left="{{' . $entidade . '.id' . $entidade . '}}" onchange="setCursosDisponiveis()"></ns-tag>'), ['ng-if' => "$entidade.id$entidade>0"]);
}
if (Poderes::verify('usuario', 'usuario', 'ler', true)->getResult()) {
    $tabs[] = array_merge(Tab::getModel('usuario-tag', 'Professores', '<ns-tag relacao="polo|usuario" id-left="{{' . $entidade . '.id' . $entidade . '}}" condicoes="condicaoUsuario"></ns-tag>'), ['ng-if' => "$entidade.id$entidade>0"]);
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
