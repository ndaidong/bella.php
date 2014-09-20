<?php

namespace Bella;

trait File{

	public static function write($file, $data){
		if($f = @fopen($file,'wb')){
			if(!is_string($data)){
				 $data = json_encode($data);
			}			
			fwrite($f, $data, 518000); 
			fclose($f);
			return true;
		}
		return false;
	}
	
	public static function read($file, $asJSON=false){
		if(file_exists($file)){
			if($s = @file_get_contents($file)){
				return !!$asJSON?json_decode($s):$s;
			}
		}
		return false;
	}	
	
	public static function delete($file){
		if(file_exists($file)){
			return unlink($file);
		}
		return false;
	}
}


