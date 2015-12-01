<?php
class TokenSecurity {
	
	//converte o arr para json, criptografa e gera um token 
	static function getTokenCrypt($arr) {
		$ip = Util::get_client_ip();
		$id = Util::getHeaderVarClient('idDevice');
		$dthr = date('Y-m-d H:i:s');
		$tk['obj'] = $arr;
		$tk['hash'] = Util::getHash();
		$tk['id'] = $id;
		$tk['ip'] = $ip;
		$tk['dthr'] = $dthr;
		$json = json_encode($tk);
		return Util::encrypt($json);
	}
	
	
	//retonar arr descriptografado caso seja válido IP ou idDevice
	static function validaToken() {
		$ip = Util::get_client_ip();
		$id = Util::getHeaderVarClient('idDevice');
	
		$tks = Util::getHeaderVarClient('tks-sec');
		$json = Util::decrypt($tks);
		
		$tk = json_decode($json,true);
		//print_r( $tk); exit;
		if ($ip == $tk['ip']) 
			return $tk['obj'];
		
		if (($id == $tk['id']) && (strlen($id) > 5 )) 
			return $tk['obj'];
		
		return false;
	}
	
}
