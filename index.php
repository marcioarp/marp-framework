<?php
ob_start();
ob_clean();
//header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Max-Age: 86400');
// cache for 1 day
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
//header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

//caso o script não funcione até o fim irá retornar erro
header("HTTP/1.0 500 Internal Server Error");

ini_set("allow_url_fopen", true);
ini_set('display_errors', '1');
ini_set('error_reporting', E_ALL);

require ('../config.php');

define('MODELS', API_PATH . 'sistema' . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR);
define('PATH_BACKUP', API_PATH . 'backup' . DIRECTORY_SEPARATOR);

require ('gateway/540.php');
require ('plugin/NumberUtil.php');
require ('plugin/Validacoes.php');

require_once ('database/Connection.php');
$GLOBALS['cnx'] = new Connection('A');
$GLOBALS['cnxComum'] = new Connection('C');

if (DEV_MOD) {
	require_once ('../_update/functs.php');
	require_once ('../_update/versaoAtual.php');
	$sql = "select max(subversao) from versao_hist";
	$cnx = $GLOBALS['cnx'];
	if (false)
		$cnx = new Connection();
	$rs = $cnx -> runSQL($sql);
	$v = $cnx -> fetchArray($rs);
	if ($v[0] < 1) {
		$v[0] = 1;
	}
	for ($i = $v[0]; $i <= $subversao; $i++) {
		$dir_sv = str_pad($i, 3, '0', STR_PAD_LEFT);
		require_once ('../_update/' . $dir_sv . '/__sql.php');
	}
} else {
	if (BD_APP_USER == 'root') {echo "Usuário root não permitido.";	exit ; }
	if (BD_APP_PASSWORD == '@147258') {echo "Senha padrão DEV não permitido."; exit ; }
	if (BD_COMUM_USER == 'root') {echo "Usuário root não permitido."; exit ; }
	if (BD_COMUM_PASSWORD == '@147258') {echo "Senha padrão DEV não permitido."; exit ; }
}

if (strlen(HASH_SEGURANCA) != 13) {
	echo "Hash de Segurança inválido, utilize 13 carecteres;";
	exit ;
}

//global $cnxSistema;
require_once ('gateway/Route.php');


//PARAMETROS
require_once(API_PATH.'sistema/models/Parametro.php');
$par = new ParametroModel($GLOBALS['cnx']);
$GLOBALS['par'] = $par;
include_once(API_PATH.'sistema/criaParametros.php');

ob_clean();
ob_start();
$start = new Route();
$start->run();
