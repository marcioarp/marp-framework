<?php

//namespace database;

require_once(API_PATH.'plugin/CryptUtil.php');
require_once(API_PATH.'plugin/DateUtil.php');
//require_once(API_PATH.'plugin/NumberUtil.php');
  
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
	private $__posicao=0;
	private $qtdRegistros=0;
	private $_eof=true;
	private $_bof=true;
	
	private $erro = false;
	
	public $log='';
	public $warning ='';
	public $limit = "";
	public $order_by = "";
	
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

	
	public function addLog($log) {
		$this->log .= "<b>".date('d/m/Y H:i:s').'</b> '.$log."\r\n";
	}

	/**
	 * parametros deve ser :
	 * $arr[0][0] = 'nome_parametro'; $arr[0][1]= 'valor'; $arr[0][2] = 'text' ou 'numeric'; $arr[0][3] = operador (=,>=,<=,like,etc)
	 * @return boolean true / false
	 */
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
		$sql .= ' '.$this->order_by;
		$sql .= ' '.$this->limit;
		//print_r($arrParametros);
		//echo $sql.'<br>'; exit;
		$this->log .= $sql;
		return $this->buscaSQL($sql);
	}

	function buscaSQL($sql) {
		//echo "aki"; exit;
		$rs = $this->cnx->runSQL($sql);
		if (!$rs) {
			$this->erro .= "\r\n ".$this->cnx->getErro().' ';
			return false;
		}
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
		return $this->busca(array(array($chave->getName(),$id,'text')));
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
			$this->__posicao = 0;
			
		}
	}
	
	
	//recebe um array de dados e carrega no obj
	public function associaDados($v) {
		//print_r($v);
		for ($i=0;$i<sizeof($this->arrCampos);$i++) {
			$nomeCampo = $this->arrCampos[$i];
			$campo = $this->$nomeCampo;
			if (FALSE) $campo = new BDField;
			if (isset($v[$nomeCampo])) {
				$campo->setValueFromBD($v[$nomeCampo]);
				//echo $v[$nomeCampo];
			} else {
				$campo->setNull();
			}
		}
		//echo $this->nasc->getValue();
	} 
	
	
	//comando update
	public function atualiza() {
		$sql = $this->preparaSQL($this->sqlUpdate);
		//echo $sql;
		$this->log .= $sql."\r\n";
		$rs = $this->cnx->runSQL($sql);
		if ($this->cnx->getErro()) {
			return false;
		} if ($this->cnx->affectedRows() <= 0) {
		 	$this->warning .= "\r\n".'Comando executado com sucesso porém nada foi alterado';
			return true;
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
			if (!$campo->isValidValue()) {
				$retorno = false; 
				$this->erro .= "\r\n". $campo->getDisplayName().' '.$campo->getValue().' é inválido'; 
			} 
		}
		return $retorno;
		
				
	}
	
	public function insere() {
		$valido = $this->validaDados();
		if (!$valido) return false; 
		$this->erro = false;
		$sql = $this->preparaSQL($this->sqlInsert);
		//echo $sql; exit;
		$this->log .= $sql."\r\n";
		$rs = $this->cnx->runSQL($sql);
		$id = $this->cnx->lastInsertID();
		$this->erro .= $this->cnx->getErro();
		$campo = $this->getFieldKey();
		
		if (!$campo) {
			$this->log .= $this->erro;
		} else {
			
			if ((isset($id)) && (strlen($campo->getValue()) < 1)) {
				$campo->setValue($id);
			}
		}
		
		if ($this->erro) {
			$this->log .= $this->erro;
			return false;
		}
		return true;
	}
	
	public function apaga() {
		$chave = $this->getFieldKey();
		$sql = $this->preparaSQL($this->sqlDelete);
		$rs = $this->cnx->runSQL($sql);
		$this->addLog($sql);
		if ($this->cnx->getErro()) {
			return false;
		}
		return true;//!($this->searchByKey($chave->getValue()) > 0);
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
		$this->__posicao = $posicao;
		if (isset($this->dados[$this->__posicao])) {
			$this->associaDados($this->dados[$this->__posicao]);
			return true;
		} else {
			return false;
		}
	}
	
	//vai para a proxima posicao
	public function next() {
		if ($this->__posicao < ($this->qtdRegistros - 1)) {
			return $this->goToPosicao($this->__posicao+1);
		} else {
			$this->_eof = true;
			return false;
		}
	}
	
	//vai para a  posicao anterior
	public function prior() {
		if ($this->__posicao > 0) {
			return $this->goToPosicao($this->__posicao-1);
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
		return $this->__posicao;
	}
	
	public function recordCount() {
		return $this->qtdRegistros;
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
		//$key = $this->getFieldKey();
		//return $this->busca(array(array($key->getName(),$valor,$key->getType())));
		return $this->searchByKey($valor);
	}
	
	//JSON FUNCTIONS
	private function preparaJson() {
		for ($i=0;$i<sizeof($this->arrCampos);$i++) {
			$nomeCampo = $this->arrCampos[$i];
			$campo = $this->$nomeCampo;
			if (false) $campo = new BDField;
			$retorno[$nomeCampo] =	$campo->getValue2UserJS();
		}	
		return $retorno;		
	}
	
	public function getJson() {
		return json_encode($this->preparaJson(),JSON_NUMERIC_CHECK);
	}
	
	public function getAllJson() {
		$retorno = $this->getAllArray();
		if ($retorno)
			return json_encode($retorno,JSON_NUMERIC_CHECK);
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
	//$setNull -> Campos não informados serão definidos como nulos
	public function setJSON($JsonOBJ,$setNull=true) { //doc
		$newValues = json_decode($JsonOBJ,true);
		return $this->associaArrFromUser($newValues,$setNull) ;
	}
	
	//setnull define como null os campos não informados pelo usuario 
	//nomes dos campos precisam ser em lowercase
	//para converter um array para lowercase utilize o comando
	//$arr2 = array_change_key_case($arr,CASE_LOWER); 
	public function associaArrFromUser($arr,$setNull=true) { //doc
		$retorno = true;
		//print_r($arr2);
		for ($i=0;$i<sizeof($this->arrCampos);$i++) {
			$nomeCampo = $this->arrCampos[$i];
			$campo = $this->$nomeCampo;
			if (false) $campo = new BDField();
			if (isset($arr[$nomeCampo])) {
				$campo->setValueFromUser($arr[$nomeCampo]);
				$this->log .= $nomeCampo . ' => ' . $campo->getValue();
				//echo "aki<br>";
			} else {
				//$campo->	
				if($setNull) $campo->setNull();
				$this->log .= "Campo não encontrado no array do usuário $nomeCampo\r\n";
			}
		}
		//exit;
		if (!$this->validaDados()) return false;
		return $retorno;
	}
	
	
	public function eof() {return $this->_eof;}

	public function bof() {return $this->_bof;}
	
	//executa o SQL
	//atribui à lista de objs
	//retorna a lista ao usuário
	public function searchToUser($sql) {
		if ($this->buscaSQL($sql) ) {
			$r["status"] = "ok";
			$r["msg"] = "";
			$r["lista"] = $this->getAllArray();
		} else {
			if (strlen($this->getErro()) > 1) {
				$r["status"] = "Erro";
				$r["msg"] = $this->getErro();
			} else {
				$r["status"] = "ok";
				$r["msg"] = '';
				$r['lista'] = array(); 
			}
		}
		return $r;
		
	}
	
	public function exportXMLDelphiDataSet($dados=true) {
		$this->first();
		$qtd = 0;
		$xml = '<?xml version="1.0" standalone="yes"?>
			<DATAPACKET Version="2.0">
				<METADATA>
					<FIELDS>
		';
		//fields descriptions
		for ($i=0;$i< sizeof($this->arrCampos);$i++) {
			$campoNome = $this->arrCampos[$i];
			$campo = $this->$campoNome;
			if (false)
				$campo = new BDField;
			if ($campo->isInteger()) {
				$xml .= '<FIELD attrname="'.$this->arrCampos[$i].'" fieldtype="i4"/>';
			} else if ($campo->isNumeric()) {
				$xml .= '<FIELD attrname="'.$this->arrCampos[$i].'" fieldtype="fixed" DECIMALS="6" WIDTH="32"/>';
			} else if ($campo->isData()) {
				$xml .= '<FIELD attrname="'.$this->arrCampos[$i].'" fieldtype="date"/>';
			} else if ($campo->isDataHora()) {
				$xml .= '<FIELD attrname="'.$this->arrCampos[$i].'" fieldtype="dateTime"/>';
			} else if (($campo->getMaxLength()<0) || ($campo->getMaxLength()>255)) {
				$xml .= '<FIELD attrname="'.$this->arrCampos[$i].'" fieldtype="bin.hex"  SUBTYPE="Text"/>';
			} else {
				$xml .= '<FIELD attrname="'.$this->arrCampos[$i].'" fieldtype="string" WIDTH="'.$campo->getMaxLength().'"/>';
			}
			$xml.="\r\n";
		}
		$xml .= '
					</FIELDS>
				</METADATA>
				<ROWDATA>
		';
		//rows 
		if ($dados) {
			while ((!$this->eof()) ) { //&& ($qtd < 100)
				$xml .= "<ROW ";
				for ($i=0;$i< sizeof($this->arrCampos);$i++) {
					$campoNome = $this->arrCampos[$i];
					$campo = $this->$campoNome;
					$xml .= $campoNome . '="'. str_replace('"', '&quot;',$campo->getValue()).'" ';
				}
				$xml .= "/>\r\n";
				$qtd++;
				$this->next();
			}
		}
		$xml.='	</ROWDATA>
			</DATAPACKET>
		';		
		
		return $xml;
	}
	
}