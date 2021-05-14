<?php

require_once './library/SistemaLibrary.php';

$t = json_decode(Helper::decodifica($router->allParam[2]));

$validadeDoLink = 15; // segundos
if (Helper::dateToMktime() > $t->dt + $validadeDoLink) {
    echo '<h1 class="text-center">Conteúdo não disponível</h1>';
    exit;
}

$link = str_replace('/', DIRECTORY_SEPARATOR, Config::getData('path') . DIRECTORY_SEPARATOR . $t->lk);
//echo $link."\n";
if (file_exists($link)) {   
    define('includeJS', true);
    include $link;
    exit;
} else {
    echo '<h1 class="text-center">Conteúdo não localizado' . $link . '</h1>';
    exit;
}
