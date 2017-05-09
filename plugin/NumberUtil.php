<?php

class NumberUtil {
	/*
	 Utilizar filtro de exibição do angular e não fazer conversão de String~;
	 //Converte valor string com decimais separador por virgula
	 // para um número PHP
	 static function StrToFloat($str) { //deprecated
	 $str = str_ireplace('.', '', $str);
	 $str = str_ireplace(',', '.', $str);
	 return $str;
	 }

	 //Converte valor string com decimais separador por ponto
	 // para uma string
	 static function FloatToStr($float) { //deprecated
	 $str = str_ireplace(',', '', $str);
	 $str = str_ireplace('.', ',', $str);
	 return $str;
	 }
	 */

	static function formataMoeda($numero) {
		return 'R$ ' . number_format($numero, 2, ',', '.');
	}

	static function isInteger($input) {
		return (ctype_digit(strval($input)));
	}
	
	static function strReaisToNumber($strValor) {
		$num = str_ireplace('.', '', $strValor);
		$num = str_ireplace(',', '.', $num);
		$num = str_ireplace('R', '', $num);
		$num = str_ireplace('$', '', $num);
		$num = str_ireplace(' ', '', $num);
		return floatval($num);
	}

}
