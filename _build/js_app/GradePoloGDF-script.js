app.controller("GradePoloController", function ($rootScope, $scope, DataLoadService, $filter, $timeout, $compile) {
    $scope.entidadeName = 'GradePolo'; // nome da entidade deste controller
    $rootScope.log($scope.entidadeName, 'Starter');
    $scope.listGetAux = ['Polo', 'Usuario', 'Curso', 'Modulo']; // ira definir os cadastros auxiliares necessarios a carga desta pagina. Caso zeja 0, não será carregado

    /** Menu de contexto **/
    $scope.GradePoloContextItens = [
        //{'link':'GradePoloOnClick(GradePolo)', 'title': '<i class="fa fa-eye" aria-hidden="true"> </i> Ver'},
        //{'link': 'GradePoloOnEdit(GradePolo)', 'title': '<i class="fa fa-edit" aria-hidden="true"> </i> Abrir'},
        //{'link': 'GradePoloRemove(GradePolo)', 'title': '<i class="fa fa-trash" aria-hidden="true"> </i> Remover'}
    ];
    $timeout(function () {
        // Substituição do incone para adicionar
        $("#btnTemplateNew").html('<i class="fa fa-4x fa-exchange" aria-hidden="true"></i>');
    });


    /** Para tratar os linktables, caso exista **/
    $scope.setVinculosOnEdit = function (id) {
        if (!id) {
            $scope.Aux.Modulos = $scope.Data.Modulos;
        }
    };


    // vai injetar as funções padrão
    $rootScope.trataEditOnLoad($scope);

    // Reescrito
    $scope.GradePoloGetAll = function () {};
    $scope.GradePolos = [];

    $scope.init = function (text, reload) {
        $rootScope.loading('show', text, 'modal');
        //delete ($scope.Data);
        //delete($scope.Professores);
        if (reload) {
            $scope.GradePolos = [];
            $scope.Data = [];
            $scope.Professores = [];
        }
        text = ((text) ? text : 'Obtendo grade curricular');
        $scope.working = true;
        $scope.Args.gdf = true;
        delete($scope.adminTemplateHtmlExtra);


        $rootScope.loading('show', text, 'modal');
        DataLoadService.getContent('GradePolo', 'gdf', $scope.Args, function (data) {
            console.info('GDF', data);
            if (data.error === false) {
                $rootScope.loading('show', 'Montando grade', 'modal');
                $scope.working = false;
                $scope.Data = data.content;
                $scope.Data.modulos = $filter('orderBy')($scope.Data.modulos, 'nomeModulo');
                $scope.GradePolos = data.content.list;
                $scope.Professores = data.content.Professores;
                $rootScope.setTypes();

                // Primeira linha antes da tabela
                $scope.adminTemplateHtmlExtra = data.content.legend;

            } else if (data.error === 'init') {
                //$scope.Args.dataInicial = data.content.semestre.inicio;
                //$scope.Args.dataFinal = data.content.semestre.fim;
                $scope.Semestres = data.content.semestres;

                // Ação ao selecionar um semestre, para definir as datas minimas
                $scope.setMinMaxDateToEncontro = function () {
                    $set = $filter('filter')($scope.Semestres, {id: $scope.Args.semestre}, true)[0];
                    //console.info('', $set);
                    $scope.encontroMinDate = $set.min_date;
                    $scope.encontroMaxDate = $set.max_date;
                    $scope.Args.primeiroEncontro = '';
                };

                $timeout(function () {
                    $("#swal2-content").addClass('text-left').html($compile(data.content.html)($scope));
                }, 500);
                $rootScope.swalConfirm({
                    title: 'Grade curricular',
                    html: '<i class="fa fa-refresh fa-spin mr-1"></i>Obtendo dados...',
                    icon: false,
                    showCancelButton: false,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: false,
                    confirmButtonText: 'Continuar <i class="fa fa-arrow-right"></i>',
                    cancelButtonText: 'Cancelar',
                    reverseButtons: true
                }, function () {
                    $scope.init(false, true);
                });
            } else {
                $scope.initErrors = data.error;
                $scope.adminTemplateHtmlExtra = '<div class="text-left mt-5 pl-5 ml-5"><p class="">Para visualizar os dados, aplique os filtros acima corretamente</p>'
                        + '<br/>Filtros obrigatórios: <ul>';
                angular.forEach(data.error, function (v, k) {
                    $scope.adminTemplateHtmlExtra += '<li>' + v + '</li>';
                });
                $scope.adminTemplateHtmlExtra += '</ul>'
                        + '</div>';
            }
            $timeout(function () {
                $rootScope.loading();
            }, 500);


        });
    };
    $scope.init(false, true);

    /* @rever : Testes locais 
     $timeout(function () {
     $scope.Args.idCurso = 7;
     $scope.Args.semestre = '2020_1';
     $scope.Args.primeiroEncontro = '12/03/2020';
     $(".swal2-confirm").click();
     }, 1500);
     /* */



    $scope.saveByDate = function (item) {
        $rootScope.loading('show', 'Salvando dados', 'modal');
        angular.forEach(item, function (v, k) {
            if (v.idGradePolo > 0) {
                v._saveByDate = true;
                $scope.save(v, true);
            }
        });
        item._isChanged = false;
        $timeout(function () {
            $scope.init('Atualizando grade');
        }, 1000);

    };



    // Salvar um item individual
    $scope.workingSave = {};

    $scope.save = function (item, ignoreReload) {
        $scope.workingSave['_' + item.idPolo] = true;
        item.aviso = null;

        DataLoadService.getContent('GradePolo', 'save', item, function (data) {
            console.info('Data', data);
            $scope.workingSave['_' + item.idPolo] = false;
            if (data.error === false) {
                item.idGradePolo = data.content.idGradePolo;

                if (item._saveByDate !== true) {
                    $rootScope.setAlert(data);
                    $timeout(function () {
                        $scope.init('Atualizando encontro');
                    }, 500);
                }
            } else {
                $rootScope.setTemplate(data.error);
                item.aviso = 'Verifique: ' + $rootScope.Template;
            }
        });
    };

    $scope.filterCursoByPolo = function () {
        return function (item) {
            $out = false;
            angular.forEach($scope.CursosFilter, function (v, k) {
                if (item.idCurso === v.idCurso) {
                    $out = true;
                }
            });
            return $out;
        };
    };

    $scope.filterModulosByPolo = function () {
        return function (item) {
            $out = false;
            angular.forEach($scope.ModulosFilter, function (v, k) {
                if (item.idModulo === v.idModulo) {
                    $out = true;
                }
            });
            return $out;
        };
    };

    $scope.filterProfessorByModulo = function () {
        return function (item) {
            $out = false;
            angular.forEach($scope.ProfessoresFilter, function (v, k) {
                if (item.idUsuario === v.id && $scope.GradePolo.idModulo === v.idModulo) {
                    $out = true;
                }
            });
            return $out;
        };
    };

    $scope.setIdPolo = function () {
        if ($scope.GradePolo.idCurso > 0) {
            DataLoadService.getContent('GradePolo', '336', {idPolo: $scope.GradePolo.idPolo, idCurso: $scope.GradePolo.idCurso}, function (data) {
                $scope.ModulosFilter = data.content;
                $scope.ProfessoresFilter = [];
                angular.forEach(data.content, function (v, k) {
                    angular.forEach(v.professores, function (prof, key) {
                        prof.idModulo = v.idModulo;
                        $scope.ProfessoresFilter.push(prof);
                    });
                });
            });
        } else {
            DataLoadService.getContent('GradePolo', '331', {idPolo: $scope.GradePolo.idPolo}, function (data) {
                $scope.CursosFilter = data.content;

            });
        }
    };

    $scope.GradePoloOnEdit = function () {
        $scope.Args.idCurso = null;
        $scope.init(false, true);
    };

    $scope.setChange = function (linha, item, tipo) {
        console.info('linha', linha);
        console.info('item', item);
        linha._isChanged = true;
        $timeout(function () {
            $scope.$apply();
        }, 500);

    };
});