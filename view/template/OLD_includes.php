<?php

$URL = Config::getData('url') . '/library';
$urlRouter = Config::getData('url') . '/ns_tr/';
//$URL_SRC = Config::getData('url') . '/src';
$URL_VIEW = Config::getData('urlView');
$version = explode(' ', file_get_contents(Config::getData('path') . '/version'))[0];
//echo Router::rotaJS('view/js/fn.js.php');
$var = "var js = [
    '$URL_VIEW/bower_components/jquery/dist/jquery.min.js', 
    '$URL_VIEW/bower_components/angular/angular.min.js', 
    '$URL_VIEW/bower_components/popper.js/dist/popper.js',
    '$URL_VIEW/bower_components/angular-file-upload-FULL_3/dist/angular-file-upload.js', 
    '$URL_VIEW/bower_components/bootstrap/dist/js/bootstrap.min.js',
    '$URL_VIEW/bower_components/angular-bootstrap/ui-bootstrap.min.js', 
    '$URL_VIEW/bower_components/angular-bootstrap/ui-bootstrap-tpls.min.js',
    '$URL_VIEW/bower_components/angular-ui-mask/src/mask.js', 
    '$URL_VIEW/bower_components/jquery-ui/jquery-ui.min.js',
    '$URL_VIEW/bower_components/jquery-mask-plugin/dist/jquery.mask.min.js',
    '$URL_VIEW/bower_components/ngInfiniteScroll/build/ng-infinite-scroll.min.js',
    '$URL_VIEW/bower_components/angular-sanitize/angular-sanitize.min.js',
    '$URL_VIEW/bower_components/jquery-animateNumber/jquery.animateNumber.min.js',
    '".Component::getUrlOnView('fn.js')."',
    '".Component::getUrlOnView('app.js')."',
    '".Component::getUrlOnView('service.js')."', 
    '".Component::getUrlOnView('AppController.js')."', 
    '".Component::getUrlOnView('angular-diretivas.js')."', 
    '".Component::getUrlOnView('angular-locale_pt-br.js')."',
    '$URL_VIEW/bower_components/chart.js/dist/Chart.min.js',    
    '$URL_VIEW/bower_components/orgchart/src/js/jquery.orgchart.js',
    '$URL_VIEW/bower_components/summernote/dist/summernote.js',
    '$URL_VIEW/bower_components/summernote/dist/lang/summernote-pt-BR.js',
    '$URL_VIEW/bower_components/angular-summernote/dist/angular-summernote.js',
    '$URL_VIEW/js/swal.js',
    '$URL_VIEW/bower_components/fine-uploader/fine-uploader.js'
    //'$URL_VIEW/bower_components/sweetalert2/src/sweetalert2.js',
];

var css = [
    '$URL_VIEW/bower_components/bootstrap/dist/css/bootstrap.min.css',
    '$URL_VIEW/bower_components/jquery-ui/themes/base/jquery-ui.min.css',
    '$URL_VIEW/bower_components/font-awesome/css/font-awesome.min.css',
    '$URL_VIEW/bower_components/smartmenus/dist/addons/bootstrap/jquery.smartmenus.bootstrap.css',
    '$URL_VIEW/bower_components/orgchart/src/css/jquery.orgchart.css',
    '$URL_VIEW/bower_components/summernote/dist/summernote.css',
    //'$URL_VIEW/bower_components/fine-uploader/fine-uploader-gallery.min.css,
    '$URL_VIEW/css/estilo.css',
    '$URL_VIEW/css/card.css',
    '$URL_VIEW/css/theme.css',
    '$URL_VIEW/css/estilo_site.css',
    '$URL_VIEW/css/nav.css',
    '$URL_VIEW/css/spinner.css',
    '$URL_VIEW/css/animate.css',
    '$URL_VIEW/css/correcoes_tema.css'
    //'$URL_VIEW/bower_components/sweetalert/src/sweetalert.css',
];

css.forEach(function (val) {
    document.write(unescape(\"%3Clink href='\"+val+\"?v=" . $version . "' rel='stylesheet'%3E\"));
});
js.forEach(function (val) {
    document.write(unescape(\"%3Cscript src='\" + val + \"?v=" . $version . "' type='text/javascript'%3E%3C/script%3E\"));
});

var ParametersURL = " . json_encode(Config::getData('params')) . ";
    

";

//$JS_INCLUDE = '<link href="' . $URL_VIEW . '/bower_components/fine-uploader/fine-uploader-gallery.css" rel="stylesheet" type="text/css"/>';
$packer = new Packer($var, 'Normal', true, false, true);
$JS_INCLUDE = '<script>' . $packer->pack() .'</script>';
//$JS_INCLUDE .= '<script>' . $packer->pack() . '</script>';
//$JS_INCLUDE .= '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@8"></script>';

unset($var);
unset($template);
unset($packer);
