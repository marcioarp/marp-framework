<?php

class Validacoes {
	// verifica se um esta esta de escrito de forma correta
	static function validarCep($cep) {
		// retira espacos em branco
		$cep = StringUtil::somenteNumeros($cep);
		// expressao regular para avaliar o cep
		
		if (strlen($cep) == 8) {
			return true;
		} else {
			return false;
		}
	}
	
	static function validarCPFCNPJ($cpf) {
		require_once(API_PATH.'plugin/CpfCnpj.php');
		$validar = new ValidaCPFCNPJ($cpf);
		return $validar->valida();
	}
	
	static function inscricaoEstadual($ie, $estado) {
		require_once(API_PATH.'plugin/InscricaoEstadual.php');
		return InscricaoEstadual::validar($ie, $estado);
	}  
}
