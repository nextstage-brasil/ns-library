app.directive('nsPost', function () {
    var ddo = {};
    ddo.restrict = "E";
    ddo.transclude = true;
    ddo.scope = {
        entidade: '@',
        valorid: '@',
    };
    ddo.templateUrl = appConfig.urlCloud + 'auto/components/nsPost.html';
    ddo.controller = function ($scope, $rootScope, $filter, $compile, $timeout, DataLoadService) {
        $scope.idComponent = Math.ceil(Math.random() * Math.pow(10, 10));
        
        $scope.init = function () {
            DataLoadService.getContent('Post', 'list', {entidadePost: $scope.entidade, valoridPost: $scope.valorid}, function (data) {
                console.info('Posts', data);
                $scope.working = false;
                $rootScope.setAlert(data);
                if (data.error === false) {
                    $scope.Posts = [];
                    angular.forEach(data.content, function(v,k){
                        v.date = $filter('date')(v.datePost, 'dd/MM/yyyy') + '<br/>' + v.timePost;
                        $scope.Posts.push(v);
                    });
                    $scope.Posts = $filter('orderBy')($scope.Posts, '-datetime');
                }
            });
        };
        $scope.init();

        $scope.new = function () {
            $scope.Post = {
                entidadePost: $scope.entidade,
                valoridPost: $scope.valorid,
                tituloPost: 'Via qq'
            };
            $("#modal-" + $scope.idComponent).modal({backdrop: 'static'});
        };

        $scope.add = function () {
            $scope.working = true;
            $rootScope.loading('show', 'Processando... ', 'modal');
            DataLoadService.getContent('Post', 'save', $scope.Post, function (data) {
                console.info('Data', data);
                $scope.working = false;
                $rootScope.setAlert(data);
                if (data.error === false) {
                    $scope.init();
                }
            });


        };
    };
    return ddo;
});
