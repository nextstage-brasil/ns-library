<?php

require_once(dirname(__FILE__) . '/../init.php');

try
{
    // Define a url utilizada
    \Gateway\ApiClient::setBaseUrl("https://transaction.stone.com.br");

    // Define a chave da loja
    \Gateway\ApiClient::setMerchantKey("merchant key");

    //Cria um objeto ApiClient
    $client = new Gateway\ApiClient();

    $instantBuyKey = "139d4c3b-3215-4c1b-93eb-5b89e544336d";

    // Faz a chamada para criação
    $response = $client->GetInstantBuyDataByInstantBuyKey($instantBuyKey);

    // Imprime responsta
    print "<pre>";
    print json_encode(array('success' => $response->isSuccess(), 'data' => $response->getData()), JSON_PRETTY_PRINT);
    print "</pre>";
}
catch (\Gateway\One\DataContract\Report\ApiError $error)
{
    // Imprime json
    print "<pre>";
    print json_encode($error, JSON_PRETTY_PRINT);
    print "</pre>";
}
catch (Exception $ex)
{
    // Imprime json
    print "<pre>";
    print json_encode($ex, JSON_PRETTY_PRINT);
    print "</pre>";
}