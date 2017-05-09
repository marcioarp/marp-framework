<?php

//namespace database;

//Classe para representar os fields do Banco de Dados
class BDField {
	protected $type; //Tipo do campo
	protected $mask_type; //Máscara para edição
	protected $required; //Definir true caso campo seja obrigatório
	protected $value; //Valor
	protected $max_length; //Tamanho máximo para edição
	protected $display_length; //Tamanho máximo para exibição
	protected $string; //Definir true caso o campo seja uma string (varchar)
	protected $description; //Breve descrição do campo
	protected $readonly; //Definir true caso campo seja somente leitura
	protected $name; //Nome do campo no BD 
	protected $displayname; //Nome do Campo
	protected $visible=true; //Definir true caso campo possa ser visível ao usuário
	protected $options = false; //Array caso um campo seja um SELECT OPTION (HTML)
	protected $chave = false; //Definir true caso seja um campo chave da tabela (Primary Key)
	protected $sql_options = false; //Definir comando SQL para que a classe busque em BD os possíveis valores para este campo
	protected $geometry = false; //Definir como true caso o campo seja geometry na tabela
	protected $defaultValue=false;
	protected $image=false;
	
	protected $arrayTipos = array ('password','cep','uf','cpf', 'cnpj', 'cpf_cnpj', 'tel_cel', 'texto', 'texto_livre', 'email', 'float', 'currency', 'int', 'data', 'hora', 'datahora', 'SN'); //Lista de todos os tipos possíveis para esta classe
	
	protected $arrayTiposString = array ('password','cep','uf','cpf', 'cnpj', 'tel_cel', 'texto', 'texto_livre', 'email', 'SN'); //Lista dos tipos String
	 
	protected $arrayTiposNumeric = array ('float', 'currency', 'int'); //Lista dos tipos Inteiros
	protected $arrayTiposDataHora = array ( 'datahora'); //Lista dos tipos Data/ Hora
	protected $arrayTiposHora = array ('hora'); //Lista dos tipos Data/ Hora
	protected $arrayTiposData = array ('data'); //Lista dos tipos Data/ Hora
	protected $arrayTiposInteger = array ('int'); //Lista dos tipos Data/ Hora
		

	//Métodos//
	//Construtor
	function __construct($tipo='texto',$requerido=false,$name='NomeCampo',$maxLength=1000000) {
		$name = strtolower($name);
		$this->setType($tipo,true);
		$this->required=$requerido;
		$this->value='';
		$this->max_length=$maxLength;//1000000 = 1MB
		$this->name = $name;
		$this->displayname = ucfirst($name);
		if ($maxLength > 50) {
			$this->display_length = 50;
		} else {
			$this->display_length = $maxLength;
		}
		
	}
	
	//Funções GETs
	function getType()      { return $this->type; }
	function getMaskType()  { return $this->mask_type; }
	function getRequire()   { return $this->required; }
	function getValue()     { return $this->value; }
	function getMaxLength() { return $this->max_length; }
	function getDisplayLength() { return $this->display_length; }
	function getDescription() { return $this->description; }
	function getReadOnly()    { return $this->readonly; }
	function getName() 		  { return $this->name; }
	function getDisplayName() { return $this->displayname; }
	function getVisible() 		  { return $this->visible; }
	function getChave() 		  { return $this->chave; }
	function getOptions() 		  { return $this->options; }
	function getDefaultValue() {return $this->defaultValue;}
	function getSQLOptions() { 
		return $this->sql_options; 
	}
	function getGeometry() 		  { return $this->geometry; }
	function getImage() 		  { return $this->image; }
	
	
	//Função que retona o valor do campo já preparado para ser inserido em um comando SQL
	//Retorna o número caso seja numérico
	//Retorna a palavra NULL caso o número seja false
	//Retorna string já com aspas para inserir no comando sql
	function getValueForSQLCmd() {
		$valor = $this->getValue();
		if ($this->isNumeric()) {
			if ( (isset($valor) || ($valor === 0)) 
			      && ($valor !== '')
			) {
				//return Util::DecimalBr2BD($valor);
				return $valor; 
			} else {
				//echo $this->getName().($valor===0); exit;
				return 'NULL';
			}
			
		} else if ($this->isDataHora()) {
			if (isset($valor)) {
				//return "'".Util::DataBr2BD($valor)."'";
				if (($valor == '') || ($valor == '0000-00-00')) {
					$this->setValue(NULL);
					return 'NULL';
				}  else {
					//echo $valor; exit;
					return "'".$valor."'";
				}
				
			} else {
				//echo $this->getName().($valor===0); exit;
				return 'NULL';
			}
			
		} else if ($this->isData()) {
			if (isset($valor)) {
				//return "'".Util::DataBr2BD($valor)."'";
				if (($valor == '') || ($valor == '0000-00-00')) {
					$this->setValue(NULL);
					return 'NULL';
				}  else {
					//echo $valor; exit;
					return "'".$valor."'";
				}
				
			} else {
				//echo $this->getName().($valor===0); exit;
				return 'NULL';
			}
		} else {
			if (($this->chave) && (strlen($valor) < 1)) {
				$nID = $this->getUniqueID();
				$this->setValue(ID_NO.'.'.$nID);
				return "'".ID_NO.'.'.$nID."'";
			} else {
				return "'".$this->removeAspas(utf8_decode($valor))."'";
			}
		}
	}


	function getValue2UserJS() {
		if ($this->isNumeric()) {
			$retorno = floatVal($this->getValue());
		} else if ($this->isDataHora()) {
			//echo date('c', strtotime($campo->getValue())); exit;
			$vTemp = $this->getValue();
			$retorno = strtotime($vTemp)*1000*1000; //date('c', strtotime($campo->getValue()));
		} else if ($this->isData()) {
			$vTemp = $this->getValue();
			if (($vTemp == '') || ($vTemp == '0000-00-00')) {
				//if ($nomeCampo == 'nascimento') {echo $vTemp;}
				$retorno = ''.$vTemp;
			} else {
				//echo $vTemp; 
				$retorno = (strtotime($vTemp)*1000)+(4*60*60*1000); //adic. 4 horas
				//echo $retorno[$nomeCampo]; exit;
			}
			//echo $retorno[$nomeCampo]; exit;
		} else {
			$retorno = $this->getValue();
		}
		return $retorno;
	}
	
	
		
	//Funções SETs
	//ajusta_mask define a máscara ideal para o campo automaticamente
	function setType($type,$ajusta_mask=true) {
		if (!in_array($type,$this->arrayTiposString,false)) {
			$this->string = false;
		}
		
		$this->type = $type;
		if ($ajusta_mask) { 
			if ($type == 'cpf')       {$this->mask_type = 'mask-cpf-cnpj'; }
			else if ($type == 'cnpj')      {$this->mask_type = 'mask-cpf-cnpj'; }
			else if ($type == 'cpf_cnpj')      {$this->mask_type = 'mask-cpf-cnpj'; }
			else if ($type == 'tel_cel')   {$this->mask_type = 'mask-telefone'; }
			else if ($type == 'email')     {$this->mask_type = 'mask-email'; }
			else if ($type == 'data')      {$this->mask_type = 'mask-date'; }
			else if ($type == 'cep')      {$this->mask_type = 'mask-cep'; }
			else {$this->mask_type = '';}
		}
	}

	function setMaskEdit($mask_edit)      { $this->mask_edit = $mask_edit;}
	function setRequire($required)        { $this->required = $required;}
	function setValue($value)             { $this->value = $value;}
	function setMaxLength($max_length)    { $this->max_length = $max_length; }
	function setDisplayLength($max_length) { $this->display_length = $max_length; }
	function setDescription($description) { $this->description = $description; }
	function setReadOnly($readonly)       { $this->readonly = $readonly; }
	function setName($name)               { $this->name = $name; }
	function setDisplayName($displayname) { $this->displayname = $displayname; }
	function setVisible($visible)         { $this->visible = $visible; }
	function setChave($chave=false)         { $this->chave = $chave; }
	//Função recebe uma matriz contendo: Legenda,Valor
	function setOptions($arrOptions)      { $this->options = $arrOptions; }
	//Comando sql deve conter, valor, descrição das options, grupo das options (opicional)
	function setSQLOptions($cmdSQL)      { $this->sql_options = $cmdSQL; }
	function setGeometry($geometry=false)         { $this->geometry = $geometry; }
	function setImage($image=false)         { $this->image= $image; }
	function setDefaultValue($value=false) {$this->defaultValue = $value;}
	function setValueFromBD($v) {
		if ($this->isNumeric()) {
			//$this->value = Util::DecimalBD2BR($v);
			$this->value = $v;
		} else if (($this->isDataHora()) || ($this->isData())) {
			//$this->value = Util::DataBD2Br($v);
			//echo $v; exit;
			if (($v == '0000-00-00') || ($v=='')) {
				$this->value = '';
			} else {
				$this->value = $v;
				//echo $v; exit;
			}
			
		} else {
			$this->value = utf8_encode($v);
		}
	}
	
	function setValueFromUser($v) {
		if ($this->getType() == 'password') {
			if ($this->getValue() != $v)
				$this->setValue(CryptUtil::encrypt($v));
			
			
		} else if ($this->isData()) {  //numero inteiro formato javascript.Date.getTime()
			if (($v == '') || ($v == '0000-00-00')) {
				$this->setValue('');
			} else {
				// multiplicar por 6 horas para forçar ajuste de fuso horario php x javascript
				$this->setValue(date('Y-m-d', ($v/1000) + (60 * 60 * 6)));
			}
			
		} else if ($this->isDataHora()) { //numero inteiro formato javascript.Date.getTime()
			if (($v == '') || ($v == '0000-00-00 00:00:00')) {
				$this->setValue('');
			} else {
				$this->setValue(date('Y-m-d H:i:s', ($v/1000)));
			}
			
		} else if ($this->isHora()) { //numero inteiro formato javascript.Date.getTime()
			if ($v == '') {
				$this->setValue('');
			} else {
				//echo $arr[$nomeCampo]; exit;
				$this->setValue(date('H:i:s', ($v/1000) + (60 * 60 )));
			}
			
		} else {
			$this->setValue($v);
			
		}
	}
		
	
	//Retorna true caso o campo seja String
	function isString() {
		return in_array($this->getType(), $this->arrayTiposString);
	}
	
	//Retorna true caso o campo seja Numerico inclusive integer
	function isNumeric() {
		return in_array($this->getType(), $this->arrayTiposNumeric);
	}
	
	//Retorna true caso o campo seja Inteiro
	function isInteger() {
		return in_array($this->getType(), $this->arrayTiposInteger);
	}
	
	
	//Retorna true caso o campo seja Data / Hora
	function isDataHora() {
		return in_array($this->getType(), $this->arrayTiposDataHora);
	}
	
	//Retorna true caso o campo seja Data / Hora
	function isData() {
		return in_array($this->getType(), $this->arrayTiposData);
	}

	//Retorna true caso o campo seja Data / Hora
	function isHora() {
		return in_array($this->getType(), $this->arrayTiposHora);
	}
	
	function isSN() {
		return $this->getType() == 'SN';
	}
	
	function isEMail() {
		return $this->getType() == 'email';
	}
	
	//retorna true caso o valor do campo seja null, '', ou 0, ou data 0000-00-00 0000-00-00 00:00:00
	function isNullOrBlank() {
		if (trim($this->getValue()) == '') return true;
		if ($this->getValue() == '0') return true;
		if ($this->getValue() == '0000-00-00') return true;
		if ($this->getValue() == '0000-00-00 00:00:00') return true;
		if ($this->getValue() == '00:00:00') return true;
		return false;
	}


	//Função para remover aspas de uma String
	//Necessária para preparar o valor à ser inserido em Banco de Dados
	function removeAspas($str='') {
		if ($str=='') {
			$retorno = $this->getValue();
		} else {
			$retorno = $str;
		}
		return str_ireplace("'","''", $retorno);;
	}
	
	//recebe o nome e o tipo do campo no banco de dados
	//retorna o tipo correspondente nesta classe
	//'cpf', 'cnpj', 'cpf_cnpj', 'tel_cel', 'texto', 'texto_livre', 'email', 'float', 
	//'currency', 'int', 'data', 'hora', 'datahora', 'SN');	
	function associaTipo($tipo, $fieldNameBD,$tamanho) {
		$fieldNameBD = strtolower($fieldNameBD);
		if (($tipo == 'int') || ($tipo == 1)  || ($tipo == 2)  || ($tipo == 3)  || ($tipo == 8)  || ($tipo == 9)) return 'int';
		if (($tipo == 'date')  || ($tipo == 7)  || ($tipo == 10)  || ($tipo == 11)  || ($tipo == 12) ) return 'data';
		if (($tipo == 'real')  || ($tipo == 4)  || ($tipo == 5) || ($tipo == 246)) return 'float';
		if (($tipo == 'string')  || ($tipo == 253)  || ($tipo == 254)  || ($tipo == 252)) {
			if ($tamanho == 1) {
				if ($fieldNameBD != 'sexo') return 'SN'; else return 'sexo';
			}
			if ( strpos( $fieldNameBD,'senha') !== false) return 'password';
			if ( strpos( $fieldNameBD,'password') !== false) return 'password';
			if ( strpos( $fieldNameBD,'cpf') !== false) return 'cpf_cnpj';
			if ( strpos( $fieldNameBD,'cnpj') !== false) return 'cpf_cnpj';
			if ( $fieldNameBD == 'uf') return 'uf';
			if ( stripos( $fieldNameBD,'empresa_uf') !== false) return 'uf';
			if ( stripos($fieldNameBD, 'uf_') !== false) return 'uf';
			if ( strpos( $fieldNameBD,'email') !== false) return 'email';
			if ( strpos( $fieldNameBD,'telefone') !== false) return 'tel_cel';
			if ( strpos( $fieldNameBD,'celular') !== false) return 'tel_cel';
			if ( $fieldNameBD == 'cep') return 'cep';
			if ( strpos( $fieldNameBD,'_cep') !== false) return 'cep';
			if ( strpos( $fieldNameBD,'cep_') !== false) return 'cep';
			if (($tamanho <= 0) || ($tamanho == 65535)) return 'texto_livre';
		}
		//echo $fieldNameBD.$tipo; exit;
		

		return 'texto';
		
	}
	
	function setNull() {
		$this->value = NULL;
	}
	
	function clear() {
		$this->setNull();
	}
	
	//protected $arrayTipos = array ('cpf', 'cnpj', 'cpf_cnpj', 'tel_cel', 'texto', 'texto_livre', 'email', 'float', 'currency', 'int', 'data', 'hora', 'datahora', 'SN'); //Lista de todos os tipos possíveis para esta classe
	
	//Verifica se o valor é válido
	function isValidValue() {
		if ($this->type == 'texto') {
			if ($this->required) {
				if (strlen($this->value) < 1) {
					return false;
				}
			}
		}
		return true;
	}

	public function getUniqueID($prefix='') {
		return StringUtil::getUniqueID();// uniqid($prefix);
	}
	
	public function setUniqueID() {
		$this->setValue($this->getUniqueID());
	}
	
	public function setNow() {
		if ($this->isData()) {
			$this->setValue(date('Y-m-d'));
		} else if ($this->isHora()) {
			$this->setValue(date('H:i:s'));
		} else if ($this->isDataHora()) {
			$this->setValue(date('Y-m-d H:i:s'));
		}
	}

	
}
