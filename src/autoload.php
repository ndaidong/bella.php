<?php
$path = realpath(dirname( __FILE__ ));
foreach(glob($path.'/utilities/*.php') as $file){
	 include_once $file;
}
include_once $path.'/Bella.php';


