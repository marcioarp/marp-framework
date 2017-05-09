<?php

class StringUtil {
	
	//Gerar um id tipo hash aleatório
	static function getHash() {
		return  md5(uniqid(rand(), true));
	}
	
	static function getUniqueID() {
		return StringUtil::getHash();
	}


	//Remover Underline da String e transforma a próxima letra em UpperCase
	//$separador é a string que ficará no lugar dos underlines.
	static function underline2UC($str,$separator='') {
		$arr = explode('_',$str);
		$str = '';
		for ($i=0;$i<sizeof($arr);$i++) {
			$str .= $separator.ucfirst($arr[$i]);
		}
		return trim($str);
	}
	

	//Remover Underline da String e transforma a próxima letra em UpperCase 
	//(a partir da segunda string)
	static function secondUnderline2UC($str) {
		$arr = explode('_',$str);
		$str = $arr[0];
		for ($i=1;$i<sizeof($arr);$i++) {
			$str .= ucfirst($arr[$i]);
		}
		return $str;
	}
	
	
	//Converte uma string em valores hexadecimais
	static function strToHex($string) {
		$hex = '';
		for ($i=0; $i<strlen($string); $i++){
			$ord = ord($string[$i]);
			$hexCode = dechex($ord);
			$hex .= substr('0'.$hexCode, -2);
		}
		return strToUpper($hex);
	}
	
	//Converte valores hexadecimais em string
	static function hexToStr($hex) {
		$string='';
		for ($i=0; $i < strlen($hex)-1; $i+=2){
			$string .= chr(hexdec($hex[$i].$hex[$i+1]));
		}
		return $string;
	}
	
	static function somenteNumeros($str) {
		return preg_replace('/\D/', '', $str);		
	}
		
	//Remove acento da string
	static function removeAcento($texto) {
		return preg_replace("[^a-zA-Z0-9 ]", "", strtr($texto, "áàãâéêíóôõúüçÁÀÃÂÉÊÍÓÔÕÚÜÇ", "aaaaeeiooouucAAAAEEIOOOUUC"));
	}

	//Substitui " por \", ' por \" e \ por \\ dentro da string
	//Dever ser usada para enviar comandos SQL ao postgres.
	static function RemoveAspas($texto) {
		//$retorno  = str_replace('\\','\\\\',$texto);
		//$retorno = str_replace('"','\"',$retorno);
		//$retorno = str_replace("'","\'",$retorno);
		$retorno = str_replace("'","\'",$texto);
		return $retorno;
	
	}
}
