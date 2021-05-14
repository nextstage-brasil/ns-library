<?php

// Automação de Criação de Sistema - 15/12/2020 05:11:53
if (!defined("SISTEMA_LIBRARY")) {
    die("Acesso direto não permitido");
}
//AppController::naoDisponivel();

include Config::getData('path') . '/view/template/template1.php';


// nome da entidade JS
$entidade = 'Matricula';
$onclick = $entidade . 'OnEdit(' . $entidade . ')';

//$viewHTML = file_get_contents(Config::getData('url') . "/_build/_sourceView/$entidade/view.php");
// titulo apresentado ao usuário. Caso não tenha sido configurado aliase, será exibido o nome da entidade
$title = Config::getData('titlePagesAliases', $entidade);

// filtros na exibição da lista
$filtros = [
    ['grid' => 'col-6 col-sm-3', 'entidade' => 'Curso'],
    ['grid' => 'col-6 col-sm-3', 'entidade' => 'Polo'],
    ['grid' => 'col-6 col-sm-3', 'entidade' => 'StatusFinanceiro'],
    ['grid' => 'col-6 col-sm-3', 'entidade' => 'TipoMatricula'],
];

// Campos apresentados na lista
$Matriculas = [
    ['label' => '#', 'atributo' => 'idMatricula', 'class' => 'text-left'],
    ['label' => Config::getAliasesField('idPolo'), 'atributo' => 'Polo.nomePolo', 'class' => 'text-left'],
    ['label' => Config::getAliasesField('idCurso'), 'atributo' => 'Curso.nomeCurso', 'class' => 'text-left'],
    ['label' => Config::getAliasesField('Aluno'), 'atributo' => 'Usuario.nomeUsuario', 'class' => 'text-left'],
    ['label' => Config::getAliasesField('isInscricaoMatricula'), 'atributo' => 'isInscricaoMatriculaF', 'class' => 'text-left'],
    ['label' => 'Valor por módulo'/* Config::getAliasesField('mensMatricula') */, 'atributo' => 'mensMatricula', 'class' => 'text-left'],
    //['label' => Config::getAliasesField('descMatricula'), 'atributo' => 'descMatricula', 'class' => 'text-left'],
    ['label' => 'Pendência financeira', 'atributo' => 'ext_a', 'class' => 'text-left'],
    ['label' => Config::getAliasesField('obsMatricula'), 'atributo' => 'obsMatricula', 'class' => 'text-left'],
        //['label' => Config::getAliasesField('extrasMatricula'), 'atributo' => 'extrasMatricula', 'class' => 'text-left'],
];
$Matriculas[0]['ngclick'] = $onclick;

// cards
$card = Card::basic($Matriculas, $entidade, false, $entidade . 'ContextItens');

// table
foreach ($Matriculas as $campo) {
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
    Form::getModel(Html::inputSelectNgRepeat('Matricula.idCurso', Config::getAliasesField('idCurso'), 'Matricula.idCurso', 'Aux.Curso', $ngClick, $ngChange), 'col-sm-3'),
    Form::getModel(Html::inputSelectNgRepeat('Matricula.idPolo', Config::getAliasesField('idPolo'), 'Matricula.idPolo', 'Aux.Polo', $ngClick, $ngChange), 'col-sm-2'),
    Form::getModel(Html::inputSelectNgRepeat('Matricula.idUsuario', 'Aluno', 'Matricula.idUsuario', 'Aux.Usuario', $ngClick, $ngChange), 'col-sm-4'),
    Form::getModel(Html::inputSelectNgRepeat('Matricula.isInscricaoMatriculaF', Config::getAliasesField('isInscricaoMatricula'), 'Matricula.isInscricaoMatricula', 'Aux.Boolean', $ngClick, $ngChange, 'Boolean'), 'col-sm-3'),
    //Form::getModel(Html::input(['ng-model' => 'Matricula.descMatricula', 'type' => 'text', 'class' => 'decimal', 'required' => 'not-required', 'ng-change' => $ngChange], Config::getAliasesField('descMatricula')), 'col-sm-4'),
    Form::getModel(Html::input(['ng-model' => 'Matricula.mensMatricula', 'type' => 'text', 'class' => 'decimal', 'required' => 'required ', 'ng-change' => $ngChange], Config::getAliasesField('mensMatricula')), 'col-sm-2'),
    Form::getModel(Html::input(['ng-model' => 'Matricula.obsMatricula', 'type' => 'text', 'class' => '', 'required' => 'not-required', 'ng-change' => $ngChange], Config::getAliasesField('obsMatricula')), 'col-sm-8'),
        //Form::getModel('<config-json title="' . Config::getAliasesField('extrasMatricula') . '" model="Matricula.extrasMatricula" grid="col-sm-6"></config-json>', 'col-sm-12'),
];

// Head de impressão dos filtros utilizados
$tableFiltros = new Table(['', '', ''], false, false, 'table-bordered', false);
$tableFiltros->setExplode(false);
$tableFiltros->addLinha([
    '<p class="text-strong">Curso</p>
                  <p ng-repeat="filter in Aux.Curso | filter: {idCurso:Args.idCurso}:true">{{filter.nomeCurso}}</p>',
    '<p class="text-strong">Usuario</p>
                  <p ng-repeat="filter in Aux.Usuario | filter: {idUsuario:Args.idUsuario}:true">{{filter.nomeUsuario}}</p>',
    '<p class="text-strong">Texto Pesquisa</p>
          <p class="text-upper">{{Args.Search}}</p>']);

// Criação do objeto Template. Retorna Head, List e Print. 
$template = new AdminTemplate($entidade, $title, $tableFiltros, $filtros, $card, $table);
$template->setForm($form);
$template->setViewHTML($viewHTML);

// HTML da aba financeiro
$table = new Table(['Tipo', 'Vencimento', 'Valor', 'Descrição', 'Status', 'Forma pgto', 'Data pgto', '']);
$table->setForeach('Matricula.Financeiro', 'item')
        ->addLinha([
            '{{item.tipoFinanceiro}}',
            '{{item.vencimentoFinanceiro}}',
            '{{item.valorFinanceiro}}',
            '{{item.descricaoFinanceiro}}',
            '{{item.statusFinanceiro}}',
            '{{item.formaPagamentoFinanceiro}}',
            '{{item.dataPagamentoFinanceiro}}',
            ''
            . '<button class="btn btn-warning btn-sm mr-1" ng-click="inscricaoInformarCasal(item)" ng-show="item.codStatus < 90"><i class="fa fa-edit mr-1"></i>Aplicar desconto</button>'
            . '<button class="btn btn-primary btn-sm" ng-click="registrarPagamento(item)" ng-show="item.codStatus < 90"><i class="fa fa-money mr-1"></i>Registrar recebimento</button>'
            . ''
        ])
;
$financeiroHTML = $table->printTable();

/* Tabs, caso seja necessário */
$tabs = [
    Tab::getModel('identificacao', 'Cadastro', $template->printForm()),
    //array_merge(Tab::getModel('arquivos', 'Arquivos <span id="' . $entidade . 'Files" class="badge badge-info">', Html::uploadFile($entidade)), ['ng-if' => "$entidade.id$entidade>0"]), 
    array_merge(Tab::getModel('financeiro', 'Financeiro <span class="badge badge-info">{{Matricula.FinanceiroPendentes}}</span>', $financeiroHTML), ['ng-if' => "$entidade.id$entidade>0"]),
];



$html = $template->printTemplate()
        . '<div id="formEdit' . $entidade . '" class="controleShow' . $entidade . ' d-print-none">'
        . (($tabs) ? '<h3>Matrícula #{{Matricula.idMatricula}} em nome de {{Matricula.Usuario.nomeUsuario}}</h3>' . Tab::printTab($tabs) : $template->printForm())
        . AdminTemplate::getButtonsStatic($entidade)
        . '</div>';


echo '<div ng-controller="' . $entidade . 'Controller" id="controllerContent" class="d-none">'
 . $html
 . '</div>';


/* caso necessario injetar JS antes do controller */
$form = (new \Form())
        ->addElement(Html::inputSelectNgRepeat('Recebe.idConta', 'Conta', 'ns21', 'Contas'), 'col-md-6')
        ->addElement(Html::inputSelectNgRepeat('Recebe.idFormaPgto', 'Forma de pagamento', 'ns21', 'FormaPgto'), 'col-md-6')
        ->printForm();
$js = "var _formRecebeFinanceiro = '$form'";
echo NsUtil\Packer::jsPack($js);

Component::init($entidade . '-script.js');

include Config::getData('path') . '/view/template/template2.php';
