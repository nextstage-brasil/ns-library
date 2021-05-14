<?php

if (!defined("SISTEMA_LIBRARY")) {
    die("Acesso direto não permitido");
}

class CardHTML {

    private $card; // armazenara os htmls
    private $ddHeader; // dados para linha de header
    private $ddFooter; // dados para linha de footer
    private $ddBody; // dados para linha de body
    private $dd;
    private $templateHeader, $templateBodyDiv, $templateBodyLink, $templateBodyDivRepeat;

    /*
      $dados = [
      [
      'icon' => 'icon',
      'title' => 'title',
      'text' =>  'text',
      'link' => 'link',
      'position' => 'H | F | B'
      ]
      ];
     */

    public function __construct($dados, $ngRepeatTitle, $ngRepeatBody, $ngRepeatFooter) {
        // preparar templates
        $this->init();
        // selecionar os dados que irão para cada posição do card
        foreach ($dados as $dd) {
            $dd['icon'] = ((!$dd['icon'] && $dd['ngclick']) ? Html::iconFafa('arrow-right') : $dd['icon']);
            $dd['id'] = 'id_' . substr(md5($dd['title']), 0, 14);
            $dd['contexto'] = (($dd['menu-contexto']) ? '<div style="position:absolute; top:0px; right:0px;" ng-show="' . $dd['id'] . '"><a menu-contexto="" menu-contexto-itens="{{' . $dd['menu-contexto'] . '}}">' . Html::iconFafa('ellipsis-v', 2) . '</a></div>' : '');
            $this->dd = $dd;
            switch ($dd['position']) {
                case 'H':
                    $this->addHeader();
                    break;
                case 'F':
                    $this->addFooter();
                    break;
                default:
                    $this->addBody();
            }
        }
    }

    private function addHeader() {
        $this->ddHeader .= Helper::escreveTemplate($this->templateHeader, $this->dd);
    }

    private function addBody() {
        $template = ((strlen($this->dd['ngclick']) > 5) ? $this->templateBodyLink : $this->templateBodyDiv);
        $this->ddBody .= Helper::escreveTemplate($template, $this->dd);
        unset($template);
    }

    private function addFooter() {
        $template = ((strlen($this->dd['ngclick']) > 5) ? $this->templateBodyLink : $this->templateBodyDiv);
        $this->ddFooter .= Helper::escreveTemplate($template, $this->dd);
        unset($template);
    }

    public function printCard() {
        $this->card = '<div class="card" style="padding:0px;">';
        $this->card .= $this->ddHeader;
        // body
        $this->card .= '<div class="card-body">';
        $this->card .= $this->ddBody;
        $this->card .= '</div>';
        // footer
        $this->card .= '<div class="card-footer">';
        $this->card .= $this->ddFooter;
        $this->card .= '</div>';
        // clearfix
        $this->card .= '<div class="clearfix"></div>';
        // fechamento div cards
        $this->card .= '</div>';
        return $this->card;
    }

    private function init() {
        // init vars
        $this->ddHeader = $this->ddBody = $this->ddFooter = $this->card = '';

        // templates
        $this->templateHeader = '
            <div class="card-item card-title %class%" id="%id%" ng-mouseover="%id% = true" ng-mouseout="%id% = false">
                <div class="col-1 card-icon">%icon%</div>
                <div class="col-11 card-item-content">
                    %contexto%
                    <p class="card-text-primary card-title-header">%title%</p>
                    <p class="card-text-secondary">%text%</p>
                </div>
            </div>';

        $this->templateBodyDiv = '
            <div class="card-item" id="%id%" ng-mouseover="%id% = true" ng-mouseout="%id% = false">
                    <div class="col-1 card-icon">%icon%</div>
                    <div class="col-11 card-item-content">
                        %contexto%
                        <p class="card-text-primary">%title%</p>
                        <p class="card-text-secondary">%text%</p>
                    </div>
                </div>';


        $this->templateBodyLink = '
            <a class="card-item" ng-click="%ngclick%" id="%id%" ng-mouseover="%id% = true" ng-mouseout="%id% = false">
                    <div class="col-1 card-icon">%icon%</div>
                    <div class="col-11 card-item-content">
                        %contexto%
                        <p class="card-text-primary">%title%</p>
                        <p class="card-text-secondary">%text%</p>
                    </div>                    
                </a>';
    }

}
