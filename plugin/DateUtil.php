<?php 

//namespace plugin;

//Esta classe contém métodos necessário para executar diversas funções comuns em todo sistema.
class DateUtil {
	static function timeUTC2Local($data) {
		$data = strtotime($data);
		$retorno = date('H:i:s',$data);
		return $retorno;
	}
	

	static function dateUTC2Local($data) {
		$data = strtotime($data);
		$retorno = date('Y-m-d',$data);
		return $retorno;
	}

	static function dateTimeUTC2Local($data) {
		$data = strtotime($data);
		$retorno = date('Y-m-d H:i:s',$data);
		return $retorno;
	}

	//Converte data e hora (se houver) do formato utilizado em Banco de Dados para BR 
	static function DataBD2Br($data) {
		$Datahora = explode(" ",$data);
		$dt = $Datahora[0];
		if (!isset($Datahora[1])) $Datahora[1]='';
		$hr = $Datahora[1];
		$retorno = implode("/",array_reverse(explode("-",$dt)));
		if (strlen($hr) > 1) 
			$retorno .= " ".$hr;
		return $retorno;
	}


	//Converte data e hora (se houver) do formato BR para o formato utilizado em Banco de Dados
	static function DataBr2BD($data) {
		$Datahora = explode(" ",$data);
		$dt = $Datahora[0];
		if (isset($Datahora[1])) {
			$hr = $Datahora[1];
		} else {
			$hr = '';
		}
		//if (!isset($Datahora[1])) $Datahora[1]='';
		$retorno = implode("-",array_reverse(explode("/",$dt)));
		if (strlen($hr) > 1) 
			$retorno .= " ".$hr;
		return $retorno;
	}
	
	//Converte a data recebida do browser para padrão php (/ 1000)
	static function dataJS2PHP($dtJS) {
		return (date('Y-m-d', $dtJS/1000));
	}
	
	
	//Retorna a próxima data válida em formato Y-m-d
	static function nextValidDate($ano,$mes,$dia) {
		if (checkdate($mes, $dia, $ano)) {
			$mes = str_pad($mes,2,'0',STR_PAD_LEFT);
			$dia = str_pad($dia,2,'0',STR_PAD_LEFT);
			return $ano.'-'.$mes.'-'.$dia;
		} else {
			if ($mes >= 12) {
				return DateUtil::nextValidDate($ano++, 1, $dia);
			} else {
				return DateUtil::nextValidDate($ano, $mes+1, 1);
			}
		}
	}
}