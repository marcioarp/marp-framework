<?php

//namespace database;

class Database {
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
	function __construct($pid_banco_dados=false) {
		//conexão ao bd_comum
		if ($pid_banco_dados == 'C') { //comum
			//echo $pid_banco_dados; exit;
			$this->server=BD_COMUM_SERVER;
			$this->user=BD_COMUM_USER;
			$this->password=BD_COMUM_PASSWORD;
			$this->port=BD_COMUM_PORT;
			$this->database_name=BD_COMUM_DATABASE;
			$this->description = 'Conexão ao BD Comum';
			$this->sgdb=BD_COMUM_SGDB;
		} else if ($pid_banco_dados == 'A') { //BD APP Producao
			$this->server=BD_APP_SERVER;
			$this->user=BD_APP_USER;
			$this->password=BD_APP_PASSWORD;
			$this->port=BD_APP_PORT;
			$this->database_name=BD_APP_DATABASE;
			$this->description = 'Conexão entrega';
			$this->sgdb=BD_APP_SGDB;
		} 
	}
}
?>