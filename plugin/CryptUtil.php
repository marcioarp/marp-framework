<?php

require_once(API_PATH.'plugin/StringUtil.php');

class CryptUtil {
	//Função para Criptografar uma String
	static function encrypt($pure_string,$encryption_key=false) {
		if (!$encryption_key) $encryption_key = HASH_SEGURANCA;
		$pure_string =  base64_encode( $pure_string);
		$iv_size = mcrypt_get_iv_size(MCRYPT_BLOWFISH, MCRYPT_MODE_ECB);
		$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
		$encrypted_string = mcrypt_encrypt(MCRYPT_BLOWFISH, $encryption_key, utf8_encode($pure_string), MCRYPT_MODE_ECB, $iv);
		$encrypted_string = StringUtil::strToHex( $encrypted_string);
		return ( $encrypted_string);
	}
	
	//Função para Descriptografar uma String
	static function decrypt($encrypted_string,$encryption_key=false) {
		if (!$encryption_key) $encryption_key = HASH_SEGURANCA;
		$encrypted_string = StringUtil::hexToStr($encrypted_string);
		$iv_size = mcrypt_get_iv_size(MCRYPT_BLOWFISH, MCRYPT_MODE_ECB);
		$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
		$decrypted_string = mcrypt_decrypt(MCRYPT_BLOWFISH, $encryption_key, $encrypted_string, MCRYPT_MODE_ECB, $iv);
		return base64_decode( $decrypted_string);
	}
}
