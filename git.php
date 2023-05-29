<?php

use NsUtil\Helper;

require 'vendor/autoload.php';

if (strlen((string)$argv[1]) === 0) {
    die("Mensagem do commit é requerido");
}
$dir = Helper::getPathApp();

NsUtil\Package::git(__DIR__ . '/version', $argv[1]);

echo shell_exec("cd $dir;git push --tags;git push");
