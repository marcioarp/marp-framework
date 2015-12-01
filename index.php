<?php
		require_once('define.php');
		require_once('../app/config.php');
		require_once('connection.php');
		require_once('util.php');
		require_once('database.php');	
		require_once('bd_field.php');	
		require_once('query.php');	
		$cnx = new Connection('T');	
		$util = new Util($cnx);
		
		?>
		<!doctype html>
		<html>
		<head>
		<meta charset="utf-8">
		<title>Documento sem título</title>
		</head>
		
		<body>
		<form action="index.php?gera_query_class">
		Gerar query, tabela: <input type="text" name='ed_tabela' />
		<input type="submit" value="ok" />
		</form>
		
		
		<form action="index.php">
		Gerar View Ionic: <input type="text" name='ed_tab_ionic' />
		<input type="submit" value="ok" />
		</form>
		
		<form action="index.php?gera_query_class">
		Gerar query: SQL:<textarea name="sql" rowrows="10" cols="50"> </textarea>
		<input type="submit" value="ok" />
		</form>
		
		<p></p>
		<form action="index.php?gerar_layout_padrao">
		layout: tabela:<input type="text" name='ed_tabela_layout' />
		<input type="submit" value="ok" />
		</form>
		
		
		
		<?php
		//$conta = new ContaItens($cnx);
		
		if (isset($_GET['ed_tabela'])) {
			$tab = $_GET['ed_tabela'];
			$sql = "select * from ".$tab.$cnx->getLimit1();
			geraClasse($sql,$cnx, $util);
		}
		
		if (isset($_GET['ed_tab_ionic'])) {
			$tab = $_GET['ed_tab_ionic'];
			$sql = "select * from ".$tab.$cnx->getLimit1();
			geraIonicView($sql,$cnx, $util);
		}
		
		
		if (isset($_GET['ed_tabela_layout'])) {
			$tab = $_GET['ed_tabela_layout'];
			$sql = "select * from ".$tab.$cnx->getLimit1();
			geraLayout($sql,$cnx, $util);
		}
		
		
		if (isset($_GET['sql'])) {
			$sql = $_GET['sql'];
			geraClasse($sql,$cnx,$util);
		}
		
		
		
		function geraLayout($sql,$cnx,$util) {
			if (false) {
				$cnx = new Conexao;
			}
			$rs = $cnx->runSQL($sql);
			
			$virgula = '';
			$arrCampos = '';
			?><textarea rows="30" cols="150" >v1.00|||<?=$cnx->fieldName($rs,0)?>|||<?php
			for ($i=0;$i<$cnx->fieldCount($rs);$i++) {
				$nome = $cnx->fieldName($rs,$i);
				echo $virgula .strtolower($nome);
				$virgula = ',';
			}
			?></textarea><?php
			
			
		}
		
		function geraIonicView($sql,$cnx,$util) {
			if (false) {
				$cnx = new Conexao;
			}
			$rs = $cnx->runSQL($sql);
			
			$virgula = '';
			$arrCampos = '';
			?><textarea rows="30" cols="150" >
		<ion-view view-title="<?=ucfirst($_GET['ed_tab_ionic'])?>">
		  <ion-content>
		  <div class="banner">
		<img src="img/banner2.png" width="400" height="180">
		</div>
		
		<div class="card">
		  <div class="item item-divider">
		    Cadastro de <?=ucfirst($_GET['ed_tab_ionic'])?>
		  </div>
		  <div  class="item item-text-wrap">
		    <div>
				<div class="list">
				<?php
				for ($i=0;$i<$cnx->fieldCount($rs);$i++) {
					$nome = $cnx->fieldName($rs,$i);
					?>
					  <label class="item item-input item-floating-label">
					    <span class="input-label"><?=ucfirst($nome)?></span>
					    <input type="text" placeholder="<?=ucfirst($nome)?>" ng-model="<?=$_GET['ed_tab_ionic'].".".
					    strtolower($nome)?>">
					  </label>
					<?php
				}
				?>
				</div>    	
		    	<button class="button button-full button-positive" ng-click="salvar()">
		  			Salvar
				</button>
		    </div>  	
		  </div>
		</div>  
		</ion-content>
		</ion-view>
		
		</textarea><?php
		}
		function geraClasse($sql,$cnx,$util) {
			$cr = "\r\n";
			$ntab = "\t";
			$bdf = new BDField();
		
			?><textarea rows="30" cols="150" ><?php
			
			if (isset($_GET['ed_tabela'] )) {
				$tabnome = $_GET['ed_tabela'];
				$crud = true;
			} else {
				$tabnome = 'sql';
				$crud = false;
			}
			echo '<?php'.$cr;
			echo $cr;
			echo "class ".ucfirst($tabnome)."Model extends Query { ".$cr;
			$rs = $cnx->runSQL($sql);
			$virgula = '';
			$arrCampos = '';
			for ($i=0;$i<$cnx->fieldCount($rs);$i++) {
				$nome = $cnx->fieldName($rs,$i);
				echo $ntab.'public $'.strtolower($nome).';'.$cr;
				$arrCampos .= $virgula ."'" . strtolower($nome)."'";
				$virgula = ',';
				
			}
			echo $ntab . 'public $arrCampos = array('.$arrCampos.'); ';
			echo $cr;
			echo $ntab.'function __construct ($cnx) { '.$cr;
			echo $ntab.$ntab . 'parent::__construct($cnx);'.$cr;
			$updatecmd = "update ".$tabnome . ' set '.$cr; 
			$virgulaUpdate = '';
			$virgulaSelect = '';
			$selectcmd = "select ".$cr;
			$insertInto = "insert into ".$tabnome . '('.$cr;
			$insertValue = '';
			$whereCmd = "where ";
			$where = '';
			for ($i=0;$i<$cnx->fieldCount($rs);$i++) {
				$tipo = $cnx->fieldType($rs,$i);
				$nome = strtolower( $cnx->fieldName($rs,$i));
				$displayName = ucfirst($nome);
				$tabela = $cnx->fieldTable($rs,$i);
				$flags = explode(' ',$cnx->fieldFlags($rs,$i));
				$tam = $cnx->fieldSize($rs,$i);
				$tipoField = $bdf->associaTipo($tipo,$nome,$tam);
				
				$requerido = 'false';
				$chave=false;
				$readonly=false;
				if ($flags)
					for ($j=0;$j<sizeof($flags);$j++) {
						if ($flags[$j] == 'not_null') $requerido='true';
						if ($flags[$j] == 'primary_key') $chave=true;
						if ($flags[$j] == 'auto_increment') {$chave=true; $readonly = true; }
						//echo $flags[$j].$cr;
					}
		
				//print_r($flags);
				//echo $tabela.':'.$nome.':'.$tipo.$cr;
				echo $ntab.$ntab.'$this->'.$nome .' = new BDField('."'$tipoField'".
					",".$requerido.",'".$nome."',".$tam.
				');'.$cr; //'.$tipo.' - '.$tam.$cr;
				if ($chave) {
					echo $ntab.$ntab.'$this->'.$nome.'->setChave(true);'.$cr;
					$where .= $whereCmd . $nome . ' = :'.$nome.":";
					$whereCmd = 'and';
					$selectcmd .= $ntab.$ntab.$ntab.$virgulaSelect.' '.$nome.$cr;
					$virgulaSelect = ',';
				} else {
					$updatecmd .= $ntab.$ntab.$ntab.$virgulaUpdate.' '.$nome.' = :'.$nome.":".$cr;
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
					$selectcmd .= $ntab.$ntab.$ntab.$virgulaSelect.' '.$nome.$cr;
					$insertInto .= $ntab.$ntab.$ntab.$virgulaUpdate.' '.$nome.$cr;
					$virgulaUpdate = ',';
					$virgulaSelect = ',';
				}
				if ($readonly) {
					echo $ntab.$ntab.'$this->'.$nome.'->setReadOnly(true);'.$cr;	
				}
				echo $ntab.$ntab.'$this->'.$nome.'->setDisplayName(\''.$displayName.'\');'.$cr;	
				echo $ntab.$ntab.'$this->'.$nome.'->setDescription(\''.$displayName.'\');'.$cr;
				$sqlDefault = "SHOW FULL COLUMNS FROM ".$tabnome." where field = '".$nome."'";
				$vDefault = mysql_fetch_array($cnx->runSQL($sqlDefault));
				if (strlen($vDefault['Default']) > 0) {
					echo $ntab.$ntab.'$this->'.$nome.'->setDefaultValue(\''.$vDefault['Default'].'\');'.$cr;
					echo $ntab.$ntab.'$this->'.$nome.'->setValue(\''.$vDefault['Default'].'\');'.$cr;
				}  	
				echo $cr;
			}
		
			$updatecmd .= $ntab.$ntab.$where;
			$selectcmd .= $ntab.$ntab.'from ' . $tabnome .$cr; 
			$selectcmd .= $ntab.$ntab;	
			$insertcmd = $insertInto.$ntab.$ntab.') values ('.$cr.$insertValue.$ntab.$ntab.')';
			$deletecmd = "delete from ".$tabnome.' '.$where;
			if ($crud) {
				echo $ntab.$ntab.'$this->sqlUpdate = "'.$updatecmd.'";'.$cr;
				echo $ntab.$ntab.'$this->sqlSelect = "'.$selectcmd.'";'.$cr;
				echo $ntab.$ntab.'$this->sqlInsert = "'.$insertcmd.'";'.$cr;
				echo $ntab.$ntab.'$this->sqlDelete = "'.$deletecmd.'";'.$cr;
			}	
			echo $ntab.$ntab.'//$this->outrasDefinicoes();'.$cr;
			
			//echo $ntab.$ntab.'$this->setSQLs();'.$cr;
			echo $ntab."}".$cr;
			if (!$crud) {
				echo $cr.$cr.$cr;
				echo $ntab.'//Função executa a busca e associa o retorno ao obj.'.$cr;
				echo $ntab.'function buscaPersonalizada($p) {'.$cr;
				$sql = str_ireplace("\n", "\n".$ntab.$ntab.$ntab, $sql);
				echo $ntab.$ntab.'$sql = "'.$sql.'";'.$cr;
				echo $ntab.$ntab.'return $this->buscaSQL($sql);'.$cr;
				echo $ntab.'}'.$cr;
			}
			echo "}".$cr.$cr;
			echo '?>'.$cr;
		
		
		
			?></textarea><?php
		}
		
		?>
		</body>
		</html>		
