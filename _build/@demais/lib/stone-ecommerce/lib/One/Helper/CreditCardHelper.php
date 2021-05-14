<?php

namespace Gateway\One\Helper;

use Gateway\One\DataContract\Enum\CreditCardBrandEnum;
use Gateway\One\DataContract\Report\CreditCardError;
use Gateway\One\DataContract\Request\CreateSaleRequestData\CreditCard;

/**
 * Class CreditCardHelper
 * @package Gateway\One\Helper
 */
abstract class CreditCardHelper
{
    /**
     * Obtém a bandeira do cartão a partir do número
     * @param string $number Número do cartão
     * @return string Bandeira do cartão
     */
    public static function getBrandByNumber($number)
    {
        // Extrai somente números do cartão
        $number = preg_replace("/[^0-9a-zA-Z ]/", "", $number);

        if (in_array(substr($number, 0, 6), array('401178', '504175', '509002', '509003', '438935', '457631', '451416', '457632', '506726', '506727', '506739', '506741', '506742', '506744', '506747', '506748', '506778', '636297', '636368', '637095')))
        {
            return CreditCardBrandEnum::ELO;
        }
        elseif (substr($number, 0, 4) == '6011' || substr($number, 0, 3) == '622' || in_array(substr($number, 0, 2), array('64', '65')))
        {
            return CreditCardBrandEnum::DISCOVER;
        }
        elseif (in_array(substr($number, 0, 3), array('301', '305')) || in_array(substr($number, 0, 2), array('36', '38')))
        {
            return CreditCardBrandEnum::DINERS;
        }
        elseif (in_array(substr($number, 0, 2), array('34', '37')))
        {
            return CreditCardBrandEnum::AMEX;
        }
        elseif (substr($number, 0, 2) == '50')
        {
            return CreditCardBrandEnum::AURA;
        }
        elseif (in_array(substr($number, 0, 2), array('38', '60')))
        {
            return CreditCardBrandEnum::HIPERCARD;
        }
        elseif ($number[0] == '4')
        {
            return CreditCardBrandEnum::VISA;
        }
        elseif ($number[0] == '5')
        {
            return CreditCardBrandEnum::MASTERCARD;
        }
        else
        {
            return null;
        }
    }

    /**
     * @param $number
     * @param $name
     * @param $expiry
     * @param $cvc
     * @return CreditCard
     * @throws CreditCardError
     */
    public static function createCreditCard($number, $name, $expiry, $cvc)
    {
        if (empty($number)) {
            throw new CreditCardError("Invalid credit card number.", "number");
        }

        if (empty($name)) {
            throw new CreditCardError("Invalid credit card holder name.", "holderName");
        }

        if (empty($cvc)) {
            throw new CreditCardError("Invalid credit card security code.", "securityCode");
        }

        // Verifica se foi enviado uma barra
        if (empty($expiry) || stristr($expiry, '/') === false) {
            throw new CreditCardError("Invalid credit card expiration date.", "expirationDate");
        }

        // Separa mes e ano da data de validade do cartão
        $expiryParts = explode('/', trim($expiry));
        $expMonth = @trim($expiryParts[0]);
        $expYear = @trim($expiryParts[1]);

        // Verifica se o mês é válido
        if ($expMonth < 1 || $expMonth > 12) {
            throw new CreditCardError("Invalid credit card expiration month.", "expirationMonth");
        }

        // Verifica se o ano é válido
        if (!in_array(strlen($expYear), array(2, 4))) {
            throw new CreditCardError("Invalid credit card expiration year.", "expirationYear");
        }

        // Extrai somente números
        $number = str_replace(array('-', '+'), '', filter_var($number, FILTER_SANITIZE_NUMBER_INT));
        $cvc = str_replace(array('-', '+'), '', filter_var($cvc, FILTER_SANITIZE_NUMBER_INT));

        // Valida número do cartão
        if (strlen($number) < 10 || strlen($number) > 24){
            throw new CreditCardError("Invalid credit card number.", "number");
        }

        // Obtém a bandeira do cartão
        $creditCardBrand = self::getBrandByNumber($number);

        // Valida a bandeira
        if ($creditCardBrand == null) {
            throw new CreditCardError("Invalid credit card brand.", "brand");
        }

        // Sanitiza o nome
        $name = filter_var(trim($name), FILTER_SANITIZE_STRING);

        // Cria um objeto de cartão de crédito
        $creditCard = new CreditCard();
        $creditCard->setCreditCardBrand($creditCardBrand);
        $creditCard->setHolderName($name);
        $creditCard->setCreditCardNumber($number);
        $creditCard->setExpMonth($expMonth);
        $creditCard->setExpYear($expYear);
        $creditCard->setSecurityCode($cvc);

        // Devolve o cartão
        return $creditCard;
    }
}