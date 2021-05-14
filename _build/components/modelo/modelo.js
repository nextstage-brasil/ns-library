app.directive('componenteModelo', function () {
    var ddo = {};
    ddo.restrict = "E";
    ddo.transclude = true;
    ddo.scope = {
        modalId: '@',
        title: '@',
        confirmAction: "&",
        cancelAction: "&"
    };
    ddo.templateUrl = appConfig.urlCloud + 'auto/components/template.html';
    ddo.controller = function ($scope, $rootScope, $filter, $compile, $timeout, DataLoadService) {};
    return ddo;

});
