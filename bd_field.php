<?php

//Classe para representar os fields do Banco de Dados
class BDField {
	protected $type; //Tipo do campo
	protected $mask_edit; //Máscara para edição
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
	
	protected $arrayTipos = array ('cpf', 'cnpj', 'cpf_cnpj', 'tel_cel', 'texto', 'texto_livre', 'email', 'float', 'currency', 'int', 'data', 'hora', 'datahora', 'SN'); //Lista de todos os tipos possíveis para esta classe
	
	protected $arrayTiposString = array ('cpf', 'cnpj', 'tel_cel', 'texto', 'texto_livre', 'email', 'SN'); //Lista dos tipos String
	 
	protected $arrayTiposNumeric = array ('float', 'currency', 'int'); //Lista dos tipos Inteiros
	protected $arrayTiposDataHora = array ('data', 'hora', 'datahora'); //Lista dos tipos Data/ Hora

	//Métodos//
	//Construtor
	function __construct($tipo='texto',$requerido=false,$name='NomeCampo',$maxLength=1000000) {
		$name = strtolower($name);
		$this->setType($tipo,false);
		$this->mask_edit='';
		$this->required=$requerido;
		$this->value='';
		$this->max_length=$maxLength;//1000000 = 1MB
		$this->name = $name;
		$this->displayname = ucfirst($name);
		
	}
	
	//Funções GETs
	function getType()      { return $this->type; }
	function getMaskEdit()  { return $this->mask_edit; }
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
	
	
	//Função que retonar o valor do campo já preparado para ser inserido em um comando SQL
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
		} if ($this->isDataHora()) {
			if ( (isset($valor) || ($valor === 0)) 
			      && ($valor !== '')
			) {
				//return "'".Util::DataBr2BD($valor)."'"; 
				return "'".$valor."'";
			} else {
				//echo $this->getName().($valor===0); exit;
				return 'NULL';
			}
		} else {
			return "'".$this->removeAspas(utf8_decode($valor))."'";
		}
	}
	
	
		
	//Funções SETs
	//ajusta_mask define a máscara ideal para o campo automaticamente
	function setType($type,$ajusta_mask=true) {
		if (!in_array($type,$this->arrayTiposString,false)) {
			$this->string = false;
		}
		
		$this->type = $type;
		if ($ajusta_mask) { 
			if ($type == 'cpf')       {$this->mask_edit = '###.###.###-##'; }
			if ($type == 'cnpj')      {$this->mask_edit = '###.###.###/####-##'; }
			if ($type == 'tel_cel')   {$this->mask_edit = '(##) #####-###'; }
			if ($type == 'email')     {$this->mask_edit = '*@*'; }
			if ($type == 'currency')  {$this->mask_edit = '###.###.###,##'; }
			if ($type == 'data')      {$this->mask_edit = '##/##/####'; }
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
	function setDefaultValue($value=false) {$this->defaultValue = $value;}
	function setValueFromBD($v) {
		if ($this->isNumeric()) {
			//$this->value = Util::DecimalBD2BR($v);
			$this->value = $v;
		} else if ($this->isDataHora()) {
			//$this->value = Util::DataBD2Br($v);
			$this->value = $v;
		} else {
			$this->value = utf8_encode($v);
		}
	}
	
	//Retorna true caso o campo seja String
	function isString() {
		return in_array($this->getType(), $this->arrayTiposString);
	}
	
	//Retorna true caso o campo seja Numerico
	function isNumeric() {
		return in_array($this->getType(), $this->arrayTiposNumeric);
	}
	
	//Retorna true caso o campo seja Data / Hora
	function isDataHora() {
		return in_array($this->getType(), $this->arrayTiposDataHora);
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
		if ($tipo == 'int') return 'int';
		if ($tipo == 'date') return 'data';
		if ($tipo == 'real') return 'float';
		if ($tipo == 'string') {
			if ($tamanho == 1) return 'SN';
			if ( strpos('cpf', $fieldNameBD) !== false) return 'cpf_cnpj';
			if ( strpos('cnpj', $fieldNameBD) !== false) return 'cpf_cnpj';
			if ($tamanho <= 0) return 'texto_livre';
		}

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
	
}
?>