<?php

/**
 * Agrupamento de chave de permissao por tipo.
 * Utilizar sempre o nome da entidade em caixa alta apontando para o grupo que irá aprecer nas permissões. 
 * Ex.: CEP => SISTEMA. As permissões para CEP fazem parte do grupo sistema
 */
$permissao_grupos_auto = [];
include_once $SistemaConfig['path'] . '/auto/config/permissao_grupos.php';
$permissao_grupos = array_merge($permissao_grupos_auto, [
    'APILOG' => 'SISTEMA',
    'APP' => 'SISTEMA',
    'AUXILIAR' => 'SISTEMA',
    'AVALIACAO' => 'SECRETARIA',
    'BOLSA' => 'FINANCEIRO',
    'CEP' => 'SISTEMA',
    'CONTA' => 'CONFIGURAÇÕES',
    'CURSO' => 'CONFIGURAÇÕES',
    'EMPRESA' => 'SISTEMA',
    'ENDERECO' => 'SISTEMA',
    'FINANCEIRO' => 'FINANCEIRO',
    'FORMAPGTO' => 'CONFIGURAÇÕES',
    'FORUM' => 'CONFIGURAÇÕES',
    'GRADEALUNO' => 'SECRETARIA',
    'GRADEPOLO' => 'SECRETARIA',
    'INDISP' => 'SECRETARIA',
    'LECIONA' => 'SISTEMA',
    'LINKTABLE' => 'SISTEMA',
    'LOGINATTEMPTS' => 'SISTEMA',
    'LTREL' => 'SISTEMA',
    'MATRICULA' => 'SECRETARIA',
    'MENSAGEMGRUPO' => 'SISTEMA',
    'MENSAGEMGRUPOUSERS' => 'SISTEMA',
    'MODULO' => 'CONFIGURAÇÕES',
    'MUNICIPIO' => 'SISTEMA',
    'PAIS' => 'SISTEMA',
    'POLO' => 'CONFIGURAÇÕES',
    'POST' => 'SISTEMA',
    'POSTREAD' => 'SISTEMA',
    'REEMBOLSO' => 'CONFIGURAÇÕES',
    'REEMBOLSOMOTIVO' => 'CONFIGURAÇÕES',
    'SHARED' => 'SISTEMA',
    'SHAREDUSER' => 'SISTEMA',
    'SISTEMAFUNCAO' => 'SISTEMA',
    'SISTEMALOG' => 'SISTEMA',
    'SITE' => 'SISTEMA',
    'SOLICITACAOCOMPRA' => 'FINANCEIRO',
    'UF' => 'SISTEMA',
    'USUARIO' => 'CONFIGURAÇÕES',
    'XGRADE' => 'SISTEMA'
        ], [
    'LOGOS' => 'SISTEMA'
        ]);
