<?php

/*
  $URL = Config::getData('url') . '/library';
  $urlRouter = Config::getData('url') . '/ns_tr/';
  $URL_SRC = Config::getData('url') . '/src';
  $URL_VIEW = Config::getData('urlView');
 */

$urlRouter = Config::getData('url') . '/ns_tr/';
$URL = Config::getData('url') . '/library';
$URL_SRC = Config::getData('url') . '/src';
$URL_VIEW = Config::getData('urlView');


$file = Config::getData('pathView') . '/js/js.ini.php';
$version = explode(' ', file_get_contents(Config::getData('path') . '/version'))[0];

//echo Router::rotaJS('view/js/fn.js.php');
$var = "var js = [
    '$URL_VIEW/bower_components/jquery/dist/jquery.min.js', 
    '$URL_VIEW/bower_components/angular/angular.min.js',
    '$URL_VIEW/bower_components/popper.js/dist/popper.js',
    '$URL_VIEW/bower_components/angular-file-upload-FULL_3/dist/angular-file-upload.min.js', 
    '$URL_VIEW/bower_components/bootstrap/dist/js/bootstrap.min.js',
    '$URL_VIEW/bower_components/angular-bootstrap/ui-bootstrap.min.js', 
    '$URL_VIEW/bower_components/angular-bootstrap/ui-bootstrap-tpls.min.js',
    '$URL_VIEW/bower_components/angular-ui-mask/dist/mask.min.js', 
    '$URL_VIEW/bower_components/jquery-ui/jquery-ui.min.js',
    '$URL_VIEW/bower_components/jquery-mask-plugin/dist/jquery.mask.min.js',
    '$URL_VIEW/bower_components/ngInfiniteScroll/build/ng-infinite-scroll.min.js',
    '$URL_VIEW/bower_components/angular-sanitize/angular-sanitize.min.js',
    '$URL_VIEW/bower_components/jquery-animateNumber/jquery.animateNumber.min.js',
    //'$URL_VIEW/bower_components/chart.js/dist/Chart.min.js',    
    //'$URL_VIEW/bower_components/orgchart/src/js/jquery.orgchart.js',
    '$URL_VIEW/bower_components/summernote/dist/summernote.js',
    '$URL_VIEW/bower_components/summernote/dist/lang/summernote-pt-BR.js',
    '$URL_VIEW/bower_components/angular-summernote/dist/angular-summernote.js',
        
    '$URL_VIEW/bower_components/daterangepicker-master/moment.min.js',
    '$URL_VIEW/bower_components/daterangepicker-master/daterangepicker.js',       
    '$URL_VIEW/bower_components/angular-moment.min.js',
    '$URL_VIEW/bower_components/swal.js',
        
];

var css = [
    '$URL_VIEW/bower_components/bootstrap/dist/css/bootstrap.min.css',
    '$URL_VIEW/bower_components/jquery-ui/themes/base/jquery-ui.min.css',
    '$URL_VIEW/bower_components/font-awesome/css/font-awesome.min.css',
    '$URL_VIEW/bower_components/smartmenus/dist/addons/bootstrap/jquery.smartmenus.bootstrap.css',
    //'$URL_VIEW/bower_components/orgchart/src/css/jquery.orgchart.css',
    '$URL_VIEW/bower_components/summernote/dist/summernote.css',
    '$URL_VIEW/css/estilo.css',
    '$URL_VIEW/css/theme.css',
    '$URL_VIEW/css/correcoes_tema.css',
    '$URL_VIEW/css/estilo_site.css',
    '$URL_VIEW/css/card.css',
    '$URL_VIEW/css/nav.css',
    '$URL_VIEW/css/loader.css',
    '$URL_VIEW/css/animate.css',        
    //'$URL_VIEW/css/tab.css',        
    '$URL_VIEW/css/ns_timeline.css',  
    '$URL_VIEW/node_modules/@sweetalert2/theme-minimal/minimal.css', 
    '$URL_VIEW/bower_components/daterangepicker-master/daterangepicker.css',
        
];

css.forEach(function (val) {
    document.write(unescape(\"%3Clink href='\"+val+\"?v=" . $version . "' rel='stylesheet'%3E\"));
});
js.forEach(function (val) {
    document.write(unescape(\"%3Cscript src='\" +val + \"?v=" . $version . "' type='text/javascript'%3E%3C/script%3E\"));
});

var ParametersURL = " . json_encode(Config::getData('params')) . ";

";

$template = '<script>';
$packer = new Packer($var, 'Normal', true, false, true);
$template .= $packer->pack();
//$template .= $var;
$template .= '</script>';
$JS_INCLUDE = $template;

$init = [
    'fn.js',
    'app.js',
    'service.js',
    'AppController.js',
    'angular-diretivas.js',
    'angular-locale_pt-br.js'
];
$JS_INCLUDE .= Component::init($init, false);

unset($var);
unset($template);
unset($packer);
