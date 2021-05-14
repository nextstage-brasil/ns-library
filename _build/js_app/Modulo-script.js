app.controller("ModuloController", function ($rootScope, $scope, DataLoadService, $filter, $timeout, $compile) {
    $scope.entidadeName = 'Modulo'; // nome da entidade deste controller
    $rootScope.log($scope.entidadeName, 'Starter');
    $scope.listGetAux = []; // ira definir os cadastros auxiliares necessarios a carga desta pagina. Caso zeja 0, não será carregado
    
    $scope.ModuloUploadfileArgs = {
        entidade: 'Modulo'
    };

    /** Menu de contexto **/
    $scope.ModuloContextItens = [
        //{'link':'ModuloOnClick(Modulo)', 'title': '<i class="fa fa-eye" aria-hidden="true"> </i> Ver'},
        {'link': 'ModuloOnEdit(Modulo)', 'title': '<i class="fa fa-edit" aria-hidden="true"> </i> Abrir'},
        {'link': 'ModuloRemove(Modulo)', 'title': '<i class="fa fa-trash" aria-hidden="true"> </i> Remover'}
    ];

    /** Para tratar os linktables, caso exista **/
    $scope.setVinculosOnEdit = function (id) {};

    // vai injetar as funções padrão
    $rootScope.trataEditOnLoad($scope);

    $scope.condicaoUsuario = {
        extrasUsuario: ['isProfessor', 'Sim']
    };

    // Demais funções exclusivas deste componente
});