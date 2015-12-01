<?php
define('HASH_SEGURANCA', 'aaaaaaaaaaaaa');
define('LOJA_NAME', 'Loja Name');
define('SISTEMA', dirname(__FILE__) . '/../sistema/');
define('SISTEMA_LINK', 'http://yourdomain/sistema/');
define('COMPILE', true);
//define('DEFAULT_TIME_ZONE','UTC');
define('DEFAULT_TIME_ZONE','Brazil/East');
date_default_timezone_set(DEFAULT_TIME_ZONE);

//servidor local cep
define('BD_COMUM_LOCAL_SERVER', 'localhost');
define('BD_COMUM_LOCAL_USER', 'root');
define('BD_COMUM_LOCAL_PASSWORD', '');
define('BD_COMUM_LOCAL_PORT', '3306');
define('BD_COMUM_LOCAL_DATABASE', 'bdcomum');
define('BD_COMUM_LOCAL_SGDB', SGDB_MYSQL);

//servidor remoto cep produççao
define('BD_COMUM_PRODUCAO_SERVER', 'cpmy0055.servidorwebfacil.com');
define('BD_COMUM_PRODUCAO_USER', '');
define('BD_COMUM_PRODUCAO_PASSWORD', '');
define('BD_COMUM_PRODUCAOL_PORT', '3306');
define('BD_COMUM_PRODUCAO_DATABASE', '');
define('BD_COMUM_PRODUCAO_SGDB', SGDB_MYSQL);

//servidor local teste
define('BD_APP_LOCAL_SERVER', 'localhost');
define('BD_APP_LOCAL_USER', 'root');
define('BD_APP_LOCAL_PASSWORD', '');
define('BD_APP_LOCAL_PORT', '3306');
define('BD_APP_LOCAL_DATABASE', 'entrega');
define('BD_APP_LOCAL_SGDB', SGDB_MYSQL);

//servidor remoto produção
define('BD_APP_PRODUCAO_SERVER', 'cpmy0055.servidorwebfacil.com');
define('BD_APP_PRODUCAO_USER', '');
define('BD_APP_PRODUCAO_PASSWORD', '');
define('BD_APP_PRODUCAO_PORT', '3306');
define('BD_APP_PRODUCAO_DATABASE', '');
define('BD_APP_PRODUCAO_SGDB', SGDB_MYSQL);


//configuração email automatico
define('MAIL_HOST','mail.centralpedidos.com.br');
define('MAIL_SMTPAuth',true);
define('MAIL_USERNAME','naoresponda@centralpedidos.com.br');
define('MAIL_PASSWORD','');
define('MAIL_SMTPSecure','tls');
define('MAIL_PORT',587);
define('MAIL_CHARSET','UTF-8');
define('MAIL_FROM_MAIL','naoresponda@centralpedidos.com.br');
define('MAIL_FROM_NAME','Central Pedidos - Não Responda');


define('TIPO_CONEXAO_BD', 'T'); //T TESTE - P PRODUCAO
define('TIPO_CONEXAO_BD_COMUM', 'C'); //C LOCAL TESTE - CP PRODUCAO






