<?php

use NsStorageLibrary\Storage\Storage;

// Aquivo utilizado para leitura de arquivos. Faz um header para storage link public
//require_once './library/SistemaLibrary.php';


$t = json_decode(Helper::decodifica($router->param[1]));
$validadeDoLink = 15 * 60; // segundos

if (Helper::dateToMktime() > $t->datetime + $validadeDoLink) {
    die('Link expirado');
}

$storage = new Storage();
$f = $storage->setPath($t->filename)->download();


//$file = $storage->getWebLink($t->filename);
header("Location:$f");
exit;
