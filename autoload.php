<?php 
function __autoload($class) {
	$strGrupo = substr($class, -5);
	$encontrado=false;
	if ($strGrupo == 'Model') {
		$class = str_replace('Model', '', $class);
		$class = Util::lcfirst($class);
		$arq = 'models/'.$class.'.php';
		$encontrado = true;
	}
	if (!$encontrado) {
		$strGrupo = substr($class, -4);
		if ($strGrupo == 'Util') {
			$class = str_replace('Util', '', $class);
			$class = Util::lcfirst($class);
			$arq = 'utils/'.$class.'Util.php';
			$encontrado = true;
		}
	}
	if (!$encontrado ) {
		if ($strGrupo == 'Ctrl') {
			$class = Util::lcfirst($class);
			$arq = 'controllers/'.$class.'Ctrl.php';
		}
	}
	
	if (!$encontrado) {
		$retorno['status'] = 'Erro';
		$retorno['msg'] = 'Classe ' . $class  .' não encontrada, arquivo '.$arq;
		JSONFuncs::sendArray2Browser($retorno);
	}
	//$arq = APP.$arq;
	//echo $arq; exit;
	require_once(APP.$arq); 
}

