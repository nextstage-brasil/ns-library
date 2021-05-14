<?php

//include __DIR__ . '/console_clear.php';
include 'd:\Dropbox\ads\webs\_package\package_excluded_default.php';

// project name
$t = explode(DIRECTORY_SEPARATOR, __DIR__);
array_pop($t);
$origem = implode(DIRECTORY_SEPARATOR, $t);

// pastas e diretorios especificos desta aplicacao, contando sempre  da raiz, nao recursivos
$excluded = [
    'ns-st/',
    'ns-app/',
];
zip($origem, $excluded);
