app.controller("PostReadController", function ($rootScope, $scope, DataLoadService, $filter, $timeout, $compile) {
        $scope.entidadeName = 'PostRead'; // nome da entidade deste controller
        $rootScope.log($scope.entidadeName, 'Starter');
        $scope.listGetAux = ['Usuario', 'Post']; // ira definir os cadastros auxiliares necessarios a carga desta pagina. Caso zeja 0, não será carregado
        
        /** Menu de contexto **/
        $scope.PostReadContextItens = [
            //{'link':'PostReadOnClick(PostRead)', 'title': '<i class="fa fa-eye" aria-hidden="true"> </i> Ver'},
            {'link':'PostReadOnEdit(PostRead)', 'title': '<i class="fa fa-edit" aria-hidden="true"> </i> Abrir'},
            {'link':'PostReadRemove(PostRead)', 'title': '<i class="fa fa-trash" aria-hidden="true"> </i> Remover'}
        ];
        
        /** Para tratar os linktables, caso exista **/
        $scope.setVinculosOnEdit = function (id) {};

        // vai injetar as funções padrão
        $rootScope.trataEditOnLoad($scope);

        // Demais funções exclusivas deste componente
});