<?php

require_once ('../library/SistemaLibrary.php');
$dirCompile = '_build/_sourceView';
if ($_GET['recompile'] !== 'ALL') {
    $lastCompile = file_get_contents(__DIR__ . '/_lastCompile.ini');
}
if (!Config::getData('dev')) {
    die('Não permitido fora do ambiente de desenvolvimento');
}

// JS do Framework
$diretorios = [__DIR__ . '/js_framework', __DIR__ . '/js_app', __DIR__ . '/js_app/componentes', __DIR__ . '/components'];
foreach ($diretorios as $dir) {
    $list = open_dir($dir, true);
    foreach ($list as $file) {
        $tocompile = realpath($dir . DIRECTORY_SEPARATOR . $file);
        if (!is_file($tocompile)) { // components
            $files = [$tocompile . DIRECTORY_SEPARATOR . $file . ".js", $tocompile . DIRECTORY_SEPARATOR . $file . ".html"];
            foreach ($files as $tocompile) {
                $tocompile = realpath($tocompile);
                if (filemtime($tocompile) > $lastCompile && file_exists($tocompile) && !is_dir($tocompile)) {
                    Component::saveComponent($tocompile);
                    echo "To compile - component: $tocompile<br/>";
                    break;
                }
            }
        } else { // arquivos
            if (filemtime($tocompile) > $lastCompile && file_exists($tocompile) && !is_dir($tocompile)) {
                Component::compileAndSaveJS($tocompile);
                echo "To compile: $tocompile<br/>";
            }
        }
    }
}
$template = new Template(Config::getData('pathView') . '/template/000-template.html', [
    'TITLE' => 'NS Compilator', 'CONTENT' => '<h1 class="text-center">Compilação para produção</h1>'
    . '<a class="btn btn-info" onclick="javascript:history.back()">Voltar</a>'
    . '<ul>'
    . implode(' ', $out)
    . '</ul>'
    . '<script>alert(\'Processo Concluído com Sucesso! \n' . count($out) . ' entidades atendidas\');history.back();</script>'
        ]);

function open_dir($dir) {
    $out = [];
    if ($handle = opendir($dir)) {
        while (false !== ($file = readdir($handle))) {
            if ($file != "." && $file != ".." && stripos($file, '__NEW__') === false) {
                $out[] = $file;
            }
        }
        closedir($handle);
    }
    return $out;
}

Helper::saveFile(__DIR__ . '/_lastCompile.ini', false, Helper::dateToMktime(), 'SOBREPOR');

echo $template->render();

