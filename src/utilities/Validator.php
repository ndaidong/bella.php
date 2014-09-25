<?php

namespace Bella;

trait Validator{
	
	public static function isValidUsername($username){
		if(!!$username && strlen($username)>4 && strlen($username)<65){
			$avoid = array(
				'user', 
				'operator',
				'moderator', 
				'admin', 
				'administer', 
				'administrator', 
				'system',
				'system.admin',
				'root',
				'mod',
				'tester',
			);
			if(!in_array($username, $avoid)){
				return preg_match('/^[A-Za-z]{1}([A-Za-z0-9]+)([\.]?)([A-Za-z0-9]+)$/', $username);
			}
		}
		return false;
	}
	
	public static function isPassword($password=''){
		return (!!$password && strlen($password)>=6);
	}

	public static function isEmail($email){
	   $isValid = true;
	   $atIndex = strrpos($email, "@");
	   if (is_bool($atIndex) && !$atIndex){
		  $isValid = false;
	   }
	   else{
		  $domain = substr($email, $atIndex+1);
		  $local = substr($email, 0, $atIndex);
		  $localLen = strlen($local);
		  $domainLen = strlen($domain);
		  if($localLen < 1 || $localLen > 64){
			 $isValid = false;
		  }
		  else if($domainLen < 1 || $domainLen > 255){
			 $isValid = false;
		  }
		  else if($local[0] == '.' || $local[$localLen-1] == '.'){
			 $isValid = false;
		  }
		  else if(preg_match('/\\.\\./', $local)){
			 $isValid = false;
		  }
		  else if(!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain)){
			 $isValid = false;
		  }
		  else if(preg_match('/\\.\\./', $domain)){
			 $isValid = false;
		  }
		  else if(!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/', str_replace("\\\\","",$local))){
			 if (!preg_match('/^"(\\\\"|[^"])+"$/', str_replace("\\\\","",$local))){
				$isValid = false;
			 }
		  }
		  if($isValid && !(checkdnsrr($domain,"MX") || checkdnsrr($domain,"A"))){
			 $isValid = false;
		  }
	   }
	   return $isValid;
	}	
	
	public static function isTicket($ticket=''){
		return static::isRandomKey($ticket, 10);
	}
	
	public static function isAccessToken($token=''){
		return static::isRandomKey($token, 128);
	}
	
	public static function isVisa($token=''){
		return static::isRandomKey($token, 81);
	}
	
	public static function isLinkId($id=''){
		return static::isRandomKey($id, 6);
	}
	public static function isUserLinkId($id=''){
		return static::isRandomKey($id, 32);
	}
	
	public static function isRandomKey($key='', $len=128){
		if(!!$key && strlen($key)===$len){
			return preg_match('/^[A-Za-z0-9]+$/', $key);
		}
		return false;
	}
}


