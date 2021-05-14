<?php

/**
 * Aliases para nome dos campos.
 * 
 * A formação do nome dos campos, se dará pela modelagem, seguindo o padrão no campo comentários: LabelParaCampo|Hint.
 * Por exemplo, para o campo nm_p podemos utilizar o comentário Nome da pessoa|Descreva o nome completo da pessoa em questão
 * Utilizar sempre lowercase nas chaves a serem editadas, mas precisa atualizar a modelagem para manter o padrão
 */
$aliases_field = [];

$filename = $SistemaConfig['path'] . '/auto/config/aliases_fields.php';
if (file_exists($filename)) {
    include_once $filename;
}


$aliases_field = array_merge($aliases_field, [
    'professor' => 'Professor',
    'idmodulo' => 'Módulo'
        ]);
