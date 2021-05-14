<?php

// Automação de Criação de Sistema - 13/01/2020 07:34:45
if (!defined("SISTEMA_LIBRARY")) {
    die("Acesso direto não permitido");
}

include Config::getData('path') . '/view/template/template1.php';

// nome da entidade JS
$entidade = 'Curso';
$onclick = $entidade . 'OnEdit(' . $entidade . ')';

//$viewHTML = file_get_contents(Config::getData('url') . "/_build/_sourceView/$entidade/view.php");
// titulo apresentado ao usuário. Caso não tenha sido configurado aliase, será exibido o nome da entidade
$title = Config::getData('titlePagesAliases', $entidade);

// filtros na exibição da lista
$filtros = [];

// Campos apresentados na lista
$Cursos = [
    ['label' => Config::getAliasesField('nomeCurso'), 'atributo' => 'nomeCurso', 'class' => 'text-left'],
    ['label' => Config::getAliasesField('cargaHorariaCurso'), 'atributo' => 'ch', 'class' => 'text-left'],
    ['label' => Config::getAliasesField('exigeAvaliacaoCurso'), 'atributo' => 'exigeAvaliacaoCursoF', 'class' => 'text-left'],
    ['label' => Config::getAliasesField('mensCurso'), 'atributo' => 'mensCurso', 'class' => 'text-left'],
    ['label' => Config::getAliasesField('vlrInscricao'), 'atributo' => "extrasCurso.vlrInscricao", 'class' => 'text-left'],
    ['label' => 'Módulos associados', 'atributo' => 'countModulos', 'class' => 'text-left'],
        //['label' => Config::getAliasesField('extrasCurso'), 'atributo' => 'extrasCurso', 'class' => 'text-left']
];
$Cursos[0]['ngclick'] = $onclick;

// cards
$card = Card::basic($Cursos, $entidade, false, $entidade . 'ContextItens');

// table
foreach ($Cursos as $campo) {
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
    Form::getModel(Html::input(['ng-model' => 'Curso.nomeCurso', 'type' => 'text', 'class' => '', 'required' => 'required ', 'ng-change' => $ngChange], Config::getAliasesField('nomeCurso')), 'col-sm-4'),
    //Form::getModel(Html::input(['ng-model' => 'Curso.cargaHorariaCurso', 'type' => 'text', 'class' => '', 'required' => 'required ', 'ng-change' => $ngChange], Config::getAliasesField('cargaHorariaCurso')), 'col-sm-4'),
    Form::getModel(Html::inputSelectNgRepeat('Curso.exigeAvaliacaoCurso', Config::getAliasesField('exigeAvaliacaoCurso'), 'Curso.exigeAvaliacaoCurso_ro', 'Aux.Boolean', $ngClick, $ngChange, 'Boolean'), 'col-sm-2'),
    Form::getModel(Html::input(['ng-model' => 'Curso.mensCurso', 'type' => 'text', 'class' => 'decimal', 'required' => 'required ', 'ng-change' => $ngChange], Config::getAliasesField('mensCurso')), 'col-sm-2'),
    Form::getModel('<config-json title="Demais dados" model="Curso.extrasCurso" grid="col-sm-6"></config-json>', 'col-sm-12')
];

// Head de impressão dos filtros utilizados
// Criação do objeto Template. Retorna Head, List e Print. 
$template = new AdminTemplate($entidade, $title, $tableFiltros, $filtros, $card, $table);
$template->setForm($form);
$template->setViewHTML($viewHTML);

/* Tabs, caso seja necessário */
$tabs = [
    Tab::getModel('identificacao', 'Cadastro', $template->printForm()),
    //array_merge(Tab::getModel('arquivos', 'Arquivos', Html::uploadFile($entidade)), ['ng-if' => "$entidade.id$entidade>0"]),
    //array_merge(Tab::getModel('modulos', Config::getAliasesTable('modulo'), '<linktable relacao="curso|modulo" id-left="{{' . $entidade . '.id' . $entidade . '}}" title="Módulos" grid-cards="col-4"></linktable>'), ['ng-if' => "$entidade.id$entidade>0"]),
    array_merge(Tab::getModel('modulo-tag', Config::getAliasesTable('modulo'), '<ns-tag tipo="list" descricao="Selecione os módulos que fazem parte deste curso arrastando para coluna Ativos e em seguida, coloque-os em ordem preferencial de execução" relacao="curso|modulo" id-left="{{' . $entidade . '.id' . $entidade . '}}" title="Módulos" grid-cards="col-4"></ns-tag>'), ['ng-if' => "$entidade.id$entidade>0"]),
];





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
