app.directive('nsTimeline', function () {
    var ddo = {};
    ddo.restrict = "E";
    ddo.transclude = false;
    ddo.scope = {
        list: '=',
        click: '&',
        context: '='
    };
    ddo.templateUrl = appConfig.urlCloud + 'auto/components/nsTimeline.html';
    ddo.controller = function ($scope, $rootScope, $filter, $compile, $timeout, DataLoadService) {
        $scope.idComponent = Math.ceil(Math.random() * Math.pow(10, 10));
    };
    return ddo;

});
