<?php

if (!defined("SISTEMA_LIBRARY")) {
    die("Acesso direto não permitido");
}

class Card {

    private $card; // armazenara os htmls
    private $ddHeader; // dados para linha de header
    private $ddFooter; // dados para linha de footer
    private $ddBody; // dados para linha de body
    private $templateHeader, $templateBody;
    private $varAngularList;
    private $bootstrapCol;
    private static $template = '
<div class="card %s mb-1" %s>
    <div class="card-body">
      <div class="card-title">%s</div>
      %s
    </div>
</div>
';
    private static $templateContent = '
        <p class="card-text mb-1 %s">
            <span class="card-subtitle mt-0">%s</span>
            <span class="card-text">%s</span>
            <span class="clearfix"></span>
        </p>
';

    /*
      $dados = [
      [
      'icon' => 'icon',
      'title' => 'title',
      'text' =>  'text',
      'link' => 'link',
      'position' => 'HEADER | FOOTER | BODY'
      ]
      ];
     */

    public function __construct($varAngularList, $bootstrapCol = '') {
        // preparar templates
        $this->init();
        $this->varAngularList = $varAngularList;
        $this->bootstrapCol = $bootstrapCol;
        $this->ddHeader = Helper::escreveTemplate($this->templateHeader, ['repeat' => $varAngularList . '.Header']);
        $this->ddBody = Helper::escreveTemplate($this->templateBody, ['repeat' => $varAngularList . '.Body']);
        $this->ddFooter = Helper::escreveTemplate($this->templateBody, ['repeat' => $varAngularList . '.Footer']);
    }

    public function printCard() {
        $this->card = '<div class="' . $this->bootstrapCol . '" ng-if="' . $this->varAngularList . '" style="padding:0px;">';
        $this->card .= '<div class="card m-2" style="padding:0px;">';
        $this->card .= $this->ddHeader;
        // body
        $this->card .= '<div class="card-body m-0 p-0">';
        $this->card .= $this->ddBody;
        $this->card .= '</div>';
        // footer
        $this->card .= '<div class="card-footer">';
        $this->card .= $this->ddFooter;
        $this->card .= '</div>';
        // clearfix
        //$this->card .= '<div class="clearfix"></div>';
        // fechamento div cards
        $this->card .= '</div></div>';
        return $this->card;
    }

    private function init() {
        // init vars
        $this->ddHeader = $this->ddBody = $this->ddFooter = $this->card = '';

        $this->template = '
<div class="card">
<div class="card-body">
  <h4 class="card-title">{title}</h4>
  <h6 class="card-subtitle mb-2 text-muted">Card subtitle</h6>
  <p class="card-text">Some quick example text to build on the card title and make up the bulk of the cards content.</p>
  <a href="#" class="card-link">Card link</a>
  <a href="#" class="card-link">Another link</a>
</div>
</div>
';


        // templates
        $this->templateHeader = '
            <div class="ns-card-item card-title {{item.class}}" ng-repeat="item in %repeat%" ng-mouseover="item.contextoShow = true" ng-mouseout="item.contextoShow = false">
                    <div class="pl-2">
                        <h5 class="mb-0 card-title text-primary"><i class="fa fa-{{item.icon}}" aria-hidden="true"></i> {{item.title}}</h5>
                        <i><small><p class="mb-0" ng-bind-html="item.text"></p></small></i>
                        <div style="position:absolute; top:0px; right:0px;" ng-show="item.contextoShow && item.menucontexto"><a menu-contexto="" menu-contexto-itens="{{item.menucontexto}}">' . Html::iconFafa('ellipsis-v', 2) . '</a></div>
                    </div>  
            </div>';

        $this->templateBody = '
                <div class="pt-3 {{item.ngclick && \'card-item-link\' || \'\'}}" ng-repeat="item in %repeat%" ng-mouseover="item.contextoShow = true" ng-mouseout="item.contextoShow = false" on-long-press="" context-itens="{{item.menucontexto}}">
                    <div class="card-icon" style="width:4%; float:left;" ng-click="ngclick(item.ngclick, item)">
                        <i ng-if="item.icon" class="fa fa-{{item.icon}}" aria-hidden="true"></i>
                    </div>
                    <div class="card-item-content" style="width:95%;float:right" style="padding:0;">
                        <div class="card-text" style="width:97%; float:left; padding:0;" ng-click="ngclick(item.ngclick, item)">
                            <h6 class="card-subtitle mb-1 text-muted" ng-bind-html="item.title"></h6>
                            <p class="card-text-secondary" ng-if="item.text" ng-bind-html="item.text"></p>
                        </div>
                        <div class="text-right" style="width:2%; float:right; padding:0px; color: #999;">
                            <div ng-show="item.contextoShow && item.menucontexto">
                                <a menu-contexto="" menu-contexto-itens="{{item.menucontexto}}">' . Html::iconFafa('ellipsis-v', 2) . '</a>
                            </div>                
                        </div>
                    </div>
                    <div class="clearfix"></div>
                </div>                
                ';
    }

    public static function getModelItem($icon, $title, $text, $id = false, $ngclick = false, $class = '', $menucontext = '') {
        $out = ['id' => $id, 'icon' => $icon, 'title' => $title, 'text' => $text, 'ngclick' => $ngclick, 'class' => $class, 'menucontexto' => $menucontext];
        return $out;
    }

    /**
     * 
     * @param type $label
     * @param type $atributo
     * @param type $class
     * @param type $ngclick
     * @return type
     */
    public static function getModelBasic($label, $atributo = 'none', $class = '', $ngclick = false) {
        $out = ['atributo' => $atributo, 'class' => $class, 'label' => $label, 'ngclick' => $ngclick];
        return $out;
    }

    /**
     * 
     * @param type $array: array php para monmtagem do card
     * @param type $objectAngular Nome do objetoangular para uso, tipo: item
     * @param type $height: altura fixa do card
     * @param type $menuContexto: menu de contexto
     * @param type $ngRepeatList: lista angular para iterar
     * @param type $msgLengthZero
     * @param type $classColunas
     * @return type
     */
    public static function basic($array, $objectAngular, $height = false, $menuContexto = 'ns21', $ngRepeatList = false, $msgLengthZero = 'Nenhum registro localizado', $classColunas = 'col-sm-4') { // ns21 pois acredito que nunca ira existir variavel com este nome
        if (is_array($array) && count($array) > 0) {
            $onclick = (($array[0]['ngclick']) ? $array[0]['ngclick'] : $objectAngular . 'OnEdit(' . $objectAngular . ')');
            $css = $array[0]['class'];
            $title = ( ($array[0]['label'] === 'img') ?
                    '<img class="img img-thumbnail img-fluid" style="max-width:100px;" src="{{' . $objectAngular . '.' . $array[0]['atributo'] . '}}" /><br/>' :
                    '<h4 class="card-title" ng-bind-html = "' . $objectAngular . '.' . $array[0]['atributo'] . '"></h4>');

            // barrinha a direita com os tres pontinhos

            $card .= ' </div>';
            /*  desliguei a condicao de so mostrar os tres pontinhos quando mouse estiver em cima. vai mostrar sempre
              $title .= '
              <div class="text-center text-dark mouseover-hand" style="padding:0px;">
              <div style="position:absolute; top:10px; right:10px;" ng-show="' . $objectAngular . '.contextoShow && ' . $menuContexto . '.length"><a menu-contexto="" menu-contexto-itens="{{' . $menuContexto . '}}">' . Html::iconFafa('ellipsis-v', 2) . '</a></div>
              </div>';
             * 
             */

            $title .= '
            <div class="text-center text-dark mouseover-hand" style="padding:0px;">
                <div style="position:absolute; top:10px; right:10px;" ng-show="' . $menuContexto . '.length"><a menu-contexto="" menu-contexto-itens="{{' . $menuContexto . '}}">' . Html::iconFafa('ellipsis-v', 2) . '</a></div>            
            </div>';


            unset($array[0]);
            $body = [];
            foreach ($array as $campo) {
                if (stripos($campo['label'], 'img') > -1) {
                    $card .= '<img class="img img-responsive ' . $campo['class'] . '" src="{{' . $objectAngular . '.' . $campo['atributo'] . '}}" />';
                    $body[] = '<img class="img img-responsive ' . $campo['class'] . '" src="{{' . $objectAngular . '.' . $campo['atributo'] . '}}" />';
                } else {
                    $campo['label'] .= (($campo['label']) ? ':<span class="ml-1"></span>' : '');
                    $text = ((substr($campo['atributo'], 0, 2) !== '{{') ?
                            '<span ng-bind-html = "' . $objectAngular . '.' . $campo['atributo'] . '"></span>' : $campo['atributo']);
                    $body[] = sprintf(self::$templateContent, 'abracada ' . $campo['class'], $campo['label'], $text);
                }
            }
            $extrasHead = ''
                    //. 'ng-click="' . $onclick . '" ' 
                    . (($height) ? ' style = "height:' . $height . 'px"' : '')
                    . 'ng-mouseover="' . $objectAngular . '.contextoShow = true" ng-mouseout="' . $objectAngular . '.contextoShow = false"';
            $card = sprintf(self::$template, $css, $extrasHead, $title, implode(' ', $body));
        } else {
            $card = 'Dados informados para gerar Card são inválidos (CC121)';
        }
        if ($ngRepeatList) {
            $card = Html::divNgRepeat($card, $ngRepeatList, $objectAngular, $classColunas, $menuContexto, $msgLengthZero);
        }
        return $card;
    }

}
