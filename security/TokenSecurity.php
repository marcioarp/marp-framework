<?php
//namespace security;

//use plugin\Util as Util;
require_once(API_PATH.'plugin/CryptUtil.php');
require_once(API_PATH.'plugin/StringUtil.php');
require_once(API_PATH.'plugin/HttpUtil.php');

class TokenSecurity {
	//converte o arr para json, criptografa e gera um token 
	static function getTokenCrypt($arr) {
		
		$ip = HttpUtil::getClientIP();
		//echo "aki"; exit;
		$id=false;//$id = HttpUtil::getHeaderVarClient('idDevice');
		$dthr = date('Y-m-d H:i:s');
		$tk['obj'] = $arr;
		$tk['hash'] = StringUtil::getHash();
		$tk['id'] = $id;
		$tk['ip'] = $ip;
		$tk['server'] = $_SERVER['SERVER_NAME'];
		$tk['dthr'] = $dthr;
		$json = json_encode($tk);
		return CryptUtil::encrypt($json);
	}
	
	
	//retonar arr descriptografado caso seja válido IP ou idDevice
	static function validaToken($killOnInvalid=true) {
		$ip = HttpUtil::getClientIP();
		$id = HttpUtil::getHeaderVarClient('idDevice');
		$server = $_SERVER['SERVER_NAME'];
	
		$tks = HttpUtil::getHeaderVarClient('Tks-Sec');
		$json = CryptUtil::decrypt($tks);
		
		$tk = json_decode($json,true);
		//print_r( $tk); exit;
		
		if ((!isset($tk['server'])) || ($server != $tk['server']) ) {
			if ($killOnInvalid) {
				$retorno['status'] = 'Erro';
				$retorno['msg'] = 'Por questões de segurança, acesso não permitido neste host, por favor saia do sitema e entre novamente.';
				http_response_code(403);
				echo json_encode($retorno);
				exit;
			} else {
				return false;
			}
		}
		if (!isset($tk['obj']['id_usuario'])) {
			$r['status'] = 'ok';
			$r['warning'] = 'Por questões de segurança, saia do sistema e faça login novamente. Obrigado.';
			//$r['console'] = $tk;
			http_response_code(200);
			echo json_encode($r);
			exit;
		}
		if (($ip == $tk['ip']) && ((strlen($ip) > 2 ))) {
			return $tk['obj'];
		}
		
		if (($id == $tk['id']) && (strlen($id) > 5 )) 
			return $tk['obj'];
		
		
		if ($killOnInvalid) {
			$retorno['status'] = 'Erro';
			$retorno['msg'] = 'Acesso não permitido para o ip '.$ip;
			//$retorno['console'] = $tk;
			http_response_code(403);
			echo json_encode($retorno);
			exit;
		}
		//exit;
		return false;
	}

	static function getTokenDecrypt() {
		if (TokenSecurity::validaToken()) {
			$tks = HttpUtil::getHeaderVarClient('Tks-Sec');
			return TokenSecurity::decryp($tks);
		} else {
			return false;
		}
	}
	
	static function decryp($token) {
		$json = CryptUtil::decrypt($token);
		return json_decode($json,true);
	}
	
}
