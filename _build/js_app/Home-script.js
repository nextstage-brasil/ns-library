app.controller("HomeController", function ($rootScope, $scope, DataLoadService, $filter, $timeout, $compile) {
    $scope.entidadeName = 'App'; // nome da entidade deste controller
    $rootScope.log($scope.entidadeName, 'Starter');
    $scope.listGetAux = []; // ira definir os cadastros auxiliares necessarios a carga desta pagina. Caso zeja 0, não será carregado

    /** Para tratar os linktables, caso exista **/
    $scope.setVinculosOnEdit = function (id) {};

    // vai injetar as funções padrão
    $rootScope.trataEditOnLoad($scope);

    // Demais funções exclusivas deste componente
    $timeout(function () {
        $(".page-title").html('');
        $scope.init();
    }, 100);

    $scope.init = function () {
        DataLoadService.getContent('app', 'homeGetCounters', {}, function (data) {
            $scope.totais = data.content;
        });
    };

    $scope.nav = function (rota) {
        $rootScope.loading('show', 'Navegando', 'modal');
        window.location.href = appConfig.urlCloud + rota;
    };
});