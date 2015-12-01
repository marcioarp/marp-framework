<?php
class Route {
	protected function route($url,$explode) {
		$ultimoExplode = array_pop($explode);
		if ($url == 'index') {
			$url =  'index.html';
			$type = 'html';
		} else {
			$type = explode('.',$ultimoExplode);
			if (isset($type[1])) {
				$type = $type[1];
			}
		}
		//echo $type;
		if (file_exists(PUBLIC_HTML.$url)) {
			switch ($type) {
				case 'css' :
					header('Content-Type: text/css');
				break;
			}	
			require_once(PUBLIC_HTML.$url);
		} else {
			echo "NÃO ENCONTRADO: ".PUBLIC_HTML.$url;
		}
	}
}