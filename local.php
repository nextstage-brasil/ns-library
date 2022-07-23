<?php

require __DIR__ . './vendor/autoload.php';

// Raiz default: no mesmo ROOT desta aplicação
$pathDefault = realpath(__DIR__ . '/../');
$LOCAL_PROJECTS = [
    $pathDefault . '/trilhasbr-backend',
    $pathDefault . '/syncpay-backend-v2',
];
$src = realpath(__DIR__) . DIRECTORY_SEPARATOR . 'src';
$vendor = 'nextstage-brasil/ns-library';
(new \NsUtil\LocalComposer())($src, $LOCAL_PROJECTS, $vendor);

