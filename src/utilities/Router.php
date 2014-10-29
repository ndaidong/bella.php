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
				$c = explode('/', $pattern);
				array_push($a, (object) [
					'pattern' => array_splice($c, 1),
					'callback' => $fn,
				]);
				static::$map[$m] = $a;
			}
		}
	}
	
	public static function get($pattern, $fn){
		static::add('GET', $pattern, $fn);
	}
	public static function post($pattern, $fn){
		static::add('POST', $pattern, $fn);
	}
	public static function put($pattern, $fn){
		static::add('PUT', $pattern, $fn);
	}
	public static function delete($pattern, $fn){
		static::add('DELETE', $pattern, $fn);
	}
	public static function request($pattern, $fn){
		static::add('GET|POST', $pattern, $fn);
	}
	public static function all($pattern, $fn){
		static::add('GET|POST|PUT|DELETE', $pattern, $fn);
	}
	
	public static function mount($path){
		static::$base = $path;
	}
	
	public static function parse(){
		$m = static::getRequestMethod();
		$p = Path::get();
		
		$maps = static::$map[$m];
		if(!!$maps && is_array($maps) && count($maps)>0){
			
			$matching = '';
			
			for($i=count($p)-1;$i>=0;$i--){
				if(!$p[$i]){
					array_splice($p, $i, 1);
				}
			}
			
			$uri = implode('/', $p);
			$min = 1000;
			
			foreach($maps as $map){
				$num = 0;
				$pattern = $map->pattern;
				for($i=count($pattern)-1;$i>=0;$i--){
					if(strpos($pattern[$i], ':')===0){
						$pattern[$i] = '(\w+)';
						$num++;
					}
				}
				$sroute = implode('/', $pattern);
				$matcount = 0;
				for($j=0;$j<count($pattern);$j++){
					if($pattern[$j]=='(\w+)' || $p[$j]==$pattern[$j]){
						$matcount++;
						continue;
					}
				}
				if($matcount==count($pattern)){
					if(preg_match_all('#^' . $sroute . '$#', $uri, $matches)){
						if($num<$min){
							$min = $num;
							$matching = (object) [
								'route' => $map, 
								'data' => array_slice($matches, 1)
							];	
						}		
					}			
				}
			}
			
			if(!!$matching && is_object($matching)){
				$args = [];
				$matches = property_exists($matching, 'data')?$matching->data:[];
				foreach($matches as $val){
					array_push($args, $val[0]);
				}
				
				$fn = $matching->route->callback;
				call_user_func_array($fn, $args);
			}
		}
		if($m=='HEAD'){
			ob_end_clean();
		}
		return Bella::end();
	}
}
