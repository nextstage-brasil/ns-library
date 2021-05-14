app.directive('configJson', function () {
    var ddo = {};
    ddo.restrict = "E";
    ddo.transclude = false;
    ddo.scope = {
        model: '=',
        grid: '@',
        title: '@'
    };
    ddo.templateUrl = appConfig.urlCloud + 'auto/components/configJson.html';
    ddo.controller = function ($scope, $rootScope) {
        //console.info('configJsonModel', $scope.model);
        $scope.grid = $scope.grid ? $scope.grid : 'col-sm-6 col-md-4 mb-3';

        $scope.snOptions = {
            height: 300,
            lang: 'pt-BR',
            tabsize: 2,
            fontNames: ['Arial', 'Verdana', 'Courier New'],
            toolbar: [
                ['edit', ['undo', 'redo']],
                ['headline', ['style']],
                ['style', ['bold', 'italic', 'underline']],
                ['fontface', ['fontname']],
                ['textsize', ['fontsize']],
                ['fontclr', ['color']],
                ['alignment', ['ul', 'ol', 'paragraph']],
                ['height', ['height']],
                ['table', ['table']],
                ['insert', ['link', 'hr']], //, 'picture', 'video', 'hr']],
                ['view', ['fullscreen', 'codeview']]
            ]
        };

        $scope.$watch('model', function (new_, old_) {
            //console.info('configJson-watch', new_);
            $scope.config = false;
            if (typeof $scope.model !== 'undefined') {
                if (typeof $scope.model.config !== 'undefined') {
                    $scope.config = $scope.model.config;
                    delete $scope.model.config;
                }
            }
            $rootScope.setTypes();
            console.info('CONFIGJSON-CONFIG', $scope.config);
            console.info('CONFIGJSON-DATA-JSON', $scope.model);
            $rootScope.setTooltip();
        });
    };
    return ddo;

});


