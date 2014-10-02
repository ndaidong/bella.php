<?php

namespace Bella;

class Remote{

	public static function type($url){
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_exec($ch);
		return curl_getinfo($ch, CURLINFO_CONTENT_TYPE);		
	}	
	
	public static function isConnectable($url){
		$handle   = curl_init($url);
		if(false=== $handle){
			return false;
		}
		set_time_limit(0);
		curl_setopt($handle, CURLOPT_HEADER, false);
		curl_setopt($handle, CURLOPT_FAILONERROR, true);
		curl_setopt($handle, CURLOPT_NOBODY, true);
		curl_setopt($handle, CURLOPT_RETURNTRANSFER, false);
		$connectable = curl_exec($handle);
		curl_close($handle);    
		return $connectable;
	}	
	
	public static function pull($url, $typeOnly=false){
      $s = '';
      $fc = false;
		if(function_exists('curl_init')){	
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
			curl_setopt($ch, CURLOPT_USERAGENT, static::getRandomUserAgent());
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT , 0);
			curl_setopt($ch, CURLOPT_TIMEOUT, 30);
			$type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
			$s = curl_exec($ch);
			curl_close($ch);
			if(!!$typeOnly){
				return $type;
			}
		}
		else if(!$typeOnly && ini_get('allow_url_fopen') == '1'){
			@$data = file_get_contents($url);
			if($data){$s = $data;}
		}
		else{
			trigger_error('Error : Cound not retrieve data from '.$url);	
		}
	  return $s;   
	}
	
	public static function push($url, $data, $wait = true){
		if(is_string($data)){
			$s = $data;
		}
		else if(is_object($data)){
			$s = 'data='.json_encode($data);
		}
		else if(is_array($data)){
			$arr = array();
			foreach($data as $key=>$val){
				array_push($arr, $key.'='.$val);
			}
			$s = implode('&', $arr);
		}
		if(function_exists('curl_init')){
			
			set_time_limit(0);
			
			$ch = @curl_init($url);                                                                      
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
			curl_setopt($ch, CURLOPT_POSTFIELDS, $s);                                                              
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, $wait);
			curl_setopt($ch, CURLOPT_FAILONERROR, !!$wait?0:1);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_USERAGENT, static::getRandomUserAgent());
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT , 0);
			curl_setopt($ch, CURLOPT_TIMEOUT, 120);
			$result = @curl_exec($ch);
			curl_close($ch);
		}
		else{
			$result = file_get_contents($url, null, stream_context_create(array(
				'http' => array(
					'method' => 'POST',
					'header' => 'Content-Type: application/json'."\r\n".'Content-Length: '.strlen($s)."\r\n",
					'content' => $s,
				),
			)));			
		}
		return $result;
	}
	
	public static function getRandomUserAgent(){
		$a = [
			'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
			'Googlebot/2.1 (+http://www.googlebot.com/bot.html)',
			'Googlebot/2.1 (+http://www.google.com/bot.html)',
		];
		
		$k = array_rand($a);
		
		return $a[$k];
	}
}
