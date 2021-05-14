app.directive('nsMediaPlayer', function () {
    var ddo = {};
    ddo.restrict = "E";
    ddo.transclude = false;
    ddo.scope = {
        fileId: '@'
    };
    ddo.templateUrl = appConfig.urlCloud + 'auto/components/nsMediaPlayer.html';
    ddo.controller = function ($scope, $rootScope, $timeout, DataLoadService) {
        $(".midia-controle-show").hide();

        $scope.showMidia = function () {
            var v = $scope.fileId;
            //$scope.working = true;
            $rootScope.loading('show', 'Preparando exibição do arquivo', 'modal');
            $scope.midia = {};
            console.info('nsMediaPlayer-Get', $scope.fileId);
            DataLoadService.getContent('uploadfile', 'getById', {idUploadfile: $scope.fileId}, function (data) {
                console.info('FILE', data);
                //$scope.working = false;
                if (data.error === false) {
                    $(".midia-controle-show").hide();
                    $scope.uploadfile = data.content;
                    $scope.midia.mime = data.content.mimeUploadfile;
                    $scope.midia.title = data.content.nomeUploadfile;
                    $scope.midia.url = data.content.filenameUrl;
                    $scope.midiaContainerShow();
                    $rootScope.setAlert(data);
                } else {
                    $rootScope.setAlert(data);
                }
            });
        };
        $scope.showMidia();


        $scope.midiaContainerClose = function () {
            var audio = $(".sound-player");
            for (i = 0; i < audio.length; i++) {
                audio[i].pause();
            }
            $("#midia-container").fadeOut();
        };

        $scope.midiaContainerShow = function () {
            $scope.text = '';
            $h = $(window).height(); // New height

            $(".mediaplayer-div").css({'height': $h + 'px', top: '0px', left: '0px', width: '100%'});
            if ($scope.midia.mime.indexOf('audio') > -1) {
                $scope.mime = 'audio';
            } else if ($scope.midia.mime.indexOf('video') > -1) {
                $scope.mime = 'video';
            } else if ($scope.midia.mime.indexOf('image') > -1) {
                $scope.mime = 'image';
            } else if ($scope.midia.mime.indexOf('pdf') > -1 || $scope.midia.mime.indexOf('text/plain') > -1) {
                $scope.mime = 'iframe';
            } else {
                $scope.mime = 'others';
                $scope.text = '<h6>Arquivo não disponível para prévia visualização</h6>';
            }
            $scope.changeSrc($scope.uploadfile.filenameUrl, $scope.mime);
        };

        $scope.changeSrc = function (filename, tipo) {
            var container = $("#midia-container");
            tipo = tipo ? tipo : 'audio';

            $("#" + tipo + "-player-source").attr('src', filename);

            var audio = $(tipo + ".sound-player");
            if (audio.length > 0) {
                audio[0].pause();
                audio[0].load();//suspends and restores all audio element
                audio[0].oncanplaythrough = audio[0].play();
            }

            $("#midia-audio").show();
            $timeout(function () {
                container.fadeIn();
            }, 500);
            $timeout(function () {
                if (tipo === 'audio') {
                    $(".mediaplayer-div").animate({height: '30%', top: '50px'});
                }
                if (tipo === 'image') {
                    //$(".mediaplayer-div").animate({height: '500px', top: '0px'});
                }
            }, 1000);
        };

    };
    return ddo;

});
