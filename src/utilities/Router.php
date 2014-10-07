<?php

namespace Bella;

trait Router{
	
	private static $base = '';
	private static $map = [
		'GET' => [],
		'POST' => [],
		'PUT' => [],
		'DELETE' => [],
		'HEAD' => [],
		'OPTIONS' => [],
	];
	
	
	private static function getRequestHeaders() {

		if(function_exists('getallheaders')){
			return getallheaders();
		}
		
		$headers = [];
		foreach($_SERVER as $name => $value){
			if((substr($name, 0, 5) == 'HTTP_') || ($name == 'CONTENT_TYPE') || ($name == 'CONTENT_LENGTH')){
				$headers[str_replace(array(' ', 'Http'), array('-', 'HTTP'), ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
			}
		}
		return $headers;
	}
	
	private static function getRequestMethod(){

		$method = $_SERVER['REQUEST_METHOD'];
		
		if($_SERVER['REQUEST_METHOD'] == 'HEAD'){
			ob_start();
			$method = 'GET';
		}
		else if($_SERVER['REQUEST_METHOD'] == 'POST'){
			$headers = static::getRequestHeaders();
			if(isset($headers['X-HTTP-Method-Override']) && in_array($headers['X-HTTP-Method-Override'], array('PUT', 'DELETE', 'PATCH'))){
				$method = $headers['X-HTTP-Method-Override'];
			}
		}
		return strtoupper($method);
	}

	
	public static function add($method, $pattern, $fn){
		if(!!$fn && is_callable($fn)){
			foreach (explode('|', $method) as $met) {
				$m = strtoupper($met);
				$a = static::$map[$m];
				array_push($a, (object) [
					'pattern' => explode('/', $pattern),
					'callback' => $fn,
				]);
				static::$map[$m] = $a;
			}
		}
	}
	
	public static function mount($path){
		static::$base = $path;
	}
	
	public static function parse(){
		$m = static::getRequestMethod();
		$p = Path::get();
		
		$maps = static::$map[$m];
		
		if(!!$maps && is_array($maps) && count($maps)>0){
			foreach($maps as $map){
				$args = [];
				for($i=0;$i<count($map->pattern);$i++){
					$pi = $map->pattern[$i];
					$ar = explode(':', $pi);
					if(count($ar)>1){
						array_push($args, $p[$i]);
					}
					else{
						if($pi==static::$base){
							continue;
						}
						if($pi==$p[$i]){
							array_push($args, $pi);
						}
						else{
							break;
						}
					}
				}
				if(count($args)>0){
					call_user_func_array($map->callback, $args);
				}
			}
		}
		if($m=='HEAD'){
			ob_end_clean();
		}
	}
}
