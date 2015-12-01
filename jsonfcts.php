<?php

class JSONFuncs {
	public static function send2Browser($json) {
		ob_clean();
		echo $json;
		exit;
	}
	
	public static function sendArray2Browser($arr) {
		$json = json_encode($arr);
		echo JSONFuncs::send2Browser($json);
		exit;
	}
}
