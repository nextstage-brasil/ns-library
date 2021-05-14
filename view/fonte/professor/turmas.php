<?php

// Automação de Criação de Sistema - 13/01/2020 07:34:45
if (!defined("SISTEMA_LIBRARY")) {
    die("Acesso direto não permitido");
}

$FLUID = false;
include Config::getData('path') . '/view/template/template1.php';

// nome da entidade JS
$entidade = 'GradePolo';
$onclick = $entidade . 'OnEdit(' . $entidade . ')';

//$viewHTML = file_get_contents(Config::getData('url') . "/_build/_sourceView/$entidade/view.php");
// titulo apresentado ao usuário. Caso não tenha sido configurado aliase, será exibido o nome da entidade
$title = 'Ambiente do professor'; // Config::getData('titlePagesAliases', $entidade);
// filtros na exibição da lista
$onChange = "init('Obtendo dados', true)";
//$periodo = Html::inputDatePickersGetLeftAndRight('Data inicial', 'Data final', 'Args.dataInicial', 'Args.dataFinal', '', '', $onChange);

//  cards com totais
$filtros = [
    '<div class="col-12 mb-2 border-bottom"><div class="row">'
    . '<div class="col-12 col-lg-3"><ns-card-home card-class="info" title="Matriculados" valor="{{Total.matriculado}}" descricao="Total de matriculas para periodo"></ns-card-home></div>'
    . '<div class="col-12 col-lg-3"><ns-card-home card-class="success" title="Confirmados" valor="{{Total.confirmado}}" descricao="Total de alunos confirmados"></ns-card-home></div>'
    . '<div class="col-12 col-lg-3"><ns-card-home card-class="danger" title="Recusados" valor="{{Total.recusado}}" descricao="Total de alunos que não irão cursar"></ns-card-home></div>'
    . '<div class="col-12 col-lg-3"><ns-card-home card-class="warning" title="Pendente" valor="{{Total.pendente}}" descricao="Total de alunos ainda não decidido"></ns-card-home></div>'
    . '</div></div>',
    //'<div class="col-4 col-lg-2">' . $periodo->left . '</div>',
    //'<div class="col-4 col-lg-2">' . $periodo->right . '</div>',
    '<div class="col-8 col-lg-4">' . Html::input(['type'=>'daterange',  'ng-model' => 'Args._periodoRange', 'ng-change' => $onChange], 'Periodo') . '</div>',
    ['grid' => 'col-6 col-lg-3', 'entidade' => 'Curso', 'label' => 'Curso'],
    ['grid' => 'col-6 col-lg-3', 'entidade' => 'Polo', 'label' => 'Polo'],
    ['grid' => 'col-6 col-sm-3', 'entidade' => 'Modulo', 'label' => 'Módulo']
];
if (Poderes::verify('gradepolo', 'gradepolo', 'ler todas as turmas', true)->getResult()) {
    $filtros[] = ['grid' => 'col-6 col-sm-3', 'entidade' => 'Usuario', 'label' => 'Professor'];
}



// Campos apresentados na lista
$GradePolos = [
    ['label' => Config::getAliasesField('idPolo'), 'atributo' => 'nomePolo', 'class' => 'text-left', 'order' => 'nomePolo'],
    ['label' => Config::getAliasesField('idModulo'), 'atributo' => 'nomeModulo', 'class' => 'text-left'],
    ['label' => Config::getAliasesField('Professor'), 'atributo' => 'nomeUsuario', 'class' => 'text-left'],
    ['label' => Config::getAliasesField('Encontros'), 'atributo' => "encontros", 'class' => 'text-left', 'order' => 'primEncontro'],
    ['label' => Config::getAliasesField('Matriculados'), 'atributo' => 'qtdeAlunosTotal', 'class' => 'text-center'],
    ['label' => Config::getAliasesField('Confirmados'), 'atributo' => 'qtdeAlunosConfirmado', 'class' => 'text-center'],
    ['label' => Config::getAliasesField('Recusados'), 'atributo' => 'qtdeAlunosRecusado', 'class' => 'text-center'],
    ['label' => Config::getAliasesField('Pendentes'), 'atributo' => 'qtdeAlunosPendente', 'class' => 'text-center'],
];
//$GradePolos[0]['ngclick'] = '';//$onclick;
// cards
$card = Card::basic($GradePolos, $entidade, false, $entidade . 'ContextItens');
// table
foreach ($GradePolos as $campo) {
    $key = (($campo['order']) ? $campo['order'] : count($th));
    $th[$key] = (($campo['label']) ? str_replace(':', '', $campo['label']) : '') . '|' . $campo['class'];
    $td[] = '{{' . $entidade . '.' . $campo['atributo'] . '}}' . '|' . str_replace('text-strong', '', $campo['class']);
}

$th[] = 'Ações';
$table = new Table($th, 'table-turmas-01', true, '', true);
$table->setFixedHeader(true);
$table->setForeach($entidade . "Filtradas = (" . $entidade . "s| filter : filtro | orderBy: 'nome" . $entidade . "')", $entidade);
$table->setMenuContexto($entidade . 'ContextItens');
//$table->setOnClick('verAlunos(' . $entidade . ')');
$table->setBindHTML(false);
$td[] = $table->menuContextAddOnTd();
$table->addLinha($td);

// form
$form = [
    Form::getModel(Html::inputSelectNgRepeat('GradePolo.idPolo', Config::getAliasesField('idPolo'), 'GradePolo.idPolo_ro', 'Aux.Polo', $ngClick, 'setIdPolo()'), 'col-sm-4'),
    Form::getModel(Html::inputSelectNgRepeat('GradePolo.idCurso', Config::getAliasesField('idCurso'), 'GradePolo.idCurso_ro', 'Aux.Curso|filter:filterCursoByPolo()', $ngClick, 'setIdPolo()'), 'col-sm-4'),
    Form::getModel(Html::inputSelectNgRepeat('GradePolo.idModulo', Config::getAliasesField('idModulo'), 'GradePolo.idModulo_ro', 'Aux.Modulo|filter:filterModulosByPolo()', $ngClick, 'setIdModulo()'), 'col-sm-4'),
    Form::getModel(Html::inputSelectNgRepeat('GradePolo.idUsuario', 'Professor', 'GradePolo.idUsuario_ro', 'Aux.Usuario|filter:filterProfessorByModulo()', $ngClick, $ngChange), 'col-sm-4'),
    Form::getModel(Html::inputDatePicker(Config::getAliasesField('dtHrGradePolo'), 'GradePolo.dtHrGradePolo', $minDate, $maxDate, $ngChange), 'col-sm-4'),
    Form::getModel(Html::inputSelectNgRepeat('GradePolo.isPresencialGradePolo', Config::getAliasesField('isPresencialGradePolo'), 'GradePolo.idCurso_ro', 'Aux.Boolean', $ngClick, $ngChange, 'Boolean'), 'col-sm-4'),
    //Form::getModel(Html::input(['ng-model' => 'GradePolo.isPresencialGradePolo', 'type' => 'text', 'class' => '', 'required' => 'required ', 'ng-change' => $ngChange], Config::getAliasesField('isPresencialGradePolo')), 'col-sm-4'),
//    Form::getModel('<config-json title="' . Config::getAliasesField('extrasGradePolo') . '" model="GradePolo.extrasGradePolo" grid="col-sm-6"></config-json>', 'col-sm-12'),
//    Form::getModel(Html::input(['ng-model' => 'GradePolo.durGradePolo', 'type' => 'number', 'class' => '', 'required' => 'not-required', 'ng-change' => $ngChange], Config::getAliasesField('durGradePolo')), 'col-sm-4'),
    Form::getModel(Html::input(['ng-model' => 'GradePolo.hrainicioGradePolo', 'type' => 'text', 'class' => 'hora', 'required' => 'not-required', 'ng-change' => $ngChange], Config::getAliasesField('hrainicioGradePolo')), 'col-sm-4')
];


// Head de impressão dos filtros utilizados
$tableFiltros = new Table(['', '', '', ''], false, false, 'table-bordered', false);
$tableFiltros->setExplode(false);
$tableFiltros->addLinha([
    '<p class="text-strong">Polo</p>
                  <p ng-repeat="filter in Aux.Polo | filter: {idPolo:Args.idPolo}:true">{{filter.nomePolo}}</p>',
    '<p class="text-strong">Leciona</p>
                  <p ng-repeat="filter in Aux.Leciona | filter: {idLeciona:Args.idLeciona}:true">{{filter.nomeLeciona}}</p>',
    '<p class="text-strong">Grade</p>
                  <p ng-repeat="filter in Aux.Grade | filter: {idGrade:Args.idGrade}:true">{{filter.nomeGrade}}</p>',
    '<p class="text-strong">Texto Pesquisa</p>
          <p class="text-upper">{{Args.Search}}</p>']);

// Criação do objeto Template. Retorna Head, List e Print. 
$template = new AdminTemplate($entidade, $title, $tableFiltros, $filtros, $card, $table);
$template->setForm($form);
$template->setViewHTML($viewHTML);

/* Tabs, caso seja necessário */
$tabs = [
    Tab::getModel('identificacao', 'Encontros', 'Encontros'),
    Tab::getModel('alunos', 'Alunos', 'Alunos'),
    Tab::getModel('tarefas', 'Tarefas', 'Tarefas'),
    Tab::getModel('avaliacoes', 'Avaliações', 'Avaliações'),
    array_merge(Tab::getModel('arquivos', 'Arquivos', Html::uploadFile($entidade)), ['ng-if' => "$entidade.id$entidade>0"])
];

$html = $template->printTemplate()
        // Form desativado
        . '<div id="formEdit' . $entidade . '" class="controleShow' . $entidade . ' d-print-none">'
        . (($tabs) ? Tab::printTab($tabs) : $template->printForm())
        . AdminTemplate::getButtonsStatic($entidade)
        . '</div>'
        //Atribuir notas
        . file_get_contents(__DIR__ . '/_notas.html')
        // Atribuir presença
        . file_get_contents(__DIR__ . '/_atribuirPresenca.html')
        // Ver alunos
        . '<div id="verAlunosDiv" class="controleShow' . $entidade . ' d-print-none"><span></span>'
        . '<div class="text-center mt-5"><button class="btn btn-secondary mr-1" ng-click="GradePoloDoClose()"><i class="fa fa-arrow-left mr-1"></i>Voltar</button</div>'
        . '</div>'
        . '';


echo '<div ng-controller="' . $entidade . 'Controller" id="controllerContent" class="d-none">'
 . $html
 . '</div>';


/* caso necessario injetar JS antes do controller  */
$html = Minify::html(file_get_contents(__DIR__ . '/_encontros.html'));
$js = "var _encontrosHTML = '$html';"
        . "var _initDate = '" . date('Y') . "-01-01';"
        . "var _endDate = '" . date('Y') . "-12-31';";
//$js = "var _encontrosHTML='Cristofer';";
echo \NsUtil\Packer::jsPack($js);
/* */

Component::init('Turmas-script.js');
Component::init('alunosPorTurma');

include Config::getData('path') . '/view/template/template2.php';
