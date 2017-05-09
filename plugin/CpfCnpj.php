<?php
/**
 * ValidaCPFCNPJ valida e formata CPF e CNPJ
 *
 * Exemplo de uso:
 * $cpf_cnpj  = new ValidaCPFCNPJ('71569042000196');
 * $formatado = $cpf_cnpj->formata(); // 71.569.042/0001-96
 * $valida    = $cpf_cnpj->valida(); // True -> Válido
 *
 */
class ValidaCPFCNPJ {
	public $tipoDoc = '';


	/**
	* Configura o valor (Construtor)
	* Remove caracteres inválidos do CPF ou CNPJ
	* @param string $valor - O CPF ou CNPJ
	*/
	function __construct($valor = null) {
		$this -> valor = preg_replace('/[^0-9]/', '', $valor);
		$this -> valor = (string)$this -> valor;
	}

	protected function verifica_cpf_cnpj() {
		if (strlen($this -> valor) === 11) {
			$this->tipoDoc = 'CPF';
			return 'CPF';
		} elseif (strlen($this -> valor) === 14) {
			$this->tipoDoc = 'CNPJ';
			return 'CNPJ';
		} else {
			$this->tipoDoc = false;
			return false;
		}
	}

	protected function verifica_igualdade() {
		$caracteres = str_split($this -> valor);

		$todos_iguais = true;

		$last_val = $caracteres[0];

		foreach ($caracteres as $val) {
			if ($last_val != $val) {
				$todos_iguais = false;
			}

			$last_val = $val;
		}

		return $todos_iguais;
	}

	protected function calc_digitos_posicoes($digitos, $posicoes = 10, $soma_digitos = 0) {
		for ($i = 0; $i < strlen($digitos); $i++) {
			$soma_digitos = $soma_digitos + ($digitos[$i] * $posicoes);
			$posicoes--;
			if ($posicoes < 2) {
				$posicoes = 9;
			}
		}
		$soma_digitos = $soma_digitos % 11;
		if ($soma_digitos < 2) {
			$soma_digitos = 0;
		} else {
			$soma_digitos = 11 - $soma_digitos;
		}
		$cpf = $digitos . $soma_digitos;
		return $cpf;
	}

	protected function valida_cpf() {
		$digitos = substr($this -> valor, 0, 9);
		$novo_cpf = $this -> calc_digitos_posicoes($digitos);
		$novo_cpf = $this -> calc_digitos_posicoes($novo_cpf, 11);
		if ($this -> verifica_igualdade()) {
			return false;
		}
		if ($novo_cpf === $this -> valor) {
			return true;
		} else {
			return false;
		}
	}

	protected function valida_cnpj() {
		$cnpj_original = $this -> valor;
		$primeiros_numeros_cnpj = substr($this -> valor, 0, 12);
		$primeiro_calculo = $this -> calc_digitos_posicoes($primeiros_numeros_cnpj, 5);
		$segundo_calculo = $this -> calc_digitos_posicoes($primeiro_calculo, 6);
		$cnpj = $segundo_calculo;
		if ($this -> verifica_igualdade()) {
			return false;
		}
		if ($cnpj === $cnpj_original) {
			return true;
		}
	}

	public function valida() {
		if ($this -> verifica_cpf_cnpj() === 'CPF') {
			return $this -> valida_cpf();
		} elseif ($this -> verifica_cpf_cnpj() === 'CNPJ') {
			return $this -> valida_cnpj();
		} else {
			return false;
		}
	}

	public function formata() {
		$formatado = false;
		if ($this -> verifica_cpf_cnpj() === 'CPF') {
			if ($this -> valida_cpf()) {
				$formatado = substr($this -> valor, 0, 3) . '.';
				$formatado .= substr($this -> valor, 3, 3) . '.';
				$formatado .= substr($this -> valor, 6, 3) . '-';
				$formatado .= substr($this -> valor, 9, 2) . '';
			}
		} elseif ($this -> verifica_cpf_cnpj() === 'CNPJ') {
			if ($this -> valida_cnpj()) {
				$formatado = substr($this -> valor, 0, 2) . '.';
				$formatado .= substr($this -> valor, 2, 3) . '.';
				$formatado .= substr($this -> valor, 5, 3) . '/';
				$formatado .= substr($this -> valor, 8, 4) . '-';
				$formatado .= substr($this -> valor, 12, 14) . '';
			}
		}
		return $formatado;
	}

}
