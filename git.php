<?php

require 'vendor/autoload.php';

if (strlen((string)$argv[1]) === 0) {
    die("Mensagem do commit é requerido");
}
$dir = __DIR__;
var_export($dir);

NsUtil\Package::git(__DIR__ . '/version', $argv[1]);

shell_exec("
cd $dir;
git push --tags;
git push
");
