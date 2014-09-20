<?php

namespace Bella;

trait Context{
	
	private static $storage = [];
	
	public static function set($key, $value){
		self::$storage[$key] = $value;
	}
	public static function get($key){
		$t = self::$storage;
		if(array_key_exists($key, $t)){
			return $t[$key];
		}
		return false;
	}	
	public static function remove($key){
		if(array_key_exists($key, self::$storage)){
			unset(self::$storage[$key]);
			return count(self::$storage);
		}
		return false;
	}
	public static function getStorage(){
		return self::$storage;
	}
}


