<?php

namespace Bella;

class Path{

	private static $path = [];
	
	public static function init(){
		$config = Config::get('settings');
		$path = str_replace('?'.$_SERVER['QUERY_STRING'], '', $_SERVER['REQUEST_URI']);
		$a = explode('/',$path);
		array_shift($a);
		$t = [];
		for($i=0;$i<count($a);$i++){
			if('/'.$a[$i].'/'!==$config->baseDir){
				array_push($t, $a[$i]);
			}
		}
		while(count($t)<7){
			array_push($t, '');
		}
		self::$path = $t;
	}

	public static function get($key = false){
		if(is_numeric($key) && $key>=0 && $key<7){
			return self::$path[$key];
		}
		return self::$path;
	}
}


