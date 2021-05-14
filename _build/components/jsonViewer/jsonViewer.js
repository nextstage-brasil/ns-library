app.directive('jsonViewer', function () {
    var ddo = {};
    ddo.restrict = "E";
    ddo.transclude = true;
    ddo.scope = {
        model: '=',
        grid: '@'
    };
    ddo.templateUrl = appConfig.urlCloud + 'auto/components/jsonViewer.html';
    ddo.controller = function ($scope, $rootScope) {
        console.info('configJsonModel', $scope.model);
        $scope.grid = $scope.grid ? $scope.grid : 'col-sm-6 mb-3';


        //$scope.$watch('model', function (new_, old_) {
//            console.info('configJson-watch', new_);
            
            
            if (typeof $scope.model !== 'undefined') {
                if (typeof $scope.model.config !== 'undefined') {
                    $scope.config = $scope.model.config;
                    delete $scope.model.config;
                }
            }
            $rootScope.setTypes();
            console.info('CONFIG', $scope.config);
            
            
        //});
    };
    return ddo;

});
