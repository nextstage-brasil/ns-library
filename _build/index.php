<?php

mb_internal_encoding('UTF-8');
$dirApps = str_replace('library' . DIRECTORY_SEPARATOR . '_build', '', __DIR__);
$root = str_replace('_build', '', __DIR__);

/*
if (!file_exists($root . '/src/config/config.php')) {
    @mkdir($root . '/src');
    die('<h1>Copie a estrutura em /_build/install para raiz do projeto</h1>');
}
*/

require '../library/SistemaLibrary.php';





$filename = Config::getData('path') . '/src/config/aliases_tables.php'; // validação  se o BUILD ja rodou
if (file_exists($filename)) {
    $extras = ''
            . '<div class="col-4"><a class="col btn btn-secondary mb-3 p-4" href="compile.php" onclick="">Compile</a></div>'
            . '<div class="col-4"><a class="col btn btn-secondary mb-3 p-4" href="compile.php?recompile=ALL" onclick="">RE-Compile ALL</a></div>'
            . '<div class="col-4"><a class="col btn btn-secondary mb-3 p-4" href="translate.php" onclick="">Translate</a></div>'
            . '<div class="col-4"><a class="col btn btn-secondary mb-3 p-4" target="_blank" href="http://bootstrapdesigntools.com/tools/bootstrap-menu-builder/">CSS Menu</a></div>'
            . '<div class="col-4"><a class="col btn btn-secondary mb-3 p-4" href="user_createdefault.php" onclick="">Default User Create</a></div>'
            . '<div class="col-4"><a class="col btn btn-secondary mb-3 p-4" href="triggerCreate.php" onclick="">Create Auditoria</a></div>'
            . '<div class="col-4"><a class="col btn btn-secondary mb-3 p-4" href="storage_teste.php" onclick="">Storage Teste</a></div>'
            . '<div class="col-12 mt-5 text-center"><a class="col btn btn-success" href="../logout" target="_blank">Go to App!</a></div>';
}

$html = '<h1 class="text-center">NS Framework - Builder</h1>';

$btns = '<div class="row">'
        . '<div class="col-4"><a class="col btn btn-secondary mb-3 p-4" href="builder.php" onclick="">Build</a></div>'
        . $extras
        . '</div>';

$html .= '<div class="row">'
        . '<div class="col-8">'.$btns.'</div>'
        . '<div class="col-4">FORM</div>'
        . '</div>';

$template = new Template('../view/template/000-template.html', ['TITLE' => 'NS FrameWork Install', 'CONTENT' => $html]);
echo $template->render();
