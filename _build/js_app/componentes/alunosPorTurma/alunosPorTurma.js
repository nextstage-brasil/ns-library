app.directive('alunosPorTurma', function () {
    var ddo = {};
    ddo.restrict = "E";
    ddo.transclude = false; // para poder ler o conteudo entre as tags
    ddo.scope = {
        codTurma: '@'
    };
    ddo.templateUrl = appConfig.urlCloud + 'auto/components/alunosPorTurma.html';
    ddo.controller = function ($scope, $rootScope, $filter, $compile, $timeout, DataLoadService) {
        $timeout(function () {
            $("#alunosPorTumaTabContent").css({
                'max-height': window.innerHeight / 2,
                'overflow-y': 'auto',
                'padding': '10px'
            });
        }, 500);

        // Modulo inicial da operação
        $scope.init = function () {
            if ($scope.codTurma.length > 0) {
                $scope.working = true;
                $rootScope.loading('show', 'Processando', 'modal');
                DataLoadService.getContent('GradePolo', 'alunosPorTurma', {codTurma: $scope.codTurma}, function (data) {
                    console.info('alunosPorTurma', data);
                    $scope.working = false;
                    $rootScope.setAlert(data);
                    if (data.error === false) {
                        $scope.Alunos = data.content.Alunos;
                        $scope.Turma = data.content.Turma;
                    }
                });
            }
        };
        $scope.$watch('codTurma', function () {
            $scope.init();
        });

        // Define a confirmação do alujno no módulo
        $scope.confirmarGradeAluno = function (item, escolha) {
            $scope.confirmarGradeAlunoData = {
                codTurma: $scope.Turma.codTurma,
                idMatricula: item.idMatricula,
                st: escolha
            };
            var config = {icon: false};
            var html_extra = '<p>Após confirmar esta ação, o sistema irá remover também a pendência financeira deste módulo caso ainda esteja em aberto</p>'
                    + '<span class="text-mutted">Confirmar remover aluno deste módulo?</span>';

            switch (escolha) {
                case 1:
                    config.title = 'Confirmação de aluno em módulo';
                    config.confirmButtonText = '<i class="fa fa-check mr-1"></i>Sim, confirmar';
                    html_extra = '<p>Após confirmar esta ação, o sistema irá gerar a pendência financeira para pagamento referente ao módulo</p>'
                            + '<span class="text-mutted">Confirmar aluno neste módulo?</span>';
                    config.confirmButtonColor = 'success';
                    break;
                case 2:
                    config.title = 'Não confirmação de aluno em módulo';
                    config.confirmButtonText = '<i class="fa fa-times mr-1"></i>Sim, remover';
                    config.confirmButtonColor = '#dc3545';
                    break;
                default:
                    config.title = 'Aluno pendente de decisão';
                    config.confirmButtonText = '<i class="fa fa-question mr-1"></i>Pendente';
                    config.confirmButtonColor = '#dc3545';
                    break;
            }
            var html = $("#alunoPorTurmaHeader").html() + $("#alunosPorTurmaConfirmacaoInclusao").html() + html_extra;
            $rootScope.swalConfirm(config, function () {
                $scope.working = true;
                $rootScope.loading('show', 'Processando... ', 'modal');
                DataLoadService.getContent('GradeAluno', 'confirmarGradeAluno', $scope.confirmarGradeAlunoData, function (data) {
                    console.info('Data', data);
                    $scope.working = false;
                    $rootScope.setAlert(data);
                    if (data.error === false) {
                        $scope.init();
                    }
                });
            }, html, $scope);
        };
    };
    return ddo;
});