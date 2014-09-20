<?php

namespace Bella;

trait Request{

	private static $data = [];
	private static $header = [];
	
	public static function init(){
		self::$data = array_merge($_GET, $_POST);
		
		$met = $_SERVER['REQUEST_METHOD'];
		
		self::$header['method'] = $met;
		
		$path = str_replace('?'.$_SERVER['QUERY_STRING'], '', $_SERVER['REQUEST_URI']);
		self::$header['path'] = $path;
		
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
		self::$header['query'] = (object) $queries;
		
		$posts = [];
		if($met=='POST'){
			$a = explode('&', $_POST);
			if(count($a)>0){
				foreach($a as $b){
					$c = explode('=', $b);
					$posts[$c[0]] = $c[1];
				}
			}
		}
		self::$header['post'] = (object) $posts;
	}

	public static function input($key = ''){
		if(!!$key){
			return isset(self::$data[$key])?self::$data[$key]:'';
		}
		return self::$data;
	}
	
	public static function getHeader(){
		return self::$header;
	}
}


