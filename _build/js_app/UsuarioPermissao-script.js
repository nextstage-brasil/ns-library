app.controller("UsuarioPermissaoController", function ($rootScope, $scope, DataLoadService, $filter, $timeout, $compile) {
        $scope.entidadeName = 'UsuarioPermissao'; // nome da entidade deste controller
        $rootScope.log($scope.entidadeName, 'Starter');
        $scope.listGetAux = ['SistemaFuncao', 'Usuario']; // ira definir os cadastros auxiliares necessarios a carga desta pagina. Caso zeja 0, não será carregado
        
        /** Menu de contexto **/
        $scope.UsuarioPermissaoContextItens = [
            //{'link':'UsuarioPermissaoOnClick(UsuarioPermissao)', 'title': '<i class="fa fa-eye" aria-hidden="true"> </i> Ver'},
            {'link':'UsuarioPermissaoOnEdit(UsuarioPermissao)', 'title': '<i class="fa fa-edit" aria-hidden="true"> </i> Abrir'},
            {'link':'UsuarioPermissaoRemove(UsuarioPermissao)', 'title': '<i class="fa fa-trash" aria-hidden="true"> </i> Remover'}
        ];
        
        /** Para tratar os linktables, caso exista **/
        $scope.setVinculosOnEdit = function (id) {};

        // vai injetar as funções padrão
        $rootScope.trataEditOnLoad($scope);

        // Demais funções exclusivas deste componente
});