<?php

require_once ('../library/SistemaLibrary.php');

// teste de configurações
$cfg = new \League\Flysystem\Config(NsStorageLibrary\Config::init());
echo 'URL: ' . $cfg->get('url') . '<br/>';
echo 'Storage private: ' . $cfg->get('StoragePrivate') . '<br/>';

$st = new \NsStorageLibrary\Storage\Storage('Local');
$file = __FILE__;
$ret = $st->loadFile($file)->setPath('nsStorageLocalTest.php')->upload();


if ($ret) {
    echo "Envio de arquivo para Storage retornou TRUE<br/>";
} else {
    echo "Retorno FALSE para envio do arquivo em storage<br/>";
}
if ($st->has('nsStorageLocalTest.php'))   {
    echo "Arquivo encontrado no storage<br/>";
} else {
    echo "Arquivo NÃO ENCONTRADO no storage<br/>";
}


