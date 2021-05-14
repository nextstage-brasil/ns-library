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

    $instantBuyKey = "04728dc3-5e0c-47c2-b6b7-9b5c421add09";
    
    $updateBuyer = new Gateway\One\DataContract\Request\UpdateInstantBuyDataRequest(); 
    
    $updateBuyer->setBuyerKey("247210dc-e02e-4c2c-9120-652e18aa8de8");

    // Faz a chamada para criação
    $response = $client->updateInstantBuyData($instantBuyKey, $updateBuyer);

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
