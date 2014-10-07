<?php

namespace Bella;

trait Cookie{
	
    public static function set($name, $value, $exp=0){
        $_COOKIE[$name] = $value; 
        $path = Config::get('settings')->cookiePath;
        return setcookie($name, $value, $exp, $path, '.'.Config::get('domain'));
    } 
    public static function get($name){
		return isset($_COOKIE[$name])?$_COOKIE[$name]:null;
    }     
    public static function remove($name){
		if(isset($_COOKIE[$name])){
			unset($_COOKIE[$name]);
		}
        return static::set($name, NULL, time()-3600);
    }
    public static function removeAll(){
		$cookiesSet = array_keys($_COOKIE);
		for($i=0; $i<count($cookiesSet); $i++){
			static::set($cookiesSet[$i], NULL, time()-3600);
		}		
	}
}


