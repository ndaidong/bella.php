<?php

namespace Bella;

trait Response{

	public static function json($data, $end=true){
		return Bella::json($data, $end);
	}
	
	public static function html($str){
		static::write('html', $str);
	}
	
	public static function xml($str){
		static::write('xml', $str);
	}
	
	private static function write($contentType, $content){
		$ct = [
			'html' => 'content-type:text/html;charset=utf-8',
			'xml' => 'content-type:text/xml;charset=utf-8'
		];
		header($ct[$contentType]);
		echo trim($content);
		exit();
	}
}


