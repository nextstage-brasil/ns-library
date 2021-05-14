app.controller("IndispController", function ($rootScope, $scope, DataLoadService, $filter, $timeout, $compile) {
    $scope.entidadeName = 'Indisp'; // nome da entidade deste controller
    $rootScope.log($scope.entidadeName, 'Starter');
    $scope.listGetAux = ['Usuario']; // ira definir os cadastros auxiliares necessarios a carga desta pagina. Caso zeja 0, não será carregado

    /** Menu de contexto **/
    $scope.IndispContextItens = [
        //{'link':'IndispOnClick(Indisp)', 'title': '<i class="fa fa-eye" aria-hidden="true"> </i> Ver'},
        {'link': 'IndispOnEdit(Indisp)', 'title': '<i class="fa fa-edit" aria-hidden="true"> </i> Abrir'},
        {'link': 'IndispRemove(Indisp)', 'title': '<i class="fa fa-trash" aria-hidden="true"> </i> Remover'}
    ];

    $scope.IndispMinDate = HOJE; // vem no PHP
    //$scope.Hoje = HOJE;


    /** Para tratar os linktables, caso exista **/
    $scope.setVinculosOnEdit = function (id) {};

    // vai injetar as funções padrão
    $rootScope.trataEditOnLoad($scope);

    // Demais funções exclusivas deste componente
});