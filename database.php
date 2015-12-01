<?php


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
			$this->port=BD_APP_PRODUCAO_PORT;
			$this->database_name=BD_APP_PRODUCAO_DATABASE;
			$this->description = 'Conexão entrega';
			$this->sgdb=BD_APP_PRODUCAO_SGDB;
		} else if  ($pid_banco_dados == 'T') { //Teste
			$this->server=BD_APP_LOCAL_SERVER;
			$this->user=BD_APP_LOCAL_USER;
			$this->password=BD_APP_LOCAL_PASSWORD;
			$this->port=BD_APP_LOCAL_PORT;
			$this->database_name=BD_APP_LOCAL_DATABASE;
			$this->description = 'Conexão ao BD Comum';
			$this->sgdb=BD_APP_LOCAL_SGDB;
			
		} 
	}
}
?>