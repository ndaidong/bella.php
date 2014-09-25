<?php

namespace Bella;

trait Request{

	private static $data = [];
	private static $header = [];
	
	public static function init(){
		
		parse_str(file_get_contents("php://input"), static::$data);
		
		static::$data = array_merge($_GET, $_POST);
		
		$met = $_SERVER['REQUEST_METHOD'];
		
		static::$header['method'] = $met;
		
		$path = str_replace('?'.$_SERVER['QUERY_STRING'], '', $_SERVER['REQUEST_URI']);
		static::$header['path'] = $path;
		
		$queries = [];
		$sQuery = $_SERVER['QUERY_STRING'];
		$a = explode('&', $sQuery);
		if(count($a)>0){
			foreach($a as $b){
				$c = explode('=', $b);
				if(is_array($c) && count($c)===2){
					$d = '';
					$v = $c[1];
					if(is_numeric($v)){
						$d = 1*$v;
					}
					else if(is_string($v)){
						$d = urldecode($v);
					}
					$queries[$c[0]] = $d;
				}
			}
		}
		static::$header['query'] = (object) $queries;
	}

	public static function input($key = ''){
		if(!!$key){
			return isset(static::$data[$key])?static::$data[$key]:'';
		}
		return static::$data;
	}
	
	public static function getHeader(){
		return static::$header;
	}
}


