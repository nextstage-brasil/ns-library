app.controller("GradePoloController", function ($rootScope, $scope, DataLoadService, $filter, $timeout, $compile) {
    $scope.entidadeName = 'GradePolo'; // nome da entidade deste controller
    $rootScope.log($scope.entidadeName, 'Starter');
    $scope.listGetAux = ['Polo', 'Usuario', 'Curso', 'Modulo']; // ira definir os cadastros auxiliares necessarios a carga desta pagina. Caso zeja 0, não será carregado
    

    /** Menu de contexto **/
    $scope.GradePoloContextItens = [
        //{'link':'GradePoloOnClick(GradePolo)', 'title': '<i class="fa fa-eye" aria-hidden="true"> </i> Ver'},
        {'link': 'GradePoloOnEdit(GradePolo)', 'title': '<i class="fa fa-edit" aria-hidden="true"> </i> Abrir'},
        {'link': 'clone(GradePolo)', 'title': '<i class="fa fa-plus" aria-hidden="true"> </i> Clonar'},
        {'link': 'GradePoloRemove(GradePolo)', 'title': '<i class="fa fa-trash" aria-hidden="true"> </i> Remover'}
    ];

    /** Para tratar os linktables, caso exista **/
    $scope.setVinculosOnEdit = function (id) {
    };


    $scope.getLists = function () {
        $scope.Lists = {};
        DataLoadService.getContent('GradePolo', 'gdf', {
            'dataInicial': '2000-01-01',
            'dataFinal': '2000-01-01',
            'idCurso': $scope.GradePolo.idCurso
        }, function (data) {
            console.info('Data', data);
            $scope.working = false;
            $rootScope.setAlert(data);
            if (data.error === false) {
                $scope.Lists.Modulos = $filter('orderBy')(data.content.modulos, 'nomeModulo');
                $scope.Lists.GradePolos = data.content.list;
                $scope.Lists.Professores = data.content.Professores;
                $scope.Lists.AllProfs = data.content.AllProfs;
            }
        });
    };



    // Ao trocar o idCurso, atualizar os poos,modulos e professores disponiveis
    $scope.$watch('GradePolo.idCurso', function (n, o) {
        if (n !== 0 && n) {
            $scope.getLists();
        }
    });


    // Ao alterar o polo, listar somente os modulos que aquele polo ministra, com base nos professores
    $scope.$watch('GradePolo.idPolo', function (n, o) {
        if (n !== 0 && n) {
            $timeout(function () {
                $scope.ITENS = {};
                $scope.Modulos = [];
                $itens = {};
                angular.forEach($scope.Lists.AllProfs, function (v, k) {
                    if (v.idPolo === n && !$scope.ITENS['_' + v.idModulo]) {
                        $scope.ITENS['_' + v.idModulo] = true;
                        var $item = $filter('filter')($scope.Lists.Modulos, {'idModulo': v.idModulo}, true)[0];
                        $scope.Modulos.push($item);
                    }
                });
                console.info('$scope.Modulos', $scope.Modulos);
                console.info('GradePolo', $scope.GradePolo);
            }, 1000);
        }
    });

    $scope.$watch('GradePolo.idModulo', function (n, o) {
        if (n !== o && n) {
            $timeout(function () {
                $scope.Professores = $filter('filter')($scope.Lists.AllProfs, {idPolo: $scope.GradePolo.idPolo, idModulo: $scope.GradePolo.idModulo}, true);
            }, 500);
        }
    });



    // vai injetar as funções padrão
    $rootScope.trataEditOnLoad($scope);
    
    $rootScope.setDateRange(moment().subtract(1, 'month').startOf('month').format('YYYY-MM-DD'), moment().add(1, 'month').endOf('month').format('YYYY-MM-DD'), $scope.Args);

    $timeout(function () {
        $(".nsTemplateSearch").hide();
        $(".nsTemplateMyFilters").removeClass("col-sm-7").addClass("col-sm-10");
    }, 100);

    // Demais funções exclusivas deste componente
    
    $scope.clone = function(item)   {
        $scope.GradePoloOnEdit({idGradePolo : -1});
        $timeout(function () {
            $scope.GradePolo = angular.copy(item);
            $scope.GradePolo.idGradePolo = null;
        }, 500);
    };
});