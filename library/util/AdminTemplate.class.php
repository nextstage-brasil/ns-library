<?php

if (!defined("SISTEMA_LIBRARY")) {
    die("Acesso direto não permitido");
}

class AdminTemplate {

    private $entidade, $title, $tableFiltros, $filtros, $head, $list, $print, $entidadeSpan, $table, $colSm, $form;
    public $textSearch;
    public $onClick;
    public $viewHTML;
    public static $filterTemplate = '
            <div class="%1$s form-group">
    <span class="floating-label-select">%3$s</span>
    <i ng-show="Args.id%2$s" ng-click="filterClear(\'id%2$s\')" class="btn btn-link filter-control-clear fa fa-times form-control-feedback hidden" aria-hidden="true"></i>
    <select class="form-control" ng-change="%4$sGetAll(\'\', true)" 
    ng-model="Args.id%2$s" 
    ng-options="item.id%2$s as item.nome%2$s for item in Aux.%2$s">
    </select>    
    </div>';

    public function __construct($entidade, $title, $tableFiltros, $filtros, $entidadeSpan, $table, $colSm = 'col-sm-6') {
        if ($table && !($table instanceof Table)) {
            $this->head = '$table not instance of Table';
            return false;
        }
        if ($tableFiltros && !($tableFiltros instanceof Table)) {
            $this->head = '$tableFilters not instance of Table';
            return false;
        }
        $this->form = [];
        // população de propriedades
        $this->entidade = $entidade;
        $this->title = $title;
        $this->entidadeSpan = $entidadeSpan;
        $this->table = $table;
        $this->colSm = $colSm;
        $this->textSearch = 'O que procura?';

        $this->setFilters($filtros);

        if ($this->filtros) {
            $this->tableFiltros = $tableFiltros;
        }
    }

    public function printTemplate($injectCode = '') {
        // montagem da saida
        $this->head = $this->getHead();
        $this->list = $this->getList();
        $this->print = $this->toPrint();

        $out = '<div class="componentContainer">'
                . $injectCode
                . $this->head
                . $this->getModalView()
                . $this->list
                . $this->print
                . '</div>';
        //$out = $this->head . $this->list;
        return $out;
    }

    public function setForm(array $form, $reset = false) {
        if ($reset) {
            $this->form = [];
        }
        $this->form = array_merge($this->form, $form);
    }

    public function printForm() {
        $form = new Form(md5(time() . $this->form[0]['content']), '', '');
        foreach ($this->form as $f) {
            $form->addElement($f['content'], $f['class']);
        }
        return $form->printForm();
    }

    public static function printFormStatic($form) {
        $f = new Form();
        foreach ($form as $item) {
            $f->addElement($item['content'], $item['class'], $item['hint']);
        }
        return Minify::html($f->printForm());
    }

    private function getHead() {
        // buttons
        $cardsSwitch = '<a id="buttonCardSwitch" class="btn btn-default" ng-click="viewTable=!viewTable" alt="Alterar exibição">' . Html::iconFafa('{{viewTable && \'id-card\' || \'list\'}}') . '</a>';
        // filtros
        $filtros .= '
                <div class="card p-1 small listEdit' . $this->entidade . ' d-print-none" ng-show="showFiltro">
                    <h5>Filtros</h5>
                <div class="row">'
                . $this->filtros
                . '</div>'
                . '</div>'
                . '<div class="clearfix d-print-none"></div>';
        /*
          $template = '<div class="row" id="templateFirstRow">'
          . '<h3 class="col-12 title text-left d-print-none" id="titlePage">' . $this->title . '</h3>'
          . '<div class="col-12 col-sm-6 text-left listEdit' . $this->entidade . ' d-print-none hidden-xs">'
          //. '<a onclick="javascript: history.back()" class="btn btn-success text-center" ng-if="idParameter"> << Voltar</a>  '
          . '<button id="btnTemplateNew" ng-click="' . $this->entidade . 'OnEdit({id' . $this->entidade . ' : -1})" class="btn btn-info mr-1 text-center"><i class="fa fa-plus-circle" aria-hidden="true"></i> Novo</button>  '
          . (($this->filtros) ? '<button id="buttonFilterShow" class="btn mr-1 {{showFiltro && \'btn-success\'|| \'btn-default\'}}" ng-click="showFiltro = !showFiltro">' . Html::iconFafa("filter") . 'Filtros</button>  ' : '')
          . '<button ng-click = "toPrint()" class = "btn text-center mr-1 {{preparandoImpressao && \'btn-success\'|| \'btn-default\'}}">' . Html::iconFafa("{{preparandoImpressao && 'spinner fa-spin' || 'print'}}") . ' {{preparandoImpressao && \'Preparando para impressão\'|| \'Imprimir\'}}</button>  '
          . $cardsSwitch
          . '</div>'
          . '<div class="col-12 col-sm-6 text-right listEdit' . $this->entidade . ' d-print-none">'
          . '<div id="divSearchTemplate">'
          . Html::search($this->textSearch)
          . '</div>'
          . '</div>'
          . '<div class="clearfix listEdit' . $this->entidade . ' d-print-none" style="margin-top: 35px;"></div>'
          . '</div>';
         * 
         */

        $template = '
        <h3 class="title text-left d-print-none d-none" id="titlePage">' . $this->title . '</h3>
        <div class="listEdit' . $this->entidade . ' d-print-none">
            <a ng-click="' . $this->entidade . 'OnEdit({id' . $this->entidade . ' : -1})" class="d-block d-sm-none text-dark btnAdd text-center"><i class="fa fa-plus-circle fa-4x" aria-hidden="true"></i></a>
            ' . (($this->filtros) ? '<a ng-click="showFiltro=!showFiltro" class="d-block d-sm-none text-dark btnShowFiltro text-center"><i class="fa fa-filter fa-2x" aria-hidden="true"></i></a>' : '') . '
            <div class="row rowFilters">
                <div class="d-none d-sm-block col-12 col-sm-1 btnTemplateNewDiv">
                    <a id="btnTemplateNew" ng-click="' . $this->entidade . 'OnEdit({id' . $this->entidade . ' : -1})" class="btn text-warning text-center"><i class="fa fa-plus-circle fa-4x" aria-hidden="true"></i><br/>NOVO</a>
                </div>
                <div class="col-12 col-sm-3 nsTemplateSearch">
                    <div id="divSearchTemplate" class="pl-2">' . Html::search($this->textSearch) . '</div>
                </div>
                <div class="d-none d-sm-block col-12 col-sm-7 nsTemplateMyFilters">
                    <div class="row">' . $this->filtros . '</div>
                </div>
                <div class="d-block d-sm-none col-12" ng-show="showFiltro">
                    <div class="row">' . $this->filtros . '</div>
                </div>                

                <div class="d-none d-sm-block col-sm-1 text-center p-0">
                    <a ng-click = "toPrint()" class = "btn text-center mr-1 {{preparandoImpressao && \'btn-warning\'|| \'btn-default\'}}">' . Html::iconFafa("{{preparandoImpressao && 'spinner fa-spin' || 'print'}}") . '</a>                    
                    ' . $cardsSwitch . '
                </div>
            </div>
        </div>
        ';

        return $template;
    }

    private function getList() {
        if ($this->tableFiltros) {
            $extraFiltros = '<div class="d-none d-print-block">'
                    . '<p class="text-strong">Filtros aplicados:</p>'
                    . $this->tableFiltros->printTable()
                    . '</div>';
        }
        $template = '';
        // infinite-scroll-use-document-bottom="true" infinite-scroll-immediate-check="true"
        // DIV
        $template .= '<div ng-show="adminTemplateHtmlExtra" ng-bind-html="adminTemplateHtmlExtra"></div>
          <div infinite-scroll="' . $this->entidade . 'GetAll()" infinite-scroll-distance="1" infinite-scroll-disabled="working">
            <div id="listEditDiv" class="listEdit' . $this->entidade . ' controleShow' . $this->entidade . ' d-print-none" ng-show="!viewTable">              
                <div class="row">
                    <div class="' . $this->colSm . ' listEditLista" style="margin-top:25px;" id="EditRow_{{' . $this->entidade . '.id' . $this->entidade . '}}" on-long-press="" context-itens="{{' . $this->entidade . 'ContextItens}}"
                    ng-repeat="' . $this->entidade . ' in ' . $this->entidade . 'Filtradas = (' . $this->entidade . 's| filter : filtro | orderBy: \'nome' . $this->entidade . '\')">
                    ' . $this->entidadeSpan . '
                    </div>
                </div>
            </div>
            
            <div class="listEdit' . $this->entidade . ' mt-4 controleShow" ng-show="viewTable">
                <h1 class="text-center d-none d-print-block">' . $this->entidade . '</h1>
                ' . $extraFiltros . '
                <div class="clearfix"></div>
                <div ng-show="' . $this->entidade . 'Filtradas.length>0">' . $this->table->printTable() . '</div>
            </div>
            <div class="clearfix"></div>
          </div>';

        // FOOTER
        $template .= '
        <div class="listEdit' . $this->entidade . ' clearfix d-print-none" style="margin-top:25px;"></div>
        <div class="listEdit' . $this->entidade . ' d-print-none">'
                . Html::msgInfoTxt('Nenhum registro localizado <p ng-if="Search">Filtro aplicado: {{Search}}</p>', $this->entidade . 'Filtradas.length === 0 &&!working', false)
                //. Html::msgWait('!working', '{{working===true && \'Carregando informações\' || working}}')
                . '<div class="clearfix" style="margin-top:50px;"></div>'
                . Html::msgInfoTxt('Fim da lista - {{' . $this->entidade . 'Filtradas.length}} resultados ', 'pagina === - 1 && ' . $this->entidade . 's.length &&!working')
                . '</div>';
        return $template;
    }

    private function toPrint() {
        if ($this->tableFiltros) {
            $extraFiltros = '<p class="text-strong">Filtros aplicados:</p>'
                    . $this->tableFiltros->printTable();
        }

        $t = '
            <!--TO PRINT -->'
                . '<div class="d-none d-print-block">'
                . '<h1 class="text-center">' . $this->title . '</h1>'
                . $extraFiltros
                . ' <div class="clearfix"></div>'
                . $this->table->printTable()
                . '</div>';

        return $t;
    }

    /**
     * 
     * @param type $prefixoFunction
     * @param type $nomeEntidadeJS
     * @param type $btnApagar
     * @param type $labelButton
     * @param type $icon
     * @return string
     */
    public static function getButtonsStatic($prefixoFunction, $nomeEntidadeJS = "", $btnApagar = true, $labelButton = [], $icon = []) {
        //labels
        $labelEntidade = Config::getAliasesTable($prefixoFunction);
        
        $labelBtnSalvar = (($labelButton['save']) ? $labelButton['save'] : "Salvar $labelEntidade");
        $labelBtnDelete = (($labelButton['delete']) ? $labelButton['delete'] : "Remover $labelEntidade");
        $labelBtnBack = (($labelButton['back']) ? $labelButton['back'] : "Fechar $labelEntidade");
        //icons
        $iconBtnSalvar = (($icon['save']) ? $icon['save'] : 'check');
        $iconBtnDelete = (($icon['delete']) ? $icon['delete'] : 'trash');
        $iconBtnBack = (($icon['back']) ? $icon['back'] : 'times');
        $idEntidade = (($nomeEntidadeJS) ? "$nomeEntidadeJS.id$nomeEntidadeJS" : "$prefixoFunction.id$prefixoFunction");
        $out = '<div class="clearfix"></div>'
                . '<div class="col-sm-12 mb-2 mt-5 text-center mt-5 btnsAdmin">'
                . (($btnApagar) ? '<button ng-if = "' . $idEntidade . '" class = "btn btn-link text-danger text-right mr-1 mb-2" ng-click = "' . $prefixoFunction . 'Remove(' . $prefixoFunction . ')"><i class = "fa fa-' . $iconBtnDelete . '" aria-hidden = "true"> </i> ' . $labelBtnDelete . '</button>' : '')
                . '<button class="btn btn-jumbo btn-secondary text-right mr-1 mb-2" ng-click="' . $prefixoFunction . 'DoClose()"><i class="fa fa-' . $iconBtnBack . '" aria-hidden="true"> </i> ' . $labelBtnBack . '</button>'
                . (($labelButton['saveIconAfter']) ? '<button class = "btn btn-jumbo btn-success mr-1 mb-2" ng-click = "' . $prefixoFunction . 'Save()">' . $labelBtnSalvar . ' <i class = "fa fa-' . $iconBtnSalvar . '" aria-hidden = "true"></i></button>' : '<button class = "btn btn-jumbo btn-success mb-2" ng-click = "' . $prefixoFunction . 'Save()"><i class = "fa fa-' . $iconBtnSalvar . '" aria-hidden = "true"></i> ' . $labelBtnSalvar . '</button>')
                . '</div>'
                . '';
        return $out;
    }

    public function getButtons() {
        return self::getButtonsStatic($this->entidade);
    }

    private function setFilters($filter) {
        if (is_array($filter)) {
            foreach ($filter as $item) {
                if (is_array($item)) {
                    $label = (($item['label']) ? $item['label'] : Config::getAliasesTable($item['entidade']));
                    $this->filtros .= sprintf(self::$filterTemplate, $item['grid'], $item['entidade'], $label, $this->entidade);
                } else {
                    $this->filtros .= $this->setFilters($item);
                }
            }
        } else {
            $this->filtros .= $filter;
        }
    }

    /**
     * Método para visualização do dashboard ou viewer da entidade
     * @return type
     */
    public function getModalView() {
        return '<div id="view' . $this->entidade . '" class="controleShow' . $this->entidade . '">' . $this->viewHTML . '</div>';
        /*
          return '<!-- template modal View -->
          <div id="modalView' . $this->entidade . '" class="modal fade" role="dialog">
          <div class="modal-dialog modal-lg">
          <!-- Modal content-->
          <div class="modal-content">
          <div class="modal-header text-center">
          <h3>' . $this->title . '</h3>
          <h4 class="modal-title"></h4>
          </div>
          <div class="modal-body">
          <div class="row mb-0">
          <div class="{{item.grid}}" ng-repeat="item in modalData">
          <h6 ng-bind-html="item.label"></h6>
          <p class="{{item.classe}}" ng-bind-html="item.text"></p>
          </div>
          </div>
          </div>
          <div class="modal-footer text-center">
          <button type="button" class="btn btn-link" data-dismiss="modal" id="btnAlertModal">Fechar</button>
          <button type="button" class="btn btn-info" data-dismiss="modal" id="btnModalQualifica" ng-click="' . $this->entidade . 'OnEdit(' . $this->entidade . ')">Editar</button>
          </div>
          </div>
          </div>
          </div>';
         * 
         */
    }

    function setViewHTML($viewHTML) {
        $this->viewHTML = $viewHTML;
    }

}
