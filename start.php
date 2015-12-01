<?php
header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Max-Age: 86400');    // cache for 1 day
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");  
header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
ini_set("allow_url_fopen", true);

require_once(dirname(__FILE__).'/540.php');
require_once(dirname(__FILE__).'/define.php');
require_once(APP.'config.php');
require_once(dirname(__FILE__).'/route.php');
require_once(dirname(__FILE__).'/controller.php');
require_once(dirname(__FILE__).'/system.php');
require_once(dirname(__FILE__).'/bd_field.php');
require_once(dirname(__FILE__).'/query.php');
require_once(dirname(__FILE__).'/database.php');
require_once(dirname(__FILE__).'/connection.php');
require_once(dirname(__FILE__).'/util.php');
require_once(dirname(__FILE__).'/jsonfcts.php');
require_once(dirname(__FILE__).'/tks.php');
require_once(dirname(__FILE__).'/autoload.php');

$cnx = new Connection(TIPO_CONEXAO_BD); 
$cnxComum = new Connection(TIPO_CONEXAO_BD_COMUM); 

$start = new System($cnx,$cnxComum);
$start->run();

