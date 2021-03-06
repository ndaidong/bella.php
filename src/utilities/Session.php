<?php

namespace Bella;

trait Session{
	
	private static $key = '';
	
	public static function init(){
		
		$rootDomain = '.'.Config::get('domain');
		ini_set('session.gc_maxlifetime', 60*60*24*30);
		session_cache_limiter('none');
		$currentCookieParams = session_get_cookie_params();
		session_set_cookie_params( 
			$currentCookieParams["lifetime"], 
			$currentCookieParams["path"], 
			$rootDomain, 
			$currentCookieParams["secure"], 
			$currentCookieParams["httponly"] 
		); 
		$sn = Config::get('application')->alias;
		if(!!$sn){
			session_name('__'.strtoupper($sn).'__');
		}
		
		if(!static::get('initialized')){
			static::set('initialized', 1);
		}				
		session_start();
	}
	
    public static function set($name, $value){
		if(isset($_SESSION[$name])){
			unset($_SESSION[$name]);
		}
		$_SESSION[$name] = $value;
    }	
    
    public static function get($name){
		if(isset($_SESSION[$name])){
			return $_SESSION[$name];
		}
		return '';
    }
    public static function getOnce($name){
		$tmp = static::get($name);
		if(!!$tmp){
			static::remove($name);
		}
		return $tmp;
    }
	
	public static function remove($name){
		if(isset($_SESSION[$name])){
			unset($_SESSION[$name]);
		}
	}  
	public static function removeAll(){
		session_destroy();
	}
}
