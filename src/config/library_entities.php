<?php

/**
 * Reversão do nome das tabelas para entidades.
 * 
 * Ex.: a tabela cad_user, espera uma chave caduser e gerará a saida CadUser que é o padrão utilizado (camelcase).
 * $lib['caduser'] = 'CadUser';
 * Utilizar sempre lowercase nas chaves a serem editadas, mas precisa atualizar a modelagem para manter o padrão
 */
$libraryEntities = [];

$filename = $SistemaConfig['path'] . '/auto/config/library_entities.php';
if (file_exists($filename)) {
    include_once $filename;
}

$libraryEntities = array_merge($libraryEntities, [
        //'login' => 'Login',
        ]);

