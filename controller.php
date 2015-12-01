<?php

class Controller {
	protected $cnx;
	protected $cnxComum;
	
	function __construct($cnx=false,$cnxComum) {
		$this->cnx = $cnx;
		$this->cnxComum = $cnxComum;
	}

	protected function view($layout,$dados=null) {
		if (!isset($layout)) {
			echo 'erro';
		} else {
			if (file_exists(VIEWS.$layout.'View.php')) {
				if (COMPILE) {
					$this->compileView($layout);	
				} else {
					require_once(VIEWS.$layout.'View.php');
				}
			} else {
				ob_clean();
				http_response_code(404);
				echo 'Página não encontrada: '.VIEWS.$layout.'View.php';
			}
		}
	}
	
	protected function compileView($arq,$layout) {
		ob_clean();
		include_once($arq); 
		$html = ob_get_clean();
		$f = fopen(SISTEMA.$layout, 'w+');
		fputs($f, $html, strlen($html));
		fclose($f);
		return SISTEMA_LINK.$layout;
	}
	
	function validaToken() {
		$tk = TokenSecurity::validaToken();
		//exit;
		if (!$tk) {
			$retorno['status'] = 'Erro';
			$retorno['msg'] = 'Entre com seu usuário e senha';
			JSONFuncs::sendArray2Browser($retorno);
		}
		return $tk;
		
	}	
}
?>