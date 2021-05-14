<?php
require __DIR__ . '/library/SistemaLibrary.php';
//echo "<h3>Em manutenção até às 16hs</h3>";
//die();

// Configurações de roteamento. Padrão: $route[] = ['prefix' => '/file', 'archive' => 'file.php']; // archive sera retornado em includeFile()
include __DIR__ . '/src/config/router.php';

// Criação do roteador. $route esta em router.php
$router = new Router($route);
Config::setData('rota', str_replace('/', '', $router->getRoute())); // rota atual
Config::setData('entidade', $router->entidade); // rota atual
Config::setData('params', $router->param); // parametros obtidos em url GET
// escolha do menu a exibir
switch ($_SESSION['user']['idUsuario']) {
    default:
        $MENUFILE = 'nav_default'; // Menu que será utilizado nessa exibição. Arquivo deve estar em sistema/config/nav
}

//@rever Validar a necessidade disso Log::navegacao(str_replace('/', '', $router->getRoute()));
// Validando se o login esta efetuado
$rotasSemLoginNecessário = ['login', 'logout', 'owner', 'recovery', 'devices', 'permisso', 'odkb0', 'odk', 'omb'];
if (!Config::getData('dev') || !Helper::compareString((string) $router->param[0], '-999')) { // nao eh o compilador, deve validar session
    if (!$_SESSION['user'] && array_search(mb_substr(mb_strtolower($router->getRoute()), 1, 200), array_map('mb_strtolower', $rotasSemLoginNecessário)) < 0) {
        header("Location:" . Config::getData('url') . "/logout");
        die();
    }
}

// ambiente de desenvolvimento
if (Config::getData('dev')) {
    $toCompile = Config::getData('url') . '/_build/compile.php';
    NsUtil\Helper::myFileGetContents($toCompile);
}

// Localizar o arquivo localizado pela rota
$locais = [
    Config::getData('path') . '/view' . strtolower($router->getRoute()) . '.php',
    Config::getData('path') . '/view/' . $router->getIncludeFile(),
    Config::getData('path') . '/view/fonte/' . $router->getIncludeFile(),
    Config::getData('pathView') . '/error404.php'
];
foreach ($locais as $local) {
    if (!is_dir($local) && file_exists($local)) {
        require_once "{$local}";
        die();
    }
}

// Caso não encontre nada, nem o 404 (Não deve chegar aqui)
die('Navegação não localizada');
