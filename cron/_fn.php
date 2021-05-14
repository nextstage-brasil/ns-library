<?php

//error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_WARNING);
//ini_set('display_errors', 0);


$ret = trim(shell_exec('hostname'));

//define('FROMCRON', true);

switch ($ret) {
    case 'NotePC-Dell': // Localhost
        $_SERVER['HTTP_HOST'] = 'localhost';
        break;
    case 'nextstage-aws-1':
        $_SERVER['HTTP_HOST'] = 'logos.usenextstep.com.br';
        break;
    default:
        echo "$ret" . "\n";
        die('CRON: Não pude localizar as configurações desta aplicação: ' . $ret . PHP_EOL);
}
$library = str_replace('cron', 'library', __DIR__);
require_once $library . DIRECTORY_SEPARATOR . 'SistemaLibrary.php';
$_SESSION['user']['nome'] = 'Arthur Robot';
define('FROMCRON', true);
//echo "Crontab" . PHP_EOL;
//echo "Conectado em " . $_SERVER['HTTP_HOST'] . PHP_EOL;


    