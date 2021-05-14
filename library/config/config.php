<?php

if (!defined('SISTEMA_LIBRARY')) {
    die('Acesso direto não permitido');
}
/* * **
 * ATENÇÃO - AS CONFIGURAÇÕES ESTÃO NO ARQUIVO src/config/cfg.php. ESTE ARQUIVO NÃO DEVE SER ALTERADO
 */
$pathLocal = str_replace(DIRECTORY_SEPARATOR . 'library' . DIRECTORY_SEPARATOR . 'config', '', __DIR__);

include $pathLocal . '/src/config/cfg.php';

// local esta definido no cfg do cliente
//$local = ( (($_SERVER["REMOTE_ADDR"] == "127.0.0.1") || ($_SERVER["REMOTE_ADDR"] == "::1")) ? true : false);
if ($local) {
    ini_set("display_errors", true);
    error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
} else {
    error_reporting(0);
}

// Não alterar 
if ($database['host']) {
    define('CONFIG', TRUE); // constante que garante acesso as classes unicamente após este script
}
define('PATH', $pathLocal);

$bucketNamePublic = $bucketName . 'p';

$SistemaConfig['token'] = md5($SistemaConfig['identificador']);

$SistemaConfig['database'] += $database;
$SistemaConfig['url'] = $url;


// Não alterar aqui pra baixo
$SistemaConfig['permissao'] = [];
$SistemaConfig['path'] = Config::getPath();
$SistemaConfig['pathRoot'] = str_replace('library', '', SistemaLibrary::getPath());
$SistemaConfig['urlView'] = $SistemaConfig['url'] . '/view';
$SistemaConfig['pathView'] = $SistemaConfig['path'] . '/view';
$SistemaConfig['pathViewUser'] = $SistemaConfig['path'] . '/view/fonte';
$SistemaConfig['dirComponents'] = $SistemaConfig['path'] . '/auto/components'; // baseado da raiz, prefixo da pasta que conterá os components gerados
$SistemaConfig['urlComponents'] = $SistemaConfig['url'] . '/auto/components'; // baseado da raiz, prefixo da pasta que conterá os components gerados
$SistemaConfig['pathTemplates'] = $SistemaConfig['pathView'] . DIRECTORY_SEPARATOR . 'template' . DIRECTORY_SEPARATOR . 'modelos';
$SistemaConfig['linkAlteraSenha'] = $SistemaConfig['url'] . '/recovery/0';
$SistemaConfig['log'] = [
    'active' => true,
    'fileLocation' => $SistemaConfig['path'] . '/ns-app/45h'
];
$SistemaConfig['fileModelJson'] = $SistemaConfig['path'] . '/src/config/model_json.php';
$SistemaConfig['errors'] = [
    'Undefined column' => 'Erro no sistema. (Cód Erro: ABS1001)',
    '42703' => 'Erro no sistema (DB42703)', // undefined column
    '23505' => 'Já existe registro com esses dados', // unicidade
    '23502' => 'Campo obrigatório não informado'
];
$SistemaConfig['dev'] = $local;
$SistemaConfig['ip'] = getenv("REMOTE_ADDR");
$SistemaConfig['rota'] = ''; // será setado dinamicamente durante a navegação
$SistemaConfig['params'] = []; // sera criado dinamicamente com os parametros de URL
// Path que armazenara os arquivos upados antes do tratamento
$SistemaConfig['pathUploadFile'] = $SistemaConfig['pathView'] . DIRECTORY_SEPARATOR . 'uploadFiles';
$SistemaConfig['translatePath'] = $SistemaConfig['path'] . '/src/config/lang';

// path-url que armazenara os arquivos de uploafile após tratamento. Será utilizado padrão YYYY/MM/filename.ext
$SistemaConfig['urlFiles'] = $SistemaConfig['urlView'] . '/files';
$SistemaConfig['pathFiles'] = $SistemaConfig['pathView'] . DIRECTORY_SEPARATOR . 'files';
$SistemaConfig['filesPrefix'] = date('Y') . '/' . date('m');

// biblioteca de substituição de titulos de páginas
$filename = $pathLocal . '/src/config/aliases_tables.php';
if (file_exists($filename)) {
    include_once($filename);
} else {
    echo '<p class="alert alert-danger text-center mb-5">Necessário rodar a instalação do sistema, pois identificador de nome de entidades não foi localizado</p>';
}
$SistemaConfig['titlePagesAliases'] = $aliases_table;

// biblioteca de entidades
$filename = $pathLocal . '/src/config/library_entities.php';
if (file_exists($filename)) {
    include_once($filename);
} else {
    echo 'file not found: ' . $filename;
    echo '<p class="alert alert-danger text-center mb-5">Necessário rodar a instalação do sistema, pois identificador de nome de campos não foi localizado</p>';
}
$SistemaConfig['entidadeName'] = $libraryEntities;


// biblioteca de substituição de nome de campos
$filename = $pathLocal . '/src/config/aliases_fields.php';
if (file_exists($filename)) {
    include_once($filename);
} else {
    echo 'file not found: ' . $filename;
    echo '<p class="alert alert-danger text-center mb-5">Necessário rodar a instalação do sistema, pois identificador de nome de campos não foi localizado</p>';
}
$SistemaConfig['aliasesField'] = $aliases_field;

// biblioteca de hints
$filename = $pathLocal . '/auto/config/hints.php';
if (file_exists($filename)) {
    include_once($filename);
} else {
    $hints = [];
    //echo '<p class="alert alert-danger text-center mb-5">Necessário rodar a instalação do sistema, pois identificador de nome de campos (hints) não foi localizado</p>';
}
$SistemaConfig['hints'] = $hints;





$text = "
var t = window.location.href;
var appConfig = {
    urlCloud: '" . $SistemaConfig['url'] . "/',
    dev: " . (($local) ? 'true' : 'false') . ",
    timeExibeError: 30
};
appConfig.rest = appConfig.urlCloud + 'api';
";
$SistemaConfig['js_config'] = '<script>' . (new Packer(Minify::js($text)))->pack() . '</script>';

