app.directive('nsApp', function () {
    var ddo = {};
    ddo.restrict = 'E';
    ddo.transclude = false; // para poder ler o conteudo entre as tags
    ddo.scope = {};
    ddo.templateUrl = appConfig.urlCloud + '/src/components_public/nsApp/nsApp.html';
    ddo.controller = function ($rootScope, $scope, DataLoadService, $filter, $timeout, $compile) {
    $scope.entidadeName = 'App'; // nome da entidade deste controller
    $rootScope.log($scope.entidadeName, 'Starter');
    $scope.listGetAux = []; // ira definir os cadastros auxiliares necessarios a carga desta pagina. Caso zeja 0, não será carregado

    /** Menu de contexto **/
    $scope.table1ContextMenu = [
        //{'link':'AppOnClick(App)', 'title': '<i class="fa fa-eye" aria-hidden="true"> </i> Ver'},
        {'link': 'AppOnEdit(App)', 'title': '<i class="fa fa-edit" aria-hidden="true"> </i> Abrir'},
        {'link': 'AppRemove(App)', 'title': '<i class="fa fa-trash" aria-hidden="true"> </i> Remover'}
    ];

    /** Para tratar os linktables, caso exista **/
    $scope.setVinculosOnEdit = function (id) {};

    // vai injetar as funções padrão
    $rootScope.trataEditOnLoad($scope);

    // Correção do formulário de pesquisa
    $timeout(function () {
        $(".floating-label").css({position: 'relative', top: '-60px', left: '0px', 'font-weight': 'normal'});
        $(".page-title").hide();
        $("#tipo_busca_1").trigger('click');
        $scope.Args.tipo_busca = 'any';
    }, 500);

    // Evento para fechar emnu de contexto a cada click na tela
    $('#search-bar').on('keyup', function (evt) {
        // controle da tecla enter
        var key_code = evt.keyCode ? evt.keyCode :
                evt.charCode ? evt.charCode :
                evt.which ? evt.which : void 0;
        // controle do enter
        if (key_code === 13) {
            $scope.search();
        }
    });
    $('#search-bar').focus();

    $scope.md5 = function (d) {
        return MD5(d);
    };

    $scope.search = function () {
        $scope.Results = [];
        $scope.timeElapsed = 0;
        $scope.working = true;
        $rootScope.loading('show', 'Obtendo dados');
        DataLoadService.getContent('App', 'search', $scope.Args, function (data) {
            $rootScope.loading();
            //console.info('Data', data);
            //$scope.working = false;
            //$rootScope.setAlert(data);
            if (data.error === false) {
                $scope.Results = data.content;
                $scope.timeElapsed = data.timeElapsed;
            }
        });
        console.info('ARGS', $scope.Args);
    };

    $scope.details = function (item) {
        $scope.ResultFilter = {};
        $("#collapseDetails").collapse('show');
        $("#div-search").hide();
        $("#table-results").hide();

        $scope.working = true;
        $rootScope.loading('show', 'Processando');
        DataLoadService.getContent('App', 'details', {nome: item.nome}, function (data) {
            console.info('Data', data);
            $scope.working = false;
            $rootScope.setAlert(data);
            if (data.error === false) {
                $scope.Registros = angular.copy(data.content.registros);
                delete (data.content.registros);
                $scope.Item = data.content;
                $scope.Item.nome = item.nome;
                $scope.ResultFilter = angular.copy($scope.Item);
            }
        });

    };
    $scope.detailsClose = function () {
        $("#div-search").show();
        $("#table-results").show();
        $("#collapseDetails").collapse('hide');
        $scope.Item = {};
        $scope.Registros = [];
    };

    // filtra os resultados a serem exibidos conforme filtros clicados
    $scope.filterList = function (item) {
        if ($scope.ResultFilter.Maes.indexOf(item.mae) > -1
                &&
                $scope.ResultFilter.Bases.indexOf(item.base) > -1
                &&
                $scope.ResultFilter.Nascimentos.indexOf(item.nascimento) > -1
                &&
                $scope.ResultFilter.Cpfs.indexOf(item.cpf) > -1

                ) {
            return true;
        } else {
            return false;
        }
    };

    $scope.contaBase = function (item, $tipo) {
        switch ($tipo) {
            case 'mae':
                $filtro = {mae: item};
                break;
            case 'nascimento':
                $filtro = {nascimento: item};
                break;
            case 'cpf':
                $filtro = {cpf: item};
                break;
            case 'base':
                $filtro = {base: item};
                break;
            default:
                return;
                break;
        }
        $n = $filter('filter')($scope.RegistrosFilter, $filtro, true);
        return $n.length;
    };



    // Adiciona ou remove os filtros na tela de detlahes. Após execução, renderiza a lista a ser exibida
    $scope.setFilter = function (tipo, valor) {
        $removido = false;
        angular.forEach($scope.ResultFilter[tipo], function (val, key) {
            if (val === valor) { // esta aqui remover e finalizar
                $scope.ResultFilter[tipo].splice(key, 1);
                $removido = true;
            }
        });
        if (!$removido) { // se não foi removido, é pra ser adicionado
            $scope.ResultFilter[tipo].push(valor);
        }
        console.info('result-filter', $scope.ResultFilter);
    };


    // temporario:
    /*
     $scope.Args.search = 'cristofer batschauer';
     $scope.search();
     $timeout(function () {
     $(".btn-info").trigger('click');
     }, 1500);
     */

}        ;
    return ddo;
});
