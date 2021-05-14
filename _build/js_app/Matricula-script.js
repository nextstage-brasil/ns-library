app.controller("MatriculaController", function ($rootScope, $scope, DataLoadService, $filter, $timeout, $compile) {
    $scope.entidadeName = 'Matricula'; // nome da entidade deste controller
    $rootScope.log($scope.entidadeName, 'Starter');
    $scope.listGetAux = ['Curso', 'Usuario', 'Polo', 'FormaPgto']; // ira definir os cadastros auxiliares necessarios a carga desta pagina. Caso zeja 0, não será carregado

    /** Menu de contexto **/
    $scope.MatriculaContextItens = [
        //{'link':'MatriculaOnClick(Matricula)', 'title': '<i class="fa fa-eye" aria-hidden="true"> </i> Ver'},
        {'link': 'MatriculaOnEdit(Matricula)', 'title': '<i class="fa fa-edit" aria-hidden="true"> </i> Abrir'},
        {'link': 'MatriculaRemove(Matricula)', 'title': '<i class="fa fa-trash" aria-hidden="true"> </i> Remover'}
    ];

    $timeout(function () {
        $("#divSearchTemplate .floating-label").html('Quem procura?');
        $(".btnTemplateNewDiv").removeClass('d-sm-block d-none').hide();
        $(".btnAdd").removeClass('d-sm-none d-block').hide();
    }, 100);

    /** Para tratar os linktables, caso exista **/
    $scope.setVinculosOnEdit = function (id) {
        $scope.Matricula.FinanceiroPendentes = 0;
        $timeout(function () {
            angular.forEach($scope.Matricula.Financeiro, function (v, k) {
                if (v.codStatus < 100) {
                    $scope.Matricula.FinanceiroPendentes++;
                }
            });
        }, 500);
    };

    // vai injetar as funções padrão
    $rootScope.trataEditOnLoad($scope);

    // Demais funções exclusivas deste componente
    $scope.registrarPagamento = function (item) {
        $scope.working = true;
        $scope.Recebe = {};
        $rootScope.loading('show', 'Preparando ambiente', 'modal');

        // Remover os menores que 1
        $scope.FormaPgto = [];
        angular.forEach($scope.Aux.FormaPgto, function (v, k) {
            if (v.idFormaPgto > 1 && v.idFormaPgto < 200) {
                $scope.FormaPgto.push(v);
            }
        });

        DataLoadService.getContent('Conta', 'list', {idPolo: $scope.Matricula.idPolo}, function (data) {
            console.info('Data', data);
            $scope.working = false;
            $rootScope.setAlert(data);
            if (data.error === false) {
                $scope.Contas = data.content;
            }
            var $config = {
                title: 'Registrar recebimento',
                icon: false,
                confirmButtonText: '<i class="fa fa-check mr-1"></i>Registrar',
                preConfirm: () => {
                    return confirm('Confirma registrar?');
                }
            };
            var $function = function () {
                $scope.working = true;
                $rootScope.loading('show', 'Processando... ', 'modal');
                DataLoadService.getContent('financeiro', 'gateway', {
                    opcao: 'listener',
                    id: item.idFinanceiro,
                    conta: $scope.Recebe.idConta,
                    formaPgto: $scope.Recebe.idFormaPgto
                }, function (data) {
                    console.info('Data', data);
                    $scope.working = false;
                    $rootScope.setAlert(data);
                    if (data.error === false) {
                        $scope.MatriculaOnEdit($scope.Matricula);
                        $timeout(function () {
                            $(".nav-tabs .nav-item a")[1].click();
                        }, 500);
                    }
                });
            };
            var $html = '<div class="text-left">' + _formRecebeFinanceiro + '</div>';
            $rootScope.swalConfirm($config, $function, $html, $scope);
        });
    };

    // Definir que a inscrição é de um casal
    $scope.inscricaoInformarCasal = function (item, idUsuarioCasado) {
        $timeout(function () {
            $(".swal2-input").addClass('decimal text-right');
            $rootScope.setTypes();
        }, 200);
        if (!idUsuarioCasado) {
            $rootScope.swalConfirm({
                title: 'Alterar valor de inscrição',
                text: 'Informe o novo valor',
                input: 'text',
                inputAttributes: {
                    autocapitalize: 'off'
                },
                icon: false,
                confirmButtonText: 'Continuar<i class="fa fa-arrow-right ml-1"></i>',
                preConfirm: () => {
                    return $(".swal2-input").val() !== '' && $(".swal2-input").val() !== '0,00';
                }
            }, function () {
                $scope.working = true;
                $rootScope.loading('show', 'Obtendo dados', 'modal');
                DataLoadService.getContent('Financeiro', 'atualizarValorInscricao', {id: item.idFinanceiro, v: $(".swal2-input").val()}, function (data) {
                    console.info('Data', data);
                    $scope.working = false;
                    $rootScope.setAlert(data);
                    if (data.error === false) {
                        $scope.MatriculaOnEdit($scope.Matricula);
                        $timeout(function () {
                            $("a[href='#financeiro']").click();
                        }, 500);
                    }
                });


            });
        }
    };
});