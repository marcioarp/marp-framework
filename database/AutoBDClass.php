<?php 

//namespace database;

//use sistema\components\Backup as BK;
include_once (API_PATH.'sistema/components/backup.php');
include_once (API_PATH.'database/BDField.php');
require_once (API_PATH.'plugin/StringUtil.php');

class AutoBDClass {
	public function createClass($cnx,$tabnome,$pathSalvar) {
			$cr = "\r\n";
			$ntab = "\t";
			$bdf = new BDField();
		
			/*
			if (isset($_GET['ed_tabela'] )) {
				$tabnome = $_GET['ed_tabela'];
				$crud = true;
			} else {
				$tabnome = 'sql';
				$crud = false;
			}
			*/
			$crud = true;
			$retorno['erro_sql'] = '';
			$classRetorno = '<?php'.$cr;
			$classRetorno .= $cr;
			//$classRetorno .= 'namespace sistema\models;'.$cr.$cr;
			//$classRetorno .= 'use database\Query as Query;'.$cr;
			//$classRetorno .= 'use database\BDField as BDField;'.$cr.$cr;
			
			$classRetorno .= 'require_once(API_PATH.\'database/Query.php\');'.$cr;
			$classRetorno .= 'require_once(API_PATH.\'database/BDField.php\');'.$cr.$cr;

			$classNome = str_ireplace('tab_', '', $tabnome);
			$classNome = StringUtil::underline2UC($classNome);
			//exit;
			$classRetorno .= "class ".ucfirst($classNome)."AutoModel extends Query { ".$cr;
			$sql = "select * from $tabnome limit 0,1";
			//echo $sql; exit;
			$rs = $cnx->runSQL($sql);
			
			$retorno['erro_sql'] .= $cnx->getErro();
			//echo $cnx->getErro(); exit;
			$virgula = '';
			$arrCampos = '';
			$arrCamposNome = '';
			//$qtdFields = $cnx->fieldCount($rs); exit;
			for ($i=0;$i<$cnx->fieldCount($rs);$i++) {
				//exit;
				$nome = $cnx->fieldName($rs,$i);
				$nomeFieldBD = strtolower($cnx->fieldName($rs,$i));
				$nome = $nomeFieldBD;// StringUtil::secondUnderline2UC(strtolower($nome));
				$classRetorno .= $ntab.'public $'.$nome.';'.$cr;
				$arrCampos .= $virgula ;
				$arrCamposNome .= $virgula;
				if (($i % 6) == 0) {
					$arrCampos .= $cr.$ntab.$ntab;
					$arrCamposNome .= $cr.$ntab.$ntab;
					
				}
				$arrCampos .=  "'".$nome."'";
				$arrCamposNome .=  "'".$nomeFieldBD."'";
				$virgula = ',';
			}
			//exit;
			$classRetorno .= $ntab . 'public $arrCampos = array('.$arrCamposNome.'); ';
			$classRetorno .= $cr;
			/*
			$classRetorno .= $ntab . 'public $arrCamposNome = array('.$arrCamposNome.'); ';
			$classRetorno .= $cr;
			*/
			$classRetorno .= $ntab.'function __construct ($cnx) { '.$cr;
			$classRetorno .= $ntab.$ntab . 'parent::__construct($cnx);'.$cr;
			$updatecmd = "update ".$tabnome . ' set '.$cr; 
			$virgulaUpdate = '';
			$virgulaSelect = '';
			$selectcmd = "select ".$cr;
			$insertInto = "insert into ".$tabnome . '('.$cr;
			$insertValue = '';
			$whereCmd = "where ";
			$where = '';
			//exit;
			for ($i=0;$i<$cnx->fieldCount($rs);$i++) {
				$tipo = $cnx->fieldType($rs,$i);
				$nomeBD = strtolower( $cnx->fieldName($rs,$i));
				$nome = $nomeBD;//StringUtil::secondUnderline2UC($nomeBD);
				$displayName = StringUtil::underline2UC($nomeBD,' ');
				$displayName = str_replace('Cpf', 'CPF / CNPJ', $displayName);
				$displayName = str_replace('Uf', 'UF', $displayName);
				$displayName = str_replace('Rg', 'RG', $displayName);
				$displayName = str_replace('Cep', 'CEP', $displayName);
				$displayName = str_replace('Num', 'Núm', $displayName);
				$displayName = str_replace('Endereco', 'Endereço', $displayName);
				$displayName = str_replace('Ra ', '', $displayName);
				$displayName = str_replace('Referencia', 'Referência', $displayName);
				$displayName = str_replace('Filiacao', 'Filiação', $displayName);
				$displayName = str_replace('Documentacao', 'Documentação', $displayName);
				$displayName = str_replace('Profissao', 'Profissão', $displayName);
				$displayName = str_replace('Conjuge', 'Cônjuge', $displayName);
				$displayName = str_replace('Crediario', 'Crediário', $displayName);
				$displayName = str_replace('Cod', 'Cód', $displayName);
				$displayName = str_replace('Informacoes', 'Informações', $displayName);
				$displayName = str_replace('Serie', 'Série', $displayName);
				$displayName = str_replace('Cfop', 'CFOP', $displayName);
				$displayName = str_replace('Operacao', 'Operação', $displayName);
				$displayName = str_replace(' De ', ' de ', $displayName);
				$displayName = str_replace('Emissao', 'Emissão', $displayName);
				$displayName = str_replace('Servico', 'Serviço', $displayName);
				$displayName = str_replace('Impressao', 'Impressão', $displayName);
				$displayName = str_replace('Contingencia', 'Contingência', $displayName);
				$displayName = str_replace('Prestacao', 'Prestação', $displayName);
				$displayName = str_replace('Municipio', 'Município', $displayName);
				$displayName = str_replace('Caracteristica ', 'Característica', $displayName);
				$displayName = str_replace('Previsao', 'Previsão', $displayName);
				$displayName = str_replace('Opcao', 'Opção', $displayName);
				$displayName = str_replace('Estacao', 'Estação', $displayName);
				$displayName = str_replace('Cnpj', 'CNPJ', $displayName);
				$displayName = str_replace('Destinatario', 'Destinatário', $displayName);
				$displayName = str_replace('Val ', 'Val.', $displayName);
				$displayName = str_replace('Cte ', 'CTe ', $displayName);
				$displayName = str_replace('Icms', 'ICMS', $displayName);
				$displayName = str_replace('Rodoviario', 'Rodoviário', $displayName);
				$displayName = str_replace('Rntrc', 'RNTRC', $displayName);
				$displayName = str_replace('Ciot', 'CIOT', $displayName);
				$displayName = str_replace('Ie ', 'IE ', $displayName);
				if ($displayName == 'Ie') $displayName = 'IE';
				$displayName = str_replace('Id ', 'ID ', $displayName);
				$displayName = str_replace('Spc', 'SPC', $displayName);
				$displayName = str_replace('Descricao', 'Descrição', $displayName);
				$displayName = str_replace('Saida', 'Saída', $displayName);
				$displayName = str_replace('Pagina', 'Página', $displayName);
				$displayName = str_replace('Certidao', 'Certidão', $displayName);
				$displayName = str_replace('Agencia', 'Agência', $displayName);
				$displayName = str_replace('Digito', 'Dígito', $displayName);
				$displayName = str_replace('Convenio', 'Convênio', $displayName);
				$displayName = str_replace(' Mes', ' Mês', $displayName);
				$displayName = str_replace('itulo', 'ítulo', $displayName);
				$displayName = str_replace('iculo', 'ículo', $displayName);
				$displayName = str_replace('Credito', 'Crédito', $displayName);
				$displayName = str_replace('Especie', 'Espécie', $displayName);
				
				if ($displayName == 'Ci') $displayName = 'CI';
				
				$tabela = $cnx->fieldTable($rs,$i);
				$flags = $cnx->fieldFlags($rs,$i);
				$tam = $cnx->fieldSize($rs,$i);
				//echo $tam; exit;
				$tipoField = $bdf->associaTipo($tipo,$nome,$tam);
				
				$requerido = 'false';
				$chave=false;
				$readonly=false;
				if ($flags & 1) $requerido='true'; //NOT NULL
				if ($flags & 2) $chave=true; //PRIMARY KEY
				if ($flags & 512) {$chave=true; $readonly = true; } //AUTO INCREMENT
		
				//print_r($flags);
				//$classRetorno .= $tabela.':'.$nome.':'.$tipo.$cr;
				$classRetorno .= $ntab.$ntab.'$this->'.$nome .' = new BDField('."'$tipoField'".
					",".$requerido.",'".$nomeBD."',".$tam.
				');'.$cr; //'.$tipo.' - '.$tam.$cr;
				if ($chave) {
					$classRetorno .= $ntab.$ntab.'$this->'.$nome.'->setChave(true);'.$cr;
					$where .= $whereCmd . $nomeBD . ' = :'.$nome.":";
					$whereCmd = 'and';
					$selectcmd .= $ntab.$ntab.$ntab.$virgulaSelect.' '.$nomeBD.$cr;
					$virgulaSelect = ',';
					$insertValue .= $ntab.$ntab.$ntab.$virgulaUpdate.' :'.$nome.':,'.$cr;
					$insertInto .= $ntab.$ntab.$ntab.$virgulaUpdate.' '.$nomeBD.','.$cr;
					
				} else {
					$updatecmd .= $ntab.$ntab.$ntab.$virgulaUpdate.' '.$nomeBD.' = :'.$nome.":".$cr;
					$insertValue .= $ntab.$ntab.$ntab.$virgulaUpdate.' :'.$nome.':'.$cr;
					/*	
					if (($tipoField == 'int') || ($tipoField == 'float'))  {
						$updatecmd .= $ntab.$ntab.$ntab.$virgulaUpdate.' '.$nome.' = :'.$nome.":".$cr;
						$insertValue .= $ntab.$ntab.$ntab.$virgulaUpdate.' :'.$nome.':'.$cr;
						
					}  else {
						$updatecmd .= $ntab.$ntab.$ntab.$virgulaUpdate.' '.$nome.' = \':'.$nome.":'".$cr;
						$insertValue .= $ntab.$ntab.$ntab.$virgulaUpdate.' \':'.$nome.':\''.$cr;
					}
					 * 
					 */
					$selectcmd .= $ntab.$ntab.$ntab.$virgulaSelect.' '.$nomeBD.$cr;
					$insertInto .= $ntab.$ntab.$ntab.$virgulaUpdate.' '.$nomeBD.$cr;
					$virgulaUpdate = ',';
					$virgulaSelect = ',';
				}
				if ($readonly) {
					$classRetorno .= $ntab.$ntab.'$this->'.$nome.'->setReadOnly(true);'.$cr;	
				}
				$classRetorno .= $ntab.$ntab.'$this->'.$nome.'->setDisplayName(\''.$displayName.'\');'.$cr;	
				$classRetorno .= $ntab.$ntab.'$this->'.$nome.'->setDescription(\''.$displayName.'\');'.$cr;
				if ((substr($nome, 0,3)) == 'ra_') {
					$classRetorno .= $ntab.$ntab.'$this->'.$nome.
					'->setSQLOptions(\'select valor,descricao from registro_auxiliar where grupo = \\\''.
					$nome.'\\\' and ativo=\\\'S\\\' \');'.$cr;
				}
				
				$sqlDefault = "SHOW FULL COLUMNS FROM ".$tabnome." where field = '".$nome."'";
				$vDefault = mysqli_fetch_array($cnx->runSQL($sqlDefault));
				if (strlen($vDefault['Default']) > 0) {
					$classRetorno .= $ntab.$ntab.'$this->'.$nome.'->setDefaultValue(\''.$vDefault['Default'].'\');'.$cr;
					$classRetorno .= $ntab.$ntab.'$this->'.$nome.'->setValue(\''.$vDefault['Default'].'\');'.$cr;
				}
				$classRetorno .= $cr;
			}
		
			$updatecmd .= $ntab.$ntab.$where;
			$selectcmd .= $ntab.$ntab.'from ' . $tabnome .$cr; 
			$selectcmd .= $ntab.$ntab;	
			$insertcmd = $insertInto.$ntab.$ntab.') values ('.$cr.$insertValue.$ntab.$ntab.')';
			$deletecmd = "delete from ".$tabnome.' '.$where;
			if ($crud) {
				$classRetorno .= $ntab.$ntab.'$this->sqlUpdate = "'.$updatecmd.'";'.$cr;
				$classRetorno .= $ntab.$ntab.'$this->sqlSelect = "'.$selectcmd.'";'.$cr;
				$classRetorno .= $ntab.$ntab.'$this->sqlInsert = "'.$insertcmd.'";'.$cr;
				$classRetorno .= $ntab.$ntab.'$this->sqlDelete = "'.$deletecmd.'";'.$cr;
			}	
			//$classRetorno .= $ntab.$ntab.'$this->outrasDefinicoes();'.$cr;
			
			//$classRetorno .= $ntab.$ntab.'$this->setSQLs();'.$cr;
			$classRetorno .= $ntab."}".$cr;
			if (!$crud) {
				$classRetorno .= $cr.$cr.$cr;
				$classRetorno .= $ntab.'//Função executa a busca e associa o retorno ao obj.'.$cr;
				$classRetorno .= $ntab.'function buscaPersonalizada($p) {'.$cr;
				$sql = str_ireplace("\n", "\n".$ntab.$ntab.$ntab, $sql);
				$classRetorno .= $ntab.$ntab.'$sql = "'.$sql.'";'.$cr;
				$classRetorno .= $ntab.$ntab.'return $this->buscaSQL($sql);'.$cr;
				$classRetorno .= $ntab.'}'.$cr;
			}
			//$classRetorno .= $ntab.'//Métodos do Usuário'.$cr;
			//$classRetorno .= $ntab.'//Função disparada pelo método construtor'.$cr;
			//$classRetorno .= $ntab.'//Utilize esta função para definir outras propriedades de BDFields'.$cr;
			//$classRetorno .= $ntab . 'public function outrasDefinicoes() {'.$cr.$ntab.'}'.$cr;


			$classRetorno .= "}".$cr.$cr;

			//echo $pathSalvar; exit;
			if (isset($pathSalvar)) {
				$arq = $pathSalvar.$classNome.'AutoModel.php';
				if (file_exists($arq)) {
					$param[0] = $classNome.'AutoModel';
					$bk = Backup::model($param);
					if ($bk['status'] != 'ok') {
						echo "Erro fatal, não foi possível fazer backup do arq. antterior, Autoclass linha 176";
						print_r($bk);
						//não foi salvo
						exit;
						return $classRetorno;
					}
				}
				$f = fopen($arq, 'w');
				fputs($f, $classRetorno, strlen($classRetorno));
				fclose($f);
				
				//arquivo extends em branco caso não exista


				//MODEL EXTENDS AUTO MODEL
				$arq = $pathSalvar.$classNome.'.php';
				if (!file_exists($arq)) {
					$classExtends = '<?php'.$cr.$cr;
					//$classExtends .= 'namespace sistema\\models;'.$cr.$cr;
					$classExtends .= 'require_once(API_PATH.\'sistema/models/'.$classNome.'AutoModel.php\');'.$cr.$cr;
					$classExtends .= 'class '.$classNome.'Model extends '.$classNome.'AutoModel {'.$cr;
					$classExtends .= $ntab.'function __construct ($cnx) {'.$cr; 
					$classExtends .= $ntab.$ntab.'parent::__construct($cnx);'.$cr;
					
					for ($i=0;$i<$cnx->fieldCount($rs);$i++) {
						$tipo = $cnx->fieldType($rs,$i);
						$nomeBD = strtolower( $cnx->fieldName($rs,$i));
						$id = substr($nomeBD, 0,3);
						$nomeTab = substr($nomeBD, 3,200);
						if ($id == 'id_') {
							$cmdSQL = "select $nomeBD, $nomeBD from $nomeTab";
							$classExtends .= $ntab.$ntab.'$this->'.$nomeBD.'->setSQLOptions("'.$cmdSQL.'");'.$cr;
						}
					}
					
					$classExtends .= $ntab.'}'.$cr;
					
					$classExtends .= '}'.$cr;
					
					$f = fopen($arq, 'w');
					fputs($f, $classExtends, strlen($classExtends));
					fclose($f);
				}
				
			}
			return $classRetorno;		
	}


}
