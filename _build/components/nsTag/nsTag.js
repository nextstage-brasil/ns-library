app.directive('nsTag', function () {
    var ddo = {};
    ddo.restrict = "E";
    ddo.transclude = true;
    ddo.scope = {
        tipo: '@',
        titulo: '@',
        relacao: '@',
        idLeft: '@',
        idRight: '@',
        descricao: '@',
        condicoes: '=',
        onchange: '&'
    };
    ddo.templateUrl = appConfig.urlCloud + 'auto/components/nsTag.html';
    ddo.controller = function ($scope, $rootScope, $filter, $compile, $timeout, DataLoadService) {
        console.info('TagStarted');
        $scope.idComponent = MD5($scope.relacao + $scope.idLeft + $scope.idRight);// Math.ceil(Math.random() * Math.pow(10, 10));
        $scope.description = $scope.descricao;
        $scope.isMobile = !$rootScope.viewTable;

        console.info('sortable-var', $scope.tipo);
        switch ($scope.tipo) {
            case 'list':
                $scope.elementToSort = 'tagsortable_' + $scope.idComponent + '_b';
                break;
            case 'sortable':
                $scope.elementToSort = 'tagsortable_' + $scope.idComponent;
                break;
            default:
                $scope.elementToSort = false;
        }

        //$scope.sortable = $scope.sortable=='true' ? true : false;
        $elem = '';

        // todos campos obrigatórios
        $scope.init = function () {
            $scope.Tags = [];
            if (parseInt($scope.idLeft) > 0 || parseInt($scope.idRight) > 0) {
                // obter todos disponiveis setando os já clicados
                $scope.tagWorking = 'Obtendo dados';
                DataLoadService.getContent('Linktable', 'tagList', {
                    relacaoLinktable: $scope.relacao,
                    idLeftLinktable: $scope.idLeft,
                    idRightLinktable: $scope.idRight,
                    condicao: $scope.condicoes
                }, function (data) {
                    $scope.title = '';
                    $scope.tagWorking = false;
                    $scope.Tags = data.content.itens;
                    $scope.h5 = $scope.titulo || data.content.title;
                    console.info('TAG-LIST', data.content);
                    $scope.badge();
                    if ($scope.tipo) {
                        $timeout(function () {
                            // sortable
                            $("#tagsortable_" + $scope.idComponent).css({'list-style-type': 'none', margin: '0', padding: '0', 'max-width': '400px'});
                            $("#tagsortable_" + $scope.idComponent + " li").css({margin: '0 3px 3px 3px', padding: '0.4em', 'padding-left': '1.5em', 'font-size': '1.4em'});
                            $("#tagsortable_" + $scope.idComponent).sortable().disableSelection();
                            $("tagsortable_" + $scope.idComponent).on('sort', function (event, ui) {
                                $scope.setSort();

                            });

                            // list
                            /*
                             $(".nsTagSortable").sortable({
                             connectWith: ".connectedSortable"
                             }).disableSelection();
                             */
                            $(".nsTagSortable").sortable().disableSelection();


                            $(".nsTagSortable").on('sortupdate', function (event, ui) {
                                $scope.setSort();
                            });
                            /* 
                             $(".nsTagSortable").on("sortremove", function (event, ui) {
                             $timeout.cancel($scope.SortRemoveTimeout); // ignorar duplicações
                             $scope.SortRemoveTimeout = $timeout(function () {
                             console.info('remove', ui);
                             // Obter o index deste elemento
                             id = parseInt(ui.item[0].id.replace('li_sortablelist_id_', ''));
                             angular.forEach($scope.Tags, function (v, k) {
                             if (v.id === id) {
                             $scope.Tags[k].sort = ui.position.top;
                             $scope.set(v);
                             console.info('ID', id);
                             }
                             });
                             }, 1000);
                             });
                             /* */

                        }, 500);
                    }
                });
            }
        };

        $scope.setSort = function () {
            if ($scope.setSortTimeout) {
                $timeout.cancel($scope.setSortTimeout);
            }

            // aguardar a conclusao do sabe
            $elem = $('#' + $scope.elementToSort);
            if ($scope.nsTagWorking || $scope.setSortWorking) {
                $timeout(function () {
                    $scope.setSort($elem);
                }, 500);
                return;
            }


            $scope.setSortTimeout = $timeout(function () {
                $scope.setSortWorking = true;
                $newTags = angular.copy($scope.Tags);
                $elem.find('li').each(function (j, li) {
                    $sort = $(this)[0].offsetTop;
                    $label = $(this)[0].textContent;
                    angular.forEach($newTags, function (v, k) {
                        if (v.label === $label && v.checked === true) {
                            $newTags[k].sort = parseInt($sort);
                        }
                    });
                });
                console.log('SetSort');
                DataLoadService.getContent('Linktable', 'setSort', {list: $newTags}, function (data) {
                    $scope.Tags = angular.copy($newTags);
                    delete ($newTags);
                    $scope.setSortWorking = false;
                });
            }, 1000);
        };


        $scope.$watch('idLeft', function () {
            $scope.init();
        });
        $scope.$watch('idRight', function () {
            $scope.init();
        });


        $scope.badge = function () {
            $rel = $scope.relacao.split("|");
            $elem = $rel[((typeof $scope.idLeft !== 'undefined') ? 1 : 0)] + '-tag';
            console.log('Elemento: ' + $elem);
            console.info('Elemento', $scope.idLeft);
            $count = 0;
            angular.forEach($scope.Tags, function (v, k) {
                if (v.checked === true) {
                    $count++;
                }
            });
            console.log('badge: '.$elem);
            $rootScope.badge($elem, $count + ' de ' + $scope.Tags.length, true);
        };


        //$scope.xab = $rootScope.sessionGetJsonMd5('xab_'+$scope.entidade);

        $scope.set = function (item) {
            angular.forEach($scope.Tags, function (v, k) {
                if (v.id === item.id) {
                    $index = k;
                }
            });
            $scope.nsTagWorking = true;
            $scope.Tags[$index].sort =
                    $scope.Tags[$index].working = true;
            $scope.Tags[$index].checked = !$scope.Tags[$index].checked;
            //$timeout(function () {
            $scope.setSort();
            if ($scope.Tags[$index].checked) { // adicionar
                DataLoadService.getContent('Linktable', 'save', {
                    relacaoLinktable: $scope.relacao,
                    idLeftLinktable: (($scope.idLeft) ? $scope.idLeft : $scope.Tags[$index]['id']),
                    idRightLinktable: (($scope.idRight) ? $scope.idRight : $scope.Tags[$index]['id']),
                }, function (data) {
                    $scope.Tags[$index].working = false;
                    if (data.content.error === false) {
                        console.info('TAG-SAVE', data.content);
                        $scope.Tags[$index]['idLinktable'] = data.content.idLinktable;
                    } else {
                        $scope.Tags[$index].checked = !$scope.Tags[$index].checked;
                        $rootScope.setAlert(data);
                    }
                    $scope.onchange();
                    //$scope.init();
                    $scope.nsTagWorking = false;
                });

            }
            // remove
            else { // remove
                DataLoadService.getContent("Linktable", "remove", {
                    relacaoLinktable: $scope.entidade + '|ENQUADRAMENTO',
                    idLinktable: $scope.Tags[$index]['idLinktable']
                }, function (data) {
                    $scope.Tags[$index].working = false;
                    if (data.content.error !== false) {
                        $scope.Tags[$index].checked = !$scope.Tags[$index].checked;
                        $rootScope.setAlert(data);
                    }
                    console.info('TAG-REMOVE', data.content);
                    //$scope.init();
                });
                $scope.onchange();
                $scope.nsTagWorking = false;

            }
            $scope.badge();
            //}, 500);

            // add
        };

        // Acoes dos arrastar de botoes




    };
    return ddo;

});
