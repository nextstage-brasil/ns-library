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

    $buyerKey = "460b3d1d-5c13-4f40-92db-36aa05729c79";

    // Faz a chamada para criação
    $response = $client->getInstantBuyDataByBuyerKey($buyerKey);

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
