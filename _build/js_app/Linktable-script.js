app.controller("LinktableController", function ($rootScope, $scope, DataLoadService, $filter, $timeout, $compile) {
        $scope.entidadeName = 'Linktable'; // nome da entidade deste controller
        $rootScope.log($scope.entidadeName, 'Starter');
        $scope.listGetAux = ['LtRel', 'Usuario', 'LeftLinktable', 'RightLinktable']; // ira definir os cadastros auxiliares necessarios a carga desta pagina. Caso zeja 0, não será carregado
        
        /** Menu de contexto **/
        $scope.LinktableContextItens = [
            //{'link':'LinktableOnClick(Linktable)', 'title': '<i class="fa fa-eye" aria-hidden="true"> </i> Ver'},
            {'link':'LinktableOnEdit(Linktable)', 'title': '<i class="fa fa-edit" aria-hidden="true"> </i> Abrir'},
            {'link':'LinktableRemove(Linktable)', 'title': '<i class="fa fa-trash" aria-hidden="true"> </i> Remover'}
        ];
        
        /** Para tratar os linktables, caso exista **/
        $scope.setVinculosOnEdit = function (id) {};

        // vai injetar as funções padrão
        $rootScope.trataEditOnLoad($scope);

        // Demais funções exclusivas deste componente
});