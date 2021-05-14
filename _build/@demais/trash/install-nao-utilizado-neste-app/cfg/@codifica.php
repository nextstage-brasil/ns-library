<?php
$t = explode(DIRECTORY_SEPARATOR, __DIR__);
array_pop($t);
array_pop($t);
array_pop($t);
$raiz = implode(DIRECTORY_SEPARATOR, $t);

$licenca_name = array_pop($t);
require $raiz . '\vendor\autoload.php';

$chave = file_get_contents($raiz .  '/_build/CHAVE_CRYPTO');
$lic = new \NsUtil\Licence($chave);

$path = __DIR__;
$diretorio = dir($path);
echo "Codificar licenças no diretório : $path" . PHP_EOL;
while ($arquivo = $diretorio->read()) {
    if (stripos($arquivo, '.ini') > 0) {
        echo "Licenca: " . $arquivo;
        $filenameOrigem = $path . DIRECTORY_SEPARATOR . $arquivo;
        $filenameDestino = str_replace('cfg', 'licence', __DIR__) . DIRECTORY_SEPARATOR . str_replace('.ini', '', $arquivo) . '_'.$licenca_name.'.lic';

        $ret = $lic->create($filenameOrigem, $filenameDestino);
        if ($ret) {
            echo " salva com sucesso!";
        } else {
            echo " com erro ao salvar";
        }
        echo PHP_EOL;
    }
}
$diretorio->close();
