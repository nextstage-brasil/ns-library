app.controller("UsuarioController", function ($rootScope, $scope, DataLoadService, $filter, $timeout, $compile) {
    console.info('LogDeUsuario', "UsuarioController");
    $scope.Usuario = {}; // objeto setado
    $scope.UsuarioNovo = {}; // entidade vazia para criação de novo objeto
    $scope.Usuarios = []; // array de objetos
    $scope.List = true; //Controla a exibição de List ou Form
    $scope.hold = {title: 'TITLE-HOLD', text: 'TEXT-HOLD'}; // Titulo e texto para evento Hold
    $scope.filtro = []; // fltro utilizado na seleção da listagem
    $scope.scroll = 0; // armazena posição do scroll para btnVoltar
    $scope.pagina = 0;
    $scope.working = false;
    $scope.Aux = {};
    $scope.Args = {}; // argumentos utilizado em getAll
    $scope.entidadeName = 'Usuario'; // nome da entidade deste controller
    $scope.locationOrigin = angular.copy(window.location.href); // salva o lolcation original de chamada
    $scope.lastLocation = angular.copy(sessionStorage.getItem('lastLocation'));
    $(".navbar-subtitle").html($('#titlePage').html());
    $scope.switchView = $rootScope.getParameterByName('tp');
    $scope.listGetAux = [];
    $scope.fotoArgs = {};

    $scope.setVinculosOnEdit = function (id) {
        console.clear();
        console.info('USUARIO', $scope.Usuario);
        $rootScope.setTypes();
        if ($scope.switchView === '1') {
            $scope.permissoesShow($scope.Usuario);
        }
    };

    $rootScope.trataEditOnLoad($scope);


    // auxiliares somente serão carregados após página completar 1sec
    $scope.getAux = function () {
        $timeout(function () {
            DataLoadService.getContent('App', 'getAux', {}, function (data) {
                $scope.Aux.Status = [];
                angular.forEach(data.content.Ativo, function (v, k) {
                    $scope.Aux.Status.push({
                        'idStatus': parseInt(v.idAtivo),
                        'nomeStatus': v.nomeAtivo
                    });
                });
                console.info('ATIVO', $scope.Aux.Ativo);
            });

            // Obtenção de auxiliares para selects
            DataLoadService.getContent("Usuario", "perfilRead", {toAux:true}, function (data) {
                $scope.Aux.UsuarioTipo = [];
                $scope.Args.idUsuarioTipo = false;
                angular.forEach(data.content, function (v, k) {
                    $scope.Aux.UsuarioTipo.push({idUsuarioTipo: v.idUsuario, nomeUsuarioTipo: v.nomeUsuario});
                });
            });
            //$scope.viewTable = false;
        }, 1000);
    };


    // watch alterações em args id municipio
    $scope.$watch("Args.idMunicipio", function (n) {
        if (parseInt(n) > 0) {
            $scope.UsuarioGetAll('Buscando dados...', true);
        }
    });
    $scope.fnNew = 'getNew';
    switch ($scope.switchView) {
        case '1': // perfil
            $scope.telaPerfil = true;
            $scope.fnNew = 'perfilNew';
            $scope.fnGetAll = 'perfilRead';
            $scope.fnSave = 'perfilSave';
            $scope.labelTitle = 'Perfil de permissões';
            $scope.UsuarioContextItens = [
                //{'link': 'permissoesAtualizarByPerfil(Usuario)', 'title': '<i class="fa fa-cog" aria-hidden="true"> </i> Atualizar Poderes dos Usuários'},
                {'link': 'UsuarioOnEdit(Usuario)', 'title': '<i class="fa fa-edit" aria-hidden="true"> </i> Editar'},
                        //{'link': 'permissoesShow(Usuario)', 'title': '<i class="fa fa-building-o" aria-hidden="true"> </i> Permissões'},
                        //{'link': 'UsuarioRemove(Usuario)', 'title': '<i class="fa fa-trash" aria-hidden="true"> </i> Remover'}
            ];
            break;
        case '3': // usuario tipo API
            $scope.telaPerfil = true;
            $scope.fnGetAll = 'userApiList';
            $scope.fnSave = 'userApiSave';
            $scope.labelTitle = 'Integração - Permissões de Acesso';
            $scope.UsuarioContextItens = [
                {'link': 'apiKeyCopyToClipboard(Usuario)', 'title': '<i class="fa fa-copy" aria-hidden="true"> </i> Obter AppKey'},
                {'link': 'UsuarioRemove(Usuario)', 'title': '<i class="fa fa-trash" aria-hidden="true"> </i> Remover'}
            ];
            $("#templatePreList").html('EndPoint: <br/>' + appConfig.urlCloud + 'shared/{Recurso}/{Ação}/{Parametro}').addClass('alert text-center').fadeIn();
            break;
        case '5': // usuario tipo Solicitante
            $scope.telaPerfil = true;
            $scope.fnGetAll = 'userSolicitanteList';
            $scope.fnSave = 'userSolicitanteSave';
            $scope.fnNew = 'userSolicitanteNew';
            $scope.labelTitle = 'Usuários Solicitantes de Demandas';
            $scope.UsuarioContextItens = [
                {'link': 'UsuarioOnEdit(Usuario)', 'title': '<i class="fa fa-edit" aria-hidden="true"> </i> Editar'},
                {'link': 'senhaAlterar(Usuario)', 'title': '<i class="fa fa-key" aria-hidden="true"> </i> Nova senha'},
                {'link': 'UsuarioRemove(Usuario)', 'title': '<i class="fa fa-trash" aria-hidden="true"> </i> Remover'}
            ];
            //$("#templatePreList").html('EndPoint: <br/>' + appConfig.urlCloud + 'shared/{Recurso}/{Ação}/{Parametro}').addClass('alert text-center').fadeIn();
            break;
        default:
            $scope.telaPerfil = false;
            $scope.fnGetAll = 'getAll';
            $scope.fnSave = 'save';
            $scope.labelTitle = 'Pessoas';
            $scope.UsuarioContextItens = [
                {'link': 'UsuarioOnEdit(Usuario)', 'title': '<i class="fa fa-edit" aria-hidden="true"> </i> Editar'},
                {'link': 'senhaAlterar(Usuario)', 'title': '<i class="fa fa-key" aria-hidden="true"> </i> Nova senha'},
                //{'link': 'permissoesShow(Usuario)', 'title': '<i class="fa fa-building-o" aria-hidden="true"> </i> Permissões'},
                {'link': 'UsuarioRemove(Usuario)', 'title': '<i class="fa fa-trash" aria-hidden="true"> </i> Remover'}
            ];
            break;
    }

    if ($scope.switchView === '15') { // mostrar somente professores
        $timeout(function () {
            $("#onlyProfessores").trigger('click');
        }, 500);

    }
    $scope.getAux();

    // setar o pagetilte conforme o ambiente
    $timeout(function () {
        $(".page-title").html($scope.labelTitle);
    }, 1000);




    // Salvar entidade. Variavel parcial controla se foi clicado no botao Salvar final da edição
    $scope.UsuarioSave = function (parcial) {

        console.info('LogDeUsuario', "UsuarioController::save()");
        $rootScope.loading('show', 'Salvando dados', 'modal');
        $scope.working = true;
        window.scrollTo(0, 0);
        DataLoadService.getContent("Usuario", $scope.fnSave, $scope.Usuario, function (data) {
            console.info("UsuarioController::save perfil()", data);
            $rootScope.setAlert(data);
            console.info('data.content.perfilUsuario', data.content.perfilUsuario);
            console.info('$scope.PerfilAtual', $scope.Usuario.perfilUsuario);

            if (data.error === false) {
                /*
                 if (data.content.perfil && data.content.tipoUsuario == 2) { // houve troca de perfil
                 $timeout(function () {
                 Swal.fire({
                 title: 'Alteração de perfil',
                 text: "Foi feito alteração no perfil do usuário. Deseja alterar os poderes conforme perfil?",
                 icon: 'question',
                 showCancelButton: true,
                 confirmButtonColor: '#3085d6',
                 cancelButtonColor: '#d33',
                 confirmButtonText: 'Alterar!',
                 cancelButtonText: 'Manter como está'
                 }).then((result) => {
                 if (result.value) {
                 $scope.permissoesAtualizarUsuarioByPerfil(data.content);
                 }
                 });
                 
                 }, 500);
                 }
                 */
                $scope.UsuarioDoClose();
                $scope.UsuarioGetAll('Atualizando Relação', true);

            }
        });
    };

    /*
     $scope.UsuarioOnEdit = function (Usuario) {
     console.clear();
     $scope.setEntidade(Usuario, function (v) {
     console.log('Criado');
     $rootScope.trocaView('formEdit' + $scope.entidadeName, $scope.entidadeName, Usuario.nomeUsuario);
     $(".usuario_avatar").html($compile('<upload-file entidade="Usuario" valorid="' + Usuario.idUsuario + '" avatar="Usuario.avatar" args="fotoArgs"></upload-file>')($scope));
     $timeout(function () {
     //$rootScope.setLocation('usuario/' + $scope.Usuario.idUsuario + '/edit');
     }, 1000);
     });
     };
     */


    // Variavel gerada em Diretiva uploadFile, e observada em app.js. Recebe o nome do arquivo que foi feito upload
    $rootScope.$watch("uploadFileName", function (newValue, oldValue) {
        if (newValue !== '') {
            console.info('newValue', newValue);
            var file = $rootScope.uploadFileEntidade !== '' ? $rootScope.uploadFileEntidade : 'Usuario';
            if (!$scope[file].Files) {
                $scope[file].Files = [];
            }
            $scope[file]['Files'].push(newValue);
        }
    });

    // método onclick para uploadfile
    $scope.uploadFileOnClick = function ($index, file) {
        file = file ? file : 'Usuario';
        var uploadFile = $scope[file].Files[$index];
        // função que será chamada, conforme o caso
        var fn = function () {
            window.open(fileurl);
        };
        // Texto a ser exibido
        var args = {
            'title': 'Arquivos Anexados',
            'body': '<strong>Arquivo: ' + uploadFile.filenameUploadfile + '</strong>',
            'btnOk': 'Download',
            'btnNegative': 'Remover Arquivo'
        };
        // tratamento para definir image como avatar
        if (uploadFile.mimeUploadfile.indexOf('image/') > -1) {
            tipo = 'image';
            args.btnOk = 'Definir Avatar';
            fn = function () {
                $scope.working = true;
                $rootScope.loading('show', 'Processando... ', 'modal');
                $scope.Usuario.avatarUsuario = uploadFile.idUploadfile;
                DataLoadService.getContent('Usuario', 'setAvatar', $scope.Usuario, function (data) {
                    console.info('SetAvatar', data);
                    $scope.working = false;
                    $rootScope.setAlert(data);
                    if (data.error === false) {
                        $scope.Usuario = data.content;
                    }
                });
            };
        }

        // para exibição ou não do arquivo
        var fileurl = uploadFile.filenameUrl ? uploadFile.filenameUrl : $rootScope.urlCloud + '/sistema/uploadFiles/' + uploadFile.filenameUploadfile;
        if (uploadFile.filenameUploadfile.indexOf('pdf') > 0 || uploadFile.filenameUploadfile.indexOf('png') > 0 || uploadFile.filenameUploadfile.indexOf('jpg') > 0 || uploadFile.filenameUploadfile.indexOf('gif') > 0) {
            args.body = '<iframe style="width:100%; height: 570px; border: 0px; " src="' + fileurl + '"></iframe>';
        }
        // action caso o click seja positivo
        $('#btnDialogModal').prop('onclick', null).off('click').on('click', function (evt) {
            fn(); // ação definida dinamicamente
            $("#dialogModal").modal('hide');
        });
        // action caso o click seja negative
        $('#btnDialogModalNegative').prop('onclick', null).off('click').on('click', function (evt) {
            $("#dialogModal").modal('hide');
            $timeout(function () {
                $scope.uploadFileRemove($index, file);
            }, 500);
        });
        // mostra Dialog
        $rootScope.showDialogModal(args);
    };


    // método para remover files de entidade
    $scope.uploadFileRemove = function ($index, file) {
        file = file ? file : 'Usuario';
        var uploadFile = $scope[file].Files[$index];
        // Texto a ser exibido
        var args = {
            'title': 'Confirma Exclusão do Arquivo?',
            'body': '<strong>Arquivo: ' + uploadFile.filenameUploadfile + '</strong><br><div class="alert alert-warning">Atenção: Esta é uma ação permantente, sem retorno!</div>',
            'btnOk': 'Fechar',
            'btnNegative': 'Sim, remover arquivo'
        };
        // action caso o click seja positivo
        $('#btnDialogModal').prop('onclick', null).off('click').on('click', function (evt) {
            $("#dialogModal").modal('hide');
        });
        $('#btnDialogModalNegative').prop('onclick', null).off('click').on('click', function (evt) {
            $rootScope.loading('show', 'Removendo Arquivo');
            $scope.working = true;
            DataLoadService.getContent('Uploadfile', 'remove', uploadFile, function (data) {
                if (data.error === false) {
                    $scope[file].Files.splice($index, 1);
                }
                $scope.working = false;
                $rootScope.setAlert(data);
            });
            $("#dialogModal").modal('hide');
        });
        // mostra Dialog
        $rootScope.showDialogModal(args);
    };

    $scope.uploadFileAlteraPerfil = function (Uploadfile) {
        Uploadfile.perfilIcon = 'spinner';
        Uploadfile.perfil = '';
        DataLoadService.getContent('Uploadfile', 'alteraClassificacao', Uploadfile, function (data) {
            Uploadfile.perfilIcon = data.content.perfilIcon;
            Uploadfile.perfil = data.content.perfil;
            $rootScope.setAlert(data);
        });
    };

    $scope.uploadFileSaveName = function (Uploadfile) {
        $rootScope.uploadFileSaveName(Uploadfile);
    };
    $scope.uploadFileShare = function (Uploadfile) {
        $rootScope.uploadFileShare(Uploadfile);
    };




    // método para impressão
    $scope.toPrint = function () {
        $scope.preparandoImpressao = true;
        $(".tablePrint").removeClass('hidden-xs hidden-sm').addClass('d-none d-print-block');
        $timeout(function () {
            if (!$scope.working) {
                $scope.UsuarioGetAll('Preparando página para impressão (' + $scope.pagina + ')');
                console.info('PAGINA', $scope.pagina);
                if ($scope.pagina > 0) {
                    $scope.toPrint();
                } else {
                    $scope.preparandoImpressao = false;
                    window.print();
                    $timeout(function () {
                        $(".tablePrint").removeClass('d-none d-print-block').addClass('hidden-xs hidden-sm');
                    }, 500);
                }
            } else {
                $scope.toPrint();
            }
        }, 500);
    };

    $scope.contador = function () {
        // contadores
        $scope.counters = {};
        angular.forEach($scope.Poderes, function (v, k) {
            $scope.counters[v.grupo] = {total: 0, setado: 0};
            angular.forEach(v.subgrupo, function (val, key) {
                angular.forEach(val.acoes, function (item, chave) {
                    $scope.counters[v.grupo]['total']++;
                    if (item.user === true) {
                        $scope.counters[v.grupo]['setado']++;
                    }
                });
            });
        });
        console.info('$scope.counters', $scope.counters);
    };
    $scope.$watch('Poderes', function () {
        $timeout(function () {
            $scope.contador();
        }, 500);
    }, true);

    $scope.permissoesShow = function (Usuario) {
        if (!Usuario.idUsuario) {
            return;
        }
        $scope.Usuario = Usuario;
        //$rootScope.trocaView('permissoesUsuario', $scope.entidadeName, 'Permissões de ' + $scope.labelTitle + ': ' + Usuario.nomeUsuario);
        //$rootScope.setLocation('Usuario/' + Usuario.idUsuario + '/2');
        $scope.Poderes = false;
        $scope.working = true;
        $rootScope.loading('show', 'Obtendo Permissões do Usuário');
        DataLoadService.getContent('Usuario', 'getPermissoes', {idUsuario: Usuario.idUsuario}, function (data) {
            console.info('getPermissoes', data);
            $scope.working = false;
            $rootScope.setAlert(data);
            if (data.error === false) {
                $scope.Poderes = data.content;
                $timeout(function () {
                    $('#nav-item-0').trigger('click');
                }, 500);
            }
        });
    };

    // método para salvar os click nos botoes de poderes
    $scope.permissoesSave = function (grupo, subgrupo, acao) {
        acao.user = !acao.user;
        DataLoadService.getContent('Usuario', 'setPermissao', {idUsuario: $scope.Usuario.idUsuario, idSistemaFuncao: acao.idfuncao}, function (data) {
            $scope.working = false;
            if (data.error === false) {
                acao.user = data.content.user;
            }
        });
    };

    $scope.permissoesAtualizarByPerfil = function (Usuario) {
        var args = {
            'title': 'Alteração de Poderes',
            'body': 'Isto irá alterar todos os usuários deste perfil para os poderes padrão. <br/><br/> <p class="alert alert-warning">Esta é uma operação sem retorno</p><br/>',
            'btnOk': 'Sim, alterar', btnCancelar: 'Não, manter como está'
        };
        // action caso o click seja positivo
        $('#btnDialogModal').prop('onclick', null).off('click').on('click', function (evt) {
            $rootScope.loading('show', 'Atualizando Usuários', 'modal');
            $scope.working = true;
            DataLoadService.getContent('Usuario', 'atualizarPoderesByPerfil', {perfilUsuario: Usuario.idUsuario}, function (data) {
                $scope.working = false;
                $rootScope.setAlert(data);
            });
        });
        // mostra Dialog
        $rootScope.showDialogModal(args);
    };

    $scope.permissoesAtualizarUsuarioByPerfil = function (Usuario) {
        if (Usuario.idUsuario > 0) {
            $rootScope.loading('show', 'Atualizando Usuário', 'modal');
            $scope.working = true;
            DataLoadService.getContent('Usuario', 'atualizarPoderesByPerfil', {idUsuario: Usuario.idUsuario, perfilUsuario: Usuario.perfilUsuario}, function (data) {
                $scope.working = false;
                $rootScope.setAlert(data);
            });
        } else {
            alert('Usuario não selecionado corretamente');
        }
    };

    $scope.setAllPoderes = function (value) {
        $rootScope.loading('show');
        $scope.Poderes = false;
        var args = {idUsuario: $scope.Usuario.idUsuario, atitude: parseInt(value)};
        DataLoadService.getContent('Usuario', 'setPermissaoAll', args, function (data) {
            $scope.Poderes = data.content;
            $rootScope.loading('hide');
        });
    };

    $scope.senhaAlterar = function (Usuario) {
        console.info('Usuario-esqueciSenha', Usuario);
        $scope.working = true;
        $rootScope.loading('show', 'Enviando nova senha', 'modal');
        DataLoadService.getContent('Usuario', 'esqueciSenha', {username: Usuario.emailUsuario}, function (data) {
            console.info('Data', data);
            $scope.working = false;
            $rootScope.setAlert(data, true);
            if (data.error === false) {
                // ações a executar se positivo
            }
        });
    };

    $scope.avatarShowInfoAlterar = function () {
        $rootScope.setAviso('Para alterar a imagem, escolha um dos arquivos no Gerenciador de Arquivos, clique sobre ele e selecione Definir Avatar', 30);
    };

    $scope.apiKeyCopyToClipboard = function (Usuario) {
        $scope.working = true;
        $rootScope.loading('show', 'Obtendo ApiKey', 'modal');
        DataLoadService.getContent('usuario', 'getApiKey', {idUsuario: Usuario.idUsuario}, function (data) {
            console.info('Data', data);
            $scope.working = false;
            data.content.result = '<p class="alert alert-warning text-center">Atenção: Esta chave dará acesso aos seus dados compartilhados no sistema</p>'

                    + '<div class="form-group"><label for="apiKeyTextArea">AppKey:</label><textarea class="form-control" rows="5" id="apiKeyTextArea">' + data.content.apiKey + '</textarea></div>';
            $rootScope.setAlert(data, true);
            $timeout(function () {
                $("#apiKeyTextArea").fadeIn().select();
                $("#btnAlertModal").html('Copiar').on('click', function (evt) {
                    document.execCommand('copy');
                    data.content.result = 'Copiado para area de trabalho';
                    $rootScope.setAlert(data);
                });
            }, 500);
        });


    };


    $scope.filterOnlyProfessores = function () {
        $scope.myfilter = $scope.myfilter ? false : {extrasUsuario: {isProfessor: 'Sim'}};
    };




    /**
     * Métodos referentes a Indisponibilidade de usuarios
     */
    $scope.IndispMinDate = HOJE; // vem no PHP
    $scope.IndispDoClose = function () {
        $scope.Indisp = false;
        $(".btnsAdmin").show();
    };
    $scope.IndispNew = function () {
        DataLoadService.getContent('indisp', 'getNew', {}, function (data) {
            data.content.idUsuario = $scope.Usuario.idUsuario;
            $scope.IndispOnEdit(data.content);
        });
    };
    $scope.IndispGetAll = function () {
        $scope.Indisp = false;
        DataLoadService.getContent('indisp', 'getByUser', {idUsuario: $scope.Usuario.idUsuario}, function (data) {
            if (data.error === false) {
                $scope.Indisps = data.content;
            } else {
                $rootScope.setAlert(data);
            }
        });


    };
    $scope.IndispOnEdit = function (item) {
        $scope.Indisp = item;
        $(".btnsAdmin").hide();
    };
    $scope.IndispSave = function () {
        $scope.working = true;
        $rootScope.loading('show', 'Salvando indisponibilidade', 'modal');
        DataLoadService.getContent('indisp', 'save', $scope.Indisp, function (data) {
            $scope.working = false;
            $rootScope.setAlert(data);
            if (data.error === false) {
                $scope.IndispGetAll();
            }
        });
    };
    $scope.IndispRemove = function () {};

    // Observar usuario alterado
    $scope.$watch('Usuario.idUsuario', function (newv, oldv) {
        if (newv !== oldv && newv > 0) {
            $scope.IndispGetAll();
        }
    });

    $scope.contador = function () {
        // contadores
        $scope.counters = {full: 0, setado: 0};
        angular.forEach($scope.Poderes, function (v, k) {
            $scope.counters[v.grupo] = {total: 0, setado: 0};
            angular.forEach(v.subgrupo, function (val, key) {
                angular.forEach(val.acoes, function (item, chave) {
                    $scope.counters.full++;
                    $scope.counters[v.grupo]['total']++;
                    if (item.user === true) {
                        $scope.counters[v.grupo]['setado']++;
                        $scope.counters.setado++;
                    }
                });
            });
        });
        console.info('$scope.counters', $scope.counters);
    };
    $scope.$watch('Poderes', function () {
        $scope.contador();
    }, true);




});
        