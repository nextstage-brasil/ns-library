app.directive('nsInput', function () {
    var ddo = {};
    ddo.restrict = "E";
    ddo.transclude = false; // para poder ler o conteudo entre as tags
    ddo.scope = {
        type: '@',
        label: '@',
        classExtra: '@',
        model: '='
    };
    ddo.templateUrl =  appConfig.urlCloud + 'auto/components/nsInput.html';
    ddo.controller = function($scope) {
        $scope.type = $scope.type || 'text';
    };
    return ddo;
});
