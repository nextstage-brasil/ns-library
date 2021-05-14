<?php
require_once ('../library/SistemaLibrary.php');
$ret = SistemaLibrary::createTriggerAuditoria();
Log::ver($ret);


$template = new Template(Config::getData('pathView') . '/template/000-template.html', [
    'TITLE' => 'NS Compilator', 'CONTENT' => '<h1 class="text-center">Compilação para produção</h1>'
    . '<a class="btn btn-info" onclick="javascript:history.back()">Voltar</a>'
    . '<ul>'
    . implode(' ', $out)
    . '</ul>'
    . '<script>alert(\''.count($ret).' Triggers Criados! Auditoria ativada\');history.back();</script>'
        ]);

echo $template->render();