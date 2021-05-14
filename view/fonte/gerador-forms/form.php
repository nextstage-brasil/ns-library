<?php

// Automação de Criação de Sistema - 13/01/2020 07:34:45
if (!defined("SISTEMA_LIBRARY")) {
    die("Acesso direto não permitido");
}

UsuarioController::loginBryan();


$form = filter_input('GET', 'f', FILTER_SANITIZE_STRING);
$crypto = new \NsUtil\Crypto(Config::getData('identificador'));

$file = $crypto->decrypt(base64_decode(Config::getData('params')[1]));
if (file_exists(__DIR__ . "/$file/form.php")) {
    include __DIR__ . "/$file/form.php";
} else {
    die('Formulário solicitado não localizado ' . $file);
}

// Saida padrão para todos os FORMs
$config = array_merge([
    'TITLE' => 'Formulário não definido',
    'CONTENT' => 'Formulário não definido',
    'URL' => '',
    'URL_API' => 'Formulário não definido',
    'JS' => 'Formulário não definido',
    'TEMA' => '',
    'FLUID' => '', //-FLUID'
        ], $FormConfig);
$template = new Template(Config::getData('path') . '/public/_x0023_html/_template.html', $config);

echo $template->render();
