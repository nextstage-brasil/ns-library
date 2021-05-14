app.controller('LoginController', function ($scope, $rootScope, DataLoadService) {
    console.log('LoginController');
    
    sessionStorage.clear();
    $scope.Login = {};
    $scope.login = function () {
        $scope.working = true;
        $rootScope.loading('show', 'Acessando sistema, aguarde', 'modal');
        DataLoadService.getContent('login', 'enter', $scope.Login, function (data) {
            //$rootScope.setAlert(data);
            $rootScope.loading('hide');
            console.info('Login Result', data.content);
            $scope.working = false;

            if (data.error === 'SW') {
                // Texto a ser exibido
                $scope.Empresas = data.content.Empresas;
                var args = {
                    'scope': $scope,
                    'title': 'Escolha',
                    'body': '<div class="row"><div class="col-12" style="margin-bottom: 10px; padding: 5px;" ng-repeat="ig in Empresas">'
                            + '<a data-dismiss="modal" class="btn btn-lg btn-primary btn-block btn-signin text-center" ng-click="setEmpresa(ig)">'
                            + '<img src="{{ig.logo}}" class="img img-circle" />'
                            + '<br/><strong>{{ig.nome}}</strong>'
                            + '</a></div></div><div class="clearfix"></div>'
                };
                $rootScope.showAlertModal(args);
            } else if (data.error === false) {
                $rootScope.loading('show', 'Bem vindo ' + data.content.nomeUsuario + '!');
                sessionStorage.setItem('user', JSON.stringify(data.content));
                
                sessionStorage.setItem('tokenApi', data.token);
                console.info('Setou primeiro token');
                sessionStorage.setItem('CS_TK_CST', data.token);
                window.location = appConfig.urlCloud + 'home';
            } else {
                $rootScope.setAlert(data);
            }



        });
    };
    $scope.forgotPassword = function () {
        if ($scope.Login.username !== '') {
            $rootScope.loading('show');
            DataLoadService.getContent('Usuario', 'esqueciSenha', $scope.Login, function (data) {
                console.info('EsqueciSenhaDATA', data);
                $rootScope.setAlert(data, true);
            });
        } else {
            alert('Informe seu login');
        }
    };
    $scope.setEmpresa = function (val) {
        $scope.Login.idEmpresa = val.idEmpresa;
        $scope.login();
    };

    // vai abrir o modal do cadastro
    $scope.cadastroShow = function () {
        $("#cadastrar").modal({backdrop: 'static'});
    };

    $scope.cad = {
        isf: true
    };

    $scope.cadastroSend = function () {
        $scope.working = true;
        $rootScope.loading('show', 'Enviando seu cadastro', 'modal');
        DataLoadService.getContent('Usuario', 'cadastro', $scope.cad, function (data) {
            console.info('Data', data);
            $scope.working = false;
            $rootScope.setAlert(data, true);
        });
    };

});


$(document).ready(function () {
    $("#user-name").focus();
    /*
     var padding_top = (window.innerHeight - 30 - $('#loginform').css('height').replace('px', '')) / 2;
     $('.content').css({'padding-top': padding_top + 'px'});
     $(".bg").animate({blurRadius: 7}, {
     duration: 500,
     easing: 'swing', // or "linear"
     // use jQuery UI or Easing plugin for more options
     step: function () {
     //console.log(this.blurRadius);
     $('.bg').css({
     "-webkit-filter": "blur(" + this.blurRadius + "px)",
     "filter": "blur(" + this.blurRadius + "px)"
     });
     },
     complete: function () {
     $("#loginform").fadeIn();
     $('.form-container').fadeIn();
     }
     });
     */
});
