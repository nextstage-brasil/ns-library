app.directive('nsColabora', function () {
    var ddo = {};
    ddo.restrict = "E";
    ddo.transclude = true;
    ddo.scope = {
        entidade: '@',
        idEntidade: '@',
        idModal: '@',
        closeAction: '&'
    };
    ddo.templateUrl = appConfig.urlCloud + 'auto/components/nsColabora.html';
    ddo.controller = function ($scope, $rootScope, $filter, $compile, $timeout, DataLoadService) {
        console.log('nsColaboraStarter');
        $scope.publicCreateWorking = false;
        $scope.idModal = $scope.idModal ? $scope.idModal : 'nsColaboraModal';
        
        $scope.sharedInit = function () {
            $scope.working = true;
            $rootScope.loading('show', 'Preparando ambiente');
            DataLoadService.getContent('shared', 'init', {entidade: $scope.entidade, id: $scope.idEntidade}, function (data) {
                console.info('Init', data);
                $scope.working = false;
                $rootScope.setAlert(data);
                if (data.error === false) {
                    $scope.EntidadeReferencia = data.content;
                }
            });
        };
        $scope.sharedInit();

        // Método quye buscará todos os shareds desta entidade
        $scope.sharedList = function () {
            $scope.publicExists = false; // ira setar um unico compartilhamento publico
            $scope.workingList = true;
            $scope.Shareds = [];
            $rootScope.loading('show', 'Obtendo dados');
            DataLoadService.getContent('shared', 'sharedList', {entidade: $scope.entidade, id: $scope.idEntidade}, function (data) {
                console.info('Data', data);
                $rootScope.setAlert(data);
                if (data.error === false) {
                    $scope.Shareds = data.content;
                    angular.forEach($scope.Shareds, function (v, k) {
                        if (v.publicShared === 'true') {
                            $scope.publicExists = true;
                        }
                    });
                }
                $scope.workingList = false;
            });
        };
        $scope.sharedList();

        $scope.sharedSet = function ($Shared) {
            console.info('SET', $Shared);
            $scope.Shared = $Shared;
            $scope.Shared.Users = [];
            $scope.comboSearchWriter();
            if ($Shared.publicShared === 'false' && $Shared.idShared > 0) {
                $scope.working = true;
                $rootScope.loading('show', 'Obtendo Integrações Autorizadas');
                DataLoadService.getContent('sharedUser', 'list', {idShared: $Shared.idShared}, function (data) {
                    console.info('getUsersToShared', data);
                    $scope.working = false;
                    $rootScope.setAlert(data);
                    if (data.error === false && data.content !== null && data.content.length > 0) {
                        $scope.Shared.Users = data.content;
                    }
                });
            }


        };

        // vai criar uma colaboração privada
        $scope.sharedNew = function (success) {
            $scope.working = true;
            $rootScope.loading('show', 'Obtendo dados iniciais');
            DataLoadService.getContent('shared', 'new', {entidadeShared: $scope.entidade, valoridShared: $scope.idEntidade}, function (data) {
                $rootScope.loading('hide');
                console.info('New', data);
                $scope.working = false;
                $scope.sharedSet(data.content);
                if (success) {
                    success();
                }
            });
        };

        // vai criar uma colaboração publica, não tem edição
        $scope.sharedNewPublic = function () {
            $scope.working = true;
            $rootScope.loading('show', 'Criando Colaboração Pública');
            DataLoadService.getContent('shared', 'newPublic', {entidadeShared: $scope.entidade, valoridShared: $scope.idEntidade}, function (data) {
                console.info('new Public', data);
                $scope.working = false;
                $rootScope.setAlert(data);
                if (data.error === false) {
                    $scope.sharedList();
                }
            });


        };

        $scope.sharedSave = function (success) {
            $scope.working = true;
            $rootScope.loading('show', 'Salvando dados');
            DataLoadService.getContent('shared', 'save', $scope.Shared, function (data) {
                console.info('SharedSaved', data);
                $scope.working = false;
                $rootScope.setAlert(data);
                if (success) {
                    success(data);
                }
                if (data.error === false) {
                    $scope.Shared = false;
                    $scope.sharedList();
                }
            });
        };
        $scope.sharedCreate = function () {
            $scope.working = true;
            $rootScope.loading('show', 'Preparando ambiente');
            DataLoadService.getContent('shared', 'save', $scope.Shared, function (data) {
                console.info('SharedCreated', data);
                $rootScope.setAlert(data);
                if (!data.error) {
                    $scope.sharedSet(data.content);

                }
            });
        };

        // Exibe balão de confirmação
        $scope.sharedRemove = function () {
            console.log('confirm');
            if (confirm('Confirma excluir colaboração?')) {
                $scope.working = true;
                $rootScope.loading('show', 'Removendo');
                DataLoadService.getContent('shared', 'remove', {idShared: $scope.Shared.idShared}, function (data) {
                    console.info('Data', data);
                    $scope.working = false;
                    $rootScope.setAlert(data);
                    if (data.error === false) {
                        $scope.sharedList();
                        $scope.Shared = false;
                    }
                });
            }
        };


        // Método para adicionar um novo usuário a uma shared
        $scope.userAdd = function (idUsuario) {
            $scope.novoUsuario = false;
            console.log('SharedUserAdd: ' + idUsuario);
            $scope.working = true;
            $rootScope.loading('show', 'Adicionando');
            DataLoadService.getContent('SharedUser', 'save', {idUsuario: idUsuario, idShared: $scope.Shared.idShared}, function (data) {
                console.info('Data', data);
                $scope.working = false;
                $rootScope.setAlert(data);
                if (data.error === false) {
                    $scope.Shared.Users.push(data.content);
                }
                $scope.comboSearchWriter();
            });
        };

        // Alert para confirmar a remoção de um shared
        $scope.userRemove = function (sharedUser, $index) {
            $scope.temp = {
                sharedUser: sharedUser,
                $index: $index
            };
            if (confirm('Confirma excluir integração?')) {
                $scope.Shared.Users.splice($scope.temp.$index, 1);
                DataLoadService.getContent('sharedUser', 'remove', {idSharedUser: $scope.temp.sharedUser.idSharedUser}, function (data) {
                    if (data.error === false) {
                        $scope.comboSearchWriter();
                    } else {
                        $rootScope.setAlert(data);
                    }
                });
            }
        };

        // Gatilho para busca de novos usuários no component <combo-search>
        $scope.$watch("novoUsuario", function (new_, old_) {
            if (new_ > 0) {
                $scope.userAdd(new_);
            }
        });

        $scope.comboSearchWriter = function () {
            var action = "searchNewIntegration/" + $scope.Shared.idShared + "/" + $scope.Shared.valoridShared;
            $("#comboSearchDiv").html($compile('<combo-search label="Integração" model="novoUsuario" initial="" ws-type="SharedUser" ws-action="' + action + '"></combo-search>')($scope));
        };
    };
    return ddo;

});
