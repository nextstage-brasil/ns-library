app.controller("GradePoloController", function ($rootScope, $scope, DataLoadService, $filter, $timeout, $compile) {
    $scope.entidadeName = 'GradePolo'; // nome da entidade deste controller
    $rootScope.log($scope.entidadeName, 'Starter');
    $scope.listGetAux = ['Polo', 'Usuario', 'Curso', 'Modulo']; // ira definir os cadastros auxiliares necessarios a carga desta pagina. Caso zeja 0, não será carregado
    $scope.presenteFiltro = {};

    /** Menu de contexto **/
    $scope.GradePoloContextItens = [
        {'link': 'verAlunos(GradePolo)', 'title': '<i class="fa fa-users mr-1" aria-hidden="true"></i>Ver alunos'},
        {'link': 'verEncontros(GradePolo)', 'title': '<i class="fa fa-address-book mr-1" aria-hidden="true"></i>Registrar presença'},
        {'link': 'atribuirNotas(GradePolo)', 'title': '<i class="fa fa-eye mr-1" aria-hidden="true"></i>Atribuir notas'}
    ];
    $timeout(function () {
        // Substituição do incone para adicionar
        $(".btnTemplateNewDiv").removeClass('d-sm-block').hide();
        $("#btnTemplateNew").removeClass('d-block').addClass('d-none');
        $(".btnAdd").removeClass('d-block').addClass('d-none');
        $(".nsTemplateSearch").addClass('d-none');

        // Reescrever o tamanho dos filtros
        $('.nsTemplateMyFilters').removeClass('col-sm-7').addClass('col-sm-11');
    });
    
    
    /** Para tratar os linktables, caso exista **/
    $scope.setVinculosOnEdit = function (id) {
        if (!id) {
            $scope.Aux.Modulos = $scope.Data.Modulos;
        }
    };
    // vai injetar as funções padrão
    $rootScope.trataEditOnLoad($scope);

    var $doCloseOrigin = angular.copy($scope.GradePoloDoClose);

    $scope.GradePoloDoClose = function () {
        $doCloseOrigin();
        $("#verAlunosDiv span").html('');
    };

    $scope.Args = {};

    // configuração do daterange
    //$scope.Args.periodoRange = moment(new Date(_initDate + 'T23:59:59')).format('DD/MM/YYYY') + ' à ' + moment(new Date(_endDate + 'T23:59:59')).format('DD/MM/YYYY');
    $rootScope.setDateRange(_initDate, _endDate, $scope.Args);

    // Reescrito
    $scope.GradePolos = [];
    $scope.GradePoloOnEdit = function () {
        $scope.Args.idCurso = null;
        $scope.init(false, true);
    };

    // Método de carga e getall
    $scope.init = function (text, reload) {
        if (reload === true) {
            text = text ? text : 'Carregando dados';
            $rootScope.loading('show', text, 'modal');
            $scope.working = true;
            $scope.GradePolos = [];
            $scope.Total = {};
            DataLoadService.getContent('GradePolo', 'turmas', $scope.Args, function (data) {
                console.info('Turmas', data);
                $scope.working = false;
                $rootScope.setAlert(data);
                if (data.error === false) {
                    // ações a executar se positivo
                    $scope.GradePolos = data.content.Turmas;
                    $scope.Total = data.content.Total;
                    angular.forEach($scope.GradePolos, function (v, k) {
                        v.encontros = $filter('date')(v.primEncontro, 'dd/MM/yyyy')
                                + ' à '
                                + $filter('date')(v.ultEncontro, 'dd/MM/yyyy');
                    });
                }
            });
        }
    };
    $scope.init(false, true);
    $scope.GradePoloGetAll = $scope.init;
    /**
     * Ira listar os alunos de uma determinada turma
     * @param {type} GradePolo
     * @returns {undefined}
     */
    $scope.atribuirNotas = function (GradePolo) {
        $scope.GradePolo = GradePolo;
        $scope.working = true;
        $rootScope.loading('show', 'Processando... ', 'modal');
        $scope.Item = {};
        $("#atribuirNotasTable").html('<i class="fa fa-refresh fa-spin mr-1"></i>Obtendo dados...');
        $scope.cardsAprovados = {semMedia: 0, aprovado: 0, reprovado: 0};
        DataLoadService.getContent('Avaliacao', 'Turma', {codTurma: GradePolo.codTurma}, function (data) {
            console.info('atribuirNotas', data);
            $scope.working = false;
            $rootScope.setAlert(data);
            if (data.error === false) {
                $scope.Alunos = data.content.Alunos;
                $scope.Turma = data.content.Turma;
                $scope.Turma.encontros = $filter('date')($scope.Turma.primEncontro, 'dd/MM/yyyy')
                        + ' à '
                        + $filter('date')($scope.Turma.ultEncontro, 'dd/MM/yyyy');
                $timeout(function () {
                    $("#atribuirNotasTable").html($compile(data.content.TableHTMLAngular)($scope));
                    $rootScope.setTypes();
                    // Setar os limites das avaliações. No setypes, ele coloca outro formato para decimal
                    $timeout(function () {
                        $(".avaliacaoInput").mask('00,99', {reverse: true});
                    }, 1000);
                    $scope.setCardsAprovados();
                }, 1000);

                console.info('TEMP', $scope.Alunos[0].notasAvaliacao);
            }
        });
        $rootScope.trocaView('atribuirNotas', $scope.entidadeName, 'Atribuição de notas');
    };

    $scope.ehMaiorQ10 = function (valor) {
        if (valor) {
            var valor = valor.replace(',', '').replace('.', '');
            return valor > 1000;
        } else {
            return false;
        }
    };

    $scope.setCardsAprovados = function () {
        $scope.cardsAprovados = {semMedia: 0, aprovado: 0, reprovado: 0};
        angular.forEach($scope.Alunos, function (v, k) {
            if (v.notafinalAvaliacao !== null) {
                var nota = parseInt(v.notafinalAvaliacao.replace(',', '').replace('.', ''));
                if (nota >= 700) {
                    $scope.cardsAprovados.aprovado++;
                } else {
                    $scope.cardsAprovados.reprovado++;
                }
            } else {
                $scope.cardsAprovados.semMedia++;
            }
        });
    };


    $scope.calculaMedia = function (Object) {
        var avas = 0;
        var media = 0;
        var avasCounter = 0;
        angular.forEach(Object.notasAvaliacao.avaliacoes, function (item, k) {
            if (item.nota) {
                avas += parseInt(item.nota.replace(',', '').replace('.', ''));
                avasCounter++;
            }
        });

        // Recuperação
        if (Object.notasAvaliacao.recuperacao) {
            console.info('avas', avas);
            var recuperacao = parseInt(Object.notasAvaliacao.recuperacao.replace(',', '').replace('.', ''));
            console.info('recuperacao', recuperacao);
            if (recuperacao > 0) {
                avas += recuperacao;
                avasCounter++;
            }
        }

        // Calculo da media
        media = avas / avasCounter;
        console.info('media', media);

        // Pontos extras
        if (Object.notasAvaliacao.ponto_extra) {
            media += parseInt(Object.notasAvaliacao.ponto_extra.replace(',', '').replace('.', ''));
            console.info('media-2', media);
        }

        if (media > 1000) {
            media = 1000;
        }
        Object.notafinalAvaliacao = nsRound(media / 100, 2).toFixed(2).toString().replace('.', ',');

        $timeout(function () {
            $rootScope.setTypes();
        }, 500);
        //console.info('Object', Object);

        $scope.setCardsAprovados();
    };

    /**
     * Avalia se o item enviado tem o seu valor menor que 10,00
     * @param {type} item
     * @returns {undefined}
     */
    $scope.limite10 = function (Object) {
        var media = 0;
        var avas = 0;
        var error = false;
        // verificar dados de avaliações e calcular média
        angular.forEach(Object.notasAvaliacao.avaliacoes, function (item, k) {
            if ($scope.ehMaiorQ10(item.nota)) {
                item.nota = '';
                error = true;
            }
            avas += item.nota
        });
        if ($scope.ehMaiorQ10(Object.notasAvaliacao.recuperacao)) {
            Object.notasAvaliacao.recuperacao = '';
            error = true;
        }
        if ($scope.ehMaiorQ10(Object.notasAvaliacao.ponto_extra)) {
            Object.notasAvaliacao.ponto_extra = '';
            error = true;
        }
        $scope.calculaMedia(Object);
        $timeout(function () {
            if (error) {
                Swal.fire('Verifique', 'Valor limite para notas: 10', 'info');
            }
        }, 100);



    };

    /**
     * IUrá salvar as notas atribuidas 
     * @returns {undefined}
     */
    $scope.atribuirNotasSave = function () {
        $rootScope.swalConfirm({}, function () {
            $scope.working = true;
            $rootScope.loading('show', 'Registrando notas', 'modal');
            DataLoadService.getContent('Avaliacao', 'turmaSave', {Alunos: $scope.Alunos, Turma: $scope.Turma}, function (data) {
                console.info('turmaSave', data);
                $scope.working = false;
                $rootScope.setAlert(data);
                if (data.error === false) {
                    $scope.Alunos = [];
                    $scope.Turma = [];
                    $scope.GradePoloDoClose();
                }
            });
        });
    };

    /**
     * Mostrará um modal para seleção do encotrno a ser exibido
     * @param {type} GradePolo
     * @returns {undefined}
     */
    $scope.verEncontros = function (GradePolo) {
        $scope.GradePolo = GradePolo;
        $scope.working = true;
        $rootScope.loading('show', 'Processando... ', 'modal');
        $scope.Item = {};
        DataLoadService.getContent('GradePolo', 'verEncontros', {codTurma: GradePolo.codTurma}, function (data) {
            console.info('alunosPorTurma', data);
            $scope.working = false;
            $rootScope.setAlert(data);
            if (data.error === false) {
                $scope.List = data.content.Turma;
                var $html = '<div>' + _encontrosHTML + '</div>';
                Swal.fire({
                    //icon: 'question',
                    confirmButtonText: 'Cancelar',
                    confirmButtonColor: 'secondary',
                    title: false,
                    html: '<i class="fa fa-refresh fa-spin mr-1"></i>Obtendo encontros...',
                    confirmButtonColor: '#999'
                });
                $timeout(function () {
                    $("#swal2-content").html($compile($html)($scope));
                }, 500);
            }

        });
        //$rootScope.trocaView('verAlunos', $scope.entidadeName, 'Alunos da turma');
    };

    /**
     * 
     * @param {type} Encontro
     * @returns {undefined}
     */
    $scope.registraPresencaShow = function (Encontro) {
        Swal.close();
        $scope.working = true;
        $rootScope.loading('show', 'Buscando alunos do encontro', 'modal');
        DataLoadService.getContent('GradePolo', 'getAlunosParaRegistroPresenca', {codTurma: Encontro.codTurma, dataEncontro: Encontro.dataEncontro}, function (data) {
            console.info('alunoPorEncontro', data);
            $scope.working = false;
            $rootScope.setAlert(data);
            if (data.error === false) {
                // @rever Descarregar variaveis da memoria
                if (data.error === false) {
                    angular.forEach(data.content, function (v, k) {
                        $scope[k] = v;
                    });
                    $scope.Turma.encontros = $filter('date')($scope.Turma.primEncontro, 'dd/MM/yyyy')
                            + ' à '
                            + $filter('date')($scope.Turma.ultEncontro, 'dd/MM/yyyy');
                    $timeout(function () {
                        $("#atribuirNotasTable").html($compile(data.content.TableHTMLAngular)($scope));
                        $rootScope.setTypes();
                    }, 1000);
                    console.info('TEMP', $scope.Alunos[0].notasAvaliacao);
                }
                $rootScope.trocaView('atribuirPresenca', 'GradePolo', 'Registro de presença em encontro');
                $timeout(function () {
                    $("#nav-contact-tab").click();
                }, 500);
            }
        });
    };

    $scope.registraPresenca = function (item) {
        $scope.working = true;
        item.isPresente = ((item.isPresente === 'true') ? 'false' : 'true');
        DataLoadService.getContent('GradeAluno', 'setPresenca', {idGradeAluno: item.idGradeAluno}, function (data) {
            console.info('Data', data);
            $scope.working = false;
            if (data.error === false) {
                item = data.content.Aluno;
            } else {
                $rootScope.setAlert(data);
            }
        });
    };

    $scope.verAlunos = function (item) {
        $("#verAlunosDiv span").html($compile('<alunos-por-turma cod-turma="' + item.codTurma + '"></alunos-por-turma>')($scope));
        $rootScope.trocaView('verAlunosDiv', 'GradePolo', 'Ver alunos por turma');
    };


});