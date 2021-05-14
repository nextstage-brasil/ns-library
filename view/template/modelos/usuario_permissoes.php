<?php
$file = '../../../library/SistemaLibrary.php';
require_once $file;
?>
<h3 class = "title text-center">{{Usuario.nomeUsuario}} Permissões</h3>
<div class = "row">
    <!--poderes -->
    <div class = "col-sm-6 mb-5">
        <button class = "btn btn-info" ng-click = "UsuarioDoClose()"><i class = "fa fa-chevron-left mr-1"></i> Voltar</button>
        <button class = "btn btn-danger" ng-click = "setAllPoderes(0)">Nenhum <i class = "fa fa-minus ml-1"></i></button>
        <button class = "btn btn-warning" ng-click = "setAllPoderes('1')">Todos <i class = "fa fa-plus mr-1"></i></button>
        <button ng-if = "btnSalvar" class = "btn btn-success" ng-click = "savePoderesGeral()">Salvar <i class = "fa fa-save mr-1"></i></button>
    </div>
    <div class = "col-sm-3 text-right">
        <?= Html::input(['ng-model' => 'filtroIdFuncao'], 'Filtrar por código') ?>
    </div>
    <div class="col-sm-3">
        <?= Html::inputSelectNgRepeat('PerfilUsuario', 'Copiar Perfil', 'NS21', 'Aux.UsuarioTipo', $ngClick, 'copyPoderes()', 'UsuarioTipo') ?>
    </div>
</div>


<div class="clearfix"></div>

<div ng-if="Poderes">
    <ul class="nav nav-tabs" role="tablist">
        <li class="nav-item" ng-repeat="value in Poderes| filter : filtroIdFuncao | orderBy: 'grupo'">
            <a id="nav-item-{{$index}}" class="nav-link mr-1" href="#{{value.grupo}}" role="tab" data-toggle="tab">{{value.grupo}} ({{counters[value.grupo]['setado']}}/{{counters[value.grupo]['total']}})</a>
        </li>
    </ul>
    <div class="tab-content mt-2">
        <div role="tabpanel" class="tab-pane fade-in" ng-repeat="value in Poderes| filter : filtroIdFuncao" id="{{value.grupo}}">
            <div ng-repeat="valor in value.subgrupo" style="height: auto;">
                <div class="row">
                    <p class="col-sm-2 text-right" style="padding-top:10px;"><strong>{{valor.nomesubgrupo}}</strong></p>
                    <p class="col-sm-10" style="float:left; ">
                        <button ng-repeat="v in valor.acoes| filter : filtroIdFuncao" 
                                data-toggle="tooltip" title="{{v.descricao}}"
                                style="margin-top: 10px; margin-right:12px;"
                                class="btn {{v.user && 'btn-success'|| 'btn-default'}} btn-sm" 
                                ng-click="permissoesSave(value, valor, v)" style="margin-right: 5px;"><span ng-if="filtroIdFuncao">{{v.idfuncao}}:</span>{{v.acaonome}}</button>
                    </p>
                    <div class="clearfix"></div>
                </div>
            </div>                    
        </div>
    </div>
    <div class="clearfix mb-5 mt-5"></div>
</div>