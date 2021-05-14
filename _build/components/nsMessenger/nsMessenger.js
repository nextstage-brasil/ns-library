app.directive('nsMessenger', function () {
    var ddo = {};
    ddo.restrict = "E";
    ddo.transclude = true;
    ddo.scope = {
        modalId: '@',
        title: '@',
        confirmAction: "&",
        cancelAction: "&"
    };
    ddo.templateUrl = appConfig.urlCloud + 'auto/components/nsMessenger.html';
    ddo.controller = function ($scope, $rootScope, $filter, $compile, $timeout, DataLoadService) {
        $scope.element = '';
        //console.info('nsMessenger starter!');
        $scope.Mensagems = [];
        $scope.userLogado = $rootScope.User.idUsuario;
        $scope.inputMessage = Date.now();
        $scope.timeOutRefresh = '';
        $scope.refresh = {
            getMessages: 10 // seconds
        };
        $scope.refresh.getConversas = $scope.refresh.getMessages + 10;
        $scope.pagina = 0;
        //$scope.notification = appConfig.urlCloud + '/view/notification.mp3';

        // setar altura do messenger de conversas
        //$("#comunicador").css({height: '30px'}, 500).show();
        //$("#comunicadorConversas").css({height: (window.innerHeight - 100) + 'px'});


        $scope.getConversas = function () {
            //console.log('status'+sessionStorage.getItem('onupload'));

            // controle para não chamar enquanto houver outras conexões em andamento
            if (sessionStorage.getItem('onupload') === 'on') {
                //console.info('Envio de fotos em andamento. Adiado busca de mensagens');
                $timeout(function () {
                    $scope.getConversas();
                }, $scope.refresh.getConversas * 5 * 1000);
                return;
            }


            $scope.working = true;
            //$rootScope.loading('show', 'Obtendo conversas');
            DataLoadService.getContent("Mensagem", "getConversas", {}, function (data) {
                //console.info('CONVERSAS', data.content);
                $scope.idUserGetConversa = false;
                if (data.error === false) {
                    $scope.Mensagems = data.content.messages;
                    $rootScope.nsMessengerNotRead = data.content.totalNotRead;
                }
                //$rootScope.setAlert(data);
                $scope.working = false;
                $timeout(function () {
                    $scope.getConversas();
                }, $scope.refresh.getConversas * 1000);
            });
        };
        $timeout(function () {
            $scope.getConversas();
        }, 2500);


        // método para atender ao clic na conversa. Ira mostrar na div mais antiga em tela
        $scope.showTalk = function (conversa) {
            $timeout.cancel($scope.timeOutRefresh);
            $scope.lastheight = false;
            $scope.Talk = {};
            $scope.Talk.idUser = conversa.idUsuario;
            $scope.ConversaAtiva = conversa;
            $scope.pagina = 0;
            $scope.getMessages(); // vai set idUser, e mostrar a conversa
            $scope.getConversas();
            $timeout(function () {
                $(".draggable").draggable();
                $(".nsMessengerConversaContainer").animate({height: (window.innerHeight - 100) + 'px'});
            }, 500);

        };




        // #################################################################################################################################

        // Obtem as mensagens
        $scope.getMessages = function (pagina) {


            // controle para não chamar enquanto houver outras conexões em andamento
            if ($rootScope.EnviandoFotos === true) {
                //console.info('Envio de fotos em andamento. Adiado busca de mensagens');
                $scope.timeOutRefresh = $timeout(function () {
                    $scope.getMessages();
                }, $scope.refresh.getMessages * 5 * 1000);
                return;
            }

            if ($scope.Talk.idUser > 0) {
                $scope.workingTalk = true;
                DataLoadService.getContent('Mensagem', 'getMessages', {pagina: pagina, id: $scope.Talk.idUser, lastUpdate: $scope.Talk.lastUpdate}, function (data) {
                    $scope.Talk.lastUpdate = data.content.lastUpdate; // a primeira enviada nao tem.. pegar os ultimos
                    //console.info('Data', data);
                    $scope.workingTalk = false;
                    if (data.error === false) {
                        if (!$scope.Talk.messages) { // primeira chamada
                            $scope.Talk = data.content;
                            $scope.watchScrollTop();
                        } else {
                            // controle de paginacao
                            if (pagina && !data.content.messages.length) {
                                $scope.pagina = -1; // ja leu todas as mensagens anteriores
                            }
                            // limpar mensagens incluidas na mão
                            $scope.Talk.messages = $rootScope.arrayFilter($scope.Talk.messages, 'data', 'Agora');
                            angular.forEach(data.content.messages, function (v, k) {
                                $scope.Talk.messages.push(v);
                                //console.info('NOVA MENSAGEM', v);
                                //console.info($scope.userLogado);
                                if (!pagina && v.destId !== $scope.userLogado) {
                                    $scope.novaMensagem = true;
                                }
                            });
                            $scope.Talk.status = data.content.status;
                        }
                        $scope.scrollToBottom();
                    }
                    $scope.timeOutRefresh = $timeout(function () {
                        $scope.getMessages();
                    }, $scope.refresh.getMessages * 1000);
                });
            } else {
                $timeout.cancel($scope.timeOutRefresh);
            }
        };



        // para observar o scrool,l no topo e obter conversas antigas
        $scope.watchScrollTop = function () {
            // autoload para scroll on top
            var div = $(".nsMessengerTalkMessengers");
            var limite = 30;
            var distancia = 0;
            angular.element(div).bind("scroll", function () {
                ////console.info('autocrollTop');
                distancia = div.prop("scrollTop");
                ////console.info('Distancia' + distancia);
                if (distancia < parseInt(limite) && $scope.pagina >= 0 && !$scope.workingTalk) {
                    $scope.pagina++;
                    $scope.getMessages($scope.pagina);
                }
            });
        };


        //$scope.getMessages();
        $('#' + $scope.inputMessage).focus();


        $scope.nsMessengerTalkClose = function (element) {
            $scope.Talk = {};
            $scope.ConversaAtiva = {};
            $timeout.cancel($scope.timeOutRefresh);
        };

        $scope.addMessage = function () {
            var msg = {
                data: 'Agora',
                destId: parseInt($scope.Talk.idUser),
                index: 99999999,
                texto: $scope.nsMessengerMessage
            };
            $scope.nsMessengerMessage = '';
            $('#' + $scope.inputMessage).focus();// limpar mensagem
            $index = $scope.Talk.messages.length;
            $scope.$apply(function () {
                $scope.Talk.messages.push(msg);
                $scope.scrollToBottom();
            });
            DataLoadService.getContent('Mensagem', 'save', msg, function (data) {
                //console.info('Mensagem Salva', data);
                if (data.error !== false) {
                    $rootScope.setAlert(data);
                }
            });
            //console.info('MESSAGES', $scope.Talk.messages);
        };

        $scope.scrollToBottom = function () {
            var div = $(".nsMessengerTalkMessengers");
            //console.info('scroll nao rolado: ' + $scope.lastheight);
            //console.info('SCROLL ' + div.prop("scrollTop"));
            var diff = (div.prop("scrollTop") - $scope.lastheight) * -1;
            if (diff < 1 || !$scope.lastheight) { // não esta rolada a barra...
                $scope.novaMensagem = false;
            }
            $timeout(function () {
                if (diff < 1 || !$scope.lastheight) { // não esta rolada a barra...
                    div.animate({scrollTop: div.prop("scrollHeight")}, 100);

                    $scope.novaMensagem = false;
                }
                $scope.lastheight = angular.copy(parseInt(div.prop("scrollHeight") - div.css('height').replace('px', '')));
            }, 500);
        };

        /** autoscroll top top **/
        /********************** */

        $scope.showNovasMensages = function () {
            $scope.lastheight = false;
            $scope.scrollToBottom();
        };


        $timeout(function () {
            $(".nsMessengerConversaContainer").css({
                'height': (window.innerHeight - 200) + 'px',
                //'margin-top': '-20px'
            });
            $("#comunicador").draggable();
            var hConversa = $(".nsMessengerConversaContainer").css('height').replace('px', '') - $("#nsMessengerHeader_" + $scope.inputMessage).css('height').replace('px', '') - 50;
            $(".nsMessengerTalkMessengers").css({
                'height': hConversa + 'px'
            });
            // trigger on keypress enter
            $('#' + $scope.inputMessage).on('keyup', function (e) {
                if (e.keyCode === 13) {
                    $scope.addMessage();
                }
            });
            // formatar o search de pessoas
            /*
             $(".nsMessengerSearchPeople input").css({
             'background-color': '#f5f5f5',
             'border': '#ccc solic 1px'
             });
             */
        }, 500);

        $scope.close = function () {
            // este elemento vai rodar dentro de um container com nome especifico, comunicador
            $timeout.cancel($scope.timeOutRefresh);
            $scope.lastheight = false;
            $scope.Talk = false;
            //$(".nsMessengerConversaContainer").hide()animate({height: (window.innerHeight - 100) + 'px'});
            $("#comunicadorConversas").hide();
            $("#comunicador").hide();
        };


        // watch para searchpeople
        $scope.$watch('nsMessengerUserFromSearch', function (newval, oldval) {
            if (newval > 0) {
                DataLoadService.getContent("Mensagem", "getConversas", {idUser: newval}, function (data) {
                    //console.info('NOVA CONVERSA', data);
                    $scope.showTalk(data.content.messages[0]);
                });
            }


        });


    };
    return ddo;

});
