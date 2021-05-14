app.controller('AppController', function ($scope, $rootScope, $compile, $timeout, DataLoadService, $compile, $window, $filter) {
    console.log('AppController-Starter');
    $rootScope.uploadFileName = '';
    $rootScope.uploadFileEntidade = '';
    $rootScope.Template;
    $scope.scripts = [];
    $rootScope.getContentCount = 0; // contador de services em atividade
    $rootScope.cargaLinktable = {};
    $("#controllerContent").removeClass('d-none'); // para somente exibir os conteudos apos a carga completa de JS
    var mp = "68747470733a2f2f7777772e746974616e65736d6564656c6c696e2e636f6d2f";

    var user = sessionStorage.getItem('user');
    if (user != 'undefined' && user !== 'FALSE' && user !== null && user !== 'null') {
        //console.info('USER', typeof user);
        user = JSON.parse(user);
        if (user.nomeUsuario !== '') {
            $rootScope.User = user;
            $rootScope.User.firstName = user.nomeUsuario;//.split(" ")[0];
        } else {
            $rootScope.User = {};
        }

    }



    $rootScope.setValueAnimate = function ($elem, value, casas_decimais) {
        casas_decimais = casas_decimais ? casas_decimais : 0;
        $elem.animateNumber({
            number: value * 3,
            numberStep: function (now, tween) {
                var floored_number = Math.floor(now) / 3,
                        target = $(tween.elem);
                $text = $filter('number')(floored_number, casas_decimais);
                target.text($text);
            }
        }, 1500);
    };


    $rootScope.badge = function (idElemPai, value, fixed) {
        $elem = $('a[href="#' + idElemPai + '"] span');
        $elem2 = $('.' + idElemPai);
        if (typeof value !== 'undefined') {
            if (value === 'hidden') {
                $elem.html('');
                $elem2.html('');
            } else {

                if (fixed === true) {
                    $elem.html(value);
                    $elem2.html(value);
                } else {
                    value = parseInt(value);
                    $rootScope.setValueAnimate($elem, value);
                    $rootScope.setValueAnimate($elem2, value);
                }
            }
        } else {
            $elem.html('<i class="fa fa-refresh fa-spin fa-fw"></i>');
            $elem2.html('<i class="fa fa-refresh fa-spin fa-fw"></i>');
        }
    };


    $rootScope.sessionSetJsonMd5 = function (name, value) {
        name = name.toLowerCase();
        content = JSON.stringify(value);
        hash = CryptoJS.AES.encrypt(content, mp);
        sessionStorage.setItem(MD5(name), hash);
    };
    $rootScope.sessionGetJsonMd5 = function (name) {
        name = name.toLowerCase();
        ret = sessionStorage.getItem(MD5(name));
        if (typeof ret !== 'string' || ret.length < 3) { // se não houver, na prmeira carga chamar o metodo
            return false;
        } else {
            decrypted = CryptoJS.AES.decrypt(ret, mp);
            hash = decrypted.toString(CryptoJS.enc.Utf8);
            return JSON.parse(hash);
        }
    };


    $timeout(function () {
        $(".nav_title").html($("#titlePage").html());
    }, 1000);

    $scope.User = $rootScope.User;


    // Aqui mudei para salvar o usuario na sesion e poder variar entre as abas.
    //$rootScope.user = JSON.parse(sessionStorage.getItem('user'));

    $scope.teste = function (ent) {
        $scope.working = true;
        $rootScope.loading('show', 'Processando... ', 'modal');
        DataLoadService.getContent('App', 'getJsComponent', {name: ent}, function (data) {
            console.info('Data', data);
            $scope.working = false;
            $rootScope.setAlert(data);
            if (data.error === false) {
                $js = '<script src="' + data.content.component + '" type="text/javascript"></script>';
                $("#bodyAppJS").html($compile($js)($scope));
                $("#bodyApp").html($compile('<ns-' + ent + '></ns-' + ent + '>')($scope));
            }
        });
        $("body").trigger('click');


    };
    $scope.hide = function (id, time) {
        $timeout(function () {
            $("#" + id).fadeOut().remove();
        }, time);
    };

    // mostrar uma tela 'loading' sobre a tela atual ao clicar nos links do menu
    $(".navbar ul li a").on('click', function (e) {
        if ($(this).attr('href') !== "#") {
            $("#alertModalVazio div div").html('');
            $("#alertModalVazio").modal({backdrop: "static"});
        }
    });


    //= sessionStorage.getItem('user') ? JSON.parse(sessionStorage.getItem('user')) : {};
    //console.info('USER', $rootScope.User);

    $rootScope.summernoteOptions = {
        height: '700',
        lang: 'pt-BR',
        toolbar: [
            ['edit', ['undo', 'redo']],
            //['headline', ['style']],
            ['style', ['bold', 'italic', 'underline', 'superscript', 'subscript', 'strikethrough', 'clear']],
            //['fontface', ['fontname']],
            ['textsize', ['fontsize']],
            //['fontclr', ['color']],
            ['alignment', ['ul', 'ol', 'paragraph']],
            //['height', ['height']],
            ['table', ['table']],
            //['insert', ['link','picture','video','hr']],
            ['view', ['fullscreen']],
                    //['help', ['help']]        
        ]
    };
    $rootScope.showAlertModal = function (args) {
        if (typeof (args.body) !== 'undefined') {
            this.setTemplate(args.body);
        }
        args.html = $rootScope.Template;

        // Se não estiver num timeout, imprime os botoes ok e cancelar (não achei o motivo)
        $timeout(function () {
            Swal.fire(args);
        }, 500);

        /*
         if (!$('#alertModal').is(':visible')) {
         $("#alertModal .modal-header h3").html(args.icon);
         $("#alertModal .modal-header h4").html(args.title);
         $("#alertModal .modal-body").html($rootScope.Template);
         if (args.scope) {
         $("#alertModal .modal-body").html($compile($rootScope.Template)(args.scope));
         }
         //$("#alertModal .modal-footer button").html(args.button);
         $("#alertModal").modal();
         }
         */
    };
    $rootScope.showDialogModal = function (args) {
        $("#dialogModal .modal-header h3 i").removeClass('fa-question-circle text-danger').addClass(args.icon ? args.icon : 'fa-question-circle text-danger');
        $("#dialogModal .modal-header h4").html(args.title);
        $("#dialogModal .modal-body").html(args.body);
        if (args.scope) {
            $("#dialogModal .modal-body").html($compile(args.body)(args.scope));
        }
        $("#btnDialogModal").html('OK');
        $('#btnDialogModalCancel').html('Cancelar');
        if (args.btnOk) {
            $("#btnDialogModal").html(args.btnOk);
        }
        if (args.btnCancelar) {
            $('#btnDialogModalCancel').html(args.btnCancelar);
        }
        $("#btnDialogModalNegative").hide();
        if (args.btnNegative) {
            $("#btnDialogModalNegative").html(args.btnNegative).show();
        }
        $timeout(function () {
            $("#dialogModal").modal();
        });
    };

    $rootScope.loading = function (escolha, msg, modal, timeToWait) {
        msg = msg ? msg : 'Processando';
        $("#barraAviso").removeClass('alert-primary alert-success alert-info alert-danger alert-warning').hide();
        if (escolha === 'show') {
            $('button').hide();
            //$('a').hide();
            $scope.timeLoading = $timeout(function () {
                msg = '<i class="fa fa-spinner fa-spin" aria-hidden="true"></i> ' + msg;
                $('#barraAviso').removeClass('alert-success alert-info alert-danger alert-warning').addClass('alert-info').show().html(msg);
                if (modal && !($('#loading').is(':visible'))) {
                    $('#loading').modal({backdrop: 'static'});
                }
            }, 250);
        } else {
            $('button').fadeIn();
            //$('a').fadeIn();
            $timeout.cancel($scope.timeLoading);
            $timeout.cancel($scope.timeModal);
            $timeout(function () {
                $('#loading').modal('hide');
            }, 300);
        }
    };

    $rootScope.setTemplate = function (val) {
        var template = '';
        if (typeof val === 'object') {
            template = '<ul>';
            $.map(val, function (value, index) {
                template += '<li>' + value + '</li>';
            });
            template += '</ul>';
        } else if (typeof val === '[object Array]') {
            template = '<ul>';
            angular.forEach(val, function (value, key) {
                template += '<li>' + value + '</li>';
            });
            template += '</ul>';
        } else {
            template = val;
        }
        $rootScope.Template = '<p class="text-center">' + template + '</p>';
    };

    $rootScope.setAlert = function (data, showMessageOK, icon, title, type) {
        //$('#loading').modal('hide');
        $rootScope.loading('hide');
        $rootScope.Template = '';
        $('#barraAviso').hide();
        var argsAlert = {
            icon: ((typeof data.content.icon !== 'undefined') ? data.content.icon : 'info'),
            title: ((typeof data.content.title !== 'undefined') ? data.content.title : 'Verifique'),
            button: 'OK',
            type: type ? type : 'warning'
        };

        //if (data.result === "SUCCESS") {
        if (data.error === false) {
            if (showMessageOK !== 'ERROR_ONLY') { // se não quiser imprimir o tudo certo, mas quando houver erro somente
                if (data.content) {
                    txt = data.content.result || 'Tudo Certo!';
                    icon = data.content.icon || 'success';
                    iconToast = icon;
                } else {
                    txt = 'Tudo Certo!';
                    icon = 'success';
                }
                $print = txt !== 'Tudo Certo!';
                if (showMessageOK) {
                    Swal.fire({
                        icon: icon,
                        title: txt,
                        width: '50em'
                    });
                } else if ($print) {
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        showCloseButton: true,
                        timer: 5000,
                        timerProgressBar: true
                    });
                    Toast.fire({
                        icon: iconToast || 'success',
                        title: txt
                    });
                }
            }
        } else {
            //console.info("rootScope.setAlert", data);
            //console.info("rootScope.setAlert-args", argsAlert);
            $rootScope.setTemplate(data.error);
            $rootScope.showAlertModal(argsAlert);
        }

    };
    /**
     * Exibe um balao de informação interativa ao usuario sobre requisições multiplas assincronas em AJAX. 
     */
    $rootScope.timeOutInfo = {};

    $rootScope.info = function (msg, tipo) {
        //console.info('INFO', msg + tipo);
        var element = $('#barraAviso');
        //element.hide();
        //$('#barraEsquerda').hide();
        clearTimeout($rootScope.timeOutInfo);
        var icone = '';
        btn = '';
        time = 3000;
        id = Math.ceil(Math.random() * Math.pow(10, 10));
        $class_tipo = '';
        switch (tipo) {
            case 'danger':
                icone = 'fa-exclamation-circle';
                element = $('#barraEsquerda');
                time = 30000;
                //$class_tipo = 'alert alert-danger';
                btn = '<a ng-click="hide(\'' + id + '\')" class="btn btn-link text-primary float-right" onclick="">OK</a>';
                break;
            case 'info':
                icone = 'fa-info-circle';
                element = $('#barraEsquerda');
                time = 10000;
                //$class_tipo = 'alert alert-info';
                btn = '<a ng-click="hide(\'' + id + '\')" class="btn btn-link text-primary float-right" onclick="">OK</a>';
                break;
            case 'warning':
                icone = 'fa-spinner fa-spin';
                btn = '';
                break;
            default:
                element = $('#barraEsquerda');
                $class_tipo = '';
                //element.hide();
                tipo = 'success';
                icone = 'fa-check';

        }
        msg2 = '<div style="cursor:pointer" id="' + id + '" ng-click="hide(\'' + id + '\')" class="' + $class_tipo + ' pt-3 pb-3 mb-3">'
                + '<i class="fa ' + icone + ' " aria-hidden="true"></i> '
                + msg + btn + '</div>';

        if (tipo === 'danger' || tipo === 'info') {
            config = {
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                //timer: 3000
            };
        } else {
            config = {
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000
            };
        }
        const Toast = Swal.mixin(config);
        $timeout(function () {
            Toast.fire({
                type: tipo,
                title: msg
            });
        }, 500);

        /*
         msg = '<i class="fa ' + icone + ' " aria-hidden="true"></i> ' + msg;
         element.removeClass('alert-success alert-info alert-danger alert-warning').addClass('alert-' + tipo).show().html(msg);
         */



    };


    /**
     * Name eh o index da variavel criada em index.php pelo router. um array onde 0=>(int) ID e os demais conforme necessidade
     * ParametersURL eh gerado em template1.php, usando o config para armazenar as variaveis do URL
     * Padrão: /Router/ID/TP/Demais
     * @param {type} name
     * @returns {String}
     */
    $rootScope.getParameterByName = function (name) {
        console.info('Parameters', ParametersURL);
        if (typeof ParametersURL === 'undefined') {
            return false;
        }
        switch (name) {
            case 'xyz':
                return ParametersURL[0];
                break;
            case 'tp':
                return ParametersURL[1];
                break;
            default:
                name = parseInt(name);
                return ParametersURL[name];
                break;
        }
        /**
         var href = window.location.href;
         name = name.replace(/[\[]/, "\\\[").replace(/[\]]/, "\\\]");
         var regexS = "[\\?&]" + name + "=([^&#]*)";
         var regex = new RegExp(regexS);
         var results = regex.exec(href);
         if (results === null)
         return "";
         else
         return decodeURIComponent(results[1].replace(/\+/g, " "));
         **/
    };
    $rootScope.idParameter = parseInt($rootScope.getParameterByName('xyz'));

    $rootScope.setAviso = function (msg, time) {
        time = time ? (time * 1000) : 5000;
        clearTimeout(timeOutInfo);
        var timeOutInfo = setTimeout(function () {
            $('#barraAviso').slideUp();
        }, time);
        msg = '<i class="fa fa-info-circle"></i> ' + msg + '<br/><p class="text-right text-secondary text-italic">Ok, entendi</p>';
        $('#barraAviso').removeClass('alert-succes alert-info alert-danger').addClass('mouseover-hand alert-info').show().html(msg).on('click', function (evt) {
            $(this).hide();
        });
    };

    // aviso de manutenção na pagina
    if (sessionStorage.getItem('cienteManutencao')) {
        $scope.cienteManutencao = true;
    }
    $scope.ciente = function () {
        sessionStorage.setItem('cienteManutencao', true);
        $scope.cienteManutencao = true;
        console.info('CIENTE', $scope.cienteManutencao);
    };


    $rootScope.setLocation = function (location) {
        $var = window.location.href.toLowerCase();
        $t = location.split('/');
        if ($var.indexOf($t[0].toLowerCase()) > 0) {
            console.log('setLocation: ' + location);
            location = location.replace($rootScope.urlCloud, '');
            $timeout(function () {
                window.history.pushState('Object', 'Categoria JavaScript', $rootScope.urlCloud + location);
            }, 100);
            sessionStorage.setItem('lastLocation', location);
        }
    };


    $rootScope.getFiles = function (entidade, id, success) {
        DataLoadService.getContent('Uploadfile', 'getFiles', {valorid: id, entidade: entidade}, function (data) {
            console.info('FILES', data.content);
            if (data.error === false && data.content) {
                success(data.content);
            } else {
                success([]);
            }
            $rootScope.setAlert(data);
        });

    };

    // Método para exibir o mapa da localização, conforme elemento definido em elem
    $rootScope.mapShow = function (elem, lat, long, zoom) {
        var uluru = {lat: parseFloat(lat), lng: parseFloat(long)};
        console.info('uluru', uluru);
        var map = new google.maps.Map(document.getElementById(elem), {
            zoom: zoom ? zoom : 15,
            center: uluru
        });
        var marker = new google.maps.Marker({
            position: uluru,
            map: map
        });
    };

    // método para controle de onde exibir os logs de javascript
    $rootScope.log = function ($label, $var) {
        if ($rootScope.urlCloud.indexOf(':8088') > 0) {
            console.info($label, $var);
        }
    };
    $rootScope.bodyAddRemoveBg = function ($escolha) {
        $timeout(function () {
            if ($escolha === 'add') {
                $('body').removeClass('default-bg-image').addClass('default-bg-image');
            } else {
                $('body').removeClass('default-bg-image');
            }
        }, 100);
    };

    // metodo para escolher a view em exibição
    $rootScope.trocaView = function (nova, entidade, title) {
        console.log('RootScope.trocaView: ' + nova);
        //$(".btnBack").removeClass('d-block d-sm-none');
        //$(".btnAdd").removeClass('d-block d-sm-none');
        $('.controleShow' + entidade).hide();
        $('.listEdit' + entidade).hide(); // fechando explicitamente para poder atuar com infite scroll somente na lista
        if (title) {
            $(".page-title").html(title);
        }
        $('#' + nova).fadeIn();
    };

    // Método para exibir somente a listEdit no carregamento
    $rootScope.doClose = function (title, entidade) {
        console.log('rootScope.doClose: ' + title);
        //$(".btnBack").addClass('d-block d-sm-none');
        //$(".btnAdd").addClass('d-block d-sm-none');
        $('.controleShow' + entidade).hide();
        $('.listEdit' + entidade).fadeIn();
        if (title) {
            $(".page-title").html(title);
        } else {
            $(".page-title").html($(".title").html());
        }
    };

    // Método para retornar a data atual, em string
    $rootScope.getDate = function (incluirHora) {
        var now = new Date();
        var time = $.datepicker.formatDate('dd/mm/yy', now);
        if (incluirHora === true) {
            time = time + ' ' + ("0" + now.getHours()).slice(-2) + ':' + ("0" + now.getMinutes()).slice(-2) + ':' + ("0" + now.getSeconds()).slice(-2);
        }
        return time;
    };

    // watch para listEdit // controle do background. somente exibir em listEdit
    $rootScope.$watch(function () {
        return angular.element('.listEdit').is(':visible');
    }, function (oldValue, newValue) {
        //console.info('----New-----', oldValue + ' - '+newValue);
        if (oldValue) {
            $rootScope.bodyAddRemoveBg('add');
        } else {
            $rootScope.bodyAddRemoveBg();
        }
    });

    /**
     * Imprime no elemento as coordenadas para o elemento
     * @param {type} element
     * @param {type} data: {cep:'', rua:'', numero:'', bairro:'', municipio:'', estado:''} OU data: {endereco:'COMPLETO'}
     * @returns {undefined}
     */
    $rootScope.getGeo = function (data, element, success) {
        element = element ? element : 'map';
        DataLoadService.getContent('App', 'getGeoByAddress', data, function (data) {
            if (data.error === false) {
                $rootScope.mapShow(element, data.content.latitude, data.content.longitude);
            }
            success(data);
        });
    };

    /**
     * Método que ora substituir os \n vindo do banco de dados por <br/>
     * @param {type} str
     * @param {type} is_xhtml
     * @returns {String}
     */
    $rootScope.nl2br = function (str, is_xhtml) {
        if (typeof str === 'undefined' || str === null) {
            return '';
        }
        var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';
        return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
    };

    /****
     * Criação de método reaproveitaveis no scope
     */
    $rootScope.fn = {};

    /**
     * Método para salvar o nome da entidade enviada em uploadfile
     * @param {type} upload
     * @returns {undefined}
     */
    $rootScope.uploadFileSaveName = function (upload, success) {
        DataLoadService.getContent('Uploadfile', 'save', upload, function (data) {
            console.info('uploadFileSaveName', data);
            $rootScope.setAlert(data);
            if (data.error === false) {
                if (success) {
                    success(data);
                }
            }
        });
        console.info('uploadFileSaveName', upload);
    };

    // Obtem o padrão de modal. entidade a ser obtida, v, os dados a serem preenchidos
    $rootScope.getModalData = function (entidade, v, success) {
        DataLoadService.getContent(entidade, 'getModalData', {}, function (data) {
            console.info('modaldata', data);
            var out = [];
            angular.forEach(data.content, function (value, key) {


                // para campos tipo data
                if (value.field.indexOf('|date') > -1) {
                    value.field = value.field.replace('|date', '');
                    var tipoDate = true;
                }
                $list = value.field.split('.');
                values = v;
                for (var i = 0; i < $list.length; i++)
                    values = values[$list[i]];
                value.text = $rootScope.nl2br(values);//v[value.field];
                value.text = tipoDate ? $filter('date')(value.text, 'dd/MM/yyyy') : value.text;
                out.push(value);


            });
            success(out);
        });
    };

    // Obtem o padrão de modal. entidade a ser obtida, v, os dados a serem preenchidos
    $rootScope.getModalDataFromLinktable = function (entidade, v, success) {
        var args = {
            relacaoLinktable: entidade + '|qqentidade',
            idRightLinktable: 1
        };
        console.info('getModalData', args);
        DataLoadService.getContent('Linktable', 'getModalData', args, function (data) {
            console.info('modaldata', data);
            var out = [];
            angular.forEach(data.content, function (value, key) {
                // para campos tipo data
                if (value.field.indexOf('|date') > -1) {
                    value.field = value.field.replace('|date', '');
                    var tipoDate = true;
                }
                $list = value.field.split('.');
                values = v;
                for (var i = 0; i < $list.length; i++)
                    values = values[$list[i]];
                value.text = $rootScope.nl2br(values);//v[value.field];
                value.text = tipoDate ? $filter('date')(value.text, 'dd/MM/yyyy') : value.text;
                out.push(value);
            });
            success(out);
        });
    };


    // OBSERVADOR DE MUDANÇA DO TAMANHO DE TELA
    $rootScope.setViewTableAuto = function (scope) {
        if ($window.innerWidth >= 992) {
            scope.viewTable = true;
            $rootScope.viewTable = true;
        } else {
            scope.viewTable = false;
            $rootScope.viewTable = false;
        }
        $timeout(function () {
            scope.$digest();
        }, 500);

    };
    $rootScope.setViewTableAuto($scope);


    // este método para inserção de métodos padrões no scope enviado
    $rootScope.trataEditOnLoad = function (scope) {
        $scope.conexoesEmAndamento = $rootScope.getContentCount;
        scope[scope.entidadeName + 's'] = []; // array de objetos
        scope.pagina = 0;
        scope.filtro = []; // fltro utilizado na seleção da listagem
        scope.scroll = 0; // armazena posição do scroll para btnVoltar
        scope.working = false;
        scope.Aux = {}; // utilizado em Aux
        scope.Args = {};// utilizado em getAll
        scope.locationOrigin = angular.copy(window.location.href); // salva o lolcation original de chamada
        scope.lastLocation = angular.copy(sessionStorage.getItem('lastLocation'));
        $(".navbar-subtitle").html($('#titlePage').html());
        /*scope.viewTable = true; // default, show tables*/
        $var = window.location.href.toLowerCase();
        scope.itsMe = $var.indexOf('/' + scope.entidadeName.toLowerCase()) > 0;
        $rootScope.setViewTableAuto(scope);


        // AUXILIARES
        //if (sessionStorage.getItem('NS_AUX') !== 'undefined') {
        $aux = sessionStorage.getItem('NS_AUX');


        if ($aux === null || scope.listGetAux.length > 0) {
            $timeout(function () {
                DataLoadService.getContent('App', 'getAux', {entidade: scope.entidadeName, extras: scope.listGetAux}, function (data) {
                    console.info('AUX', data.content);
                    scope.Aux = data.content;
                    sessionStorage.setItem('NS_AUX', JSON.stringify(data.content));
                });
            }, 1000);
        } else {
            scope.Aux = JSON.parse($aux);
        }
        //}


        angular.element($window).on('resize', function () {
            $rootScope.setViewTableAuto(scope);
        });

        if (scope.entidadeName === '') {
            alert('entidade n informada');
        }

        // CONTROLE DA PRIMEIRA CARGA
        var args = [];
        args['id' + scope.entidadeName] = $rootScope.idParameter;
        if (ParametersURL[1] === 'edit' && scope.itsMe) {
            if (ParametersURL[2] === 'return') {
                $(".listEdit").hide();
            }
            //$rootScope.loading('show', 'Preparando ambiente');
            $timeout(function () {
                scope[scope.entidadeName + 'OnEdit'](args);
            });
        }
        if (ParametersURL[1] === 'view' && $rootScope.idParameter > 0 && scope.itsMe) {
            if (ParametersURL[2] === 'return') {
                $(".listEdit").hide();
            }
            //$rootScope.loading('show', 'Preparando ambiente');
            $timeout(function () {
                scope[scope.entidadeName + 'OnClick'](args);
            }, 1000);
        }


        // método para impressão
        scope.toPrint = function () {
            scope.preparandoImpressao = true;
            $(".tablePrint").removeClass('hidden-xs hidden-sm').addClass('d-none d-print-block');
            $timeout(function () {
                if (!scope.working) {
                    scope[scope.entidadeName + 'GetAll']('Preparando página para impressão (' + scope.pagina + ')');
                    $rootScope.log('PAGINA', scope.pagina);
                    if (scope.pagina > 0) {
                        scope.toPrint();
                    } else {
                        scope.preparandoImpressao = false;
                        window.print();
                        $timeout(function () {
                            $(".tablePrint").removeClass('d-none d-print-block').addClass('hidden-xs hidden-sm');
                        }, 500);
                    }
                } else {
                    scope.toPrint();
                }
            }, 500);
        };

        // Método para fechar form e voltar a lista
        scope[scope.entidadeName + 'DoClose'] = function (retorna) {
            // somente se eu estiver na minha propria rota farei isso automaticamente
            if (scope.itsMe) {
                // retornar para chamada anterior, pois veio do search
                if (!retorna && ParametersURL[2] === 'return') {
                    $rootScope.loading('show', 'Retornando...');
                    window.location = $rootScope.urlCloud + scope.lastLocation;
                    return;
                }
                $rootScope.setLocation(scope.entidadeName);
            }
            if (ParametersURL[2] !== 'return') {
                $rootScope.doClose(false, scope.entidadeName);
            }
            scope[scope.entidadeName] = false;
        };
        //if (window.location.indexOf('/'+scope.entidadeName) > -1) {

        scope[scope.entidadeName + 'DoClose'](true); // primeira a fechar para exibir listEdit




        // Método para limpar o filtro de busca
        scope.filterClear = function (arg) {
            console.info('filtroClear', arg);
            if (arg === 'Search') {
                scope.Search = '';
                scope.Args.Search = '';
                $(".Search").focus();
            }
            scope.Args['id' + arg] = '';
            scope.Args[arg] = '';
            scope[scope.entidadeName + 'GetAll']('Atualizando relação por filtros', true);
        };

        scope.clearFilter = function (varname) {
            scope[varname] = '';
            $timeout(function () {
                console.log('focus');
                $('[ng-model="' + varname + '"]').focus();
            });
        };

        // método para atender o search no topo das paginas
        scope.doSearch = function () {
            clearTimeout(scope.searchTime); // limpa o timeout anterior cada chamada pra zerar o contador
            scope.searchTime = setTimeout(function () { // vai executar isso  0,5 segundos após a pessoa parar de digitar
                console.info('dadosParaSearch', scope.Search);
                scope.working = true;
                scope.Args.Search = scope.Search;
                scope[scope.entidadeName + 'GetAll']('Efetuando pesquisa', true);
            }, 500);
        };

        // chamada de funcao para criação de nova entidade
        scope[scope.entidadeName + 'New'] = function (success) {
            $rootScope.loading('show', 'Preparando ambiente');
            fnNew = scope.fnNew ? scope.fnNew : 'getNew';
            DataLoadService.getContent(scope.entidadeName, fnNew, {}, function (data) {
                $rootScope.log(scope.entidadeName + 'New', data);
                scope[scope.entidadeName] = data.content;
                $rootScope.loading('hide');
                success(data.content);
            });
        };

        // Lista de entidades
        scope[scope.entidadeName + 'GetAll'] = function (msg, reload) {
            if (reload) {
                scope[scope.entidadeName + 's'] = [];
                scope.pagina = 0;
            }
            if (scope.pagina >= 0 && $('.listEdit' + scope.entidadeName).is(':visible')) {
                scope.working = true;
                scope.Args.pagina = scope.pagina;
                scope.pagina++;
                console.info('args para pesquisa', scope.Args);
                $rootScope.loading('show', msg ? msg : 'Obtendo dados <small>(' + scope.pagina + ')</small>');
                fnGetAll = scope.fnGetAll ? scope.fnGetAll : 'getAll';
                DataLoadService.getContent(scope.entidadeName, fnGetAll, scope.Args, function (data) {
                    console.info(scope.entidadeName+'getall', data);
                    delete (data.content.comboSearchList);

                    if (typeof data.content.Aux !== 'undefined') {
                        // Enviar os dados para os auxiliares
                        angular.forEach(data.content.Aux, function (v, k) {
                            scope.Aux[k] = v;
                        });
                    }
                    if (typeof data.content.list !== 'undefined') {
                        // Trazer o list para o item principal
                        data.content = data.content.list;
                    }
                    $rootScope.log(scope.entidadeName + 'GetAll ' + scope.pagina, data);
                    $rootScope.loading('hide');
                    if (data.error !== false || data.content === null || data.content === false || data.content.length === 0) {
                        scope.pagina = -1;
                    } else {
                        if (data.error === false) {
                            angular.forEach(data.content, function (val, key) {
                                scope[scope.entidadeName + 's'].push(val);
                            });
                        }
                        $rootScope.setAlert(data);
                    }
                    scope.working = false;
                });
            }
        };



        // Método para setar a entidade. Não decide se vai exibir ou editar
        scope['setEntidade'] = function (item, success) {
            nomeCampoId = ['id' + scope.entidadeName];
            console.info('SUCCESS', item);
            id = item[nomeCampoId];
            if (id > 0) {
                scope.working = true;
                $rootScope.loading('show', 'Obtendo dados', 'modal');
                DataLoadService.getContent(scope.entidadeName, 'getById', {id: id}, function (data) {
                    $rootScope.loading('hide');
                    scope.working = false;
                    scope[scope.entidadeName] = data.content;
                    scope.setVinculosOnEdit(data.content[nomeCampoId]);
                    success(data.content);
                    $rootScope.setTypes();

                });
            } else {
                scope[scope.entidadeName + 'New'](function (data) {
                    console.log('Criado novo');
                    scope.setVinculosOnEdit(data[nomeCampoId]);
                    success(data);

                });
            }
        };

        // exibe modal para view
        scope[scope.entidadeName + 'OnClick'] = function (item) {
            scope.setEntidade(item, function (v) {
                console.info('setentidadeV', v);
                $rootScope.trocaView('view' + scope.entidadeName, scope.entidadeName, item['nome' + scope.entidadeName]);
                $rootScope.setLocation(scope.entidadeName + '/' + item['id' + scope.entidadeName] + '/view');
            });

            //scope[scope.entidadeName + 'OnEdit'](item);

            /* Caso queira mostrar modal, ao inve´s de edit 
             $scope.setEntidade(%entidade%, function (v) {
             $rootScope.getModalData('%entidade%', v, function(data){
             $scope.modalData = data;
             $("#modalView"+$scope.entidadeName).modal('show');
             });
             });
             */
        };

        // exibe form para edição
        scope[scope.entidadeName + 'OnEdit'] = function (item) {
            scope.setEntidade(item, function (v) {
                $rootScope.trocaView('formEdit' + scope.entidadeName, scope.entidadeName, item['nome' + scope.entidadeName]);
                $rootScope.setLocation(scope.entidadeName + '/' + item['id' + scope.entidadeName] + '/edit');
                $timeout(function () {
                    $(".nav-tabs .nav-item a")[0].click();
                }, 100);

            });
        };

        // Salvar entidade. Variavel parcial controla se foi clicado no botao Salvar final da edição
        scope[scope.entidadeName + 'Save'] = function (partial) {
            $rootScope.log(scope.entidadeName + 'Save', scope[scope.entidadeName]);
            $rootScope.loading('show', 'Salvando dados', 'modal');
            window.scrollTo(0, 0);
            DataLoadService.getContent(scope.entidadeName, 'save', scope[scope.entidadeName], function (data) {
                $rootScope.log(scope.entidadeName + 'SaveExec', data);
                $rootScope.setAlert(data);
                if (data.error === false) {
                    scope[scope.entidadeName + 'DoClose']();
                    scope[scope.entidadeName + 'GetAll']('Atualizando relação', true);
                }
            });
        };

        // Remover entidade
        scope[scope.entidadeName + 'Remove'] = function (item) {
            nomeCampoId = ['id' + scope.entidadeName];
            id = item[nomeCampoId];

            // exibir confirmação de remoção
            $rootScope.swalConfirm({
                title: 'Confirma remover?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: 'red',
                cancelButtonColor: 'secondary',
                confirmButtonText: 'Sim, remover',
                cancelButtonText: 'Cancelar'
            }, function () {
                DataLoadService.getContent(scope.entidadeName, 'remove', {id: id}, function (data) {
                    console.info(scope.entidadeName + '-Removed', data);
                    if (data.error === false) {
                        scope[scope.entidadeName + 's'] = $rootScope.arrayFilter(scope[scope.entidadeName + 's'], nomeCampoId, id);
                        scope[scope.entidadeName + 'DoClose']();
                        $rootScope.undo(data, scope.entidadeName, scope);
                    } else {
                        $rootScope.setAlert(data);
                    }
                });
            });

        };

        scope.showModal = function (modalId) {
            console.info('Call modal', modalId);
            $("#" + modalId).modal({backdrop: "static"});
        };







    }; // fechar injection by trataOnEdit


    // método para tratar o desfazer nas exclusões
    $rootScope.undo = function (data, entidade, $scope) {
        if (data.error === false) {
            var msg = 'Removido com sucesso. <div class="row">'
                    + '<div class="col-6 text-left"><a class="btn btn-link text-warning text-left" id="' + entidade + 'RemoveUndo">Desfazer</a></div>'
                    + '<div class="col-6 text-right"><a class="btn btn-link text-primary text-right" id="' + entidade + 'CloseUndo">OK</a></div>'
                    + '</div>';
            data.content.result = msg;
            data.trash = 'trash';
            $rootScope.setAlert(data);
            $('#' + entidade + 'CloseUndo').prop('onclick', null).off('click').on('click', function (evt) {
                $("#barraUndo").fadeOut();
            });
            $('#' + entidade + 'RemoveUndo').prop('onclick', null).off('click').on('click', function (evt) {
                $("#barraUndo").hide();
                $scope.working = true;
                $rootScope.loading('show', 'Revertendo', 'modal');
                DataLoadService.getContent('Trash', 'undo', {idTrash: data.content.idTrash}, function (data) {
                    $scope.working = false;
                    $rootScope.setAlert(data);
                    if (data.error === false) {
                        $scope[entidade + 'GetAll']('Atualizando relação', 'true');
                    }
                });
            });

            //$scope.getAll('Atualizando relação', true);
        } else {
            $rootScope.setAlert(data);
        }
    };

    // mmétodo que devolve um filter, retirando o array indesejado
    $rootScope.arrayFilter = function (list, cpoCompare, value) {
        var n = [];
        list.forEach(function (v, k) {
            if (v[cpoCompare] !== value) {
                n.push(v);
            }
        });
        console.info('LIST', n);
        return n;
    };

    $rootScope.sessaoRenovar = function () {
        //$scope.working = true;
        //$rootScope.loading('show', 'Renovando sessão');
        $("#divTempoSessao").removeClass('btn btn-warning');
        DataLoadService.getContent('App', 'sessionRenew', {}, function (data) {
            //$scope.working = false;
            $rootScope.setAlert(data);
        });
    };

    /** Controle de sessão, para cocnidir com o prazo do TOKEN **/
    $scope.sessionRenew = function () {
        $rootScope.sessaoRenovar();

    };
    $rootScope.tempoDisponivel = 15 * 1000 * 60;

    $rootScope.pulseElem = function (elem, time, duracao) {
        elem.removeClass('d-none');
        time = time ? time : 800;
        elem.animate({opacity: 0.3}, time, function () {
            elem.animate({opacity: 1}, time);
        });

        $rootScope.pulseelemTime = $timeout(function () {
            //console.info('pulse');
            $rootScope.pulseElem(elem, time);
        }, (time * 2));

        // para um apusalção com duração definida
        if (duracao) {
            $timeout(function () {
                $rootScope.stopPulseElem(elem);
            }, duracao * 1000);
        }

    };
    $rootScope.stopPulseElem = function () {
        $timeout.cancel($rootScope.pulseelemTime);
        $rootScope.pulseelemTime = false;
    };

    $rootScope.bryan = function (scope, fnTrataOnEnd) {
        if (!('webkitSpeechRecognition' in window)) {
            alert('Seu browser não está habilitado para utilizar microfone');
        } else {
            scope.recognition = new webkitSpeechRecognition();
            var final_transcript = '';
            scope.recognition.lang = "pt-BR";
            scope.recognition.continuous = false;
            scope.recognition.interimResults = true;

            $('body').append('<div id="brainMenuSearchSpeech" style="position:absolute;width:100%;height:100%;opacity:0.3;z-index:100;background:#eee; text-align="center"; padding-top: "150px";></div>');
            var elem = $('#brainMenuSearchSpeech'); // esta no template1.html

            elem.html('<i class="fa fa-microphone" aria-hidden="true"></i> Diga o que está procurando').fadeIn();
            $rootScope.pulseElem(elem);

            scope.recognition.onresult = function (event) {
                var interim_transcript = '';
                $rootScope.stopPulseElem();

                for (var i = event.resultIndex; i < event.results.length; ++i) {
                    if (event.results[i].isFinal) {
                        final_transcript += event.results[i][0].transcript;
                        elem.html('');
                    } else {
                        interim_transcript += event.results[i][0].transcript;
                        elem.html(interim_transcript + ' <i class="fa fa-spinner fa-spin" aria-hidden="true"></i>');
                    }
                }
                /** controle para parar automatico 
                 if (interim_transcript.indexOf('parar') > -1) {
                 scope.recognition.stop();
                 return;
                 }
                 **/

                console.log(final_transcript);
                console.log(interim_transcript);
            };
            scope.recognition.onend = function () {
                f = final_transcript;
                console.log('F: ' + f);
                scope[fnTrataOnEnd](f);
                elem.html('').hide();
                $rootScope.stopPulseElem();
            };
            scope.recognition.start();
        }
    };



    // tempo disponivel ira resetar o temporarizador (em segundos)
    $rootScope.tempoSessao = function (tempoDisponivel) {
        // reiniciar o contador
        if (tempoDisponivel > 0) {
            console.log('sessao reinicada: ' + tempoDisponivel);
            $timeout.cancel($rootScope.timeoutTempoSessao);
            $rootScope.tempoDisponivel = tempoDisponivel;// *  1000;
            return;
        }


        // segue pq ja tem tempo de sessao
        if ($rootScope.tempoDisponivel / 1000 < 65) {
            disp = $rootScope.tempoDisponivel / 1000;
            segundos = 1;
            sufixo = ' segs';
        } else {
            disp = $rootScope.tempoDisponivel / 1000 / 60;
            segundos = Math.round($rootScope.tempoDisponivel / 1000 % 60);
            sufixo = ' min';
        }

        console.log('XA21-Tempo disponivel: ' + disp);
        console.log('XA21-Tempo disponivel (segundos): ' + disp);


        if (disp <= 0) {
            $rootScope.User = false;
            window.location.href = appConfig.urlCloud + 'logout';
        } else if (disp <= 1) {
            $(".tempoSessao").html('Sessão expirada!');
            $rootScope.User = false;
            sessionStorage.setItem('user', 'FALSE');
            sessionStorage.setItem('CS_TK_CST', 'FALSE');



            // setTimeout(function () {
            $('#barraAviso').hide();
            $('body').removeClass('default-bg-image');
            $('body').removeClass('forms');
            $(".modal").modal('hide');
            $("nav").hide();
            $("#content").html('<div class="alert alert-secondary text-center" style="margin-top:55px;color:#999;">'
                    + '<h3 class="text-dark">Sessão encerrada por inatividade</h3>'
                    + '<a class="btn btn-primary" href="' + appConfig.urlCloud + 'logout">Ir para login</a>'
                    + '</div>');
            // });
            $timeout.cancel($rootScope.timeoutTempoSessao);
            setTimeout(function () {
                //// encerrar navegação
                sessionStorage.setItem('m_print', 'Sessão encerrada por inatividade');
                window.location.href = appConfig.urlCloud + 'logout';
            }, 10000);
            return false;
        } else if ($rootScope.tempoDisponivel / 1000 < 300) {
            $(".tempoSessao").html('<br/>Sessão expira em ' + Math.round(disp) + sufixo + ' por inatividade.<br/> <span class="text-warning">Clique aqui para renovar</span>').show();
        } else {
        }


        console.info($rootScope.tempoDisponivel + 'Sessão expira em ' + disp + sufixo);
        console.info('Tempo de sessão em segundos: ' + segundos);

        $rootScope.tempoDisponivel = $rootScope.tempoDisponivel - (1000 * segundos);

        $rootScope.timeoutTempoSessao = $timeout(function () {
            $rootScope.tempoSessao();
        }, 1000 * segundos);

    };


    $rootScope.setTooltip = function () {
        console.log('ToolTip');
        $timeout(function () {
            $('[data-toggle="tooltip"]').tooltip(
                    {
                        position: {
                            my: "left bottom",
                            at: "left top",
                            using: function (position, feedback) {
                                $(this).css(position);
                                $("<div>")
                                        .addClass("arrow")
                                        .addClass(feedback.vertical)
                                        .addClass(feedback.horizontal)
                                        .appendTo(this);
                            }
                        }
                    }
            );
        }, 1000);
    };
    $rootScope.setTooltip();

    $rootScope.setTypes = function () {
        $rootScope.setTooltip();
        $rootScope.setTypeTO = $timeout(function () {
            // formatar campo tipo double
            $('.decimal').mask('000.000.000.000,00', {reverse: true});
            $(".cep").mask('99999-999');
            $(".fone").mask('(99)999999990');
            $('.cpf').mask('999.999.999-99');

            // duracao
            var mask = "H:MM",
                    pattern = {
                        'translation': {
                            'H': {
                                pattern: /[0-5]/
                            },
                            'M': {
                                pattern: /[0-59]/
                            }
                        }
                    };
            $(".duracao").mask(mask, pattern);

            var maskHora = "HH:MM",
                    pattern = {
                        'translation': {
                            'H': {
                                pattern: /[0-23]/
                            },
                            'M': {
                                pattern: /[0-59]/
                            }
                        }
                    };
            $(".hora").mask(maskHora, pattern);
        }, 1500);
    };
    $rootScope.setTypes();


    $rootScope.setDateRange = function (startDate, endDate, arg) {
        moment.locale('pt-br');
        var start = startDate ? moment(new Date(startDate + 'T23:59:59')) : moment().startOf('month');
        var end = endDate ? moment(new Date(endDate + 'T23:59:59')) : moment();

        arg._periodoRange = start.format('DD/MM/YYYY') + ' à ' + end.format('DD/MM/YYYY');

        // daterange
        $('.daterange').attr('readonly', 'readonly').addClass('no-floating').daterangepicker({
            startDate: start.format('DD-MM-YYYY'),
            endDate: end.format('DD-MM-YYYY'),
            locale: {
                closeText: 'Fechar',
                prevText: '&#x3c;Anterior',
                nextText: 'Pr&oacute;ximo&#x3e;',
                currentText: 'Hoje',
                monthNames: ['Janeiro', 'Fevereiro', 'Mar&ccedil;o', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'],
                monthNamesShort: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
                dayNames: ['Domingo', 'Segunda-feira', 'Ter&ccedil;a-feira', 'Quarta-feira', 'Quinta-feira', 'Sexta-feira', 'Sabado'],
                dayNamesShort: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sab'],
                dayNamesMin: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sab'],
                weekHeader: 'Sm',
                dateFormat: 'dd/mm/yy',
                firstDay: 0,
                isRTL: false,
                showMonthAfterYear: false,
                yearSuffix: '',
                format: 'DD/MM/YYYY',
                daysOfWeek: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sab'],
                customRangeLabel: 'Escolher periodo',
                "separator": " à ",
                "applyLabel": "Aplicar",
                "cancelLabel": "Cancelar",
                "fromLabel": "De",
                "toLabel": "Para"
            },
            ranges: {
                'Hoje': [moment(), moment()],
                'Ontem': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                '7 Dias': [moment().subtract(6, 'days'), moment()],
                '30 Dias': [moment().subtract(29, 'days'), moment()],
                'Este mês': [moment().startOf('month'), moment().endOf('month')],
                'Mês passado': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                'Próximo mês': [moment().add(1, 'month').startOf('month'), moment().add(1, 'month').endOf('month')],
                'Este ano': [moment().startOf('year'), moment().endOf('year')],
                'Ano passado': [moment().subtract(1, 'years').startOf('year'), moment().subtract(1, 'years').endOf('year')],
                //'Próximo ano': [moment().add(1, 'month').startOf('month'), moment().add(1, 'month').endOf('month')],
                'Valor inicial': [start, end]
            }
        }, function (start, end, label) {
            //console.log("A new date selection was made: " + start.format('DD-MM-YYYY') + ' to ' + end.format('DD-MM-YYYY'));
        });
    };



    $rootScope.siteCall = function (action, args, loading, success) {
        $scope.working = true;
        if (loading) {
            $rootScope.loading('show', loading, 'modal');
        }
        args._nsm = action;
        DataLoadService.getContent('App', 'site', args, function (data) {
            $scope.working = false;
            success(data);
        });


    };


    /********************************************/


    $rootScope.swalConfirm = function (config, success, htmlInsert, scope) {
        my_config =
                {
                    title: 'Confirma?',
                    text: '',
                    html: '',
                    icon: 'warning',
                    showCancelButton: true,
                    /*confirmButtonColor: '#28a745',*/
                    cancelButtonColor: '#999',
                    confirmButtonText: '<i class="fa fa-check mr-1"></i>Sim',
                    cancelButtonText: '<i class="fa fa-times mr-1"></i>Cancelar',
                    reverseButtons: true
                };

        // Se vier a inserção de HTML na função, já introduzir
        if (htmlInsert && scope) {
            delete(config.text);
            config.html = '<i class="fa fa-refresh fa-spin mr-1"></i>Preparando dados...';
            $timeout(function () {
                $("#swal2-content").html($compile(htmlInsert)(scope));
            }, 500);
        }
        if (typeof config === 'string') {
            my_config.text = config;
        } else if (typeof config === 'object') {
            if (config.html) {
                delete my_config.text;
            } else {
                delete my_config.html;
            }
            angular.forEach(my_config, function (v, k) {
                if (config[k]) {
                    my_config[k] = config[k];
                }
            });
            angular.forEach(config, function (v, k) {
                my_config[k] = config[k];
            });
        }
        // @26/08/2020 - Definição da cor do botão true para danger
        if ((my_config.icon === 'warning' || my_config.title.indexOf('remover') > -1) && !config.confirmButtonColor) {
            my_config.confirmButtonColor = '#dc3545';
        }

        Swal.fire(my_config).then((result) => {
            if (result.value) {
                success();
            }
        });
    };




});


app.controller('SessaoController', function (DataLoadService, $timeout) {
    $timeout(function () {
        DataLoadService.getContent('App', 'validaLogin', {}, function (data) {
        });
    }, 3000);
});