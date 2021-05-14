<?php

ini_set('display_errors', 1);
error_reporting(E_ERROR);

$pathAplicacao = str_replace('\_build\install', '', __DIR__);
$pathIoncubeProject = __DIR__ . '\ioncube\ioncube-post.bat';
$pathOutput = 'C:\app_encoded';

require $pathAplicacao . '/vendor/autoload.php';

// pastas e diretorios especificos desta aplicacao, contando sempre  da raiz, nao recursivos
$excluded = [
    'sch.php',
    'ingest/',
    'storage/',
    'app/',
    'test/',
    'ns-st',
    'ns-app'
];

// Criar o package
shell_exec('cls');
NsUtil\Package::run($pathAplicacao, $excluded, $pathOutput, $pathIoncubeProject);

/*
  // Acionar ioncube
  echo "\nCodificado arquivos PHP";
  $cmd = 'call ' . __DIR__ . '\ioncube\encoder-to-production.bat';
  shell_exec($cmd);

  // Gerando build
  echo "\nGerando build";
  $cmd = 'call ' . $pathIoncubeProject.' > nul';
  shell_exec($cmd);
 */

// Gerar deploy files
echo "\nGerando deploy files";
$deploy = file_get_contents(__DIR__ . '/@deploy-default.sh');
$configs = [
    'aws' => ['dirHome' => '/home/logos', 'usuario' => 'logos'],
];
foreach ($configs as $key => $val) {
    file_put_contents(__DIR__ . '/deploy-' . $key . '.sh', str_replace(array_keys($val), array_values($val), $deploy));
}



echo "\n\nCompletado. Clique para encerrar.";

