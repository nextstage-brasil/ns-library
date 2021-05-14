<?php

// Configurações especificas para host habilitados para uso
$config = hash('md5', $_SERVER['HTTP_HOST']);
$bucketName = 'nslogos154890'; // nome da pasta ou diretorio principal que ira comportar os arquivos
$local = false;
$sendMail = [
    'host' => 'mail.usenextstep.com.br',
    'username' => 'contato@usenextstep.com.br',
    'usertxt' => 'Nextstep',
    'password' => 'Data@3280',
    'port' => 465,
    'smtpSecure' => 'ssl',
    'SMTPAuth' => true
];

switch ($config) {
    case 'd41d8cd98f00b204e9800998ecf8427e': // localhost
    case '421aa90e079fa326b6494f812ad13e79':
        $database = [
            'host' => 'localhost',
            'user' => 'postgres',
            'pwd' => '102030',
            'database' => 'logos',
            'port' => 6432,
            'schema' => 'public'
        ];
        $url = 'https://localhost/logos_server';
        $local = true;
        break;
    case '3693ae47f86111ead7f05f2096923837': // logos.usenextstep.com.br
        // FTP: GpWUwKdlf6FwW1DR9
        $database = [
            'host' => 'db11.usenextstep.com.br',
            'user' => 'nextstage',
            'pwd' => 'bl0nuIFvHj00ET49vH76nziDTGuKMSZrQl1eo7dus1p1bM5DFz',
            'database' => 'logos',
            'port' => 6432,
            'schema' => 'public'
        ];
        $url = 'https://logos.usenextstep.com.br';
        break;
    case 'producao': // producao

        break;

    default: // não configurado neste servidor
        $database = [];
}



$SistemaConfig = [
    'identificador' => 'logos_13-01-202-ef90cfaa7efb6f2e5dbd2469ba5200d5cf4357d09a60a6fe5ae04a34ae75f1c7', //ATENÇÃO: NÃO ALTERAR APÓS INSTALAÇÃO, POIS OS ARQUIVO SERÃO CRIPTOGRAFADOS UTILIZADO ESTE CAMPO
    'name' => 'Cloudeduc Plataforma de Gestão de Ensino',
    'title' => 'Cloudeduc Plataforma de Gestão de Ensino', // <title> exibido no HTML
    'admin' => 'nextstage', // Nome do admin
    'emailAdmin' => 'ns@nextstage.com.br', // email do admin
    'timeShowError' => 30, // tempo de exibição de erro na tela da view, antes de mudar a pagina
    'keyGoogle' => '', // chave do google para obtenção de maps, se necessário
    'validaIdEmpresa' => false,
    'database' => ['type' => 'postgres'],
    'sendMail' => $sendMail,
    'components' => [], // componentes extras por rota a carregar. Nome absoluto, conforme pasta
    'onlyDev' => [''], // rotas de acesso exclusivo para login developer
    'onlyAdm' => [], // rotas de acesso exclusivo para login adminsitrativo
    'filesPrefix' => '', // utilizado pelo storage para prefixar uma pasta por empresa/cliente
    'pagseguro' => [
        'email' => 'cristofer.batschauer@gmail.com', 
        'token' => '56F4915E83904E8AB42C8F97CDA156B7', 'sandbox' => true,
        'urlRetorno' => $url . '/pagseguro/listen'],
    'sessionTimeout' => 60, // minutos para que a sessão seja encerrada, em caso de inatividade 
];

/* ESSAS SÃO AS CONFIGURAÇÕES A SEREM DEFINIDAS - ATÉ AQUI */




