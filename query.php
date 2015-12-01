<?php

//require_once('bd_field.php');
 
class Query {
	//@var Connection
	protected $cnx;
	protected $sqlSelect='';
	protected $sqlInsert='';
	protected $sqlDelete='';
	protected $sqlUpdate='';
	protected $arrCampos = '';
	protected $arrCamposUpdate = '';

	private $dados=false;
	private $posicao=0;
	private $qtdRegistros=0;
	private $_eof=true;
	private $_bof=true;
	
	private $erro = false;
	
	public $log='';
	public $limit = "";
	
	//$cnx deve ser uma conexão com o banco de dados
	function __construct($cnx) {
		if (false) {
			$this->cnx = new Connection;
		}
		$this->cnx = $cnx;
	}
		
	
	
	public function getSelect() { return $this->sqlSelect; }
	public function getInsert() { return $this->sqlInsert; }
	public function getDelete() { return $this->sqlDelete; }
	public function getUpdate() { return $this->sqlUpdate; }
	public function getErro() {return $this->erro;}	

	

	//parametros deve ser :
	//$arr[0][0] = 'nome_parametro'; $arr[0][1]= 'valor'; $arr[0][2] = 'text' ou 'numeric'; $arr[0][3] = operador (=,>=,<=,like,etc)
	//return true / false
	public function busca($arrParametros=false) {
		$sql = $this->sqlSelect;
		$tagWhere = ' where ';
		if ($arrParametros) {
			for ($i=0;$i<sizeof($arrParametros);$i++) {
				if(isset($arrParametros[$i][3])) {
					$operador = $arrParametros[$i][3];
				} else {
					$operador = ' = ';
				}
				
				
				if ($arrParametros[$i][2] == 'text') {
					$sql .= $tagWhere . $arrParametros[$i][0] . " $operador '" . (utf8_decode($arrParametros[$i][1]))."'";
					
				} else {
					$sql .= $tagWhere . $arrParametros[$i][0] . " $operador " . $arrParametros[$i][1];
					
				}
				
				$tagWhere = ' and ';
			}
		}
		$sql .= ' '.$this->limit;
		//print_r($arrParametros);
		//echo $sql.'<br>'; exit;
		$this->log .= $sql;
		return $this->buscaSQL($sql);
	}

	function buscaSQL($sql) {
		//echo "aki"; exit;
		$rs = $this->cnx->runSQL($sql);
		$registros = $this->cnx->recordCount($rs);
		//echo $registros; exit; 
		if ($registros > 0) {
			$this->carregaDados($rs);
			return true;
		} else {
			$erro = $this->cnx->getErro();
			if ($erro) {
				$this->log .= $erro;
				return false;
			} 
			
			$this->qtdRegistros=0;
			$this->_bof = true;
			$this->_eof = true;
			return false;
		}
	}
	
	//realiza busca pelo campo chave da classe
	//a chave de ser um unico campo numerico
	public function searchByKey($id) {
		$chave = $this->getFieldKey();
		return $this->busca(array(array($chave->getName(),$id,'numeric')));
	}
	
	//coloca todo conteúdo do do resource em um array
	protected function carregaDados($rs) {
		$this->dados = false;
		if ($this->cnx->fieldCount($rs) > 0) {
			$i=0;
			while ($v = $this->cnx->fetchArray($rs)) {
				$this->dados[$i] = $v;
				$i++;
			}
			$this->qtdRegistros = $this->cnx->recordCount($rs);
			$this->_bof = false;
			$this->_eof = false;
			$this->associaDados($this->dados[0]);
			$this->posicao = 0;
			
		}
	}
	
	
	//recebe um array de dados e carrega no obj
	public function associaDados($v) {
		for ($i=0;$i<sizeof($this->arrCampos);$i++) {
			$nomeCampo = $this->arrCampos[$i];
			$campo = $this->$nomeCampo;
			if (FALSE) $campo = new BDField;
			if (isset($v[$nomeCampo])) {
				$campo->setValueFromBD($v[$nomeCampo]);
			}
		}	
	} 
	
	
	//comando update
	public function atualiza() {
		$sql = $this->preparaSQL($this->sqlUpdate);
		//echo $sql;
		$this->log .= $sql."\r\n";
		$rs = $this->cnx->runSQL($sql);
		if ($this->cnx->getErro()) {
			return false;
		} else {
			return true;
		}
	}
	
	public function validaDados() {
		$retorno = true;
		for ($i=0;$i<sizeof($this->arrCampos);$i++) {
			$nomeCampo =$this->arrCampos[$i]; 
			$campo = $this->$nomeCampo;
			if (false) $campo = new BDField();
			if (!$campo->isValidValue()) {$retorno = false; $this->erro .= "\r\n". $campo->getDisplayName().' inválido'; } 
		}
		return $retorno;
		
				
	}
	
	public function insere() {
		$valido = $this->validaDados();
		if (!$valido) return false; 
		$sql = $this->preparaSQL($this->sqlInsert);
		//echo $sql; exit;
		$this->log .= $sql."\r\n";
		$rs = $this->cnx->runSQL($sql);
		$id = $this->cnx->lastInsertID();
		$this->erro .= $this->cnx->getErro();
		$campo = $this->getFieldKey();
		$campo->setValue($id);
		return $id > 0;
	}
	
	public function apaga() {
		$chave = $this->getFieldKey();
		$sql = $this->preparaSQL($this->sqlDelete);
		$rs = $this->cnx->runSQL($sql);
		if ($this->cnx->getErro()) {
			return false;
		}
		return !($this->searchByKey($chave->getValue()) > 0);
		
	}
	
	//retorna o campo chave da tabela
	public function getFieldKey() {
		for ($i=0;$i<sizeof($this->arrCampos);$i++) {
			$nomeCampo =$this->arrCampos[$i]; 
			$campo = $this->$nomeCampo;
			if (false) $campo = new BDField();
			if ($campo->getChave()) {
				return $campo;
			}
		}
		return false;
		
	}
	
	private function preparaSQL($sql) {
		for ($i=0;$i<sizeof($this->arrCampos);$i++) {
			$nomeCampo =$this->arrCampos[$i]; 
			$campo = $this->$nomeCampo;
			$sql = str_ireplace(':'.$nomeCampo.':',	( $campo->getValueForSQLCmd()), $sql);
		}
		return $sql;
		
	}
	
	//define os dados da posical atual
	private function goToPosicao($posicao) {
		if ($posicao >= $this->qtdRegistros) return false;
		$this->posicao = $posicao;
		if (isset($this->dados[$this->posicao])) {
			$this->associaDados($this->dados[$this->posicao]);
			return true;
		} else {
			return false;
		}
	}
	
	//vai para a proxima posicao
	public function next() {
		if ($this->posicao < ($this->qtdRegistros - 1)) {
			return $this->goToPosicao($this->posicao+1);
		} else {
			$this->_eof = true;
			return false;
		}
	}
	
	//vai para a  posicao anterior
	public function prior() {
		if ($this->posicao > 0) {
			return $this->goToPosicao($this->posicao-1);
		} else {
			$this->_bof = true;
			return false;
		}
	}
	
	//vai para a  utlima posicao 
	public function last() {
		return $this->goToPosicao($this->qtdRegistros-1);
	}
	
	//vai para a  primeira posicao 
	public function first() {
		return $this->goToPosicao(0);
	}
	
	public function getCursorPos() {
		return $this->posicao;
	}
	
	public function getFieldByName($name) {		
		if (false) $retorno = new BDField;
		$retorno = $this->$name;
		return  $retorno;
	}
	
	public function setValueByFieldName($fieldname, $value) {
		$campo = $this->$fieldname; 
		$campo->setValue($value);
	}
	
	public function buscaByPrimaryKey($valor) {
		$key = $this->getFieldKey();
		return $this->busca(array(array($key->getName(),$valor,$key->getType())));
	}
	
	//JSON FUNCTIONS
	private function preparaJson() {
		for ($i=0;$i<sizeof($this->arrCampos);$i++) {
			$nomeCampo = $this->arrCampos[$i];
			$campo = $this->$nomeCampo;
			$retorno[$nomeCampo] =	$campo->getValue();
		}	
		return $retorno;		
	}
	public function getJson() {
		return json_encode($this->preparaJson());
	}
	
	public function getAllJson() {
		$retorno = $this->getAllArray();
		if ($retorno)
			return json_encode($retorno);
		else 
			return false;
	}
	
	public function getAllArray() {
		$i=0;
		$this->first();
		$retorno=false;
		//echo $this->qtdRegistros; exit;
		while (!$this->_eof) {
			$retorno[$i] = $this->preparaJson();
			$this->next();
			$i++;
		}
		
		return $retorno;
		
	}
	
	public function getAsArray() {
		return $this->preparaJson();	
	}
	
	public function getAllJson2Browser() {
		ob_clean();
		echo $this->getAllJson();
		
		exit;
	}
	
	//Recebe um JSON e associa ao obj
	//Campos não informados serão definidos como nulos
	public function setJSON($JsonOBJ,$setNull=true) {
		$newValues = json_decode($JsonOBJ,true);
		return $this->associaArrFromUser($newValues,$setNull) ;
	}
	
	public function associaArrFromUser($arr,$setNull=true) {
		$retorno = true;
		for ($i=0;$i<sizeof($this->arrCampos);$i++) {
			$nomeCampo = $this->arrCampos[$i];
			$campo = $this->$nomeCampo;
			if (isset($arr[$nomeCampo])) {
				$campo->setValue($arr[$nome<?php


class BancoDados {
	//variáveis do bd
	public $id_banco_dados; 
	public $server;
	public $user ;
	public $password; 
	public $port; 
	public $database_name; 
	public $description; 
	public $sgdb=SGDB_POSTGRES; //Define qual o Gerenciador do Banco de Dados (MySQL / PostgreSQL)
	
	//Métodos//
	//Construtor	
	function __construct($pid_banco_dados=0) {
		//conexão ao bd_comum
		if ($pid_banco_dados == 'C') {
			$this->server=BD_COMUM_LOCAL_SERVER;
			$this->user=BD_COMUM_LOCAL_USER;
			$this->password=BD_COMUM_LOCAL_PASSWORD;
			$this->port=BD_COMUM_LOCAL_PORT;
			$this->database_name=BD_COMUM_LOCAL_DATABASE;
			$this->description = 'Conexão ao BD Comum';
			$this->sgdb=BD_COMUM_LOCAL_SGDB;
			
		} else if ($pid_banco_dados == 'CP') { //comum producao
			$this->server=BD_COMUM_PRODUCAO_SERVER;
			$this->user=BD_COMUM_PRODUCAO_USER;
			$this->password=BD_COMUM_PRODUCAO_PASSWORD;
			$this->port=BD_COMUM_PRODUCAOL_PORT;
			$this->database_name=BD_COMUM_PRODUCAO_DATABASE;
			$this->description = 'Conexão ao BD Comum';
			$this->sgdb=BD_COMUM_PRODUCAO_SGDB;
		} else if ($pid_banco_dados == 'P') { //Producao
			$this->server=BD_APP_PRODUCAO_SERVER;
			$this->user=BD_APP_PRODUCAO_USER;
			$this->password=BD_APP_PRODUCAO_PASSWORD;
			$this->port=