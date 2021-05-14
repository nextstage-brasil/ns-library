<?php

if (!defined("SISTEMA_LIBRARY")) {
    die("Acesso direto não permitido");
}
$time = new Eficiencia();

// verificar se deve exibir nesta url
require_once Config::getData('pathView') . '/template/includes.php';
/*
  $menuUser = [
  ['href' => 'meusdados', 'label' => 'Meus dados'],
  ['href' => 'meusite', 'label' => 'Meu site'],
  ['href' => '#', 'label' => 'Comunicador <span class="badge badge-info badge-comunicador"></span>', 'class' => 'btnComunicadorShow', 'icon' => 'comment']
  ];
 */

$js = '<script>var ParametersURL = ' . json_encode(Config::getData('params')) . ';</script>';
$template = new Template(__DIR__ . '/modelos/template-1.html', [
    'fluid' => $FLUID ? '-fluid' : '',
    'title' => Config::getData('title'),
    'js' => $JS_INCLUDE
    . '<script>'
    . 'var ParametersURL = ' . json_encode(Config::getData('params')) . ';'
    . 'var ROTA = \'' . Config::getData('rota') . '\';'
    . '</script>'
    . Config::getData('js_config')
    ,
    'url' => Config::getData('url'),
    'nav' => Nav::get($MENUFILE, $menuUser),
    'icon' => Config::getData('urlView') . '/images/logo.png',
    'devMode' => AppLibraryController::notificationHtml() . constant('DEVMODE'),
        ]);


echo $template->render();






