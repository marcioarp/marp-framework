<?php

class FileUtils {
	//ignore é um array com nomes de arquivos e pastas que devem ser ignorados
	//string remove e o texto que deve ser omitido no nome das pastas : ex c:\xamp\htdocs
	//results é usando na recursão, não deve ser informado inicialmente.
	function getDirContents($dir, $ignore = array(), $stringRemove = '', &$results = array()) {
		$files = scandir($dir);
		foreach ($files as $key => $value) {
			$path = realpath($dir . DIRECTORY_SEPARATOR . $value);
			if (!is_dir($path)) {
				$dirTemp = str_ireplace($stringRemove, '', $path);
				if (!in_array($dirTemp, $ignore))
					$results[] = $dirTemp;
			} else if ($value != "." && $value != "..") {
				$dirTemp = str_ireplace($stringRemove, '', $path) . DIRECTORY_SEPARATOR;
				if (!in_array($dirTemp, $ignore)) {
					$results[] = $dirTemp;
					$this -> getDirContents($path, $ignore, $stringRemove, $results);
				}
			}
		}
		return $results;
	}

	//retona a extenççao de um arquivo
	static function getExtensaoArquivo($arquivo) {
		return strtolower(end(explode(".", $arquivo)));
	}

	//Compara dois diretório e retorna o link relativo entre eles.
	//dir2 = diretório destino
	//dir1 = ditretório origem
	function diretorioRelativo($dir2, $dir1 = false, $barra = 'auto') {
		if ($dir1 == false)
			$dir1 = dirname(__FILE__);
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
				$dirtemp = substr($dir2, strlen($dir1) + 1);
				return $dirtemp;
			}
		} else if ($dir2 == $dir1) {
			return '';
		}

		$ultimocaracter = substr($dir2, -1);
		if ($ultimocaracter == $barra)
			$dir2 = substr($dir2, 0, -1);

		$arrDir1 = explode($barra, $dir1);
		$arrDir2 = explode($barra, $dir2);
		$diferente = false;
		$str = '';
		for ($i = 0; $i < sizeof($arrDir1); $i++) {
			if (!$diferente) {
				if ($arrDir1[$i] != $arrDir2[$i]) {
					$pos = $i;
					$diferente = true;
				}
			}

			if ($diferente) {
				$str .= '..' . $barra;
			}

		}
		for ($i = $pos; $i < sizeof($arrDir2); $i++) {
			$str .= $arrDir2[$i] . $barra;
		}
		//$str .= $barra;
		return $str;

	}

	//Criar pastas em diretório caso não exista de forma recursiva
	static function recursive_mkdir($path, $mode = 0775, $diretorio_separador = DIRECTORY_SEPARATOR) {
		$old = umask(0);
		$dirs = explode($diretorio_separador, $path);
		$count = count($dirs);
		if ($diretorio_separador == '/') {
			$path = '.';
			$start = 0;
		} else {
			$path = $dirs[0];
			$start = 1;
		}
		for ($i = $start; $i < $count; ++$i) {
			$path .= $diretorio_separador . $dirs[$i];
			if (!is_dir($path) && !mkdir($path, $mode)) {
				umask($old);
				return false;
			}
		}
		umask($old);
		return true;
	}

	//envia um arquivo para download
	//processado automaticamente pelo requisicao.js
	//usar somente para arquivos texto
	static function forceDownloadVVS($filename, $fullPath, $contentType = false) {
		$fullFile = $fullPath . $filename;
		if (!file_exists($fullFile)) {
			$r['status'] = 'Erro';
			$r['msg'] = "Arquivo não localizado no backend";
			return $r;
		}

		if (!$contentType) {
			$contentType = mime_content_type($fullFile);
		}

		ob_clean();
		header('Content-Description: File Transfer');
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		header('Content-Length: ' . filesize($fullFile));
		header("Content-Type: " . $contentType);
		header('Content-Disposition: attachment; filename="' . $filename . '"');
		header('VVS-Force-Download:true');
		header('VVS-Download-File-Name:' . $filename);

		http_response_code(200);
		readfile($fullFile);
		exit(0);

	}

	//redireciona o usuário para download do arquivo, baseado em um token
	//processado automaticamente pelo requisicao.js
	static function redirectToDownloadVVS($filename, $fullPath, $msg = '') {
		ob_clean();
		header('VVS-Redirect-Download:true');

		$a['filename'] = $fullPath . $filename;
		$a['hash'] = StringUtil::getHash();
		$tk = TokenSecurity::getTokenCrypt($a);

		$r['status'] = 'ok';
		$r['msg'] = $msg;
		$r['link'] = 'Midia/downloadFile/' . $tk;
		return $r;
	}

	public static function resize_image($original_arq, $novo_arq, $w, $h, $background = false) {
		list($width, $height) = getimagesize($original_arq);
		$r = $width / $height;
		$difw = $width - $w;
		$difh = $height - $h;
		if ($difw < $difh) {
			$newwidth = $h * $r;
			$newheight = $h;
		} else {
			$newheight = $w / $r;
			$newwidth = $w;
		}
		$type = FileUtils::getImageType($original_arq);

		if (($type == 'jpg') || ($type == 'jpeg')) {
			$src = imagecreatefromjpeg($original_arq);
		} else if ($type == 'gif') {
			$src = imagecreatefromgif($original_arq);
		} else if ($type == 'bmp') {
			$src = imagecreatefromwbmp($original_arq);
		} else {//png
			$src = imagecreatefrompng($original_arq);
		}

		if ($background)
			$dst = imagecreatetruecolor($w, $h);
		else
			$dst = imagecreatetruecolor($newwidth, $newheight);

		imagecopyresampled($dst, $src, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);

		if (($type == 'jpg') || ($type == 'jpeg')) {
			imagejpeg($dst, $novo_arq, 100);
		} else if ($type == 'gif') {
			imagegif($dst, $novo_arq);
		} else if ($type == 'bmp') {
			image2wbmp($dst, $novo_arq);
		} else {//png
			imagepng($dst, $novo_arq);
		}
		return true;
	}

	static function sendFileToBrowser($fullFileName) {
		if (!file_exists($fullFileName)) {
			$r['status'] = 'Erro';
			$r['msg'] = 'Arquivo não encontrado';
			return $r;
		}
		ob_clean();
		header('Content-Description: File Transfer');
		header_remove('Content-Type');
		header_remove('Content-Disposition');
		header('Content-Type: ' . mime_content_type($fullFileName));
		header('Content-Disposition: attachment; filename=' . basename($fullFileName));
		header('Content-Transfer-Encoding: binary');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');
		header('Content-Length: ' . filesize($fullFileName));
		//echo mime_content_type($fullFileName);

		readfile($fullFileName);
	}

	static function MimeType($path) {
		$result = false;

		if (is_file($path) === true) {
			if (function_exists('finfo_open') === true) {
				$finfo = finfo_open(FILEINFO_MIME_TYPE);

				if (is_resource($finfo) === true) {
					$result = finfo_file($finfo, $path);
				}

				finfo_close($finfo);
			} else if (function_exists('mime_content_type') === true) {
				$result = preg_replace('~^(.+);.*$~', '$1', mime_content_type($path));
			} else if (function_exists('exif_imagetype') === true) {
				$result = image_type_to_mime_type(exif_imagetype($path));
			}
		}

		return $result;
	}

	static function getImageType($fileName) {
		
		$extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
		if ($extension) return $extension;
		
		$image_info = getImageSize($fileName);
		switch ($image_info['mime']) {
			case 'image/gif' :
				$extension = 'gif';
				break;
			case 'image/jpeg' :
				$extension = 'jpg';
				break;
			case 'image/png' :
				$extension = 'png';
				break;
			default :
				// handle errors
				break;
		}
		if ($extension) return $extension;
		
		$extension = FileUtils::MimeType($fileName);
		
	}

}
