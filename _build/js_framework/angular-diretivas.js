// Add this directive where you keep your directives
app.directive('onLongPress', function ($timeout, $uibModal, $compile) {
    return {
        restrict: 'A',
        link: function ($scope, $elm, $attrs) {
            $elm.on('contextmenu', function (evt) {
                $("#menuContext li").remove();
                $("#menuContext").attr('ng-controller', $attrs.contextController);
                var itens = JSON.parse($attrs.contextItens);
                var lis = "";
                $.each(itens, function (i, v) {
                    if (typeof (v.ngshow) === 'string') {
                        lis += '<li ng-show="' + v.ngshow + '"><a ng-click="' + v.link + '">' + v.title + '</a></li>';
                    } else {
                        lis += '<li><a ng-click="' + v.link + '">' + v.title + '</a></li>';
                    }
                });
                $timeout(function () { // serve para identificar o valor da variavel e o angularjs processar o ng-show
                    //$("#menuContext").append(lis); // SERVE PARA O ANGULAR COMPILAR O HTML ANTES DE INSERIR NO DOM E OBSERVAR ALTERAÇÕES
                    $("#menuContext").append($compile(lis)($scope)); // SERVE PARA O ANGULAR COMPILAR O HTML ANTES DE INSERIR NO DOM E OBSERVAR ALTERAÇÕES
                });
                showContext(evt);
            });
            $elm.on('touchstart', function (evt) {
                // Locally scoped variable that will keep track of the long press
                $scope.longPress = true;
                // We'll set a timeout for 600 ms for a long press
                $timeout(function () {
                    if ($scope.longPress) {
                        showContext(evt);
                        // If the touchend event hasn't fired,
                        // apply the function given in on the element's on-long-press attribute
                        $scope.$apply(function () {
                            $scope.$eval($attrs.onLongPress)
                        });
                    }
                }, 600);
            });
            $elm.on('touchend mouseup', function (evt) {
                // Prevent the onLongPress event from firing
                $scope.longPress = false;
                // If there is an on-touch-end function attached to this element, apply it
                if ($attrs.onTouchEnd) {
                    $scope.$apply(function () {
                        $scope.$eval($attrs.onTouchEnd)
                    });
                }
            });
        }
    };
});


// Add this directive where you keep your directives
app.directive('menuContexto', function ($timeout, $uibModal, $compile) {
    return {
        restrict: 'A',
        link: function ($scope, $elm, $attrs) {
            $elm.on('click', function (evt) {
                $("#menuContext li").remove();
                var itens = JSON.parse($attrs.menuContextoItens);
                var lis = "";
                $.each(itens, function (i, v) {
                    if (typeof (v.ngshow) === 'string') {
                        lis += '<li ng-show="' + v.ngshow + '"><a ng-click="' + v.link + '">' + v.title + '</a></li>';
                    } else {
                        lis += '<li><a ng-click="' + v.link + '">' + v.title + '</a></li>';
                    }
                });

                $timeout(function () { // serve para identificar o valor da variavel e o angularjs processar o ng-show
                    //$("#menuContext").append(lis); // SERVE PARA O ANGULAR COMPILAR O HTML ANTES DE INSERIR NO DOM E OBSERVAR ALTERAÇÕES
                    $("#menuContext").append($compile(lis)($scope)); // SERVE PARA O ANGULAR COMPILAR O HTML ANTES DE INSERIR NO DOM E OBSERVAR ALTERAÇÕES
                    showContext(evt);
                });
            });
        }
    };
});



// // Diretiva para formatação de numero em double brasileiro
//Usar no input: format="number"
app.directive('format', ['$filter', function ($filter) {
        return {
            require: '?ngModel',
            link: function (scope, elem, attrs, ctrl) {
                if (!ctrl)
                    return;
                //ctrl.$formatters.unshift(primeira(a));

                //ctrl.$parsers.unshift(segunda(viewValue));

                function primeira(a) {
                    return $filter(attrs.format)(ctrl.$modelValue);
                }
                ;
                function segunda(viewValue) {
                    if (viewValue.length <= 3) {
                        viewValue = '00' + viewValue;
                    }
                    var value = viewValue.toString();
                    value = value.replace(/\D/g, "");
                    value = value.replace(/(\d{2})$/, ",$1");
                    value = value.replace(/(\d+)(\d{3},\d{2})$/g, "$1.$2");
                    var qtdLoop = (value.length - 3) / 3;
                    var count = 0;
                    while (qtdLoop > count)
                    {
                        count++;
                        value = value.replace(/(\d+)(\d{3}.*)/, "$1.$2");
                    }
                    var plainNumber = value.replace(/^(0)(\d)/g, "$2");
                    elem.val(plainNumber);
                    return plainNumber;
                }
                ;
                scope.$watch(attrs.ngModel, function (newValue, oldValue) {
                    primeira();
                    if (newValue)
                        elem.val(segunda(newValue));
                });
            }
        };
    }]);
/**
 * Diretiva para inserir uma div com status exibir enquanto wait nao existir
 */
app.directive('wait', function () {
    return {
        restrict: 'E',
        scope: {msg: '@', wait: '@'},
        template: '<div class="alert alert-info text-center" ng-show="!wait"><span class="glyphicon glyphicon-refresh glyphicon-spin"></span> {{msg}}</div>'
    };
});
app.directive('notFound', function () {
    return {
        restrict: 'E',
        scope: {msg: '@', var : '@'},
        template: '<div class="alert alert-warning text-center" ng-if="!var"><span class="glyphicon glyphicon-info-sign"></span> {{msg}}</div>'
    };
});
app.directive('info', function () {
    return {
        restrict: 'E',
        scope: {msg: '@'},
        template: '<div class="alert alert-info text-center"><i class="fa fa-info-circle" aria-hidden="true"></i> {{msg}}</div>'
    };
});
app.directive('tip', function () {
    return {
        restrict: 'E',
        transclude: true,
        template: '<span class="tip text-italic text-warning"><i class="fa fa-info mr-1" aria-hidden="true"></i><small><i><span class="text-italic" ng-transclude></span></small></i></span>'
    };
});

app.directive('badge', function () {
    return {
        scope: {value: '@'},
        restrict: 'E',
        transclude: false,
        template: '<span class="badge badge-light" ng-bind-html="value"></span>',
    };
});



app.directive('searchByImage', function ($rootScope, DataLoadService) {
    return {
        restrict: 'E',
        scope: {btnText: '@', btnIcon: '@', entidade: '@', maxsize: '@', thumbs: '@', printAvatar: '@', multiple: '@'},
        templateUrl: appConfig.urlCloud + 'view/template/uploadfile.html',
        controller: function ($scope) {
            $scope.progress = 0;
            $scope.avatar = false;
            $scope.progress = 0;

            $scope.uploader = DataLoadService.getUploadFile($scope.maxsize, $scope.thumbs);
            $scope.uploader.onProgressItem = function (fileItem, progress) {
                $scope.progress = progress;
            };
            $scope.uploader.onSuccessItem = function (fileItem, response, status, headers) {
                $rootScope.loading('hide');
                $rootScope.setAlert(response);
                if (response.result === 'SUCCESS') {
                    if (response.content === null) {
                        $scope.progress = false;
                    } else {
                        $rootScope.uploadFileName = response.content; //.filename;
                    }
                }
            };
            $scope.uploader.onCompleteAll = function (item, response, status, headers) {
                console.info('onCompleteAll', status);
                $scope.progress = false;
            };
            // ação ao clicar no botão de adicionar
            $scope.openUpload = function (entidade) {
                $rootScope.uploadFileEntidade = entidade; // salva o nome pra usar em FILES no controller de fora
                document.getElementById('fileToUpload').click();
            };
        },
        link: function ($scope, elem, attrs, ctrl) {
        }

    };
});

app.directive('datepicker', function () {
    return {
        scope: {maxDate: '@', minDate: '@'},
        require: 'ngModel',
        link: function ($scope, $elem) {
            $elem.datepicker({
                dateFormat: 'dd/mm/yy',
                maxDate: new Date($scope.maxDate + ' 23:59:59'),
                minDate: new Date($scope.minDate + ' 00:00:00'),
                dayNames: ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado', 'Domingo'],
                dayNamesMin: ['D', 'S', 'T', 'Q', 'Q', 'S', 'S', 'D'],
                dayNamesShort: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'],
                monthNames: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'],
                monthNamesShort: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
                nextText: 'Próximo',
                prevText: 'Anterior',
                //changeMonth: true, // esta causando erro no tipo de input
                //changeYear: true,
            });
            $elem.mask('99/99/9999');
        }
    };
});

app.directive('number', function ($timeout) {
    return {
        require: 'ngModel',
        link: function (scope, elem) {
            $timeout(function () {
                elem.mask('000.000.000.000,00', {reverse: true});
            });

        }
    };
});

app.directive('convertToNumber', function () {
    return {
        require: 'ngModel',
        link: function (scope, element, attrs, ngModel) {
            ngModel.$parsers.push(function (val) {
                return val != null ? parseInt(val, 10) : null;
            });
            ngModel.$formatters.push(function (val) {
                return val != null ? '' + val : null;
            });
        }
    };
});

app.filter('cep', function () {
    return function (cep) {
        return cep.substr(0, 5) + '-' + cep.substr(5, 3);
    };
});



app.directive('combos', function ($rootScope, DataLoadService) {
    return {
        restrict: 'AE',
        scope: {source: '@', destine: '@'},
        require: '?ngModel',
        templateUrl: appConfig.urlCloud + 'view/template/combos.html',
        controller: function ($scope, DataLoadService) {
            $scope.listSource = [];
            // obter itens da listA
            DataLoadService.getContent($scope.source, 'getAll', {}, function (data) {
                console.info('Data-' + $scope.source, data);
                angular.forEach(data.content, function (v, k) {
                    var op = document.createElement('option');
                    op.value = v['id' + $scope.source];
                    op.text = v['nome' + $scope.source];
                    $("#" + $scope.source).append(op);
                });
                console.info('listSource', $scope.listSource);
            });
        }
    };
});


// filter para retornar a primeira letra maiscula e as demais minusuculas
app.filter('capitalize', function () {
    return function (input, scope) {
        if (input != null)
            input = input.toLowerCase();
        return input.substring(0, 1).toUpperCase() + input.substring(1);
    }
});

/**
 * Exibe um loading enquanto o ng-src esta sendo carregado. Duração de timeout de 10 segundos
 */
app.directive('spinnerLoad', function ($timeout) {
    return {
        restrict: 'A',
        link: function (scope, elem, attrs) {
            var obs = attrs.observe ? attrs.observe : 'ngSrc';
            scope.$watch(obs, function () {
                elem.hide();
                elem.after('<i class="fa fa-spinner fa-lg fa-spin"></i>');  // add spinner
                setTimeout(function () {
                    elem.next('i.fa-spinner').remove();
                }, 10000);
            });

            elem.on('load', function () {
                elem.show();
                elem.next('i.fa-spinner').remove(); // remove spinner
                elem.next('i.fa-spinner').remove(); // remove spinner
            });
        }
    };
});

app.filter('trustUrl', function ($sce) {
    return function (url) {
        return $sce.trustAsResourceUrl(url); 
    };
});

app.directive('ngThumb', function ($window) {
    var helper = {
        support: !!($window.FileReader && $window.CanvasRenderingContext2D),
        isFile: function (item) {
            return angular.isObject(item) && item instanceof $window.File;
        },
        isImage: function (file) {
            var type = '|' + file.type.slice(file.type.lastIndexOf('/') + 1) + '|';
            return '|jpg|png|jpeg|bmp|gif|'.indexOf(type) !== -1;
        }
    };

    return {
        restrict: 'A',
        template: '<canvas/>',
        link: function (scope, element, attributes) {
            if (!helper.support)
                return;

            var params = scope.$eval(attributes.ngThumb);

            if (!helper.isFile(params.file))
                return;
            if (!helper.isImage(params.file))
                return;

            var canvas = element.find('canvas');
            var reader = new FileReader();

            reader.onload = onLoadFile;
            reader.readAsDataURL(params.file);

            function onLoadFile(event) {
                var img = new Image();
                img.onload = onLoadImage;
                img.src = event.target.result;
            }

            function onLoadImage() {
                var width = params.width || this.width / this.height * params.height;
                var height = params.height || this.height / this.width * params.width;
                canvas.attr({width: width, height: height});
                canvas[0].getContext('2d').drawImage(this, 0, 0, width, height);
            }
        }
    };
});



app.directive('datepickernew', function () {
    return {
        scope: {maxDate: '@', minDate: '@'},
        require: 'ngModel',
        controller: function ($scope, $element) {
            $scope.init = function () {
                console.log('datepicker-reload');
                // max date
                $var = $scope.maxDate.split('/');
                maxDate = new Date($var[2] + '-' + $var[1] + '-' + $var[0] + ' 23:59:00');
                // min date
                $var = $scope.minDate.split('/');
                minDate = new Date($var[2] + '-' + $var[1] + '-' + $var[0] + ' 00:01:00');
                $element.datepicker('destroy');
                $element.datepicker({
                    dateFormat: 'dd/mm/yy',
                    maxDate: maxDate,
                    minDate: minDate,
                    dayNames: ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado', 'Domingo'],
                    dayNamesMin: ['D', 'S', 'T', 'Q', 'Q', 'S', 'S', 'D'],
                    dayNamesShort: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'],
                    monthNames: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'],
                    monthNamesShort: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
                    nextText: 'Próximo',
                    prevText: 'Anterior',
                    changeMonth: true,
                    changeYear: true
                });
                $element.mask('99/99/9999');

            };
            $scope.$watch('minDate', function () {
                console.log('datepicker-watch');
                $scope.init();
            });
            $scope.$watch('maxDate', function () {
                console.log('datepicker-watch');
                $scope.init();
            });
        },
        link: function ($scope, $elem) {
            $scope.init();
        }
    };
});