<?php

namespace Bella;

class Config{
	
	private static $config = [];
	
	public static function init(){
		
		$path = realpath(dirname( __FILE__ ));
		
		$condir = $path.'/../configs/';
		
		$settings = parse_ini_file($condir.'bella.ini');
		
		$appSettings = parse_ini_file('conf/app.ini');
		if(!!$appSettings){
			foreach($appSettings as $key=>$var){
				$settings[$key] = $var;
			}
		}

		$protocol = 'http://';
		$svname = $_SERVER['SERVER_NAME'];
		$_sva = explode('.', $svname);
		if(count($_sva)>2){
			array_shift($_sva);
			$svname = implode('.', $_sva);
		}

		self::$config = [
			'active'	=> $settings['active']*1,
			'engine'	=> $settings['engine'],
			'version'	=> $settings['version'],
			'debug'		=> $settings['debug'],
			'pubdomain'	=> $settings['domain'],
			'global' 	=> include_once $condir.'global.php',
			'api' 		=> include_once $condir.'api.php',
			'databases' => include_once $condir.'databases.php',
			'mails' 	=> include_once $condir.'mails.php',
			'protocol'	=> $protocol,
			'hostname'	=> $svname,
			'domain'	=> $svname,
			'server'	=> $protocol.$svname,
		];
		
		$env_configs = [
			'global' 	=> null,
			'api' 		=> null,
			'databases' => null,
			'mails' 	=> null,
		];
		
		$environ = $settings['env'];
		
		$publicDomain = isset($settings['domain'])?$settings['domain']:'';
		$pos = false;
		if(!!$publicDomain){
			$pos = strripos($svname, $settings['domain']);
		}
		
		if($pos===false){
			$environ = 'development';
		}
		else{
			$environ = 'production';
		}
		
		self::$config['environment'] = $environ;
		
		$env = 'conf/'.$environ.'/';
		
		foreach($env_configs as $key=>$value){
			$file = $env.$key.'.php';
			if(file_exists($file)){
				$env_configs[$key] = include_once $file;
			}
		}	
		
		foreach($env_configs as $key=>$value){
			if(isset($value)){
				if((is_object($value) || is_array($value))){
					if(isset(self::$config[$key])){
						foreach($value as $_k=>$_v){
							if(isset(self::$config[$key][$_k])){
								$tmp = (object) self::$config[$key][$_k];
								if((is_object($_v) || is_array($_v))){
									foreach($_v as $__k=>$__v){
										$tmp->$__k = $__v;
									}
								}
								else{
									$tmp = $_v;
								}
								self::$config[$key][$_k] = $tmp;
							}
							else if($key=='global'){
								self::$config['global'][$_k] = $_v;
							}
						}
					}
				}
				else{
					self::$config[$key] = $value;
				}
			}
		}
		//self::$config['global']['publicDomain'] = 
		
		if(self::$config['debug']===1 || $environ =='development'){
			error_reporting (E_ALL);
			ini_set ('display_errors', 'On');
		} 
		else{
			error_reporting(0);
			ini_set ('display_errors', 'Off');
		}		

		echo json_encode(self::$config);exit;
	}
	
	public static function get($key){
		$val = isset(static::$config[$key])?static::$config[$key]:false;
		return is_array($val)?(object) $val:$val;
	}
	
	public static function set($key, $values){
		if(isset(static::$config[$key])){
			foreach($values as $k=>$v){
				static::$config[$key][$k] = $v;
			}
		}
	}

}
