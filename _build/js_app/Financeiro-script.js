app.controller("FinanceiroController", function ($rootScope, $scope, DataLoadService, $filter, $timeout, $compile) {
    $scope.entidadeName = 'Financeiro'; // nome da entidade deste controller
    $rootScope.log($scope.entidadeName, 'Starter');
    //$scope.listGetAux = ['Matricula', 'Status', 'Auxiliar', 'FormaPgto', 'Conta']; // ira definir os cadastros auxiliares necessarios a carga desta pagina. Caso zeja 0, não será carregado
    $scope.listGetAux = [];

    /** Menu de contexto **/
    $scope.FinanceiroContextItens = [
        //{'link':'FinanceiroOnClick(Financeiro)', 'title': '<i class="fa fa-eye" aria-hidden="true"> </i> Ver'},
        {'link': 'FinanceiroOnEdit(Financeiro)', 'title': '<i class="fa fa-edit" aria-hidden="true"> </i> Abrir'},
        {'link': 'FinanceiroRemove(Financeiro)', 'title': '<i class="fa fa-trash" aria-hidden="true"> </i> Remover'}
    ];

    /** Para tratar os linktables, caso exista **/
    $scope.setVinculosOnEdit = function (id) {};

    // vai injetar as funções padrão
    $rootScope.trataEditOnLoad($scope);

    // Demais funções exclusivas deste componente
    $scope.FinanceiroOnEdit = function (Financeiro) {
        Swal.fire('Em desenvolvimento');
    };

    $scope.init = function () {
        $rootScope.setDateRange(_initDate, _endDate, $scope.Args);
        $timeout(function () {
            // Substituição do incone para adicionar
            $(".btnTemplateNewDiv").removeClass('d-sm-block').hide();
            $("#btnTemplateNew").removeClass('d-block').addClass('d-none');
            $(".btnAdd").removeClass('d-block').addClass('d-none');
            $(".nsTemplateSearch").addClass('d-none');

            // Reescrever o tamanho dos filtros
            $('.nsTemplateMyFilters').removeClass('col-sm-7').addClass('col-sm-11');
        });
        $scope.Total = {
            recebido:982.54, 
            receber: 654.21, 
            inadimplentes: 21, 
            outros: 48
        };
    };
    $scope.init();

});