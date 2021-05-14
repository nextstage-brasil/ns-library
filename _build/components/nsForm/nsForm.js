app.directive('nsForm', function () {
    var ddo = {};
    ddo.restrict = "E";
    ddo.transclude = true; // para poder ler o conteudo entre as tags
    ddo.scope = {
        name: '@',
        method: '@',
        action: '@',
        onsubmit: '@'
    };
    ddo.templateUrl = appConfig.urlCloud + 'auto/components/nsForm.html';
    ddo.controller = function($scope) {
        $scope.name = $scope.name || 'form_'+Math.ceil(Math.random() * Math.pow(10,10));
        $scope.method = $scope.method || 'post';
        $scope.onsubmit = $scope.onsubmit || 'return false;';
    };
    return ddo;
});
