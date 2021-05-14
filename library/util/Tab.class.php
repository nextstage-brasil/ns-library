<?php

class Tab {

    public static function getModel($id, $label, $content, $ngif=false) {
        $span = '<span class="badge badge-info ml-1"></span>';
        $out = ['id' => $id, 'label' => $label . $span, 'content' => $content];
        if ($ngif) {
            $out['ng-if'] = $ngif;
        }
        return $out;
    }

    /** Alteração efetuada em 12/03/2018, adaptação para bootstrap 4 * */
    public static function printTab($tabs) {
        $templateUl = '<ul class="nav nav-tabs" role="tablist">%s</ul>';
        $templateContent = '<div class="tab-content mt-2">%s</div>';
        $templateLi = '<li class="nav-item" %s><a class="nav-link %s mr-1" href="#%s" role="tab" data-toggle="tab">%s</a></li>';
        $templateTabPanel = '<div role="tabpanel" class="tab-pane fade-in %s" id="%s">%s</div>';
        $li = [];
        $panel = [];
        $active = 'active';
        // montagem de paineis
        foreach ($tabs as $val) {
            $ngif = '';
            if ($val['ng-if']) {
                $ngif = 'ng-if="' . $val['ng-if'] . '"';
            }
            $li[] = sprintf($templateLi, $ngif, $active, $val['id'], $val['label']);
            $panel[] = sprintf($templateTabPanel, $active, $val['id'], $val['content']);
            $active = '';
        }
        // saida
        $out = sprintf($templateUl, implode(' ', $li))
                . sprintf($templateContent, implode(' ', $panel));
        return $out;
    }

    public static function vinculoSetTabs($vinculos, $entidade, $fileJS = false) {
        $labelCampoNeeded = "$entidade.id$entidade>0";
        $js = [];
        $out = [];
        $js[] = '$scope.setVinculosOnEdit = function (id) {';
        asort($vinculos);
        foreach ($vinculos as $vinculo) {
            if ($vinculo === 'arquivos') {
                $out[] = array_merge(Tab::getModel('vinculoFiles', 'Arquivos', Html::uploadFile($entidade)), ['ng-if' => $labelCampoNeeded]);
                $js[] = '$("#arquivos").html($compile(\'<upload-file entidade="\'+$scope.entidadeName+\'" valorid="\' + id+ \'"></risco>\')($scope));';
            } else {
                $label = Config::getData('titlePagesAliases', ucwords($vinculo));
                $idTab = 'vinculo_' . $vinculo;
                $idDiv = 'vinculo' . ucwords($vinculo);
                $out[] = array_merge(Tab::getModel($idTab, $label, '<div id = "' . $idDiv . '"></div>'), ['ng-if' => $labelCampoNeeded]);
                if ($vinculo === 'risco') {
                    $js[] = '$("#vinculoRisco").html($compile(\'<risco entidade="' . Helper::upper($entidade) . '" id="\' + id+ \'"></risco>\')($scope));';
                } else {
                    $js[] = '$("#' . $idDiv . '").html($compile(\'<linktable title="' . $label . '" view="false" grid-cards="col-sm-4" text-search="O que deseja buscar?" relacao="' . $entidade . '|' . $vinculo . '" id-left="\' + id + \'"/>\')($scope));';
                }
            }
        }
        $js[] = '};';
        if ($fileJS) {
            $txt = file_get_contents($fileJS);
            $string = explode('$scope.setVinculosOnEdit = function (id) {', $txt);
            if (count($string) > 1) {
                $inicio = $string[0];
                $string = explode('};', $string[1]);
                unset($string[0]);
                $fim = implode("};\n", $string);
            } else {
                $inicio = 'NÃO LOCALIZADO FUNCAO PARA ATUALIZAR.';
                $fim = '$scope.setVinculosOnEdit = function (id) {};';
                $fim .= '$scope.setVinculosOnEdit(v.id' . $entidade . ');';
                $js = [];
            }
            //Helper::saveFile($fileJS.'-OLD', '', $inicio . implode("\n", $js) . $fim);
            Helper::saveFile($fileJS, '', $inicio . implode("\n", $js) . $fim);
            Log::ver('Novo JS Salvo com ' . ((count($js) > 0) ? 'sucesso!' : 'ERRO. Verifique o arquivo'));
        }
        return $out;
    }

}
