app.controller("PoloController", function ($rootScope, $scope, DataLoadService, $filter, $timeout, $compile) {
    $scope.entidadeName = 'Polo'; // nome da entidade deste controller
    $rootScope.log($scope.entidadeName, 'Starter');
    $scope.listGetAux = []; // ira definir os cadastros auxiliares necessarios a carga desta pagina. Caso zeja 0, não será carregado

    /** Menu de contexto **/
    $scope.PoloContextItens = [
        //{'link':'PoloOnClick(Polo)', 'title': '<i class="fa fa-eye" aria-hidden="true"> </i> Ver'},
        {'link': 'PoloOnEdit(Polo)', 'title': '<i class="fa fa-edit" aria-hidden="true"> </i> Abrir'},
        {'link': 'PoloRemove(Polo)', 'title': '<i class="fa fa-trash" aria-hidden="true"> </i> Remover'}
    ];

    /** Para tratar os linktables, caso exista **/
    $scope.setVinculosOnEdit = function (id) {};

    // vai injetar as funções padrão
    $rootScope.trataEditOnLoad($scope);

    $scope.condicaoUsuario = {
        extrasUsuario: ['isProfessor', 'Sim']
    };

    $scope.setCursosDisponiveis = function () {
        console.clear();
        console.log('$scope.setCursosDisponiveis');
        $("#usuario-tag").html($compile('<ns-tag relacao="polo|usuario" id-left="' + $scope.Polo.idPolo + '" condicoes="condicaoUsuario"></ns-tag>')($scope));
    };

    $scope.init = function () {
        $scope.Aux.Secretarias = [];
        $scope.Aux.Responsaveis = [];
        DataLoadService.getContent('Usuario', 'getAll', {users: 1}, function (data) {
            angular.forEach(data.content, function (v, k) {
                if (v.perfilUsuario === 12) {
                    $scope.Aux.Secretarias.push(v);
                }
                $scope.Aux.Responsaveis.push(v);
            });
        });
        console.info('Aux', $scope.Aux);
    };
    $scope.init();


    // Demais funções exclusivas deste componente
});