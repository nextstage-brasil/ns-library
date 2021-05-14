app.directive('nsAddress', function () {
    var ddo = {};
    ddo.restrict = "E";
    ddo.transclude = false;
    ddo.scope = {
        entidade: '@', // entidade que contem os endereços
        valorid: '@', // id da entidade a buscar
        limite: '@' // quantidade máxima de endereços aceita para entidade
    };
    ddo.templateUrl = appConfig.urlCloud + 'auto/components/nsAddress.html';
    ddo.controller = function ($scope, $rootScope, $filter, $compile, $timeout, DataLoadService) {
        console.log('nsAddress Directive Starter');
        $("#map").hide();
        $scope.valorid = parseInt($scope.valorid);
        $scope.limite = $scope.limite ? parseInt($scope.limite) : 9999; // limitado ou ilimitado

        $scope.enderecoContextItens = [
            {'link': 'enderecoSet(endereco)', 'title': '<i class="fa fa-edit" aria-hidden="true"> </i> Editar'},
            {'link': 'enderecoRemove(endereco, $index)', 'title': '<i class="fa fa-trash" aria-hidden="true"> </i> Remover'}
        ];

        $scope.enderecoGetAll = function () {
            $scope.Enderecos = [];
            $scope.working = true;
            DataLoadService.getContent('Endereco', 'getAll', {entidadeEndereco: $scope.entidade, valoridEndereco: $scope.valorid}, function (data) {
                console.info('Data', data);
                $scope.working = false;
                $rootScope.setAlert(data);
                if (data.error === false) {
                    $scope.Enderecos = data.content;
                }
                if ($scope.limite === '1') {
                    $scope.enderecoSet($scope.Enderecos[0] || false);
                }
            });
        };
        $scope.enderecoGetAll();


        // set endereço para edição ou criação de novo
        $scope.enderecoSet = function (Endereco) {
            //console.info('Endereco', Endereco);
            //$scope.Endereco = Endereco;
            //$scope.Endereco.cepEndereco = $filter('cep')($scope.Endereco.cepEndereco);
            $scope.buscaMunicipio = '';
            //$scope.Aux.MunicipioShow = false;
            $scope.buscaCepUf = false;
            //console.info('type', typeof Endereco.idEndereco);
            if (typeof Endereco.idEndereco !== 'number') { // novo
                $scope.titleEdit = 'Novo endereço';
                DataLoadService.getContent('Endereco', 'getNew', {}, function (data) {
                    $scope.Endereco = data.content;
                    $scope.buscaMunicipio = '';
                    $scope.Endereco.statusEndereco = 'INFORMADO';
                    $scope.setMap();
                });
            } else {
                $scope.Endereco = Endereco;
                $scope.setMap();
            }
            $timeout(function () {
                $(".cep").mask('99999-999');
            }, 3000);

        };

        $scope.setMap = function () {
            $('#map').hide();
            if ($scope.Endereco.latitudeEndereco && $scope.Endereco.longitudeEndereco) {
                $rootScope.mapShow('map', $scope.Endereco.latitudeEndereco, $scope.Endereco.longitudeEndereco);
                $('#map').fadeIn();
            } else {
                $('#map').html('Latitude/Longitude não definida');
            }
        };


        $scope.enderecoSave = function () {
            $rootScope.loading('show', 'Salvando Endereço');
            if ($scope.workingObtemCep)   {
                $timeout(function(){
                    $scope.enderecoSave();
                }, 1000);
            }
            $scope.Endereco.entidadeEndereco = $scope.entidade;
            $scope.Endereco.valoridEndereco = $scope.valorid;
            DataLoadService.getContent('Endereco', 'save', $scope.Endereco, function (data) {
                $rootScope.setAlert(data);
                console.info('enderecoSave', data.content);
                if (data.error === false) {
                    $scope.enderecoGetAll();
                    $scope.Enderecos.push(data.content);
                    $scope.Endereco = false;
                }
            });
        };

        $scope.enderecoRemove = function (Endereco, $index) {
            var args = {
                'title': 'Confirma Exclusão de Endereço?',
                'body': '<strong>' + Endereco.ruaEndereco + '</strong>'
            };

            // action caso o click seja positivo
            $('#btnDialogModal').prop('onclick', null).off('click').on('click', function (evt) {
                $rootScope.loading('show', 'Removendo endereço');
                DataLoadService.getContent('Endereco', 'remove', Endereco, function (data) {
                    $rootScope.setAlert(data);
                    if (data.error === false) {
                        $scope.Enderecos.splice($index, 1);
                        $scope.Endereco = false;
                    }
                });
                $("#dialogModal").modal('hide');
            });
            // mostra Dialog
            $rootScope.showDialogModal(args);
        };

        $scope.buscaCep = function () {
            if ($scope.Endereco.cepEndereco !== '') {
                $scope.workingObtemCep = true;
                $rootScope.loading('show', 'Obtendo dados do CEP');
                DataLoadService.getContent('Cep', 'cep', {cep: $scope.Endereco.cepEndereco}, function (data) {
                    console.info('CEP', $scope.Endereco);
                    if (data.error === false && data.content !== false) {
                        $scope.Endereco.ruaEndereco = data.content.logradouro;
                        $scope.Endereco.bairroEndereco = data.content.bairro;
                        $scope.Endereco.Municipio = {
                            nomeMunicipio: '',
                            Uf: {siglaUf: data.content.uf}
                        };


                        $scope.buscaMunicipio = data.content.localidade;
                        $scope.buscaCepUf = data.content.uf;
                        DataLoadService.getContent('Municipio', 'getAll', {nomeMunicipio: data.content.localidade, siglaUf: data.content.uf}, function (data) {
                            console.info('getmuncipio', data);
                            if (data.error === false) {
                                $scope.Endereco.idUf = data.content[0].idUf;
                                $scope.Endereco.idMunicipio = data.content[0].idMunicipio;
                                $scope.Endereco.Municipio.nomeMunicipio = data.content[0].nomeMunicipio;
                            }
                            $rootScope.getGeo({
                                cep: $scope.Endereco.cepEndereco,
                                rua: $scope.Endereco.ruaEndereco,
                                numero: $scope.Endereco.numeroEndereco,
                                bairro: $scope.Endereco.bairroEndereco,
                                municipio: $scope.Endereco.municipioEndereco,
                                estado: $scope.Endereco.ufEndereco,
                                idMunicipio: $scope.Endereco.idMunicipio,
                            }, 'map', function (data) {
                                $scope.Endereco.latitudeEndereco = data.content.latitude;
                                $scope.Endereco.longitudeEndereco = data.content.longitude;
                                $scope.setMap();
                            });
                            $rootScope.setAlert(data);
                            $scope.workingObtemCep = false;
                        });
                    } else {
                        $scope.workingObtemCep = false;
                        data.error = 'CEP ' + $scope.Endereco.cepEndereco + ' não Localizado';
                        $rootScope.setAlert(data, true);
                    }

                });
            }
        };

        $timeout(function () {
            // iniciar AUX - extras ira buscar uma coleção de objetos definidos no controller
            DataLoadService.getContent('App', 'getAux', {}, function (data) {
                $scope.Aux = data.content;
                console.info('AUX INIT', $scope.Aux);
            });
            $(".cep").mask('99999-999');
        }, 3000);

    };
    return ddo;

});
