<?php

// pra ser obrigatorio singleton
if (!defined("SISTEMA_LIBRARY")) {
    die("Acesso direto não permitido");
}
$init = ['alertModal', 'comboSearch', 'configJson', 'linktable',
    'modalData', 'modelo', 'nsAddress', 'nsCardHome', 'nsColabora', 'nsForm',
    'nsInput', 'nsMediaPlayer', 'nsMessenger', 'nsTimeline', 'uploadFile', 'nsTag'];
Component::init($init);

// componentes extras, conforme necessidade da rota
$c = Config::getData('components');
if ($c[$router->entidade]) {
    Component::init($c[$router->entidade]);
}

$js = "HOJE = '".date('d/m/Y')."';";
Component::packAndPrint($js);


$template = new Template(__DIR__ . '/modelos/template-2.html', [
    'url' => Config::getData('url'),
    'keyGoogle' => Config::getData('keyGoogle'),
    'footerLeft' => $time->end()->text,
    'footerCenter' => Config::getData('name'),
    'footerRight' => '<span id="divTempoSessao" style="display:none;"><i class="fa fa-clock-o" aria-hidden="true"></i> <span class="tempoSessao"></span></span>', //<a class="" href="' . Config::getData('url') . '/Versao">Versão</a>',
    'extras' => '',
        ]);
$var = $template->render();
$var = Minify::html($var);
$var = "document.write('$var');";
$pack = new Packer($var);
echo '<script>' . $pack->pack() . '</script>';


// para processar a chamada efetuada em template1 de Buffer Mininy
ob_end_flush();
