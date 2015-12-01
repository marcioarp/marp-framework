<?php
class System extends Route {
	private $_url; //
	private $_explode;
	private $_controller;
	private $_action;
	private $_parameters;
	private $_data;
	private $_query;
	private $cnx;
	private $cnxComum;
	
	
	function __construct($cnx=false,$cnxComum) {
		$this->cnx = $cnx;
		$this->cnxComum = $cnxComum;
		$this->setUrl();
		$this->setExplode();
		$this->setController();
		$this->setAction();
		$this->setParameters();
		$this->setData();
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
		$this->_url = (empty($_GET['url'])) ? 'index/' : $_GET['url'];
	}
	private function setExplode() {
		$this->_explode = explode('/',$this->_url);
	}
	private function setController() {
		$this->_controller = $this->_explode[0];
	}
	private function setAction() {
		$this->_action = empty($this->_explode[1]) ? 'index_action' : $this->_explode[1];
	}
	private function setParameters() {
		if (isset($this->_explode[2])) {
			for ($i=2;$i<sizeof($this->_explode);$i++) {
				$this->_parameters[$i-2]=$this->_explode[$i];
			}
		} else {
			$this->_parameters = null;
		}
	}
	
	private function setData() {
		$this->_data = file_get_contents('php://input');
	}
	
	public function run() {
		if (file_exists(CONTROLLERS.$this->_controller.'Ctrl.php')) {
			require_once(CONTROLLERS.$this->_controller.'Ctrl.php');
			
			if (class_exists($this->_controller)) {
				$controller = new $this->_controller($this->cnx, $this->cnxComum);
				$action = $this->_action;
				if (method_exists($controller,$action)) {
					$controller->$action($this->_parameters,$this->_data);
				} else {
					//metodo nao existe	
					parent::route($this->_url,$this->_explode);
				}
			} else {
				//classe nao existe
				//parent::route($this->_url,$this->_explode);
				//http_response_code(404);
				header("HTTP/1.0 404 Not Found");
				echo "Classe não encontrada: ".$this->_controller;
			}
		} else {
			//arq controller nao encontrado
			//parent::route($this->_url,$this->_explode);
			//echo phpinfo();
			//http_response_code(404);
			header("HTTP/1.0 404 Not Found");
			echo "Controller n enc";
		}
	}
}
?>