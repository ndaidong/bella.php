<?php

namespace Bella;

trait Except{
	
    public static function showError($msg = 'Unknown exception'){
		if(Config::get('_debug')==1){
			Response::json(array(
				'error'=>$msg
			));
		}
    }
}


