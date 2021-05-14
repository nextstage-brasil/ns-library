app.directive('alertModal', function () {
    var ddo = {};
    ddo.restrict = "E";
    ddo.transclude = true; // para poder ler o conteudo entre as tags
    ddo.scope = {
        modalId: '@',
        title: '@',
        confirmAction: '&',
        cancelAction: '&',
        btnConfirmText: '@',
        nsvar: '=' // variavel comum
    };
    ddo.templateUrl = appConfig.urlCloud + 'auto/components/alertModal.html';
    ddo.controller = function ($scope) {
        console.log('Create modal: ' + $scope.modalId);
        $scope.btnConfirmText = $scope.btnConfirmText ? $scope.btnConfirmText : 'Fechar';
    };
    return ddo;
});

/** To Use:
<alert-modal modal-id="" title="" confirm-action="" cancel-action="" btn-confirm-text=""></alert-modal>
 * 
 */
