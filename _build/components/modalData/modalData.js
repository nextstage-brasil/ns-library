app.directive('modalData', function () {
    var ddo = {};
    ddo.restrict = "E";
    ddo.transclude = false; // para poder ler o conteudo entre as tags
    ddo.scope = {
        modalId: '@',
        modalData: '=', // dados a serem preenchidos
        data: '=', // Grid com os campos
        title: '@',
        actionLabel: '@',
        closeLabel: '@',
        actionFunction: '&'
    };
    ddo.controller = function($scope){
        // controle para saber se mostra o botão de action, e seu label
        $scope.showAction = $scope.actionLabel && true || false;
    };
    ddo.templateUrl = appConfig.urlCloud + 'auto/components/modalData.html';
    return ddo;
});
