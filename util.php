<?php 

//Esta classe contém métodos necessário para executar diversas funções comuns em todo sistema.
class Util {
	private $conexao; //Conexão ao BD postgres do 
	protected $erro=false; //Armazenar erro?
	public $log; //log de erros
	
	//Métodos//
	
	//Construtor
	function __construct($pConexao) {
		if (false) {
			$this->conexao = new Conexao;
		}
		$this->conexao = $pConexao;
	}
	
	//Retorna erro
	function getErro() {
		return $this->erro;
	}

	//Registra o log 
	//Inclui data / hora no log
	function registraLog($log) {
		$this->log .= '<b>'.date('H:i:s').'</b> - '.$log." <br>\r\n";
	}
	
	static function timeUTC2Local($data) {
		$data = strtotime($data);
		$retorno = date('H:i:s',$data);
		return $retorno;
	}
	

	static function dateUTC2Local($data) {
		$data = strtotime($data);
		$retorno = date('Y-m-d',$data);
		return $retorno;
	}

	static function dateTimeUTC2Local($data) {
		$data = strtotime($data);
		$retorno = date('Y-m-d H:i:s',$data);
		return $retorno;
	}

	//Converte data do formato utilizado em Banco de Dados para BR 
	static function DataBD2Br($data) {
		$Datahora = explode(" ",$data);
		$dt = $Datahora[0];
		if (!isset($Datahora[1])) $Datahora[1]='';
		$hr = $Datahora[1];
		$retorno = implode("/",array_reverse(explode("-",$dt)));
		if (strlen($hr) > 1) 
			$retorno .= " ".$hr;
		return $retorno;
	}

	static function lcfirst($str) {
		$str[0] = strtolower($str[0]);
		return $str;
	}

	//Converte data do formato BR para o formato utilizado em Banco de Dados
	static function DataBr2BD($data) {
		$Datahora = explode(" ",$data);
		$dt = $Datahora[0];
		if (isset($Datahora[1])) {
			$hr = $Datahora[1];
		} else {
			$hr = '';
		}
		//if (!isset($Datahora[1])) $Datahora[1]='';
		$retorno = implode("-",array_reverse(explode("/",$dt)));
		if (strlen($hr) > 1) 
			$retorno .= " ".$hr;
		return $retorno;
	}
	
	//Converte número do formato BR TO US
	static function DecimalBr2BD($valor) {
		 return (str_replace(",",".",$valor));	
	}
	
	//Converte número do formato US para BR
	static function DecimalBD2BR($valor) {
		 return (str_replace(".",",",$valor));	
	}
	
	//Converte número do formato US para BR com R$
	static function DecimalBD2Moeda($valor) {
		return 'R$ '. number_format($valor,2,',','.');
	}
	
	
	//Converte o valor do Postgres para formato BR de acordo com o tipo
	//Tipo: timestamp / float8
	function ConverterPg2Br($campo,$tipo) {
		$retorno = $campo;
		if ($tipo == 'timestamp') {
			$retorno = $this->DataBD2Br($campo);
		} else if ($tipo == 'float8') {
			$retorno = $this->DecimalBD2Br($campo);
		}
		return $retorno;
		
	}
	
	
	
	//Remove acento da string
	static function removeAcento($texto) {
		return preg_replace("[^a-zA-Z0-9 ]", "", strtr($texto, "áàãâéêíóôõúüçÁÀÃÂÉÊÍÓÔÕÚÜÇ", "aaaaeeiooouucAAAAEEIOOOUUC"));
	}
	

	
	//Retona a moeda por extenso
	static function MoedaPorExtenso($valor=0,$tipo=0,$caixa="alta") {
		
		$valor = strval($valor);
		$valor = str_replace(",",".",$valor);
	
		if($tipo==1){
		
		
		$singular = array("centavo", "real", "mil", "milhão", "bilhão", "trilhão", "quatrilhão");
		$plural = array("centavos", "reais", "mil", "milhões", "bilhões", "trilhões",
		"quatrilhões");
		}else{
		
		$pos   = strpos($valor,".");
		$valor = substr($valor,0,$pos);
		
		$singular = array("", "", "mil", "milhão", "bilhão", "trilhão", "quatrilhão");
		$plural = array("", "", "mil", "milhões", "bilhões", "trilhões",
		"quatrilhões");
		}
		
	
		$c = array("", "cem", "duzentos", "trezentos", "quatrocentos",
			"quinhentos", "seiscentos", "setecentos", "oitocentos", "novecentos");
		$d = array("", "dez", "vinte", "trinta", "quarenta", "cinquenta",
			"sessenta", "setenta", "oitenta", "noventa");
		$d10 = array("dez", "onze", "doze", "treze", "quatorze", "quinze",
			"dezesseis", "dezesete", "dezoito", "dezenove");
		$u = array("", "um", "dois", "três", "quatro", "cinco", "seis",
			"sete", "oito", "nove");
	
		$z=0;
	
		$valor = number_format($valor, 2, ".", ".");
		$inteiro = explode(".", $valor);
		for($i=0;$i<count($inteiro);$i++)
			for($ii=strlen($inteiro[$i]);$ii<3;$ii++)
				$inteiro[$i] = "0".$inteiro[$i];
	
		$fim = count($inteiro) - ($inteiro[count($inteiro)-1] > 0 ? 1 : 2);
		for ($i=0;$i<count($inteiro);$i++) {
			$valor = $inteiro[$i];
			$rc = (($valor > 100) && ($valor < 200)) ? "cento" : $c[$valor[0]];
			$rd = ($valor[1] < 2) ? "" : $d[$valor[1]];
			$ru = ($valor > 0) ? (($valor[1] == 1) ? $d10[$valor[2]] : $u[$valor[2]]) : "";
	
			$r = $rc.(($rc && ($rd || $ru)) ? " e " : "").$rd.(($rd && $ru) ? " e " : "").$ru;
			$t = count($inteiro)-1-$i;
			$r .= $r ? " ".($valor > 1 ? $plural[$t] : $singular[$t]) : "";
			if ($valor == "000")$z++; elseif ($z > 0) $z--;
			if (($t==1) && ($z>0) && ($inteiro[0] > 0)) $r .= (($z>1) ? " de " : "").$plural[$t];
			if ($r) $rt = $rt . ((($i > 0) && ($i <= $fim) && ($inteiro[0] > 0) && ($z < 1)) ? ( ($i < $fim) ? " e " : " e ") : " ") . $r;
		}
		
		if($caixa=="alta"){
		$rt = strtoupper($rt);
		}
		$maiusculas = array("Á","À","Â","Ã","É","Ê","Í","Ó","Ô","Õ","Ú","Û");
		$minusculas = array("á","à","â","ã","é","ê","í","ó","ô","õ","ú","û");
		
		
		for($i=0;$i<count($maiusculas);$i++){
		
			$rt = preg_replace($minusculas[$i],$maiusculas[$i],$rt);	
		}     
		
		return $rt;                      
			
	
	}
	
	//retona a extenççao de um arquivo
	static function getExtensaoArquivo($arquivo) {
		return strtolower(end(explode(".", $arquivo)));
	}

	
	
	// Retorna horário local de uma UF em GMT
	// por ex:
	//   -2.5 subtrair duas horas e meia
	//   +3   somar 3 horas
	//   -3   horário de Brasília 
	// considerarHorarioVerao = true irá somar uma hora 
	// em caso da UF adotar horário de verão
	//ATENÇÃO
	//as ilhas com fuso -2 (Fernando de Noronha, São Pedro, etc) não estão contemplados neste método
	// **** IMPORTANTE  ****
	// Tabelas necessárias se encontram apenas no servidor avalanche
	//
	function BuscarFusoHorario($uf,$considerarHorarioVerao=true,$dataReferencia='0000-00-00' ) {
		$sql = 'select fuso_horario from  shp_br_estados uf 
			where "UF" = \''.$uf.'\'
		';
		$rs = $this->conexao->runSQL($sql);
		$vFuso = pg_fetch_array($rs);
		$ajustaFuso = $vFuso[0];
		//print_r($vFuso);
		if ($dataReferencia == '0000-00-00') $dataReferencia = date('Y-m-d H:i:s');
		
		if ($considerarHorarioVerao) {
			$sql = "select count(*) from horario_verao_uf hvu
				inner join horario_verao hv  on hvu.id_horarioverao = hv.id
				where uf = '$uf' and inicio <= '$dataReferencia' and fim >= '$dataReferencia'
			";
			
			$this->registraLog($sql);
			
			$rs = $this->conexao->runSQL($sql);
			//echo pg_last_error($bd);
			$vFuso = pg_fetch_array($rs);
			if ($vFuso[0] > 0) {
				$ajustaFuso += 1;
			}
			$this->registraLog($vFuso[0]);
		}
		
		$this->registraLog($ajustaFuso);
		$ajustaFusoInt = intval($ajustaFuso);
		
		$strSoma = $ajustaFusoInt.' hours';
		
		$retorno = date('Y-m-d H:i:s', strtotime($dataReferencia.' '.$strSoma));
		/*
		echo $dataReferencia.'<br>';	
		echo $dataReferencia.' '.$ajustaFusoInt.' hours'.'<br>';
		echo $retorno.'<br>';	
		echo $ajustaFusoInt.'<br>';
		echo $ajustaFuso.'<br>';
		*/
		return $retorno;
	}
	
	
	//Substitui " por \", ' por \" e \ por \\ dentro da string
	//Dever ser usada para enviar comandos SQL ao postgres.
	static function RemoveAspas($texto) {
		//$retorno  = str_replace('\\','\\\\',$texto);
		//$retorno = str_replace('"','\"',$retorno);
		//$retorno = str_replace("'","\'",$retorno);
		$retorno = str_replace("'","''",$texto);
		return $retorno;
	
	}
	
	

	//retornar um select HTML, 
	//deve ser passado cmd sql onde o primeiro campo são os valores e o segundo são as opcoes
	//opcionalmente pode ser passado o terceiro campo no sql que irá agrupar as opcoes, neste caso 
	//deverá ser setado o parametro grpuo como true
	function montaComboSQL($sql, $nomeCombo, $permitirBranco = false, $padrao = '', $grupo = false) {
	    $rs = $this->conexao->runSQL($sql);
	    if ($this->conexao->getErro())
	        return $this->cnx>getErro();
	    $retorno = "<select name='$nomeCombo' id = '$nomeCombo'>";
	    if ($permitirBranco) {
	        $retorno .= "<option value=''> - </option>";
	    }
	    $FecharGrupo = '';
	    $grupoAntes = '';
	    while ($v = $this->conexao->fetchArray($rs)) {
	        if ($grupo) {
	            if ($grupoAntes <> $v[2]) {
	                $retorno .= $FecharGrupo;
	                $retorno .= "<optgroup label='" . $v[2] . "'>";
	                $FecharGrupo = '</optgroup>';
	                $grupoAntes = $v[2];
	            }
	        } if ($v[0] == $padrao) {
	            $retorno .= "<option value='" . ($v[0]) . "' selected>" . ($v[1]) . "</option>";
	        } else {
	            $retorno .= "<option value='" . ($v[0]) . "'>" . ($v[1]) . "</option>";
	        }
	    }
	    $retorno .= '</select>';
	    return $retorno;
	}
	
	//retornar um select HTML, 
	//deve ser passado cmd sql onde o primeiro campo são os valores e o segundo são as opcoes
	//opcionalmente pode ser passado o terceiro campo no sql que irá agrupar as opcoes, neste caso 
	//deverá ser setado o parametro grpuo como true
	function montaComboArray($valores, $opcoes, $nomeCombo, $permitirBranco = false, $padrao = '') {
	    $retorno = "<select name='$nomeCombo' id = '$nomeCombo'>";
	    if ($permitirBranco) {
	        $retorno .= "<option value=''> - </option>";
	    }
	    $FecharGrupo = '';
	    $grupoAntes = '';
	
	    for ($i = 0; $i < sizeof($valores); $i++) {
	        if ($valores[$i] == $padrao) {
	            $retorno .= "<option value='" . ($valores[$i]) . "' selected>" . ($opcoes[$i]) . "</option>";
	        } else {
	            $retorno .= "<option value='" . ($valores[$i]) . "'>" . ($opcoes[$i]) . "</option>";
	        }
	    }
	    $retorno .= '</select>';
	    return $retorno;
	}
	
	/**
	 * Função para gerar senhas aleatórias
	 *
	 * @author    Thiago Belem <contato@thiagobelem.net>
	 *
	 * @param integer $tamanho Tamanho da senha a ser gerada
	 * @param boolean $maiusculas Se terá letras maiúsculas
	 * @param boolean $numeros Se terá números
	 * @param boolean $simbolos Se terá símbolos
	 *
	 * @return string A senha gerada
	 */
	function geraSenha($tamanho = 8, $maiusculas = true, $numeros = true, $simbolos = false) {
	    $lmin = 'abcdefghijklmnopqrstuvwxyz';
	    $lmai = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
	    $num = '1234567890';
	    $simb = '!@#$%*-';
	    $retorno = '';
	    $caracteres = '';
	
	    $caracteres .= $lmin;
	    if ($maiusculas)
	        $caracteres .= $lmai;
	    if ($numeros)
	        $caracteres .= $num;
	    if ($simbolos)
	        $caracteres .= $simb;
	
	    $len = strlen($caracteres);
	    for ($n = 1; $n <= $tamanho; $n++) {
	        $rand = mt_rand(1, $len);
	        $retorno .= $caracteres[$rand - 1];
	    }
	    return $retorno;
	}
	

	
	//Converte a primeira letra da string em cada elemente da array para maiúscula
	function primeiraMaiscusculaArray($arr) {
		for ($i=0; $i<sizeof($arr); $i++) {
			$arr[$i] = ucfirst($arr[$i]);
		}
		return $arr;
	}
	
	//Gera tabela baseada em um retorno SQL do Banco de Dados
	function geraTable($resultPG,$class='',$linkButtonsOk=false) {
		if ($class != '') {
			$class =  'class="'.$class.'"';
			$retorno = '<table '.$class.'>'."\r\n";
		} else {
			$retorno = '<table>';
		}
		$retorno .= '<tr>';
		if ($linkButtonsOk) {
			$retorno .= '<th></th>';
		}
		$qtdCampos = $this->conexao->fieldCount($resultPG);
		for ($i=0; $i < $qtdCampos; $i++) {
			$retorno .= '<th>'.$this->conexao->fieldName($resultPG,$i).'</th>';
		}
		$retorno .= '</tr>'."\r\n";
		while ($v = $this->conexao->fetchArray($resultPG)) {
			$retorno .= '<tr>';
			if ($linkButtonsOk) {
          		$retorno .= '<td><a href="'.$linkButtonsOk.'&busca_'.$this->conexao->fieldName($resultPG,0).'='.
					$v[0].'" class="btn btn-default btn-sm" role="button">OK</a></td>';
			}
			for ($i=0; $i < $qtdCampos; $i++) {
				$retorno .= '<td>'.$v[$i].'</td>';
			}
			$retorno .= '</tr>'."\r\n";
		}
		$retorno .= '</table>'."\r\n";
		return $retorno;
		
	}
	
	
	//Carrega uma imagem a patir de um arquivo local ou link web	
	//Ignora erro irá colocar uma mensagem de erro no lugar da imagem,
	//caso false, irá exibir o erro real e finalizar o script (exceto se o acesso a imagem estiver bloqueada pelo suporte, 
	//neste caso será gerada uma imagem em branco)
	function LoadImage($imgname,$ignorarerro=true,$tipo='png',$msg_erro='Imagem não carregada: ',$suporteOnError=false)	{
		/* Attempt to open */
		$this->erro = "";
		try {
			if ($tipo == 'jpg') {
				if ($ignorarerro)
					$im = @imagecreatefromjpeg($imgname);
				else
					$im = imagecreatefromjpeg($imgname);
			} else if ($tipo == 'gif') {
				if ($ignorarerro)
					$im = @imagecreatefromgif($imgname);
				else
					$im = imagecreatefromgif($imgname);
			} else if ($tipo == 'bmp') {
				if ($ignorarerro)
					$im = @imagecreatefromwbmp($imgname);
				else
					$im = imagecreatefromwbmp($imgname);
			} else {
				if ($ignorarerro)
					$im = @imagecreatefrompng($imgname);
				else
					$im = imagecreatefrompng($imgname);
			}
		} catch (Exception $e) {
			/*
			if ($e->getSeverity() === E_ERROR) {
				echo("E_ERROR triggered.\n");
			} else if ($e->getSeverity() === E_WARNING) {
				echo("E_WARNING triggered.\n");
			} 
			*/
			//echo $e->getMessage();
			$msgExcept = $e->getMessage();
			if (strpos($msgExcept,'Network is unreachable') > 0) {
				$this->erro = "acesso bloqueado";
				if ($suporteOnError) {
					$arrErro[0] = 'Erro ao carregar a imagem ';
					$arrErro[1] = 'Favor entrar em contado com o suporte';
					$arrErro[2] = 'Liberar servidor la nina para acessar o site abaixo:';
					$arrErro[3] = $imgname;
					$arrErro[4] = $msgExcept;
					$im = $this->imagemDeErro(640,600,$arrErro);
				} else {
					$arrErro[0] = 'Erro ao carregar a imagem ';
					$arrErro[1] = $imgname;
					$arrErro[2] = $msgExcept;
					$im = $this->imagemDeErro(640,600,$arrErro);
				}
			} 
				
		}
		
		/* See if it failed */
		if(!$im) {
			$erro_load = $im;
			if (! $ignorarerro) {
				echo $im;
				exit;
			}
			/* Create a black image */
			$im  = imagecreatetruecolor(640, 600);
			$bgc = imagecolorallocate($im, 255, 255, 255);
			$tc  = imagecolorallocate($im, 0, 0, 0);
	
			imagefilledrectangle($im, 0, 0, 640, 600, $bgc);
	
			/* Output an error message */
			imagestring($im, 1, 5, 5, $msg_erro . $imgname, $tc);
			imagestring($im, 1, 30, 30, $erro_load,  $tc);
		}
	
		return $im;
	}
	
	//Cria uma imagem com mensagens de erro
	//Ideal para substituir uma imagem eventualmente não encontrada
	//arrMsgErro - todas msg para exibir na imagem (cada item será uma linha)
	function imagemDeErro($tamanho,$altura,$arrMsgErro,$corfundo=false,$cortexto=false) {
		$im  = imagecreatetruecolor($tamanho, $altura);
		if (!$corfundo) $corfundo = imagecolorallocate($im, 255, 255, 255);
		if (!$cortexto) $corfundo = $tc  = imagecolorallocate($im, 0, 255, 255);
		imagefilledrectangle($im, 0, 0, $tamanho, $altura, $corfundo);
		$xtexto=5;
		for ($i=0; $i<sizeof($arrMsgErro); $i++) {
			imagestring($im, 2, 5, $xtexto, $arrMsgErro[$i], $cortexto);
			$xtexto += 15;
		}
		return $im;
	}
	
	
	/***********************************
	* FUNCOES GEO
	***********************************/
	//Converte um geomotry recebido do banco de dados para um array múltiplo PHP
	//Recebe um resource pg_query
	//Retorna pontos minimo e maximo nas 2 primeiras linhas da array
	function Geometry2MultiArray($geometry) {
		$arrRetorno[0][0][0] =  1000; $arrRetorno[0][0][1] =  1000;
		$arrRetorno[0][1][0] = -1000; $arrRetorno[0][1][1] = -1000;
		
		$poligono = substr( $geometry,16,strlen($geometry));  //REMOVER MULPOLYGON (
		$poligono = substr( $poligono,0,strlen($poligono)-3); //REMOVE PARENTESES DO FIM
		$arrGeometry = explode ('((',$poligono);
		for ($j=1; $j <= sizeof($arrGeometry); $j++) {
			if (! isset($arrGeometry[$j])) { continue; }
			$arrPol = explode(',',$arrGeometry[$j]);
			for ($i=0; $i <= sizeof($arrPol) - 1; $i++) {
				$TLatLon = explode(' ',$arrPol[$i]);
				if (isset($TLatLon[0])) $lat = $TLatLon[0]; else continue;
				if (isset($TLatLon[1])) $lon = $TLatLon[1]; else continue;
				
				
				if ($lat && $lon) {
					$arrRetorno[$j][$i][0] = $lat;
					$arrRetorno[$j][$i][1] = $lon;
			
					if ($arrRetorno[0][0][0] > $lat) $arrRetorno[0][0][0] = $lat;
					if ($arrRetorno[0][0][1] > $lon) $arrRetorno[0][0][1] = $lon;
			
					if ($arrRetorno[0][1][0] < $lat) $arrRetorno[0][1][0] = $lat;
					if ($arrRetorno[0][1][1] < $lon) $arrRetorno[0][1][1] = $lon;
				}
			}
		}
		
		if (($arrRetorno[0][1][1] - $arrRetorno[0][1][0]) > 0)
			$proporcao = ($arrRetorno[0][0][1] - $arrRetorno[0][0][0]) / ($arrRetorno[0][1][1] - $arrRetorno[0][1][0]);
		else if (!isset($proporcao))
			$proporcao = 1;
		$proporcaoIdeal = 1.1;
		if ($proporcao > $proporcaoIdeal) {
			//lon
			$arrRetorno[0][0][1] -= ($proporcao - $proporcaoIdeal) / 2;
			$arrRetorno[0][1][1] += ($proporcao - $proporcaoIdeal) / 2;
		} else {
			//lat
			$arrRetorno[0][0][0] -= ($proporcaoIdeal - $proporcao) / 2;
			$arrRetorno[0][1][0] += ($proporcaoIdeal - $proporcao) / 2;
		}
		return $arrRetorno;
	}
	
	//Retonar um array contendo diversos geomotrys do banco de dados em pontos polygon para ser utilizado em imagem
	//linha de inicio no array
	function ArrayGemoetry2ImgPolygon($tamH,$tamW,$borda,$arrGeom,$rangLat,$rangLon,$LinhaInicio=-1) {
		
		if ($LinhaInicio == -1) $LinhaInicio = 2;
		/*
		print_r($arrGeom);
		print_r($rangLon);
		exit;
		*/
		
		$j = -1;
		for ($i=$LinhaInicio; $i <= sizeof($arrGeom) - 1; $i+=1) {
			/*
			$retorno[++$j]= $tamW - round($tamW * (($rangLon[1] - $arrGeom[$i][0]) / $rangLon[2]));
			$retorno[++$j]= round($tamH * (($rangLat[1] - $arrGeom[$i][1]) / $rangLat[2]));
			*/
			
			$retorno[++$j]= $tamW - round($tamW * (($rangLon[1] - $arrGeom[$i][0]) / $rangLon[2]));
			$retorno[++$j]= round($tamH * (($rangLat[1] - $arrGeom[$i][1]) / $rangLat[2]));
			
		}
		return $retorno;
	}
	
	
	
	//Converte Pontos de geomotry para pontos polygon para ser utilizado em imagens
	function PointGemoetry2ImgPolygon($tamH,$tamW,$borda,$lat,$lon,$rangLat,$rangLon) {
		$j = -1;
		$retorno[++$j]= $borda + $tamW - round($tamW * (($rangLon[1] - $lon) / $rangLon[2]));
		$retorno[++$j]= $borda + round($tamH * (($rangLat[1] - $lat) / $rangLat[2]));
		return $retorno;
	}
	
	//Converte um geomotry recebido do banco de dados para um array PHP
	//Recebe um resource pg_query
	//Retorna pontos minimo e maximo nas 2 primeiras linhas da array
	function Geometry2Array($geometry) {
		$arrRetorno[0][0] =  1000; $arrRetorno[0][1] =  1000;
		$arrRetorno[1][0] = -1000; $arrRetorno[1][1] = -1000;
		
		$poligono = substr( $geometry,15,strlen($geometry));  //REMOVER MULPOLYGON (((
		$poligono = substr( $poligono,0,strlen($poligono)-3); //REMOVE PARENTESES DO FIM
		$poligono = str_replace(')','',$poligono);
		$poligono = str_replace('(','',$poligono);
		$arrPol = explode(',',$poligono);
		for ($i=0; $i <= sizeof($arrPol) - 1; $i++) {
			$TLatLon = explode(' ',$arrPol[$i]);
			$lat = $TLatLon[0];
			$lon = $TLatLon[1];
			$arrRetorno[$i+2][0] = $lat;
			$arrRetorno[$i+2][1] = $lon;
	
			if ($arrRetorno[0][0] > $lat) $arrRetorno[0][0] = $lat;
			if ($arrRetorno[0][1] > $lon) $arrRetorno[0][1] = $lon;
	
			if ($arrRetorno[1][0] < $lat) $arrRetorno[1][0] = $lat;
			if ($arrRetorno[1][1] < $lon) $arrRetorno[1][1] = $lon;
		}
		/*
		//criar borda
		$arrRetorno[0][0] -= 0.01;
		$arrRetorno[0][1] -= 0.01;
		$arrRetorno[1][0] += 0.01;
		$arrRetorno[1][1] += 0.01;
		*/
		$proporcao = ($arrRetorno[0][1] - $arrRetorno[0][0]) / ($arrRetorno[1][1] - $arrRetorno[1][0]);
		$proporcaoIdeal = 1.1;
		if ($proporcao > $proporcaoIdeal) {
			//lon
			$arrRetorno[0][1] -= ($proporcao - $proporcaoIdeal) / 2;
			$arrRetorno[1][1] += ($proporcao - $proporcaoIdeal) / 2;
		} else {
			//lat
			$arrRetorno[0][0] -= ($proporcaoIdeal - $proporcao) / 2;
			$arrRetorno[1][0] += ($proporcaoIdeal - $proporcao) / 2;
		}
		//echo $proporcao;
		//exit;
	
		return $arrRetorno;
	}
	
	

	//Criar pastas em diretório caso não exista de forma recursiva
	function recursive_mkdir($path,$mode=0775,$diretorio_separador='/') {
		$old = umask(0);
		$dirs = explode($diretorio_separador , $path);
		$count = count($dirs);
		$path = '.';
		for ($i = 0; $i < $count; ++$i) {
			$path .= $diretorio_separador . $dirs[$i];
			if (!is_dir($path) && !mkdir($path, $mode)) {
				umask($old);
				return false;
			}
		}
		umask($old);
		return true;
	}
	
	//Compara dois diretório e retorna o link relativo entre eles.
	//dir2 = diretório destino
	//dir1 = ditretório origem
	function diretorioRelativo($dir2,$dir1=false,$barra='auto') {
		if ($dir1==false) $dir1 = dirname(__FILE__);
		if ($barra == 'auto') {
			if (PHP_OS == 'Linux') {
				$barra = '/';
			} else {
				$barra = '\\';
			}
		}
		
		if (strlen($dir2) > strlen($dir1)) {
			$dirtemp = substr($dir2, 0, strlen($dir1));
			if ($dirtemp == $dir1) {
				$dirtemp = substr($dir2,strlen($dir1)+1);
				return $dirtemp;
			}
		} else if ($dir2 == $dir1) {
			return '';
		}
		
		$ultimocaracter = substr($dir2,-1);
		if ($ultimocaracter == $barra) $dir2 = substr($dir2,0,-1); 

		$arrDir1 = explode($barra, $dir1);
		$arrDir2 = explode($barra, $dir2);
		$diferente = false;
		$str='';
		for ($i=0; $i<sizeof($arrDir1); $i++) {
			if (!$diferente) {
				if ($arrDir1[$i] != $arrDir2[$i]) {
					$pos = $i;
					$diferente = true;
				}
			}
			
			if ($diferente) {
				$str.='..'.$barra;
			}
			
		}
		for ($i=$pos; $i<sizeof($arrDir2); $i++) {
			$str .= $arrDir2[$i].$barra;
		}
		//$str .= $barra;
		return  $str;
		
		
	}
	
	//Verifica se algum item do array contém o valor informado em $str
	//Retorna true caso encontre
	//o parâmetro parcial compara se parte da string está contida no em algum item array
	function buscaStringContemArray($str,$arr,$parcial=true) {
		$retorno = false;
		for ($i=0; $i<sizeof($arr); $i++) {
			if ($parcial) 
				if (stripos($str,$arr[$i]) > 0) {
					$retorno = true;
				}
			else
				if ($str == $arr[$i] ) $retorno = true;
		}
		return $retorno;
	}
	
	//Gerar um id tipo hash aleatório
	static function getHash() {
		return  md5(uniqid(rand(), true));
	}
	
	//Função para Criptografar uma String
	static function encrypt($pure_string,$encryption_key=false) {
		if (!$encryption_key) $encryption_key = HASH_SEGURANCA;
		$pure_string =  base64_encode( $pure_string);
		$iv_size = mcrypt_get_iv_size(MCRYPT_BLOWFISH, MCRYPT_MODE_ECB);
		$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
		$encrypted_string = mcrypt_encrypt(MCRYPT_BLOWFISH, $encryption_key, utf8_encode($pure_string), MCRYPT_MODE_ECB, $iv);
		$encrypted_string = Util::strToHex( $encrypted_string);
		return ( $encrypted_string);
	}
	
	//Função para Desriptografar uma String
	static function decrypt($encrypted_string,$encryption_key=false) {
		if (!$encryption_key) $encryption_key = HASH_SEGURANCA;
		$encrypted_string = Util::hexToStr($encrypted_string);
		$iv_size = mcrypt_get_iv_size(MCRYPT_BLOWFISH, MCRYPT_MODE_ECB);
		$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
		$decrypted_string = mcrypt_decrypt(MCRYPT_BLOWFISH, $encryption_key, $encrypted_string, MCRYPT_MODE_ECB, $iv);
		return base64_decode( $decrypted_string);
	}	
	
	//Converte uma string em valores hexadecimais
	static function strToHex($string) {
		$hex = '';
		for ($i=0; $i<strlen($string); $i++){
			$ord = ord($string[$i]);
			$hexCode = dechex($ord);
			$hex .= substr('0'.$hexCode, -2);
		}
		return strToUpper($hex);
	}
	
	//Converte valores hexadecimais em string
	static function hexToStr($hex) {
		$string='';
		for ($i=0; $i < strlen($hex)-1; $i+=2){
			$string .= chr(hexdec($hex[$i].$hex[$i+1]));
		}
		return $string;
	}
	
	//Retorna o id do município localizado em uma latitude / longitude específica.
	function getIdMunicLatLon($lat,$lon) {
		$sql = "select id_municipio from ger_municipio
				where ST_INTERSECTS(the_geom, st_point($lon,$lat))";
		$rs = $this->conexao->runSQL($sql);
		$v = pg_fetch_array($rs);
		return $v[0];
	}
	
	//Calcula a diferença entre duas datas (data / hora)
	//Retorna um texto no formato:
	// x anos, x meses e x dias
	function diferencaAnoMesDiaDatas($ini,$fim) {
		$data1 = new DateTime( $ini );
		$data2 = new DateTime( $fim );

		$intervalo = $data1->diff( $data2 );

		return "{$intervalo->y} anos, {$intervalo->m} meses e {$intervalo->d} dias"; 
	}
	
	//Calcula a diferença entre duas datas (data / hora)
	//Retorna a quantidade de dias
	function diferencaDiasDatas($ini,$fim) {
		// Calcula a diferença em segundos entre as datas
		$diferenca = strtotime($fim) - strtotime($ini);
	
		//Calcula a diferença em dias
		$dias = floor($diferenca / (60 * 60 * 24));
		
		return $dias;

	}
	
	//Calcula a diferença entre duas datas (data / hora)
	//Retorna um texto no formato 
	//x dias e x horas
	function diferencaDiasHorasDatas($ini,$fim) {
		// Calcula a diferença em segundos entre as datas
		$diferenca = strtotime($fim) - strtotime($ini);
	
		//Calcula a diferença em dias
		$dias = floor($diferenca / (60 * 60 * 24));
		
		$horas = $diferenca - ($dias * 60 * 60 * 24);
		$horas = floor( $horas /  (60 * 60));
		
		return $dias . ' dia(s) e '.$horas . ' hora(s)';

	}
	
	//Remove os números de uma string
	static function somenteNumero($str) {
    	return preg_replace("/[^0-9]/", "", $str);
	}
	
	// Function to get the client IP address
	static function get_client_ip() {
	    $ipaddress = '';
	    if (isset($_SERVER['HTTP_CLIENT_IP']))
	        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
	    else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
	        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
	    else if(isset($_SERVER['HTTP_X_FORWARDED']))
	        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
	    else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
	        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
	    else if(isset($_SERVER['HTTP_FORWARDED']))
	        $ipaddress = $_SERVER['HTTP_FORWARDED'];
	    else if(isset($_SERVER['REMOTE_ADDR']))
	        $ipaddress = $_SERVER['REMOTE_ADDR'];
	    else
	        $ipaddress = 'UNKNOWN';
	    return $ipaddress;
	}
	
	static function getHeaderVarClient($varName) {
		$headers = apache_request_headers();
		
		foreach ($headers as $header => $value) {
			//echo $header.'>'.$value."\r\n";
			if ($header == $varName) {
				return $value;
			}
		}
		//exit;
		return false;
	}	
	
	
	
	
}
?>