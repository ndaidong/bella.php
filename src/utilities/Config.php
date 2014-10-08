<?php

namespace Bella;

class Config{
	
	private static $config;
	
	public static function init(){
		
		$path = realpath(dirname( __FILE__ ));
		
		$condir = $path.'/../configs/';
		
		$bc = static::getIni($condir.'bella.ini');

		$protocol = isset($_SERVER['HTTPS'])?'https':'http';
		$svname = $_SERVER['SERVER_NAME'];
		$_sva = explode('.', $svname);
		
		$baseDomain = $svname;
		if(count($_sva)>2){
			array_shift($_sva);
			$baseDomain = implode('.', $_sva);
		}
		
		$bc->server = $protocol.'://'.$_SERVER['SERVER_NAME'];
		$bc->baseDomain = $baseDomain;
		$bc->domain = $svname;
		$bc->protocol = $protocol;
		$bc->port = $_SERVER['SERVER_PORT'];
		
		$environ = $bc->environment;
		
		if(!$environ || $environ=='labs'){
			
			if(file_exists('conf/app.ini')){
				$tmp = static::getIni('conf/app.ini');
				foreach($tmp as $_k=>$_v){
					$bc->$_k = $_v;
				}
			}
			
			$publicDomain = isset($bc->pubDomain)?$bc->pubDomain:'';
			$pos = false;
			if(!!$publicDomain){
				$pos = strripos($svname, $publicDomain);
			}
			
			if($pos===false){
				$environ = 'development';
			}
			else{
				if(strpos($svname, 'test-')===false && strpos($svname, 'test.')===false){
					$environ = 'production';
				}
				else{
					$environ = 'test';
				}
			}
			$bc->environment = $environ;
		}
		
		$ac = static::getIni('conf/'.$environ.'.ini');

		if(isset($ac) && (is_array($ac) || is_object($ac))){
			foreach($ac as $k=>$v){
				if(is_object($v) || is_array($v)){
					if(!isset($bc->$k)){
						$bc->$k = $v;
					}
					else{
						foreach($v as $_k=>$_v){
							$bc->$k->$_k = $_v;
						}
					}
				}
				else{
					$bc->$k = $v;
				}
			}
		}
		
		static::$config = $bc;
		
		if($bc->debug===0 || $environ=='production'){
			error_reporting(0);
			ini_set ('display_errors', 'Off');
		}
		else{
			error_reporting (E_ALL);
			ini_set ('display_errors', 'On');
		}		

		echo json_encode(self::$config);exit;
	}
	
	public static function get($key){
		$val = isset(static::$config->$key)?static::$config->$key:false;
		return is_array($val)?(object) $val:$val;
	}

	
	private static function getIni($file){
		if(!file_exists($file)){
			return false;
		}
		$ob = (object) parse_ini_file($file, true);
		
		foreach($ob as $key=>$val){
			if(is_string($val)){
				$ob->$key = trim($val);
			}
			else if(is_array($val)){
				$ob->$key = (object) $val;
			}
		}		
		return $ob;
	}
}
