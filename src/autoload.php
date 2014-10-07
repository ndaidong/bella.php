<?php

date_default_timezone_set("GMT");

if(get_magic_quotes_gpc()){
	function stripslashes_deep($value){
		return (is_array($value)?array_map('stripslashes_deep', $value):stripslashes($value));
	}
	$_POST = array_map('stripslashes_deep', $_POST);
	$_GET = array_map('stripslashes_deep', $_GET);
	$_REQUEST = array_map('stripslashes_deep', $_REQUEST);
	$_COOKIE = array_map('stripslashes_deep', $_COOKIE);
}

include_once 'vendor/autoload.php';
		
$path = realpath(dirname( __FILE__ ));
foreach(glob($path.'/utilities/*.php') as $file){
	 include_once $file;
}
include_once $path.'/Bella.php';


