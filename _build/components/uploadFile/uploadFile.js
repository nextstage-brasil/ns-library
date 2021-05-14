app.directive('uploadFile', function () {
    var ddo = {};
    ddo.restrict = "E";
    ddo.transclude = false;
    ddo.scope = {
        // Definições obrigatórias
        entidade: '@',
        valorid: '@',
        // Definições opcionais
        badgeId: '@',
        anexar: '@', // define se exibe botão anexar. Default false
        modelo: '@', // define se exibe botão modelos. Default false;
        multiple: '@', // define se permite anexar multiplos arquivos. Default: true;
        btnText: '@', // texto do botão UPLOAD. Default: Escolher Arquivo
        btnIcon: '@', // ICON da aplicação. Default: Plus
        maxsize: '@', // tamanho maximo de imagem paara redimensionar antes de upload. Default: 1600px;
        avatar: '=', // link da imagem atual de avatar
        args: '=' // args para o search
    };
    ddo.templateUrl = appConfig.urlCloud + 'auto/components/uploadFile.html';
    ddo.controller = function ($scope, $rootScope, $filter, $compile, $timeout, DataLoadService) {
        //console.log('uploadfile starter - ' + $scope.entidade);
        $scope.progress = 00;
        $scope.sendItem = 0;
        $scope.progressItem = 0;
        $scope.btnText = $scope.btnText ? $scope.btnText : 'Adicionar';
        $scope.btnIcon = $scope.btnIcon ? $scope.btnIcon : 'plus';
        $scope.maxsize = $scope.maxsize ? $scope.maxsize : 3000;
        $scope.showAnexar = $scope.anexar === 'true' ? true : false;
        $scope.showTemplate = $scope.modelo === 'true' ? true : false;
        $scope.idInput = Math.ceil(Math.random() * Math.pow(10, 10));
        $scope.multiple = $scope.multiple === 'false' || $scope.avatar ? 'false' : 'true';
        $scope.first = false;
        $scope.list = [];
        $scope.titleOriginal = $('title').text();



        if ($scope.avatar) {
            $scope.entidade = 'Uploadfile';
            $scope.valorid = 1;
        }
        //$scope.avatar = $scope.avatar === 'true' ? true : false;
        $scope.list = [];
        $scope.pagina = 0;

        $scope.contextItens = [
            {'link': 'show(item)', 'title': '<i class="fa fa-eye" aria-hidden="true"> </i> Visualizar'},
            {'link': 'renameShow(item)', 'title': '<i class="fa fa-edit" aria-hidden="true"> </i> Renomear'},
            {'link': 'download(item)', 'title': '<i class="fa fa-download" aria-hidden="true"> </i> Download'},
            {'link': 'remove(item)', 'title': '<i class="fa fa-trash" aria-hidden="true"> </i> Remover'}
        ];


        // validação de requerimentos minimos
        $timeout(function () {
            if (!$scope.entidade || !$scope.valorid) {
                throw new Error('Diretiva uploadFile: required is invalid!');
            }
            var largThumbs = $(".uploadfileBoxThumbs").css('width');
            $(".uploadfileBoxThumbs").css({'height': largThumbs * 94.4 / 100 + 'px'});

        }, 1000);




        $scope.$watch('valorid', function (n, o) {
            $timeout(function () {
                console.log('RENEw valorid:' + $scope.valorid);
                if (n !== o) {
                    console.log('NOVO Uploadfile valorid ' + $scope.entidade);
                    $scope.init();
                } else {
                    console.log('RENEw valorid. Mesmo valor: ' + n + '-' + o);
                }
            }, 500);
        });

        // criar component controller
        //$scope.uploader = DataLoadService.getUploadFile($scope.maxsize, $scope.thumbs, $scope.entidade, $scope.valorid, $scope.args);



        $scope.getfiles = function (reload) {
            if (reload) {
                $scope.pagina = 0;
                $scope.first = false;
                $scope.list = [];
            }
            if ($scope.pagina >= 0) {

                $scope.args.pagina = $scope.pagina;
                $scope.working = true;
                DataLoadService.getContent('Uploadfile', 'getFiles', $scope.args, function (data) {
                    $scope.pagina++;
                    //console.info('LISTA DE ARQUIVOS', data);
                    $rootScope.loading('hide');
                    if (data.content.files) {
                        if (data.content.files.length <= 0) {
                            $scope.pagina = -1;
                        }
                    }
                    if (data.error === false) {
                        angular.forEach(data.content.files, function (v, k) {
                            $scope.list.push(v);
                        });
                    }
                    if (data.content.count) {
                        $rootScope.setValueAnimate($("#" + $scope.badgeId), data.content.count);
                    }
                    $scope.working = false;

                });
            }

        };


        // mostrar incialmente arquivos na raiz, limitados a 100;
        $scope.init = function () {
            // Args
            if (typeof $scope.args == 'undefined') {
                $scope.args = {};
            }

            console.log('Valorid: ' + $scope.valorid);
            $scope.pagina = 0;
            $scope.working = true;
            $scope.args.entidade = $scope.entidade;
            $scope.args.valorid = $scope.valorid;
            if ($scope.pagina >= 0) {
                if ($scope.avatar) {
                    if ($scope.avatar.idUploadfile > 0) {
                        DataLoadService.getContent('Uploadfile', 'getById', {idUploadfile: $scope.avatar.idUploadfile}, function (data) {
                            //console.info('getAvatarInit', data);
                            $scope.avatar = data.content;
                            $rootScope.loading('hide');
                        });
                    } else {
                        $rootScope.loading('hide');
                        $scope.avatar.thumbs = appConfig.urlCloud + 'view/images/sem-imagem.png';
                    }
                } else {
                    $scope.getfiles(true); // na carga, reload 
                }
            }

            $scope.uploader.headers.Data = DataLoadService.param($scope.args);

            console.info('ARGS valorid', $scope.uploader);

        };


        // criar component controller
        $scope.uploader = DataLoadService.getUploadFile($scope.maxsize, $scope.thumbs, $scope.entidade, $scope.valorid, $scope.args);
        console.info('uploader valorid', $scope.uploader);


        $scope.uploader.onProgressItem = function (fileItem, progress) {
            //console.info('progress', progress);
            $scope.progress = progress;
        };
        // chama qunado um item foi uploaded success
        $scope.uploader.onSuccessItem = function (fileItem, response, status, headers) {
            $rootScope.setAlert(response);
            //console.info('onSuccessItem', fileItem);



            //console.info('UPLOADFILE-RESPONSE', response);
            $rootScope.loading('hide');
            if (response.status === 200) {
                $scope.uploader.filaTamanhoEnviado += fileItem._file.size;

                // item enviado e salvo com sucesso. Atualizar ng-model
                // 27/09/2019 - removido do push, pois ao finalizar, irei chamar init p´ra vir paginado as fotos.
                //$scope.list.push(response.content);


                if ($scope.avatar) { // substituir o avatar atual
                    $scope.avatar = response.content;
                    $('body').trigger('mousemove');

                    /* deixa o ato de salvar pra entidade que invocou
                     DataLoadService.getContent($scope.entidade, 'setAvatar', {entidade: $scope.entidade, valorid: $scope.valorid, idUploadfile: response.content.idUploadfile}, function (data) {
                     //console.info('UploadfileSetAvatar', data);
                     });
                     */
                }
            }
        };
        $scope.uploader.onCompleteAll = function () {
            $(".btnsAdmin").fadeIn(); // retornarbotoes de salvar somente após conclusão de envio de arquivos
            $scope.progress = false;
            $scope.uploader.countFileUpload = 0;
            $scope.progressItem = false;
            $scope.uploader.filaTamanhoEnviado = 0;
            $scope.uploader.clearQueue();
            $scope.sendItem = 0;
            $scope.uploader.filaTamanho = 0;
            $rootScope.loading('hide');
            var data = {content: {result: ''}, error: false};
            if (!$scope.avatar) { // substituir o avatar atual
                $rootScope.setAlert(data, true, 'success', 'Envio de Arquivos Finalizado', 'success');
                $timeout(function () {
                    $scope.init();
                }, 2500);
            } else {
                $rootScope.setAlert(data);
            }
            $('title').text($scope.titleOriginal);
            $rootScope.setValueAnimate($("#" + $scope.badgeId), $scope.list.length);
        };

        $scope.uploader.onCompleteItem = function (fileItem, response, status, headers) {
            $scope.sendItem++;
            $scope.progressItem = $scope.sendItem / $scope.uploader.countFileUpload * 100;

            //fileItem.cancel();
            /*
             $rootScope.loading('show', 'Enviado ' + $scope.sendItem + '/' + $scope.uploader.countFileUpload + ' arquivos');
             */

            $('title').text('(' + $scope.uploader.progress + '% Enviado');

            $scope.progress = 0;
            //console.log('progressItem: ' + $scope.progressItem);

            // Atualizar o token a cada n itens, pois o upload pode durar mais que a sessão ativa permite
            if ($scope.sendItem % 10 === 0) {
                //console.log('Token renovado após 5 uploads');
                //console.info('success-item', response);
                sessionStorage.setItem('CS_TK_CST', response.token);
                if (response.expire > 0) {
                    $rootScope.tempoSessao(response.expire);
                }
                // renovar token da conexão
                $scope.uploader.headers.Token = response.token;
            }

        };
        $scope.uploader.onErrorItem = function (item, response, status, headers) {
            //console.info("Upload-ERRO-SERVICE", response);
            $(".btnsAdmin").fadeIn(); // retornarbotoes de salvar somente após conclusão de envio de arquivos
            switch (status) {
                case 401: // token inválido
                    window.location = $rootScope.urlCloud + 'logout';
                    break;
                default:
                    var error = response.error !== null ? response.error : 'Ocorreu um erro não identificado';
                    $('#barraAviso').hide();
                    $(".modal").modal('hide');
                    error = '<div class="text-center">' + error + '</div>'
                            + '<div class="text-info text-center" style=:"margin-top:35px;">Esta mensagem fechará em <span class="tempoToCloseAviso">10 segundos<br/></span></div>';
                    $("#content").html('<div class="text-center" style="margin-top:55px;">' + error + '</div>');
                    setInterval(function () {
                        window.location = $rootScope.urlCloud;
                    }, 120000);
                    throw new Error(status);
                    break;
            }

        };
        // ação ao clicar no botão de adicionar
        $scope.openUpload = function (entidade) {
            //$rootScope.uploadFileEntidade = entidade; // salva o nome pra usar em FILES no controller de fora
            document.getElementById($scope.idInput).click();
        };

        // processo para atachar arquivos neste uploadfile
        $scope.attachShow = function (entidade) {
            $rootScope.uploadFileEntidade = entidade; // salva o nome pra usar em FILES no controller de fora
            $("#divUfAttach_" + $scope.idInput).html($compile('<combo-search label="Nome do arquivo" model="newAttached" initial="" ws-type="Uploadfile" ws-action="searchByName"></combo-search>')($scope));
            $timeout(function () {
                $("#ufAttach_" + $scope.idInput).modal({backdrop: "static"});
            }, 100);
        };
        $scope.attachAdd = function (idUploadfile) {
            //console.log('Atachar 2' + idUploadfile);
            $rootScope.loading('show', 'Anexando arquivo');
            $("#ufAttach_" + $scope.idInput).modal('hide');
            $scope.newAttached = -1;
            DataLoadService.getContent('uploadfile', 'getById', {idUploadfile: idUploadfile}, function (data) {
                //console.info('Data', data);
                $scope.working = false;
                $rootScope.setAlert(data);
                if (data.error === false) {
                    data.content.entidadeUploadfile = 'CLASSIFICAR';
                    $rootScope.uploadFileName = data.content;
                }
            });
        };

        $scope.fromTemplateShow = function (entidade) {
            $("#divUFTemplate_" + $scope.idInput).html($compile('<ns-template modal-id="ufNsTemplate_{{idInput}}"></ns-template>')($scope));
            $("#ufNsTemplate_" + $scope.idInput).modal({backdrop: "static"});
            $timeout(function () {
                $(".nsTemplateTitle").hide();
            }, 500);
        };

        // Gatilho para anexar arquivo em <search>
        $scope.$watch("newAttached", function (n, o) {
            if (n > 0) {
                $scope.attachAdd(n);
            }
        });
        /*
         $scope.$watch("uploader.queue.length", function (n, o) {
         if (n > 0) {
         $rootScope.loading('show', 'Preparando Arquivos', 'modal');
         }
         });
         */

        // Abre o modal para enviar os arquivos
        $scope.openModal = function () {
            $("#modalSend_" + $scope.idInput).modal({backdrop: 'static'});
        };
        $scope.save = function () {
            DataLoadService.getContent('Uploadfile', 'save', $scope.Uploadfile, function (data) {
                $rootScope.setAlert(data);
            });
        };

        $scope.show = function (item) {
            //console.info('show-uploadfile', item);
            $("#uploadFileViewer").html($compile('<ns-media-player file-id="' + item.idUploadfile + '"></ns-media-player>')($scope));
        };

        $scope.renameShow = function (item) {
            $scope.Uploadfile = item;
            $("#ufRename_" + $scope.idInput).modal({backdrop: "static"});
        };


        $scope.renameSave = function () {
            DataLoadService.getContent('Uploadfile', 'saveName', {id: $scope.Uploadfile.idUploadfile, name: $scope.Uploadfile.nomeUploadfile}, function (data) {
                //console.info('Data', data);
                $scope.working = false;
                $rootScope.setAlert(data);
                if (data.error === false) {
                    // ações a executar se positivo
                }
            });


        };

        $scope.download = function (item) {
            alert('em desenvolvimento');
        };

        $scope.remove = function ($item, $index) {
            if (confirm('Remover arquivo?')) {
                //$scope.list.splice($index, 1);
                $scope.list = $rootScope.arrayFilter($scope.list, 'idUploadfile', $item.idUploadfile);
                DataLoadService.getContent('Uploadfile', 'remove', $item, function (data) {
                    //console.info('uploadfile-remove', data);
                    if (data.error !== false) {
                        $rootScope.setAlert(data);
                        $scope.list.push($item);
                    }
                    $("#" + $scope.badgeId).html($scope.list.length);
                });
            }
        };

        $timeout(function () {
            if ($scope.avatar) {
                // CALLBACKS
                $scope.uploader.onAfterAddingFile = function (item) {
                    $rootScope.loading({backdrop: 'static'});
                    $scope.avatar.thumbs = appConfig.urlCloud + 'view/images/processando_live.gif';
                    item.upload();
                };
            }
        }, 500);


        $scope.init();

    };
    return ddo;

});
