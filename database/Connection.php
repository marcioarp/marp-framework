<?php

//namespace database;

//require_once(API_PATH.'database/Database.php');
require_once('Database.php');

class Connection {
	public $conectado = false;
	public $resource; //Resource de conexão ao BD
	public $bd; //Instância da classe Database
	protected $erro; //Erro: true / false
	private $utilBD=false; //Classe Util
	
	
	//Métodos//
	//Construtor
	function __construct($idDB=false) {
		if ($idDB != false) {
			$this->bd = new Database($idDB);
		}
	}
	
	
	//Retona o resource do banco de dados
	function getResourceBancoDados() {
		return $this->resource;
	}
	
	//Define a classe Útil
	function setUtilBD($u) {
		$this->utilBD = $u;
	}


	//Conecta ao banco de dados
	//Retorna o resource da conexão
	function conectar() {
		if ($this->bd->sgdb == 'MySQL') {
			$retorno = mysqli_connect($this->bd->server,
				$this->bd->user,
				 $this->bd->password,$this->bd->database_name,$this->bd->port 
			) or die (mysqli_error());
			//$bd = mysqli_select_db($this->bd->database_name,$retorno) or die (mysqli_error());
			
		} else {
			$retorno = pg_connect($this->getStringConexao());
		}

		if (!$retorno) {
			$this->conectado = false;
			$this->erro .= pg_last_error();
			return false;
			exit;
		}
		$this->resource = $retorno;
		$this->conectado = true;
		return $retorno;
	}
	
	
	//Executa um comando SQL
	//Retorna pg_resource
	function runSQL($sql) {
		if (!$this->conectado) {
			$this->conectar();
		}
		$sql = str_replace("\\", "\\\\", $sql);
		if ($this->bd->sgdb == 'MySQL') {
			$rs = mysqli_query($this->resource,$sql) or ($this->erro .= mysqli_error($this->resource));
			$this->erro .= mysqli_error($this->resource); //
		} else {
			$rs   = pg_query($this->resource,$sql);
			$this->erro .= pg_last_error($this->resource);
		}
		if ($this->erro) {
			return false;
		}
		return $rs;
	}
	
	//Retorna erro caso ocorrao ao executar o comando no método runSQL
	function getErro() {
		return $this->erro;
	}
	
	//Retorna a string da conexão
	function getStringConexao() {
		return 
			' host='.$this->bd->server.
			' port='.$this->bd->port.
			' dbname='.$this->bd->database_name.
			' user='.$this->bd->user.
			' password='.$this->bd->password;
	}
	
	
	//Fecha a conexão
	function close(	) {
		if ($this->bd->sgdb == 'MySQL') {
	 		return mysqli_close($this->resource);	
		} else {
	 		return pg_close($this->resource);	
		}
	}
	
	function fetchArray($rs,$type=MYSQL_BOTH,$encodeUTF8=false) {
		if ($this->bd->sgdb == 'MySQL') {
			$v = mysqli_fetch_array($rs,$type);
			if ($encodeUTF8) {
				if (is_array($v))
					return array_map('utf8_encode',$v);
				else 
					return $v;
				
			} else return $v;	
		} else {
	 		return pg_fetch_array($rs);			
		}
	}
	
	function fetchAll($rs,$type=MYSQL_BOTH,$encodeUTF8=true) {
		if ($this->bd->sgdb == 'MySQL') {
			$i=0;
			$retorno=false;
	 		while ($v=mysqli_fetch_array($rs,$type)) {
	 			if ($encodeUTF8)
					if (is_array($v))
	 					$retorno[$i] = array_map('utf8_encode',$v);
					else
						$retorno[$i] = $v;
					
				else 
					$retorno[$i]=$v;
				$i++;
				//echo $i;
	 		}
			//var_dump($retorno); exit;
			return $retorno;
		} else {
			if ($encodeUTF8) {
				return 'erro, encode utf8 não disponível para postgresql';
			}
	 		return pg_fetch_all($rs);			
		}
	}
	
	 
	function getLimit1 () {
		if ($this->bd->sgdb == 'MySQL') {
	 		return " limit 0, 1 ";
		} else {
	 		return ' limit 1 offset 0 ';			
		}
	}
	
	function fieldCount($rs) {
		if ($this->bd->sgdb == 'MySQL') {
	 		return mysqli_num_fields($rs);
		} else {
	 		return pg_num_fields($rs);
		}
	}
	
	function recordCount($rs) {
		if ($this->bd->sgdb == 'MySQL') {
	 		return mysqli_num_rows($rs);
		} else {
	 		return pg_num_rows($rs);
		}
	}
	
	function fieldType($rs, $off_set_field) {
		if ($this->bd->sgdb == 'MySQL') {
	 		$campo = mysqli_fetch_field_direct($rs,$off_set_field);
			return $campo->type;
		} else {
	 		return pg_field_type($rs,$off_set_field);
		}
	}
	
	function fieldName($rs, $off_set_field) {
		if ($this->bd->sgdb == 'MySQL') {
	 		$campo = mysqli_fetch_field_direct($rs,$off_set_field);
			return $campo->name;
		} else {
	 		return pg_field_name($rs,$off_set_field);
		}
	}
	
	function fieldTable($rs, $off_set_field) {
		if ($this->bd->sgdb == 'MySQL') {
	 		$campo = mysqli_fetch_field_direct($rs,$off_set_field);
			return $campo->table;
		} else {
	 		return pg_field_table($rs,$off_set_field);
		}
	}
	
	function fieldFlags($rs, $off_set_field) {
		if ($this->bd->sgdb == 'MySQL') {
	 		$campo = mysqli_fetch_field_direct($rs,$off_set_field);
			return $campo->flags;
		} else {
	 		return array(0=>'FUNÇÃO NÃO DISPONÍVEL NESTE SGDB');
		}
	}	

	function fieldSize($rs, $off_set_field) {
		if ($this->bd->sgdb == 'MySQL') {
	 		$campo = mysqli_fetch_field_direct($rs,$off_set_field);
			//var_dump($campo); exit;
			return $campo->length;
			//return $campo[$off_set_field];
		} else {
	 		return pg_field_size($rs,$off_set_field);
		}
	}	
	
	public function mysql_fetch_all_qualified_array($rs) {
		$j=0;
		while ($v = mysqli_fetch_array($rs)) {
			for ($i = 0; $i < mysqli_num_fields($rs); ++$i) {
			    $table = mysqli_field_table($rs, $i);
			    $field = mysqli_field_name($rs, $i);
			    $retorno[$j][$table][$field] =  utf8_encode( $v[$i] );
			}
			$j++;
		}
		return $retorno;
	}
	
	function lastInsertID() {
		if ($this->bd->sgdb == 'MySQL') {
			$sql = "select last_insert_id()";
			$rs = $this->runSQL($sql);
			if ($rs) {
				$v = mysqli_fetch_array($rs);
	 			return $v[0];
			} else {
				return array(0=>'Nenhum ID encontrado');
			}
		} else {
	 		return array(0=>'FUNÇÃO NÃO DISPONÍVEL NESTE SGDB');
		}
	}
	
	function allFields($rs) {
		if ($this->bd->sgdb == 'MySQL') {
			return mysqli_fetch_fields($rs);
		} else {
			return false;
		}
	}
	
	//Pega o maior valor do campo na tabela
	//e atualizar os valors nulos do mesmo campo com auto_increment
	//Ajuste necessário para adicionar primary key em tabela que não tem pk auto inc
	//Campo referencia é o campo que será usado no where pra identificar cada registro (como se fosse a primary key)
	function atualizaNullsAutoIncrement($tabela, $campo,$campoReferencia) {
		$sql = "select max($campo) from $tabela";
		$rs = $this->runSQL($sql);
		$v = $this->fetchArray($rs);
		$id = $v[0];
		if (!($id>0)) $id=1;
		$sql = "select $campoReferencia from $tabela where $campo is null";
		$rs = $this->runSQL($sql);
		while ($v = $this->fetchArray($rs)) {
			$id++;
			$sql = "update $tabela set $campo = $id where $campoReferencia = '".$v[0]."' and $campo is null	";
			$rs2 = $this->runSQL($sql);
		}				
	}
	
	//executa sql e retorna um array com todos os dados
	public function sql2Array($sql) {
		$rs = $this->runSQl($sql);
		$v = $this->fetchAll($rs,MYSQL_ASSOC,true);
		return $v;
	}
	
	//retornar as linhas afetas pelo  comando sql
	//rs = false, retorna o último comando executado
	public function affectedRows($rs=false) {
		if (!$rs) $rs = $this->resource;
		return mysqli_affected_rows($rs);
	}


	//protected $arrayTipos = array ('password','cep','uf','cpf', 'cnpj', 'cpf_cnpj', 'tel_cel', 'texto', 'texto_livre', 'email', 'float', 'currency', 'int', 'data', 'hora', 'datahora', 'SN'); //Lista de todos os tipos possíveis para esta classe
	public static function mysqlIDDataTypeConvert($id) {
		$tipo="NC";
		if ($id == 1) $tipo='inteiro';
		else if ($id == 2) $tipo='int';
		else if ($id == 3) $tipo='int';
		else if ($id == 4) $tipo='float';
		else if ($id == 5) $tipo='float';
		else if ($id == 7) $tipo='datahora';
		else if ($id == 8) $tipo='int';
		else if ($id == 9) $tipo='int';
		else if ($id == 10) $tipo='data';
		else if ($id == 11) $tipo='hora';
		else if ($id == 12) $tipo='datahora';
		else if ($id == 13) $tipo='int';
		else if ($id == 16) $tipo='int';
		else if ($id == 246) $tipo='float';
		else if ($id == 253) $tipo='texto';
		else if ($id == 254) $tipo='texto';
		return $tipo;
						
				/* 
		$mysql_data_type_hash = array(
		    1=>'tinyint',
		    2=>'smallint',
		    3=>'int',
		    4=>'float',
		    5=>'double',
		    7=>'timestamp',
		    8=>'bigint',
		    9=>'mediumint',
		    10=>'date',
		    11=>'time',
		    12=>'datetime',
		    13=>'year',
		    16=>'bit',
		    //252 is currently mapped to all text and blob types (MySQL 5.0.51a)
		    253=>'varchar',
		    254=>'char',
		    246=>'decimal'
		);
		*/
	}


	/*
	* Retorna o resultado do sql em um formato padrão para já enviar ao usuário 
	* fieldResult é o nome do campo no result que será usado para enviar ao usuário
	*/
	function returnAllToUser($sql,$fieldResult='lista',$type=MYSQL_ASSOC,$utf8=true) {
		$rs = $this->runSQL($sql);

		if (!$this->getErro())  {
			$r["status"] = "ok";
			$r["msg"] = "";
			$r[$fieldResult] = $this->fetchAll($rs,$type,$utf8);
		} else {
			$r["status"] = "Erro";
			$r["msg"] = $this->getErro();
		}
		
		return $r;		
	}
	
}
?>