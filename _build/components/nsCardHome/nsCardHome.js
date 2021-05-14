app.directive('nsCardHome', function () {
    var ddo = {};
    ddo.restrict = "E";
    ddo.transclude = true;
    ddo.scope = {
        cardClass: '@',
        title: '@',
        valor: '@',
        descricao: '@',
        click: '&'
    };
    ddo.templateUrl = appConfig.urlCloud + 'auto/components/nsCardHome.html';
    return ddo;

});
