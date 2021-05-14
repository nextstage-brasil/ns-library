app.controller('UnigraceController', function UnigraceController($rootScope, $scope, $http, $timeout) {
    //console.log('UnigraceController');
    $scope.Form = _data;


    $scope.send = function () {
        //console.info('FORM', $scope.Form);
        $scope.Form.idCurso = 7; // fixo para este form
        $rootScope.loading('show', 'Enviando matricula', 'modal');
        $rootScope.call('site/inscricao', $scope.Form, false, function (data) {
            //console.info('site/inscricao', data);
            /*$rootScope.setAlert(data, true);*/
            $rootScope.loading();
            if (data.error === false) {
                var html = '<div class="alert alert-sucess"><h3>Sua inscrição foi registrada com sucesso!</h3></div>';
                var pgto = '<div class="text-center"><p style="text-align: center;"><strong>INSCRIÇÕES E PAGAMENTO DOS MÓDULOS<br>DEVERÃO SER FEITOS NA CONTA:&nbsp;<br>'
                        + '</strong>Banco do Brasil<br><strong>Agência*</strong>: 3174<br><strong>Conta**</strong>: 25004-X<br/>CNPJ</strong>: 01.191.423/0001-07<br><strong></p>'
                        + '<p style="text-align: center;">Ou pela chave <strong>PIX</strong>:<br/>secretaria.institucional@palavravivachurch.org</p>'
                        + '<p style="text-align: center;"><small>Se for solicitado o dígito:<br/>Na agência adicione o número "7": 3174-7. <br/>Na conta troque o "X" pelo número "0": 25004-0.</small></p>'
                        + '</div>';
                $(".ns-content").html(html);
                setTimeout(function () {
                    $(".ns-form").html(pgto);
                }, 500);
                
            } else {
                $rootScope.setAlert(data);
            }
        });

    };
});