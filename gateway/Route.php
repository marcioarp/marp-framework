<?php

//namespace gateway;

//use \plugin\JSONFuncs as JSONFuncs;
//use \plugin\Util as Util;

require_once(API_PATH.'plugin/JSONFuncs.php');
require_once(API_PATH.'security/TokenSecurity.php');

class Route {
	private $_url; 
	private $_explode;
	private $_component;
	private $_parameters;
	private $_data;
	private $_query;
	private $currPathDoc=false;
	private $_pathComponent;
	private $urlBase; 
	
	
	function __construct() {
		$this->setUrl();
		$this->setExplode();
		$this->setComponent();
		$this->setData();
		$this->setQuery();
	}


	//teste
	private function setQuery() {
		if (isset($_GET['url'])) {
			$this->_url = $_GET['url'];
		} else {
			$this->_url = substr($_SERVER['PATH_INFO'],1,1000);
		}
	}
	
	private function setUrl() {
		if (isset($_GET['url'])) {
			$this->_url = $_GET['url'];
		} else {
			$this->_url = substr($_SERVER['PATH_INFO'],1,1000);
		}
		$urlCompleta = $_SERVER['REDIRECT_URL'];
		$this->urlBase = substr($urlCompleta, 0,strpos($urlCompleta, $this->_url));
		$GLOBALS['urlBase'] = $this->urlBase; 
	}
	
	private function setExplode() {
		$this->_explode = explode('/',$this->_url);
	}
	
	private function setComponent() {
		//$strCompoment = '\\'.NAMESPACE_SISTEMA.'\\components\\';
		$pathTemp = 'components'.DIRECTORY_SEPARATOR;
		$this->_component = false;
		$pos = 0;
		for ($i=0; $i<sizeof($this->_explode);$i++) {
			if ($this->_component !== false) {
				$this->_parameters[$i-$pos]=$this->_explode[$i];

			} else if (is_dir(API_PATH.'sistema'.DIRECTORY_SEPARATOR.$pathTemp.$this->_explode[$i])) {
				//$strCompoment .= $this->_explode[$i] . '\\';
				$pathTemp .= $this->_explode[$i].DIRECTORY_SEPARATOR;

			} else if (file_exists(API_PATH.'sistema'.DIRECTORY_SEPARATOR.$pathTemp.$this->_explode[$i].'.php')) {
				//$strCompoment .= $this->_explode[$i];
				$this->_component = $this->_explode[$i];
				$pathTemp .= $this->_explode[$i].'.php';
				$pos = $i+1;

			}
		}
		//exit;
		if ($this->_component == false) {
			header("HTTP/1.0 404 Not Found");
			$retorno['status'] = 'erro';
			$retorno['msg'] = 'Arquivo não encontrado. '.$pathTemp;
			$retorno['exp'] = $this->_explode;
			$retorno['arq'] = API_PATH.'sistema'.DIRECTORY_SEPARATOR.$pathTemp.$this->_explode[$i].'.php';
			JSONFuncs::sendArray2Browser($retorno);
		}
		$this->_pathComponent = $pathTemp;
		require_once(API_PATH.'sistema'.DIRECTORY_SEPARATOR.$pathTemp);
	}


	private function setData() {
		$this->_data = json_decode(file_get_contents('php://input'),true);
		//if (!isset($this->_data)) $this->_data = false;
	}
	
	private function saveSample($controller,$action) {
		$class = $controller;
		$method = $action;
		$params = $this->_parameters;
		$data = $this->_data;
		$query = $this->_query;
				
		$path = DOC_PATH.$this->_pathComponent.DIRECTORY_SEPARATOR.$action;
		Util::recursive_mkdir($path);
		for ($i=0;$i,100;$i++) {
			$pathTemp = $path.DIRECTORY_SEPARATOR.str_pad($i, 2,'0',STR_PAD_LEFT);
			if (!is_dir($pathTemp)) {

				Util::recursive_mkdir($pathTemp);

				$params =  print_r($params, true);
				$fp = fopen($pathTemp.DIRECTORY_SEPARATOR.'parameters.txt', 'w+');
				//echo $pathTemp.'/parameters.txt'; exit;
				fputs($fp, $params, strlen($params));
				fclose($fp);
				
				//$data = json_decode($data,true);
				$data = json_encode($data);
				$fp = fopen($pathTemp.DIRECTORY_SEPARATOR.'data.json', 'w+');
				fputs($fp, $data, strlen($data));
				fclose($fp);

				$fp = fopen($pathTemp.DIRECTORY_SEPARATOR.'query.txt', 'w+');
				fputs($fp, $query, strlen($query));
				fclose($fp);
				$this->currPathDoc=$pathTemp;
				return true;
			}
		}
		return false;
	}
	
	public function saveResponse($data,$json=true) {
		if ($json) {
			$arq = 'response.json';
		} else {
			$arq = 'response.txt';
		}
		if ($this->currPathDoc) {
			$fp = fopen($this->currPathDoc.DIRECTORY_SEPARATOR.$arq, 'w+');
			fputs($fp, $data, strlen($data));
			fclose($fp);
		}
	}
	
	public function isPermiss() {
		$controller = str_replace('\\', '/', $this->_pathComponent);
		$action = $this->_parameters[0];
		$id_acesso = $controller.'/'.$action;
		$sql = "select ra_tipo_permissao from acesso where id_acesso='".$id_acesso."'";
		$cnx = $GLOBALS['cnx'];
		if (false) $cnx = new Connection;
		$ok = false;
		$rs = $cnx->runSQL($sql);
		if ($cnx->recordCount($rs) <= 0) {
			$sql = "insert into acesso (id_acesso, ra_tipo_permissao,qtd_requisicoes) 
				values ('".$id_acesso."','NAVT',1)
			";
			$cnx->runSQL($sql);
			$acesso = 'NAVT';
		} else {
			$v = $cnx->fetchArray($rs,MYSQL_ASSOC);
			$acesso = $v['ra_tipo_permissao'];
			
		}
		
		
		if ($acesso == 'PUBL' ) {
			$GLOBALS['tk'] = false;
			$ok = true;
		} else if ($acesso == 'NAVT' ) {
			$tk = TokenSecurity::validaToken(false);
			if ($tk) {
				$sql = "select ra_tipo_permissao from acesso_usuario where id_usuario = '".$tk['id_usuario'].
					"' and id_acesso='".$id_acesso."' ";
				$rs = $cnx->runSQL($sql);
				if ($cnx->recordCount($rs) > 0) {
					$v = $cnx->fetchArray($rs,MYSQL_ASSOC);
					if ($v['ra_tipo_permissao'] == 'DENY') {
						$r['status'] = 'Erro';
						$r['msg'] = "Sem permissão para executar esta operação. NAVT 1";
						http_response_code(403);
						JSONFuncs::sendArray2Browser($r);
					}
				}
				$GLOBALS['tk'] = $tk;
				$ok = true;
			} else {
				$r['status'] = 'Erro';
				$r['msg'] = "Sem permissão para executar esta operação. NAVT 2";
				$r['console'] = $tk;
				http_response_code(403);
				JSONFuncs::sendArray2Browser($r);
			}
			
		} else if ($acesso == 'ROOT') {
			$tk = TokenSecurity::validaToken();
			$GLOBALS['tk'] = $tk;
			if ($tk['id_usuario'] == 1) {
				$ok = true;
			} else {
				$r['status'] = 'Erro';
				$r['msg'] = "Sem permissão para executar esta operação.";
				http_response_code(403);
				JSONFuncs::sendArray2Browser($r);
			}
		} else if ($acesso == 'VTUS') {
			$tk = TokenSecurity::validaToken();
			$GLOBALS['tk'] = $tk;
			$ok =  true;
		} else if ($acesso == 'PERM') {
			$r['status'] = 'Erro';
			$r['msg'] = "Método aguardando definição de ACESSO";
			JSONFuncs::sendArray2Browser($r);
		}
		
		
		if ($ok) {
			$sql = "update acesso set qtd_requisicoes = qtd_requisicoes + 1 where id_acesso = '$id_acesso'";
			$cnx->runSQL($sql);
			return true;
		} else {
			echo $cnx->getErro(); exit;
		}
	}


	public function run() {
		if (class_exists($this->_component)) {

			$controller = new $this->_component($this);
			$action = $this->_parameters[0];
			//print_r($this->_parameters); exit;
			if (method_exists($controller,$action)) {
				//array_splice($this->_parameters, 0,1);
				if (!$this->isPermiss()) exit;
				$p = $this->_parameters;
				array_splice($p,0,1);
				if (DOC_MOD) { $this->saveSample($this->_component,$action);	}
				
				$retorno = $controller->$action($p,$this->_data);
				
				$this->sendResponse($retorno);
				exit;
			}
			
			$action = strtolower($_SERVER['REQUEST_METHOD']);
			if (method_exists($controller,$action)) {
				if (DOC_MOD) { $this->saveSample($this->_component,$action);	}
				$retorno = $controller->$action($this->_parameters,$this->_data);
				$this->sendResponse($retorno);
				exit;
			} else {
				//metodo nao existe	
				header("HTTP/1.0 404 Not Found");
				$retorno['status'] = 'erro';
				$retorno['msg'] = 'Método ('.$this->_component.'.'.$action.') não encontrado. ' ;
				JSONFuncs::sendArray2Browser($retorno);
			}
		} else {
			//classe nao existe
			//parent::route($this->_url,$this->_explode);
			//http_response_code(404);
			header("HTTP/1.0 404 Not Found");
			$retorno['status'] = 'erro';
			$retorno['msg'] = 'Classe ('.$this->_component.') não encontrado. ';
			JSONFuncs::sendArray2Browser($retorno);
		}
	}

	private function sendResponse($resp) {
		//caso seja modo dev não limpar a saida automaticamente
		$saida = ob_get_contents();
		$saida = trim($saida);
		if ((strlen($saida)>0) && DEV_MOD) {
			echo $this->_url;//.' '.$this->_data.' '.$this->_explode  ;
			echo "\r\n HTTP/1.0 500 Internal Server Error.";
			header("HTTP/1.0 500 Internal Server Error");
			exit;
		} else if ((isset($resp['httpCode'])) && (!RESPONSE_ALWAYS_200)) {
			http_response_code($resp['httpCode']);
		} else {
			http_response_code('200');
		}
		
		if (is_array($resp)) {
			if (!RESPONSE_STATUS) {
				if (isset($resp['dados']))
					$json = json_encode($resp['dados']);
				else
					$json = json_encode($resp);//,JSON_FORCE_OBJECT);
			} else {
				$json = json_encode($resp);//,JSON_FORCE_OBJECT);
			}
			if (DOC_MOD) {
				$this->saveResponse($json);
			}
			//ob_clean();
			echo $json;
		} else {
			if (DOC_MOD) {
				$this->saveResponse($resp,false);
			}
			echo $resp;
		}
	}
}

