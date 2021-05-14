<?php

/**
 * Configuração de rotas
 * 
 * O builder irá gerar o arquivo de rotas automáticas para cada entidade do sistema. 
 * Caso necessário criar rotas extras, basta adicionar ao arquivo abaixo.
 */
if (!defined("SISTEMA_LIBRARY")) {
    die("Acesso direto não permitido");
}
$route = [];
include_once Config::getData('path') . '/auto/config/router_default.php'; // Esta variavel esta criada em src/config/Config.php
//
//
// Rotas defindas para painel atual
$route[0] = ['prefix' => '/', 'archive' => 'home.php'];
$route[1] = ['prefix' => '/home', 'archive' => 'home.php'];


// Rotas manuais
$route = array_merge($route, [
    // Rotas sistemicas
    ['prefix' => '/nav_cs', 'archive' => 'cs.php'],
    ['prefix' => '/nav_user', 'archive' => 'user.php'],
    ['prefix' => '/nav_df', 'archive' => 'df.php'],
    ['prefix' => '/ns_tr', 'archive' => 'ns_tr.php'],
    ['prefix' => '/fr', 'archive' => 'file.php'],
    ['prefix' => '/od1', 'archive' => 'od1.php'],
    ['prefix' => '/adminOnly', 'archive' => 'adminOnly.php'],
    // Rotas manuais
    ['prefix' => '/gdf', 'archive' => 'gradeFull.php'],
    ['prefix' => '/checkout', 'archive' => 'site/checkout.php'],
    ['prefix' => '/turmas', 'archive' => 'professor/turmas.php'],
    ['prefix' => '/form-inscricao', 'archive' => 'gerador-forms/form.php'],
    ['prefix' => '/form-getlink', 'archive' => 'gerador-forms/getlink.php'],
    ['prefix' => '/inscricao', 'archive' => 'inscricao.php']
        ]);

