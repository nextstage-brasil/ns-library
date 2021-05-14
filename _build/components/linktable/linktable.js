app.directive('linktable', function ($rootScope, DataLoadService, $timeout, $compile, $filter) {
    return {
        restrict: 'AE',
        scope: {
            view: '@', // define se deve mostrar na ficha (tratamento especial ou na edição (padrão)
            relacao: '@', // relação a ser obtida, ex Pessoa|Profissao
            idLeft: '@', // id da entidade a esqeruda, normalmente a entidade setada
            idRight: '@', // id da entidade a direta, normalmete o vinculo
            title: '@', // titulo
            gridCards: '@', // grid dos cards, default col-4
            textSearch: '@', // 
            btnText: '@',
            badge: '@',
        },
        templateUrl: appConfig.urlCloud + 'auto/components/linktable.html',
        controller: function ($scope, DataLoadService, $filter, $compile) {
            $scope.init = function () {
                //console.info('LogDeLinktable', "LinktableController");
                $scope.Linktable = false;
                $scope.LinktableNovo = {}; // entidade vazia para criação de novo objeto
                $scope.Linktables = []; // array de objetos
                $scope.List = true; //Controla a exibição de List ou Form
                $scope.btnText = $scope.btnText ? $scope.btnText : 'Adicionar ' + $scope.title;
                $scope.textSearch = $scope.textSearch ? $scope.textSearch : 'Filtrar ' + $scope.title;

                $scope.filtro = [];
                $scope.scroll = 0; // armazena posição do scroll para btnVoltar
                $scope.LinktablePagina = 0;
                $scope.linktableWorking = false;
                $scope.Aux = {};
                $scope.Args = {
                    relacaoLinktable: $filter('uppercase')($scope.relacao),
                    idLeftLinktable: $scope.idLeft,
                    idRightLinktable: $scope.idRight,
                    extraLinktable: {}
                }; // argumentos utilizado em getAll



                //console.info('ARGS-To_card', $scope.Args);
                var r = $scope.relacao.split('|');
                $scope.EntidadeRelacionada = $filter('capitalize')($scope.idLeft && r[1] || r[0]);
                $scope.EntidadeEditada = $filter('capitalize')($scope.idLeft && r[0] || r[1]);
                $scope.gridCards = $scope.gridCards ? $scope.gridCards : 'col-sm-4';
                $scope.linkNew = appConfig.urlCloud + $scope.EntidadeRelacionada + '/0/edit/return';

                var session_xabagaia = 'xab_' + $scope.EntidadeRelacionada;
                var session_xabagaia_edit = 'xab_' + $scope.EntidadeEditada;
                var session_card = 'card_' + $scope.Args.relacaoLinktable;

                // Itens que aparecerão no menu de contexto
                $scope.LinktableContextItens = [
                    {'link': 'LinktableOnClick(Linktable)', 'title': '<i class="fa fa-eye" aria-hidden="true"> </i> Ver', 'ngshow': 'xabagaia.ns100r'},
                    {'link': 'editarVinculo(Linktable)', 'title': '<i class="fa fa-edit" aria-hidden="true"> </i> Editar Vinculo', 'ngshow': 'xabagaia_edit.ns100u'},
                    {'link': 'ClubeHtShow(Linktable)', 'title': '<i class="fa fa-list" aria-hidden="true"> </i> Movimentações', 'ngshow': 'xabagaia_edit.ns100u && (Linktable.nomeClube || Linktable.linktable.hts)'},
                    {'link': 'toQualify(Linktable)', 'title': '<i class="fa fa-edit" aria-hidden="true"> </i> Editar ' + $scope.title, 'ngshow': 'xabagaia_edit.ns100u'},
                    {'link': 'LinktableOnHold(Linktable, \'Remover\')', 'title': '<i class="fa fa-trash" aria-hidden="true"> </i> Remover Vinculo', 'ngshow': 'xabagaia_edit.ns100d'}
                ];


                // Itens que aparecerão no menu de contexto
                $scope.LinktableContextItens = [
                    {'link': 'LinktableOnClick(Linktable)', 'title': '<i class="fa fa-eye" aria-hidden="true"> </i> Ver'},
                    {'link': 'editarVinculo(Linktable)', 'title': '<i class="fa fa-edit" aria-hidden="true"> </i> Editar Vinculo'},
                    {'link': 'ClubeHtShow(Linktable)', 'title': '<i class="fa fa-list" aria-hidden="true"> </i> Movimentações', 'ngshow': 'xabagaia_edit.ns100u && (Linktable.nomeClube || Linktable.linktable.hts)'},
                    {'link': 'toQualify(Linktable)', 'title': '<i class="fa fa-edit" aria-hidden="true"> </i> Editar ' + $scope.title},
                    {'link': 'LinktableOnHold(Linktable, \'Remover\')', 'title': '<i class="fa fa-trash" aria-hidden="true"> </i> Remover Vinculo'}
                ];




                // controle para mostrar na ficha ou tela de edição
                $timeout(function () {
                    $scope.view = (($scope.view === 'true') ? true : false);

                    if ($scope.view) {
                        $scope.LinktableContextItens = [];
                    }
                    /***
                     // Xabagaia com obtenção uma vez na sessão
                     $scope.xabagaia = $rootScope.sessionGetJsonMd5(session_xabagaia); // entidade relacionada// sessionStorage.getItem(session_xabagaia);
                     if (!$scope.xabagaia) {
                     // Para o caso de view, obter o xabagaia para saber se mostra ou não olink
                     $rootScope.trataXabagaia($scope.EntidadeRelacionada, function (data) {
                     $rootScope.sessionSetJsonMd5(session_xabagaia, data.content);
                     $scope.xabagaia = data.content;
                     });
                     }
                     
                     // Xabagaia com obtenção uma vez na sessão
                     $scope.xabagaia_edit = $rootScope.sessionGetJsonMd5(session_xabagaia_edit);// Entidade que esta sendo editada
                     if (!$scope.xabagaia_edit) {
                     // Para o caso de view, obter o xabagaia para saber se mostra ou não olink
                     $rootScope.trataXabagaia($scope.EntidadeEditada, function (data) {
                     $rootScope.sessionSetJsonMd5(session_xabagaia_edit, data.content);
                     $scope.xabagaia_edit = data.content;
                     });
                     }
                     ***/

                }, 1000);




                //$scope.idSet = $scope.idLeft > 0 ? $scope.idLeft : $scope.idRight;

                /* avaliando a necessidade disso -desligado em 13/05/2019. Não vi necessidadem, pois isso eh tratado no controller a cada chamada
                 // corrigir a relação enviada
                 DataLoadService.getContent('linktable', 'getKeyRelacao', $scope.Args, function (data) {
                 //console.info('keyRelacao', data.content);
                 $scope.Args = data.content;
                 });
                 */


                // definir as entidades envolvidas
                var t = $scope.relacao.split("|");
                $scope.relacaoLeft = t[0];
                $scope.relacaoRight = t[1];

                var ID = $scope.relacao.replace('|', '_') + 'L' + $scope.idLeft + 'R' + $scope.idRight;
                $scope.ID = ID;
                $scope.relacaoID = 'CARDS_' + ID;
                $scope.relacaoIDTable = 'TABLE_' + ID;
                $scope.relacaoIDView = 'VIEW_' + ID;
                $scope.searchID = 'SEARCH_' + ID;

                // somente um dos lados deve vir setado
                if ($scope.idLeft && $scope.idRight) {
                    alert('Somente uma referência deve ser setada (left or right)');
                    throw new Error('Somente uma referência deve ser setada (left or right)');
                }
                // 
                //$scope.locationOrigin = angular.copy(window.location.href); // salva o lolcation original de chamada
                $scope.lastLocation = angular.copy(sessionStorage.getItem('lastLocation'));


                $scope.setCard = function (json) {
                    var html = '';
                    html = json.card.replace(/coluna-card/g, $scope.gridCards);
                    $('#' + $scope.relacaoID).html($compile(html)($scope));
                    $('#' + $scope.relacaoIDTable).html($compile(json.table)($scope));
                    $('#' + $scope.relacaoIDView).html($compile(json.view)($scope));
                };


                // Buscar CARD especifico para esta relação
                $timeout(function () {
                    card = $rootScope.sessionGetJsonMd5(session_card);
                    if (!card) {
                        DataLoadService.getContent('Linktable', 'getCard', $scope.Args, function (data) {
                            card = data.content;
                            $rootScope.sessionSetJsonMd5(session_card, data.content);
                            $scope.setCard(data.content);
                        });
                    } else {
                        $scope.setCard(card);
                    }
                }, 500);



                // método para encaminhar a tela para edição de dados
                $scope.toQualify = function (Linktable, id) {
                    $rootScope.loading('show', 'Abrindo Qualificação', 'modal');
                    if (!id) {
                        Linktable = typeof Linktable != 'object' ? $scope.SettedLinkTable : Linktable;
                        id = Linktable['id' + $scope.EntidadeRelacionada];
                    }
                    window.location = $rootScope.urlCloud + $scope.EntidadeRelacionada + '/' + id + '/edit/return';
                };




                // chamada de funcao para criação de nova entidade
                $scope.LinktableNew = function (success) {
                    $scope.LinktableExistente = false;
                    DataLoadService.getContent("Linktable", "getNew", {}, function (data) {
                        //console.info("LinktableController::getNew()", data);
                        $scope.Linktable = data.content;
                        $scope.Linktable.Entidade = $scope.entidade; // setar entidade que ira relacionar com este linktable
                        //console.info('RISCO', $scope.Linktable);
                        success(data.content);
                    });
                };


                // Método para fechar form e voltar a lista
                $scope.LinktableDoClose = function () {
                    //console.info('LinktableDoClose()');
                    $scope.Linktable = false;
                    $scope.LinktableExistente = false;
                    $scope.Args.extraLinktable = {};
                    $scope.htShow = false;
                    $("#ht_" + $scope.ID).html('');
                }; // argumentos utilizado em getAll
                //$scope.LinktableDoClose(); // executando a primeira vez para mostrar a listEdit      

                // para escrever badges nas abas, se existirem
                //var $elem = $('a[href="#vinculo_' + $filter('lowercase')($scope.EntidadeRelacionada) + '"] span');
                if ($scope.badge) {
                    var $elem = $scope.badge;
                } else {
                    var $elem = 'vinculo_' + $filter('lowercase')($scope.EntidadeRelacionada);
                }
                $rootScope.badge($elem);
                /*
                 $rootScope.badge($elem);
                 $scope.$watch('Linktables.length', function (newVal, oldVal) {
                 $total = $scope.Linktables.length;
                 $rootScope.badge($elem, 12);
                 $rootScope.linkTableTotais[$filter('lowercase')($scope.EntidadeRelacionada)] = $total;
                 $rootScope.somaLinkTableTotais();
                 });
                 */

                $scope.linktableSetTotal = function (count) {
                    //$total = $scope.Linktables.length;
                    if (count >= 0) {
                        $total = parseInt(count);
                        $rootScope.badge($elem, $total);
                        $rootScope.linkTableTotais[$filter('lowercase')($scope.EntidadeRelacionada)] = $total;
                        $rootScope.somaLinkTableTotais();
                        $scope.Count = $total;
                        //console.log('card-setar total 1: ' + $scope.EntidadeRelacionada + ' - ' + $total);

                    } else {
                        args = angular.copy($scope.Args);
                        args.count = true;
                        DataLoadService.getContent('linktable', 'getAll', args, function (data) {
                            //console.info('ContadorLinktable', data.content);
                            $scope.working = false;
                            $rootScope.setAlert(data);
                            if (data.error === false) {
                                $total = parseInt(data.content);
                            }
                            $rootScope.badge($elem, $total);
                            $rootScope.linkTableTotais[$filter('lowercase')($scope.EntidadeRelacionada)] = $total;
                            $rootScope.somaLinkTableTotais();
                            $scope.Count = $total;
                        });
                        //console.log('card-setar total 2: ' + $scope.EntidadeRelacionada + ' - ' + $total);
                    }
                };


                // Lista de entidades
                $scope.LinktableGetAll = function (msg, reload) {
                    //console.info('LinktableGetAll: ' + $('#LinktableList').is(':visible'));



                    // validar se já existe os dados. CAso sim, carregar estes pois vira do controller que chamou. Zerar após ler
                    if (typeof $rootScope.cargaLinktable['vinculo' + $scope.EntidadeRelacionada] !== 'undefined') {
                        $scope.Linktables = angular.copy($rootScope.cargaLinktable['vinculo' + $scope.EntidadeRelacionada]);
                        $count = parseInt(angular.copy($rootScope.cargaLinktable['vinculo' + $scope.EntidadeRelacionada + '_count']));
                        //console.info('LINKTABLE-CARGA-' + $scope.Linktables);
                        $scope.linktableSetTotal($count);
                        delete $rootScope.cargaLinktable['vinculo' + $scope.EntidadeRelacionada]; // pq só serve na carga inicila. após cada interçaõ deve seguir o adrão aqui
                        // se tiver mais registros, somente pagina. se nao encerrar
                        if ($scope.Linktables.length < $count) {
                            $scope.LinktablePagina++;
                        } else {
                            $scope.LinktablePagina = -1;
                        }
                        return;
                    }





                    if (reload) {
                        $scope.LinktableDoClose();
                        $scope.LinktablePagina = 0;
                        $scope.linktableSetTotal();
                        $rootScope.loading('show', 'Obtendo dados');

                    }
                    if ($scope.LinktablePagina >= 0 && !$scope.Linktable) {

                        //alert('vai chamar getall');
                        $scope.idLinktable = '';
                        $scope.Linktable = false;
                        $scope.linktableWorking = true;
                        $scope.Args.pagina = $scope.LinktablePagina;
                        $scope.LinktablePagina++;
                        //$rootScope.loading('show', msg ? msg : 'Obtendo Linktables <small>(' + $scope.LinktablePagina + ')</small>');
                        //$rootScope.badge($elem);
                        DataLoadService.getContent("Linktable", "getAll", $scope.Args, function (data) {
                            //console.info("Linktable::getAll()-pag:" + $scope.LinktablePagina, data);

                            if (reload) {
                                $rootScope.loading('hide');
                                $scope.Linktables = [];
                            }
                            if (data.error !== false || data.content === null || data.content === false || data.content.length === 0) {
                                $rootScope.setAlert(data);
                                ////console.info('ERROR ON GET-ALL: ', data.error);
                                $scope.LinktablePagina = -1;
                            } else {
                                //console.info('LinktableOK[LIST]', data.content);
                                if (data.error === false) {
                                    angular.forEach(data.content, function (val, key) {
                                        $scope.Linktables.push(val);
                                    });
                                }
                                $rootScope.setAlert(data);
                            }
                            //$scope.linktableSetTotal();
                            $scope.linktableWorking = false;

                        });
                    }

                };



                // método para atender o search no topo das paginas
                $scope.doSearch = function () {
                    clearTimeout($scope.searchTime); // limpa o timeout anterior cada chamada pra zerar o contador
                    $scope.searchTime = setTimeout(function () { // vai executar isso  0,5 segundos após a pessoa parar de digitar
                        //console.info('dadosParaSearch', $scope.Search);
                        $scope.linktableWorking = true;
                        $scope.Args.Search = $scope.Search;
                        $scope.LinktableGetAll('Obtendo dados', true);
                        $timeout(function () {
                            $scope.Args.Search = false;
                        }, 500);
                    }, appConfig.timeWaitKeyUp);
                };





                // Remover entidade
                $scope.LinktableRemove = function (Linktable) {
                    //console.info('Linktable::REMOVE', Linktable);
                    DataLoadService.getContent("Linktable", "remove", {relacaoLinktable: $scope.relacao, idLinktable: Linktable.linktable.id_linktable}, function (data) {
                        if (data.error === false) {
                            $scope.LinktableDoClose();
                            $scope.LinktableGetAll('Atualizando relação', true);
                            $scope.LinktableExistente = false;
                        }
                        $rootScope.setAlert(data);
                    });
                };

                // Método para setar a entidade. Não decide se vai exibir ou editar
                $scope.setEntidade = function (Linktable, success) {
                    if (typeof Linktable.linktable === 'object') {
                        if (Linktable.linktable.id_linktable > 0) {
                            $rootScope.loading('hide');
                            $scope.Linktable = Linktable;
                            success(Linktable);
                        }
                    } else {
                        $scope.LinktableNew(function (data) {
                            success(data);
                        });
                    }
                };

                //$scope.entidadeName = 'qqUma';
                //$rootScope.trataEditOnLoad($scope.entidadeName, $scope);

                // exibe modal para view
                $scope.LinktableOnClick = function (Linktable) {
                    $rootScope.federacaoShowFicha(Linktable['id' + $scope.EntidadeRelacionada], $scope.EntidadeRelacionada);
                    return;


                    /*****************
                     // console.info('onclick-linktable', Linktable);
                     //console.info('EntidadeRelacionada', $scope.EntidadeRelacionada);
                     if ($scope.view) {
                     switch ($scope.EntidadeRelacionada) {
                     case 'Pessoa':
                     ScopeStorage.get('ctrPai').pessoaShowFicha(Linktable.idPessoa, true);
                     break;
                     default:
                     $rootScope.federacaoShowFicha(Linktable['id' + $scope.EntidadeRelacionada], $scope.EntidadeRelacionada);
                     break;
                     }
                     return;
                     }
                     //console.info('linktable_set', Linktable);
                     id = Linktable['id' + $scope.EntidadeRelacionada];
                     $scope.SettedLinkTable = Linktable;
                     //console.info('modalView', Linktable);
                     switch ($scope.EntidadeRelacionada) {
                     case 'Pessoa':
                     ScopeStorage.get('ctrPai').pessoaShowFicha(Linktable.idPessoa, true);
                     break;
                     case'Clube':
                     DataLoadService.getContent($scope.EntidadeRelacionada, 'getWiki', {id: id}, function (data) {
                     //console.info('ClubeWiki', data);
                     $scope.working = false;
                     $rootScope.setAlert(data);
                     if (data.error === false) {
                     $scope.extras = data.content.extras;
                     //$scope.toQualify = data.content;
                     $("#divToWrite").html($compile('<alert-modal modal-id="wiki" confirm-action="toQualify()" cancel-action="" btn-confirm-text="Editar">' + data.content.wiki + '</alert-modal>')($scope));
                     $timeout(function () {
                     
                     $("#wiki").modal('show');
                     }, 500);
                     }
                     });
                     break;
                     default:
                     $scope.showModal(Linktable);
                     break;
                     
                     
                     }
                     *************/
                };

                $scope.showModal = function (Linktable) {
                    $scope.setEntidade(Linktable, function (v) {
                        $scope.SettedLinkTable = v;
                        //console.info('modaldata-V', v);
                        $scope.idSet = v['id' + $scope.wsType]; // para poder criar o link de qualificação
                        $rootScope.loading('show', 'Obtendo dados');

                        DataLoadService.getContent($scope.EntidadeRelacionada, 'read/' + Linktable['id' + $scope.EntidadeRelacionada], {}, function (data) {
                            //console.info('Linktable - Read', data);
                            $scope.Linktable = data.content;

                            DataLoadService.getContent('Linktable', 'getModalData', $scope.Args, function (data) {
                                $rootScope.loading('hide');
                                //console.info('modaldata', data);
                                var out = [];
                                angular.forEach(data.content, function (value, key) {
                                    // para campos tipo data
                                    if (value.field.indexOf('|date') > -1) {
                                        value.field = value.field.replace('|date', '');
                                        var tipoDate = true;
                                    }
                                    $list = value.field.split('.');
                                    values = v;
                                    for (var i = 0; i < $list.length; i++)
                                        values = values[$list[i]];
                                    value.text = $rootScope.nl2br(values);//v[value.field];
                                    value.text = tipoDate ? $filter('date')(value.text, 'dd/MM/yyyy') : value.text;

                                    out.push(value);
                                });
                                //console.info('MODALDATA', out);
                                $scope.modalData = out;
                                $("#linktableModalView" + $scope.ID).modal('show');
                                $scope.Linktable = false;
                            });

                        });
                    });
                };



                $scope.LinktableOnEdit = function (Linktable) {
                    $scope.Linktable = {};
                    $scope.idRightSearch = -1;
                    $scope.searchExtras = {entidade: $scope.entidade, id: $scope.id};

                    if (!$scope.RELACAO) {
                        $rootScope.loading('show', 'Preparando edição de vinculo');
                        DataLoadService.getContent('Linktable', 'getRelacao', $scope.Args, function (data) {
                            $scope.RELACAO = data.content.relacao;
                            $rootScope.loading('hide');
                        });
                    }

                    //////////////////////////////////////////////////////////
                    // controle pro search nao buscar relações que já existem
                    $scope.searchExtras = {idNotIn: []};
                    angular.forEach($scope.Linktables, function (v, k) {
                        id = v['id' + $scope.EntidadeRelacionada];
                        $scope.searchExtras.idNotIn.push(id);
                    });
                    //////////////////////////////////////////////////////////


                    $scope.textSearch = 'Informe um texto para pesquisa em ' + $scope.title;// $scope.textSearch ? $scope.textSearch : 'Digite o que deseja adicionar';
                    var html = '<combo-search ng-show="RELACAO" label="' + $scope.textSearch + '" model="idRightSearch" initial="{{}}" ws-type="{{RELACAO}}" ws-action="getAll" extras="searchExtras"></combo-search>';
                    $("#" + $scope.searchID).html($compile(html)($scope));
                    $scope.setEntidade(Linktable, function (v) {
                        //console.info('LinktableEdit', v);
                    });




                };

                // Método para controlar onHold (Mobile)
                $scope.LinktableOnHold = function (Linktable, Escolha) {
                    Escolha = Escolha ? Escolha : 'atualizar';
                    Swal.fire({
                        title: 'Confirma ' + Escolha + ' Vinculo?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Sim, remover!',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.value) {
                            $scope.LinktableRemove(Linktable);
                        }
                    });
                };

                // Método para limpar o filtro de busca
                $scope.filterClear = function (arg) {
                    //console.info('filtroClear', arg);
                    if (arg === 'Search') {
                        $scope.Search = '';
                        $scope.Args.Search = '';
                    }
                    $scope.Args['id' + arg] = false;
                    $scope.LinktableGetAll('Atualizando relação por filtros', true);
                };


                // Observar LinktableExistente, pois será a seleção no comboBox. Ao Selecinoar, será exibido o Form de edição
                $scope.$watch("idRightSearch", function (newValue, oldValue) {
                    console.info('idrightsearch', $scope.Linktable);
                    if (newValue > 0) {
                        console.log('Associar');
                        $scope.idLinktable = parseInt(newValue);
                        $scope.LinktableDoClose();
                        $scope.LinktableAssociate();
                    }
                });

                $scope.$watch("filtro", function (newValue, oldValue) {
                    //console.info('FILTRO' + $scope.relacao, newValue);
                });


                $scope.LinktableAssociate = function (Linktable) {
                    console.info('LinktableAssociate', Linktable);
                    $scope.linktableWorking = true;
                    $rootScope.loading('show', 'Processando... ');
                    DataLoadService.getContent('Linktable', 'save', {
                        relacaoLinktable: $scope.relacao,
                        idLeftLinktable: (($scope.idLeft) ? $scope.idLeft : $scope.idRightSearch),
                        idRightLinktable: (($scope.idRight) ? $scope.idRight : $scope.idRightSearch),
                        extraLinktable: $scope.Args.extraLinktable
                    }, function (data) {
                        $rootScope.setAlert(data);
                        //console.info('DataLinktableAssociate', data);
                        $scope.linktableWorking = false;
                        $rootScope.loading('hide');
                        if (data.error === false) {
                            if (data.content.extraLinktable.config) {
                                v = {linktable: {id_linktable: data.content.idLinktable}};
                                $scope.editarVinculo(v);
                            } else {
                                $scope.LinktableDoClose();
                                $scope.LinktableGetAll('Atualizando relação', true);
                                //$scope.LinktableExistente = false;
                            }
                        }
                        /*
                         
                         $rootScope.setAlert(data);
                         $scope.LinktableDoClose();
                         $scope.LinktableGetAll('Atualizando relação', true);
                         $scope.LinktableExistente = false;
                         } else {
                         $scope.Aux = data.content.Aux;
                         $scope.LinktableShowExtra(data.content.args);
                         }
                         */
                    });
                };
                /*
                 $scope.LinktableShowExtra = function (args) {
                 args.scope = $scope;
                 $('#btnDialogModal').prop('onclick', null).off('click').on('click', function (evt) {
                 $scope.LinktableAssociate();
                 });
                 $timeout(function () {
                 $rootScope.showDialogModal(args);
                 }, 500);
                 };
                 * 
                 */

                // Criar o ambiente para edição do vinculo em linktable 
                $scope.editarVinculo = function (linktable) {
                    //console.info('ReadLinktable', linktable);
                    $scope.working = true;
                    $rootScope.loading('show', 'Abrindo edição de vinculo');
                    //console.info('id__', "linktable-edit-" + $scope.ID);
                    DataLoadService.getContent('Linktable', 'getById', {idLinktable: linktable.linktable.id_linktable}, function (data) {
                        //console.info('editarVinculo', data);
                        data.content.referencia = linktable['nome' + $scope.EntidadeRelacionada];
                        //console.info('Linktable-edit', data.content);
                        $scope.working = false;
                        $rootScope.setAlert(data);
                        if (data.content.extraLinktable.config === null) {
                            alert('Nada a editar');
                        } else {
                            if (data.error === false) {
                                $scope.Linktable = data.content;
                            }
                        }
                    });
                };

                $scope.save = function () {
                    $scope.working = true;
                    $rootScope.loading('show', 'Processando', 'modal');
                    delete($scope.Linktable.Usuario);
                    DataLoadService.getContent('linktable', 'save', $scope.Linktable, function (data) {
                        //console.info('Data', data);
                        $scope.working = false;
                        $rootScope.setAlert(data);
                        if (data.error === false) {
                            $scope.LinktableDoClose();
                            $scope.LinktableGetAll('Atualizando relação', true);
                        }
                    });


                };


                // ################################ Funções referente aos registros em linktable

                // ira mostrar os registros
                $scope.ClubeHtShow = function (Linktable) {
                    // console.clear();
                    // console.info('ClubeHtShow', Linktable);
                    $("#ht_" + $scope.ID).html($compile('<h4 class="text-left mt-3 mb-4">Registro de movimentos</h4>'
                            + '<ns-historico-linktable btn-text="Registro" tipo="5" btn-new-text="movimentação" alerta="false" badge-id="ht-ht" entidade="LINKTABLE" valorid="' + Linktable.linktable.id_linktable + '"></ns-historico-linktable>')($scope));
                    //$rootScope.loading('show');
                    $timeout(function () {
                        $("#modalht_" + $scope.ID).modal({backdrop: 'static'});
                        $("#modalht_" + $scope.ID + " h3").html(Linktable.nomeClube);
                    }, 300);
                };
                $scope.closeModal = function () {
                    // console.clear();
                    $timeout(function () {
                        $("#ht_" + $scope.ID).html($compile('')($scope));
                    }, 1500);

                    $scope.LinktableGetAll('Atualizando dados', true);
                };

                // mostra cronologia de movimentações em clubes
                $scope.clubeShowMovimentacoes = function () {
                    $scope.working = true;
                    $rootScope.loading('show', 'Obtendo dados', 'modal');
                    DataLoadService.getContent('Clube', 'jgd_mvto', {id: $scope.idLeft}, function (data) {
                        console.info('Data', data);
                        $scope.working = false;
                        $rootScope.setAlert(data);
                        if (data.error === false) {
                            $scope.mvtos = data.content.table;
                        }

                        $("#ht_" + $scope.ID).html($compile('<span ng-bind-html="mvtos"></span>')($scope));
                        //$rootScope.loading('show');
                        $timeout(function () {
                            $("#modalht_" + $scope.ID).modal({backdrop: 'static'});
                            $("#modalht_" + $scope.ID + " h3").html('Cronologia');
                        }, 300);
                    });
                };

            };




            // observar alterações nos IDs
            $scope.$watch('idLeft', function (newV, oldV) {
                console.log('idleft-watch', newV);
                if (newV > 0) {
                    $scope.init();
                    $timeout($scope.LinktableGetAll(), 500);
                    // console.clear();
                    // console.log('Watch: OLDV: ' + oldV + ', newv: ' + newV);
                    //$scope.Linktables = [];
                    //$scope.LinktableGetAll('', true);
                }
            });

        }// fecha controller
    };
});
