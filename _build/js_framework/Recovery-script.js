app.controller('RecoveryController', function ($scope, $rootScope, DataLoadService) {
    console.log('RecoveryController');
    $scope.args = {tokenSenha: $rootScope.getParameterByName(1)};
    $scope.alteracaoEfetuada = false;
    $scope.alteraSenha = function () {
        $scope.working = true;
        $rootScope.loading('show', 'Processando');
        console.info('ARGS', $scope.args);
        DataLoadService.getContent('Usuario', 'alteraSenha', $scope.args, function (data) {
            console.info('Data', data);
            $scope.working = false;
            $rootScope.setAlert(data);
            if (data.error === false) {
                $scope.alteracaoEfetuada = true;
                Swal.fire({
                    icon: 'success',
                    title: 'Tudo certo!',
                    html: 'Sua senha esta cadastra e seu cadastro habilitado para uso!',
                    onClose: function () {
                        location.href = appConfig.urlCloud;
                    }
                });

            }
        });
    };

    //$rootScope.setAlert({error: false}, true);

});

