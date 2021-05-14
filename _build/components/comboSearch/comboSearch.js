app.directive('comboSearch', function () {
    var ddo = {};
    ddo.restrict = "E";
    ddo.transclude = true;
    ddo.scope = {label: '@', model: '=', initial: '@', wsType: '@', wsAction: '@', extras: '=', placeholder: '@', css: '@', btnAdd: '@'};
    ddo.templateUrl = appConfig.urlCloud + 'auto/components/comboSearch.html';
    ddo.controller = function ($scope, $rootScope, $filter, $compile, $timeout, DataLoadService) {
        $scope.wsAction = $scope.wsAction ? $scope.wsAction : 'getByFilter';
        $scope.buscar = $timeout();
        $scope.atuou = false; // armazena se houve busca, para poder mostrar balao negando encontrar algo
        $scope.printBtnAdicionar = $scope.btnAdd === 'false' ? false : true;
        $scope.init = function () {
            //console.log('comboSearch::init()' + $scope.wsType);
            $scope.comboSearch = {
                text: '', id: 0, SearchExtras: $scope.extras
            };
        };
        $scope.init();
        // metodo para executar a busca. pressupoe a existencia de getComboSearch no controller
        $scope.get = function (atualizar) {
            $scope.atuou = false;
            $timeout.cancel($scope.buscar);
            $scope.comboSearch.list = [];
            if (atualizar === true) {
                $scope.buscar = $timeout(function () { // executara após 0,5secs parar digitação
                    //$rootScope.loading('show', 'Buscando por filtros');
                    $scope.working = true;
                    $scope.comboSearch.Search = $scope.initial;
                    //console.info('SEARCH', $scope.comboSearch);
                    DataLoadService.getContent($scope.wsType, $scope.wsAction, $scope.comboSearch, function (data) {
                        console.info('comboSearch', data);
                        console.info('comboSearchlist', data.content.comboSearchList);
                        $scope.atuou = true;
                        if (data.error === false) {
                            $scope.comboSearch.list = data.content.comboSearchList;
                        }
                        $scope.working = false;
                        $rootScope.setAlert(data);
                        console.info('$scope.comboSearch.list', $scope.comboSearch.list);
                    });
                }, 500);
            }
        };
        $scope.set = function (item) {
            $scope.initial = item.value;
            $scope.model = item.id;
            $rootScope.SearchResult = item.id;
            $rootScope.SearchResultText = item.value;
            console.info('MODEL SELECIONADO', $scope.model);
            console.info('MODEL SELECIONADO', $scope.initial);
            $scope.get(false);

        };
        $scope.$watch('model', function (newVal, oldVal) {
            if (newVal <= 0) {
                $scope.initial = '';
            }
        });

        $scope.bryan = function () {
            if (!('webkitSpeechRecognition' in window)) {
                alert('Seu browser não está habilitado para utilizar microfone');
            } else {
                $scope.recognition = new webkitSpeechRecognition();
                var final_transcript = '';
                $scope.recognition.lang = "pt-BR";
                $scope.recognition.continuous = false;
                $scope.recognition.interimResults = true;
                var elem = $('#searchSpeech');
                elem.html('<i class="fa fa-microphone" aria-hidden="true"></i> Diga o que está procurando').fadeIn();
                $rootScope.pulseElem(elem);

                $scope.recognition.onresult = function (event) {
                    var interim_transcript = '';
                    $rootScope.stopPulseElem();

                    for (var i = event.resultIndex; i < event.results.length; ++i) {
                        if (event.results[i].isFinal) {
                            final_transcript += event.results[i][0].transcript;
                            elem.html('');
                        } else {
                            interim_transcript += event.results[i][0].transcript;
                            elem.html(interim_transcript + ' <i class="fa fa-spinner fa-spin" aria-hidden="true"></i>');
                        }
                    }
                    console.log(final_transcript);
                    console.log(interim_transcript);
                };
                $scope.recognition.onend = function () {
                    $scope.initial = final_transcript;
                    $scope.get(true);
                    elem.html('');
                    $rootScope.stopPulseElem();
                };
                $scope.recognition.start();
            }
        };


    };
    return ddo;

});
