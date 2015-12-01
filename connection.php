<?php
if (false) {
	include('util.php');
	include('../model/banco_dados.php');
}


class Connection {
	public $conectado = false;
	public $resource; //Resource de conexão ao BD
	public $bd; //Instancia da classe BancoDados
	protected $erro; //Erro: true / false
	private $utilBD=false; //Classe Util
	
	
	//Métodos//
	//Construtor
	function __construct($pid_banco_dados) {
		$this->bd = new BancoDados($pid_banco_dados);
		if ($pid_banco_dados == 'C') {
			//$this->atualizaNullsAutoIncrement('tab_cep', 'codigo', 'cep');
			//ALTER TABLE `tab_cep`	CHANGE COLUMN `Codigo` `Codigo` INT(11) NOT NULL AUTO_INCREMENT FIRST,	ADD PRIMARY KEY (`Codigo`);
			//ALTER TABLE `tab_bairro` 	CHANGE COLUMN `id_bairro` `id_bairro` INT(11) NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (`id_bairro`);
			//ALTER TABLE `tab_cidades`	CHANGE COLUMN `id_cidade` `id_cidade` INT(11) NOT NULL AUTO_INCREMENT FIRST,	ADD PRIMARY KEY (`id_cidade`);
		}
		//$this->utilBD = new Util($this);
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
			$retorno = mysql_connect($this->bd->server.':'.$this->bd->port,
				$this->bd->user,
				 $this->bd->password 
			);
			$bd = mysql_select_db($this->bd->database_name,$retorno);
			
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
			$rs = mysql_query($sql,$this->resource);
			$this->erro .= mysql_error($this->resource); //
		} else {
			$rs   = pg_query($this->resource,$sql);
			$this->erro .= pg_last_error($this->resource);
		}
		if ($this->erro) {
			//echo $sql;
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
	 		return mysql_close($this->resource);	
		} else {
	 		return pg_close($this->resource);	
		}
	}
	
	function fetchArray($rs) {
		if ($this->bd->sgdb == 'MySQL') {
	 		return mysql_fetch_array($rs);	
		} else {
	 		return pg_fetch_array($rs);			
		}
	}
	
	function fetchAll($rs,$type=MYSQL_BOTH,$encodeUTF8=false) {
		if ($this->bd->sgdb == 'MySQL') {
			$i=0;
			$retorno=false;
	 		while ($v=mysql_fetch_array($rs,$type)) {
	 			if ($encodeUTF8)
	 				$retorno[$i] = array_map('utf8_encode',$v);
				else 
					$retorno[$i]=$v;
				$i++;
	 		}
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
	 		return mysql_num_fields($rs);
		} else {
	 		return pg_num_fields($rs);
		}
	}
	
	function recordCount($rs) {
		if ($this->bd->sgdb == 'MySQL') {
	 		return mysql_num_rows($rs);
		} else {
	 		return pg_num_rows($rs);
		}
	}
	
	function fieldType($rs, $off_set_field) {
		if ($this->bd->sgdb == 'MySQL') {
	 		return mysql_field_type($rs,$off_set_field);
		} else {
	 		return pg_field_type($rs,$off_set_field);
		}
	}
	
	function fieldName($rs, $off_set_field) {
		if ($this->bd->sgdb == 'MySQL') {
	 		return mysql_field_name($rs,$off_set_field);
		} else {
	 		return pg_field_name($rs,$off_set_field);
		}
	}
	
	function fieldTable($rs, $off_set_field) {
		if ($this->bd->sgdb == 'MySQL') {
	 		return mysql_field_table($rs,$off_set_field);
		} else {
	 		return pg_field_table($rs,$off_set_field);
		}
	}
	
	function fieldFlags($rs, $off_set_field) {
		if ($this->bd->sgdb == 'MySQL') {
	 		return mysql_field_flags($rs,$off_set_field);
		} else {
	 		return array(0=>'FUNÇÃO NÃO DISPONÍVEL NESTE SGDB');
		}
	}	

	function fieldSize($rs, $off_set_field) {
		if ($this->bd->sgdb == 'MySQL') {
	 		return mysql_field_len($rs,$off_set_field);
		} else {
	 		return pg_field_size($rs,$off_set_field);
		}
	}	
	
	public function mysql_fetch_all_qualified_array($rs) {
		$j=0;
		while ($v = mysql_fetch_array($rs)) {
			for ($i = 0; $i < mysql_num_fields($rs); ++$i) {
			    $table = mysql_field_table($rs, $i);
			    $field = mysql_field_name($rs, $i);
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
			$v = mysql_fetch_array($rs);
	 		return $v[0];
		} else {
	 		return array(0=>'FUNÇÃO NÃO DISPONÍVEL NESTE SGDB');
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
	
}
?>