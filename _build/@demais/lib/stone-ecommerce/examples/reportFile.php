<?php

require_once(dirname(__FILE__) . '/../init.php');

try
{
    // Define a url utilizada
    \Gateway\ApiClient::setBaseUrl("");

    // Define a chave da loja
    \Gateway\ApiClient::setMerchantKey("merchant key");

    //Cria um objeto ApiClient
    $client = new Gateway\ApiClient();

    // Faz a chamada para criação
    $response = $client->SearchTransactionReportFile('20150928');

    // Imprime resposta
    print "<pre>";
    var_dump($response);
    print "</pre>";
}
catch (\Gateway\One\DataContract\Report\ApiError $error)
{
    // Imprime json
    print "<pre>";
    print ($error);
    print "</pre>";
}
catch (Exception $ex)
{
    // Imprime json
    print "<pre>";
    print ($ex);
    print "</pre>";
}