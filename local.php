<?php

require __DIR__ . './vendor/autoload.php';

// Raiz default: no mesmo ROOT desta aplicação
$pathDefault = realpath(__DIR__ . '/../');
$pathWSL = realpath('Z:\home\cristofer\apps');
$LOCAL_PROJECTS = [
    $pathWSL . '/nextstage/trilhasbr-backend',
    // $pathWSL . '/5labs/cnpj',
    // $pathDefault . '/syncpay-backend-v2',
    // $pathWSL . '/nextstage/gitlab-integration'
];
$src = realpath(__DIR__) . DIRECTORY_SEPARATOR . 'src';
$vendor = 'nextstage-brasil/ns-library';
(new \NsUtil\LocalComposer())($src, $LOCAL_PROJECTS, $vendor);

