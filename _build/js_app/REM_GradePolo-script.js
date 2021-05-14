app.controller("GradePoloController", function ($rootScope, $scope, DataLoadService, $filter, $timeout, $compile) {
    $scope.entidadeName = 'GradePolo'; // nome da entidade deste controller
    $rootScope.log($scope.entidadeName, 'Starter');
    $scope.listGetAux = ['Polo', 'Leciona', 'Grade']; // ira definir os cadastros auxiliares necessarios a carga desta pagina. Caso zeja 0, não será carregado

    /** Menu de contexto **/
    $scope.GradePoloContextItens = [
        //{'link':'GradePoloOnClick(GradePolo)', 'title': '<i class="fa fa-eye" aria-hidden="true"> </i> Ver'},
        {'link': 'GradePoloOnEdit(GradePolo)', 'title': '<i class="fa fa-edit" aria-hidden="true"> </i> Abrir'},
        {'link': 'GradePoloRemove(GradePolo)', 'title': '<i class="fa fa-trash" aria-hidden="true"> </i> Remover'}
    ];


    /** Para tratar os linktables, caso exista **/
    $scope.setVinculosOnEdit = function (id) {};

    // vai injetar as funções padrão
    $rootScope.trataEditOnLoad($scope);

    // Demais funções exclusivas deste componente

});