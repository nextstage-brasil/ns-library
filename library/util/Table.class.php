<?php

class Table {

    public $head;
    private $linha;
    private $css;
    private $headTamanho;
    private $onClick;
    private $foreach;
    private $explode;
    private $infiniteScroll;
    private $menuContexto;
    private $fixedHeader = true;
    public $bindHTML = false;

    public function __construct($campos, $idTabela = false, $zebra = true, $css = '', $head = true, $infinitescroll = false) {
        if (is_array($campos)) {
            $this->linha = false;
            $this->headTamanho = count($campos);
            $t['elements'] = $campos;
            $t['id'] = (($idTabela) ? $idTabela : md5(microtime()));
            $t['css'] = 'table ' . (($zebra) ? ' table-striped ' : '') . ' ' . $css;
            $this->infiniteScroll = $infinitescroll;
            $this->setHead($t, $head);
            $this->explode = true;
        } else {
            die(__METHOD__ . __LINE__ . ': Head Not Array' . var_dump(debug_backtrace(-1)));
        }
    }

    function setFixedHeader(bool $fixedHeader) {
        $this->fixedHeader = $fixedHeader;
        return $this;
    }

    public function setCss($text) {
        $this->css = (string) $text;
        return $this;
    }

    public function setOnClick($text) {
        $this->onClick = $text;
        $this->css = $this->css . ' mouseover-hand '; // setando automaticamente
        return $this;
    }

    public function setForeach($conjunto, $variavel) {
        $this->foreach = ' ng-repeat="' . $variavel . ' in ' . $conjunto . ' | orderBy: $order:reverseSort" ';
        return $this;
    }

    public function setMenuContexto($menuContexto) {
        $this->menuContexto = $menuContexto;
        return $this;
    }

    function setBindHTML($bindHTML) {
        $this->bindHTML = $bindHTML;
        return $this;
    }

    private function setHead($var, $head) {
        $inf = (($this->infiniteScroll) ? ' infinite-scroll="' . $this->infiniteScroll . '" infinite-scroll-distance="1" infinite-scroll-disabled="working"' : '');
        $responsive = (($this->fixedHeader) ? '' : 'table-responsive');
        $this->head = '<div class="' . $responsive . '"' . $inf . '>';
        $this->head .= '
                <table id="' . $var['id'] . '" class="' . $var['css'] . (($this->fixedHeader) ? 'tableHeaderFixed' : '') . '">';
        if ($head) {
            $this->head .= '<thead><tr class="">';
            foreach ($var['elements'] as $key => $val) {
                $dd = explode("|", $val);
                $css = $dd[1];
                $val = $dd[0];
                $this->head .= '
                    <th scope="col" class="mouseover-hand ' . $css . '" ' . ((!is_int($key)) ? ' ng-click="$order = \'' . $key . '\'; reverseSort = !reverseSort" ' : '') . '>'
                        . $val
                        . ((!is_int($key)) ? '' // imprimir os icones caso tenha sido definido as chaves a ordernar a lista
                        . ' <span ng-show="$order == \'' . $key . '\'"><span ng-show="!reverseSort"><i class="fa fa-chevron-down"></i></span><span ng-show="reverseSort"><i class="fa fa-chevron-up"></i></span></span>'
                        . ' <span ng-show="!$order || $order != \'' . $key . '\'"><i class="fa fa-sort"></i></span>' : '')
                        . '</th>';
            }
            $this->head .= '</tr></thead><tbody>';
        }
    }

    public function setExplode($var) {
        $this->explode = (boolean) $var;
        return $this;
    }

    /**
     * 
     * @param array $line
     * @param type $idLinha
     * @param type $isHtml
     */
    public function addLinha(array $line, $idLinha = false, $isHtml = false) {
        if (count($line) != $this->headTamanho) {
            if (Config::getData('dev')) {
                Log::ver($line);
            }
            die(__METHOD__ . __LINE__ . ': error: Array Line tem ' . count($line) . '  objetos e Header tem ' . $this->headTamanho . ' objetos');
        }
        $idLinha = $idLinha ? $idLinha : substr(md5(date('h-m-s')), 0, 6);
        $onClick = (($this->onClick) ? 'ng-click="' . $this->onClick . '"' : '');
        $this->linha .= '<tr ' . $this->foreach . ' id="' . $idLinha . '" class="' . $this->css . (($this->onClick) ? 'mouseover-hand' : '') . ' table-line" ' . $onClick . ''
                . (($this->menuContexto) ? 'on-long-press="" context-itens="{{' . $this->menuContexto . '}}"' : '')
                . '>';
        /*         * *
          foreach ($line as $val) {
          if ($this->explode) {
          $dd = explode("|", $val);
          if (stripos($val, 'filter:') !== false || stripos($val, 'currency') !== false || stripos($val, 'date:') !== false || stripos($val, 'cep') !== false) {
          $css = str_replace("}}", "", $dd[2]);
          $angular = str_replace('}}', '', $dd[1]);
          $fecha = '}}';
          $val = "$dd[0] | $angular $fecha";
          } else {
          $css = $dd[1];
          $val = $dd[0];
          }
          $this->linha .= '<td class="' . $css . '">' . $val . '</td>';
          } else {
          $this->linha .= '<td ng-bind-html="'.$val.'"></td>';
          }
          }
          $this->linha .= '</tr>';
         */


        foreach ($line as $val) {
            if ($isHtml) {
                $this->linha .= '<td>' . $val . '</td>';
            } else {
                if ($this->explode) {
                    $dd = explode("|", $val);
                    if (stripos($val, 'filter:') !== false || stripos($val, 'currency') !== false || stripos($val, 'date:') !== false || stripos($val, 'cep') !== false) {
                        $css = str_replace("}}", "", $dd[2]);
                        $angular = str_replace('}}', '', $dd[1]);
                        $fecha = '}}';
                        $val = "$dd[0] | $angular $fecha";
                    } else {
                        $css = $dd[1];
                        $val = $dd[0];
                    }
                    //$this->linha .= '<td class="' . $css . '">' . $val . '</td>';
                } else {
                    //$this->linha .= '<td>' . $val . '</td>';
                    //$this->linha .= '<td ng-bind-html="'.$val.'"></td>';
                }

                if (!$this->bindHTML) {
                    $this->linha .= '<td class="' . $css . '">' . $val . '</td>';
                } else {
                    $this->linha .= '<td class="' . $css . '" ng-bind-html="' . str_replace(['{{', '}}'], '', $val) . '"></td>';
                }
            }
        }

        $this->linha .= '</tr>';



        $this->onClick = false;
        return $this;
    }

    public function printTable() {
        return $this->head . $this->linha . '</tbody></table></div>';
    }

    public function actions($id, $dir, $edita = true, $exclui = true, $autoriza = false, $rejeita = false, $extras = false) {
        $out = '<div align="center" id="action_' . $id . '">' .
                (($autoriza) ? '<a href=""><span class="glyphicon glyphicon-check" aria-hidden="true"></span></a>&nbsp;' : '') .
                (($rejeita) ? ' <a href="javascript: void(0)><span class="glyphicon glyphicon-minus" aria-hidden="true"></span></a>&nbsp;' : '') .
                (($edita) ? ' <a href="' . strtolower($dir) . '-edit.php?id=' . $id . '"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></a>' : '') .
                (($exclui) ? ' <a href="javascript:void(0)" onclick="javascript:delete(\'' . $dir . '\', ' . $id . ')" href="' . strtolower($dir) . '-edit.php?action=excluir&id=' . $id . '"><span class="glyphicon glyphicon-trash" aria-hidden="true"></span></a>' : '') .
                (($extras) ? $extras : '');
        '</div>';
        return $out;
    }

    public function menuContextAddOnTd($condicao = false) {
        $con = (($condicao) ? 'ng-show="' . $condicao . '"' : '');
        return '
            <div ' . $con . ' class="text-center text-dark mouseover-hand" style="padding:0px;">
                <div ng-show="' . $this->menuContexto . '.length"><a class="pl-3 pr-3" menu-contexto="" menu-contexto-itens="{{' . $this->menuContexto . '}}">' . Html::iconFafa('ellipsis-v', 2) . '</a></div>            
            </div>';
    }

}
