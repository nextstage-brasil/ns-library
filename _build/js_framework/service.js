app.service('DataLoadService', function ($http, $rootScope, FileUploader, $filter, $timeout) {
    $rootScope.urlCloud = appConfig.urlCloud; // variavel em gerada em src/Config
    this.dev = appConfig.dev;
    this.rest = appConfig.rest;
    lastExpireValid = false;
    this.getContent = function (tipo, action, data, success) {
        // isso permite enviar no tipo a url absoluta deste getContent
        var rest = this.rest + '/' + tipo + '/' + action;
        if (tipo.indexOf('/') > -1) {
            rest = tipo + '/' + action;
            var t = tipo.split('/');
            tipo = t[t.length - 1];
        }
        //console.log('REST: ' + rest);
        $rootScope.working = true;
        // sem sessão registrada
        if ($rootScope.User
                || (tipo === 'App' && action === 'site')
                || (action === 'enter' && tipo === 'login')
                || (tipo === 'Usuario' && (action === 'alteraSenha'
                        || action === 'esqueciSenha'
                        || action === 'cadastro'
                        ))
                ) {
            //data.action = action;
            //data.tipo = tipo;
            var $headers = {
                'Content-Type': 'application/x-www-form-urlencoded',
                'Api-Key': this.key
            };

            // Decidir como enviar a Authorization
            if (tipo === 'login') {
                $headers.Authorization = 'Basic ' + btoa(data.username + ':' + data.password);
                delete (data.username);
                delete(data.password);
            } else {
                $headers.Authorization = 'Bearer ' + sessionStorage.getItem('CS_TK_CST');
            }

            // Verificar se precisa renovar a sessão

            //data.key = this.key;
            console.info('CHAMADA-DE-GET-CONTENT', data.tipo + ':' + data.action);
            $rootScope.getContentCount++;
            $http({
                method: 'POST',
                url: rest,
                data: this.param(data), // pass in data as strings
                timeout: data.timeout ? data.timeout : 60000,
                headers: $headers// set the headers so angular passing info as form data (not request payload)
            }).then(function (response) {
                $rootScope.working = false;
                $rootScope.getContentCount--;
                //$rootScope.User.token = response.data.token;
                // logar somente em localhost
                console.log('response-api-' + data.tipo, response.data.expire);
                console.log('DL - ' + data.action + '::' + data.tipo, response);
                //$rootScope.tempoDisponivel = response.data.expire * 60 * 1000;


                // Renovação de Token automática
                if (response.data.token) {
                    sessionStorage.setItem('CS_TK_CST', response.data.token);
                    /*
                     if (response.data.expire) {
                     author = action + '/' + tipo;
                     console.info('Tempo disponível renovado. ' + response.data.expire + ' Autor: ', author);
                     lastExpireValid = response.data.expire;
                     
                     }
                     */
                    //Persistir usuario por causa do token entre páginas
                    if ($rootScope.User) {
                        $rootScope.User.token = response.data.token;
                    } else {
                        $rootScope.User = {token: response.data.token};
                    }
                }
                sessionStorage.setItem('user', JSON.stringify($rootScope.User));


                // Registro das atividades em timeout, para evitar ficar fazendo diversas
                $timeout.cancel($rootScope.refreshTime);
                $rootScope.refreshTime = $timeout(function () {
                    /**
                     // playsound novas mensagens
                     console.info('Novas mensagens: ' + response.data.mnr);
                     if (response.data.mnr > 0) {
                     //$("#btnNewMessage").show();
                     elem = angular.element("#btnNewMessage");
                     $rootScope.pulseElem(elem, 1000, 10);
                     $(".badge-comunicador").html(response.data.mnr);
                     $qtdeMsgs = parseInt(sessionStorage.getItem('CS_MNR'));
                     if ($qtdeMsgs !== response.data.mnr) { // só tocar som quando tiver mensagem nova na session
                     angular.element('#mnr-sound-player').trigger('play');
                     sessionStorage.setItem('CS_MNR', response.data.mnr);
                     }
                     }
                     **/

                    // tempo de sessao
                    var seconds = new Date().getTime() / 1000;
                    if (typeof response.data.expire !== 'undefined') {
                        $rootScope.tempoSessao(parseInt(response.data.expire - seconds) * 1000);
                        lastExpireValid = false;
                    }
                    $('.decimal').mask('000.000.000.000,00', {reverse: true});
                }, 2000);

                if (!success) {
                    return response.data;
                } else {
                    // Retorno da função
                    success(response.data);
                }


            }, function errorCallback(response) {
                $rootScope.getContentCount--;
                console.info('CHAMADA-DE-GET-CONTENT', data.tipo + ':' + data.action);
                console.info("ERRO-SERVICE", response);
                if (response.xhrStatus === 'abort') {
                    response.status = 504;
                }


                switch (response.status) {
                    case 401:
                        location.href = appConfig.urlCloud + 'logout';
                        /*
                         args = {
                         title: 'Sessão expirada',
                         icon: 'info',
                         confirmButtonColor: '#3085d6',
                         confirmButtonText: 'Ok',
                         onClose: function () {
                         location.href = appConfig.urlCloud + 'logout';
                         }
                         };
                         Swal.fire(args);*/
                        throw new Error(response);
                        break;
                    case 504: // timeout
                        console.info('ERROR-504', response);
                        $action = response.config.url.split('/api/');
                        //$rootScope.setAlert({error: 'Houve um problema na rede não identificado (timeout)<br/>Verifique se suas atividades estão devidamente registradas.'});
                        $rootScope.setAlert({error: 'DEV: Pesquisa a ser tratada por conter resultados excessivos'});
                        throw new Error(response);
                        break;
                    default:
                        $rootScope.loading('hide');
                        if (!tempoSecondsToClose) {
                            setTimeout(function () {
                                var error = response.data !== null ? response.data.error : 'Ocorreu um erro não identificado';
                                $('#barraAviso').hide();
                                $(".modal").modal('hide');
                                error = '<div class="alert alert-secondary text-center text-strong">' + error + '</div>'
                                        + '<div class="text-info text-center" style=:"margin-top:35px;">Esta mensagem fechará em <span class="tempoToCloseAviso">' + appConfig.timeExibeError + ' segundos<br/></span></div>';
                                $("#content").html('<div class="text-center" style="margin-top:55px;">' + error + '</div>');
                                $('#barraEsquerda').hide();
                            });
                            setInterval(function () {
                                window.location = $rootScope.urlCloud;
                                /// window.location = window.location;//$rootScope.urlCloud;
                            }, (appConfig.timeExibeError - 1) * 1000);
                            tempoToCloseAviso(appConfig.timeExibeError);
                            throw new Error(response.status);
                        }
                }

            });
        } else {
            console.log('semlogin: ' + tipo + ':' + action);
            //console.info('USER-NOLOGIN', sessionStorage.getItem('user'));
            window.location = 'logout';
        }
    };
    this.ajax = function (url, vars, method, headers, success) {
        $http({
            method: method,
            url: url,
            data: this.param(vars),
            headers: headers
        }).then(function (response) {
            //console.info('AJAX SUCCESS', response);
            var dados = response.data;
            success(dados);
        }, function errorCallback(response) {
            //console.info('AJAX ERROR', response);
            alert("Erro na chamada de serviços");
        });
    };
    // serializable
    this.param = function (data) {
        var returnString = '';
        var d = '';
        for (d in data) {
            if (data.hasOwnProperty(d)) {
                // o caracter '&' estava gerando erro na serialização. variavel com conteudo html que possuem espaco.
                if (typeof data[d] === 'object') {
                    var temp = JSON.stringify(data[d]);
                    temp = temp.replace(/&/g, 'NS21');
                    returnString += d + '=' + temp + '&';
                } else {
                    if (typeof data[d] === 'string') {
                        data[d] = data[d].replace(/&/g, 'NS21');
                    }
                    returnString += d + '=' + data[d] + '&';
                }
            }
        }
        // Remove last ampersand and return
        return returnString.slice(0, returnString.length - 1);
    };
    /***********************************************************************************************************
     * UPLOAD FILES *
     ***********************************************************************************************************/

    var b64toBlob = function (b64Data, contentType, sliceSize) {
        contentType = contentType || '';
        sliceSize = sliceSize || 512;
        var byteCharacters = atob(b64Data);
        var byteArrays = [];
        for (var offset = 0; offset < byteCharacters.length; offset += sliceSize) {
            var slice = byteCharacters.slice(offset, offset + sliceSize);
            var byteNumbers = new Array(slice.length);
            for (var i = 0; i < slice.length; i++) {
                byteNumbers[i] = slice.charCodeAt(i);
            }

            var byteArray = new Uint8Array(byteNumbers);
            byteArrays.push(byteArray);
        }

        var blob = new Blob(byteArrays, {type: contentType});
        return blob;
    };



    this.getUploadFile = function (maxsize, thumbs, entidade, valorid, args) {
        maxsize = maxsize ? maxsize : 3000;
        var uploader = new FileUploader({
            method: 'POST',
            url: this.rest + '/App/uploadFile',
            timeout: 120000,
            headers: {
                'Data': this.param(args),
                Authorization: 'Bearer ' + sessionStorage.getItem('CS_TK_CST')
                        //'Token': sessionStorage.getItem('CS_TK_CST')
            }});

        // FILTERS
        uploader.filters.push({
            name: 'customFilter',
            fn: function (item /*{File|FileLikeObject}*/, options) {
                return this.queue.length < 1000;
            }
        });
        uploader.maxsize = maxsize ? maxsize : 3000; // maximo em pixels
        uploader.countFileUpload = 0;
        uploader.filaTamanho = 0;
        uploader.filaTamanhoEnviado = 0;
        uploader.errorAddingFile = [];
        /*
         // CALLBACKS
         uploader.onAfterAddingAll = function(addedFileItems) {
         $rootScope.loading('hide');
         };
         */

        uploader.onAfterAddingAll = function (addedFileItems) {
            console.info('onAfterAddingAll', uploader.errorAddingFile);
            if (uploader.errorAddingFile.length > 0) {
                html = '';
                angular.forEach(uploader.errorAddingFile, function (v, k) {
                    html += v.error + '<br/>';
                });
                $timeout(function () {
                    // esta importado em template1
                    Swal.fire({
                        title: "Ocorreram alguns erros durante a carga das fotos",
                        text: html,
                        icon: "warning",
                        dangerMode: true,
                        html: true
                    });
                }, 100);
            }
            $timeout(function () {
                $(".btnSend").removeClass('animate rubberBand').addClass('animate rubberBand');
                uploader.errorAddingFile = [];
            }, 500);
            uploader.uploadAll();
        };
        uploader.onAfterAddingFile = function (item) {

            // Bloqueio para seleção de arquivos com mais de 20MB
            var maxSizeFile = 50; //em MB
            if (item._file.size > maxSizeFile * 1024 * 1024) {
                size = item._file.size / 1024 / 1024;
                size = $filter('number')(size, 2);
                uploader.errorAddingFile.push({error: 'Arquivo ' + item._file.name + ' tem ' + size + 'MB'});
                item.remove();
            } else {
                uploader.filaTamanho += item._file.size;
                uploader.countFileUpload++; // adicionar um para cada arquivop a ser processado, enviado. Sera decrementado na diretiva, on sucomplete
            }
            $(".btnsAdmin").hide(); // obrigar esperar acao terminar para concluir
        };
        return uploader;
        //--------------------------------------fim
    };
    /*********** UPLOAD FILES - FIM *******************************************/




}); // fehca service dataloadservice