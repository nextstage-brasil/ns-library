<?php

class Nav {

    public function __construct() {
        
    }

    /**
     * 
     * @param type $fileConfig Nome do arquivo dentro da pasta /sistema/config/nav que contem o array de configuração, sem extensao
     * @param type $logo Nome do arquivo dentro da pasta sistema/images que contem o logo a ser exibido
     */
    public static function get($fileConfig, $menuUser = false, $logo = 'logo-alone.png') {
        $menu = ((is_array($menuUser)) ? $menuUser : []);
        $menuUser = [];
        $t = '<a class="dropdown-item %s" href="%s"><i class="fa fa-%s"></i> %s</a>';
        foreach ($menu as $item) {
            $item['icon'] = (($item['icon']) ? $item['icon'] : 'angle-right');
            $menuUser[] = sprintf($t, $item['class'], $item['link'], $item['icon'], $item['label']);
        }
        // abrir arquivo enviado 
        $file = Config::getData('path') . '/src/config/nav/' . $fileConfig . '.php';
        Helper::directorySeparator($file);

        if (!file_exists($file)) {
            return "File '$fileConfig' not exists";
        }
        include $file;
//<img src="' . Config::getData('urlView') . '/images/' . $logo . '" alt="" style="max-width:40px;max-height:40px;">
        $out = '
            <nav id="cs-navbar" class="navbar navbar-expand-lg fixed-top my-0 pt-0 pb-0">
            <div class="container">
  <a class="navbar-brand text-warning" href="#"><small style="color:white;">UNIGRACE</small>
  
      </a> 
      <span class="text-secondary d-block d-sm-none text-strong nav_title"></span>
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>

  <div class="collapse navbar-collapse" id="navbarSupportedContent">
    <ul class="navbar-nav mr-auto">' . self::geraNavs($nav) . '</ul>
    
                            <div class="dropdown">
                          <button type="button" class="btn btn-link" data-toggle="dropdown">
                                <i class="fa fa-user"></i> <span class="dropdown-toggle" ng-bind-html="User.firstName"></span>
                          </button>
                          <div class="dropdown-menu">
                          ' . implode(' ', $menuUser) . '
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="' . Config::getData('url') . '/logout"><i class="fa fa-sign-out"></i> Sair</a>
                          </div>
                        </div>
                        </div>

  </div>
  </div>
</nav>';


        return $out;
    }

    private static function geraNavs($nav) {
        $tMaster = '
    <li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="%s" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="fa fa-%s mr-1"></i>
            %s
            </a>';
        $tMasterSemFilho = '
            <li class="nav-item">
    <a class="nav-link" href="%s">
        <i class="fa fa-%s mr-1"></i>%s</a>';
        $tItem = '
                <a id="navlink_%s" class="dropdown-item p-2" href="%s"><i class="fa fa-%s mr-1"></i> %s</a>';
        $tSubMenu = '
            <!-- NOVO SUBMENU -->
                <div class="dropdown-submenu"><a class="dropdown-item p-2 dropdown-toggle" href="#"><i class="fa fa-%s mr-1"></i> %s</a>';
        $out = [];

        foreach ($nav as $value) {
            // cria os dropdown se houver array
            if (is_array($value['dropdown'])) {
                //asort($value['dropdown']);
                $out[] = sprintf($tMaster, $value['link'], $value['icon'], $value['label']);
                $out[] = '<div class="dropdown-menu" aria-labelledby = "navbarDropdown">';
                foreach ($value['dropdown'] as $v) {
                    if (is_array($v['dropdown'])) { // com submenu
                        //asort($v['dropdown']);
                        $out[] = sprintf($tSubMenu, $v['icon'], $v['label']) . '<ul class="dropdown-menu">';
                        /*                         * ** ROTINA PARA ORDENAR O ARRAY POR LABEL *** */
                        foreach ($v['dropdown'] as $v1) {
                            if (is_array($v1['dropdown'])) { // com submenu
                                // ordenar submenu por label
                                //asort($v1['dropdown']);
                                $out[] = sprintf($tSubMenu, $v1['label']) . '<ul class="dropdown-menu">';
                                /*                                 * ** ROTINA PARA ORDENAR O ARRAY POR LABEL *** */
                                foreach ($v1['dropdown'] as $v2) {
                                    $out[] = '<li>' . sprintf($tItem, Helper::sanitize($v['link']), $v2['link'], $v2['icon'], $v2['label']) . '</li>';
                                }
                                $out[] = '</ul><div>';
                            } else {
                                // seria ultimo nivel
                                $out[] = '<li>' . is_array($v1['dropdown']) . sprintf($tItem, Helper::sanitize($v['link']), $v1['link'], $v1['icon'], $v1['label']) . '</li>';
                            }
                        }
                        $out[] = '</ul><div>';
                    } else { // menu simples, sem filhos
                        $out[] = sprintf($tItem, Helper::sanitize($v['link']), $v['link'], $v['icon'], $v['label']);
                    }
                }
                $out[] = "</div>";
            } else {
                $out[] = sprintf($tMasterSemFilho, $value['link'], $value['icon'], $value['label']);
            }
            // fecha li
            $out[] = "</li>";
        }

        return implode(' ', $out);
    }

}
