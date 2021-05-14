<?php

if (!defined("SISTEMA_LIBRARY")) {
	die("Acesso direto não permitido");
}
/**
 * Versão 0.0.1 
 * 
 */

class Irpf   {

	public function __construct () {

	}


	public static function calculaValorIRRFBaseLiquido($valorLiquido) {
		$valorLiquido = Helper::decimalFormat(number_format($valorLiquido, 2));
        // IRRF 2017
		if ($valorLiquido > 4664.68) {
			$aliquota = 0.275;
			$parcelaDeduzir = 869.36;
		} elseif (($valorLiquido >= 3751.06) && ($valorLiquido <= 4664.68)) {
			$aliquota = 0.225;
			$parcelaDeduzir = 636.13;
		} elseif (($valorLiquido >= 2826.66) && ($valorLiquido <= 3751.05)) {
			$aliquota = 0.15;
			$parcelaDeduzir = 354.8;
		} elseif (($valorLiquido >= 1903.99) && ($valorLiquido <= 2826.65)) {
			$aliquota = 0.075;
			$parcelaDeduzir = 142.80;
		} else {
			return (double) 0.0;
		}
		return ($valorLiquido * $aliquota - $parcelaDeduzir) / (1 - $aliquota);
	}
}