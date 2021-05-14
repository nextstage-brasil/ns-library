<?php

require_once '../../SistemaLibrary.php';
$cardEndereco = Card::basic([
            Card::getModelBasic('Municipio', 'Municipio.nomeMunicipio + \'/\'+endereco.Municipio.Uf.siglaUf', false, 'enderecoSet(endereco)'),
            Card::getModelBasic('', 'nomeEndereco', 'text-center text-italic'),
            Card::getModelBasic('CEP', 'cepEndereco | cep'),
            Card::getModelBasic('End.', 'ruaEndereco+\' \'+endereco.numeroEndereco+\' \'+endereco.complementoEndereco'),
            Card::getModelBasic('Bairro', 'bairroEndereco')
                ], 'endereco', false, 'enderecoContextItens', 'Enderecos', 'Nenhum endereço localizado');

$form = [
    Form::getModel(Html::input(['ng-model' => 'Endereco.nomeEndereco', 'ng-change' => $ngChange], 'TITULO'), 'col-sm-4'),
    Form::getModel(Html::input(['ng-model' => 'Endereco.cepEndereco', 'ng-blur' => 'buscaCep()', 'class' => 'cep'], 'CEP'), 'col-sm-2'),
    Form::getModel(Html::input(['ng-model' => 'Endereco.numeroEndereco', 'ng-change' => $ngChange], 'NÚMERO'), 'col-sm-2'),
    Form::getModel(Html::input(['ng-model' => 'Endereco.complementoEndereco', 'ng-change' => $ngChange], 'COMPLEMENTO'), 'col-sm-2'),
    //Form::getModel(Html::input(['ng-model' => 'Endereco.statusEndereco', 'ng-change' => $ngChange], 'STATUS'), 'col-sm-5'),
    Form::getModel(Html::inputSelectNgRepeat('Endereco.statusEndereco', 'STATUS', 'Endereco.statusEndereco_ro', 'Aux.StatusPessoaEndereco', $ngClick, $ngChange, 'StatusPessoaEndereco'), 'col-sm-2'),
    Form::getModel(Html::input(['ng-model' => 'Endereco.ruaEndereco', 'ng-change' => $ngChange], 'RUA , AVENIDA...'), 'col-sm-8'),
    Form::getModel(Html::input(['ng-model' => 'Endereco.bairroEndereco', 'ng-change' => $ngChange], 'BAIRRO'), 'col-sm-4'),
    Form::getModel(Html::comboSearch('MUNICIPIO', 'Endereco.idMunicipio', 'Endereco.Municipio.nomeMunicipio+\'/\'+Endereco.Municipio.Uf.siglaUf', 'Municipio', 'getAll'), 'col-sm-6'),
    Form::getModel(Html::input(['ng-model' => 'Endereco.latitudeEndereco', 'ng-change' => $ngChange], 'LATITUDE'), 'col-sm-3'),
    Form::getModel(Html::input(['ng-model' => 'Endereco.longitudeEndereco', 'ng-change' => $ngChange], 'LONGITUDE'), 'col-sm-3'),
    Form::getModel(Html::input(['ng-model' => 'Endereco.pontoReferenciaEndereco', 'ng-change' => $ngChange], 'PONTO REFERENCIA'), 'col-sm-12')
];
$template = new AdminTemplate($entidade, $title, $tableFiltros, $filtros, $entidadeSpan, new Table([]));
$template->setForm($form);

$html = '
<div class="container">
    ' . Html::loadingInLine('Obtendo endereços') . '
    <div ng-show="!Endereco && !working">
        <div class="text-left" ng-show="Enderecos.length < limite">
            <a ng-click="enderecoSet({})" class="btn btn-info text-center"><i class="fa fa-plus-circle" aria-hidden="true"></i> Novo Endereço</a>
        </div>
        <br/>
        
        <div ng-show="Enderecos.length">
            ' . $cardEndereco . '
        </div>
    </div>
    <div ng-show="Endereco">
        <h5>Endereço</h5>
        <br/>
        <div class="row">
            ' . $template->printForm() . '
        </div>

        

        <div class="col-sm-12 text-center mt-1 mb-5">
            <button ng-if="limite>1" class="btn btn-info text-right" ng-click="Endereco = false"><i class="fa fa-times" aria-hidden="true"> </i> Fechar</button>
            <button class="btn btn-success" ng-click="enderecoSave()"><i class="fa fa-check" aria-hidden="true"></i> Salvar Endereço</button>
        </div>
        
<div id="map" style="height: 400px;width: 100%;"></div>
    </div>
</div>

<div class="clearfix"></div>
        ';

Helper::saveFile(SistemaLibrary::getPath() . '/components/nsAddress/nsAddress.html', false, $html, 'SOBREPOR');

echo 'Arquivo Atualizado.<br/>';
echo SistemaLibrary::getPath() . '/components/nsAddress/nsAddress.html';

