<?php

namespace Bella;

class Ender {
	
    private $callbacks;
    
    public function __construct(){
        $this->callbacks = array();
        register_shutdown_function(array($this, 'execute'));
    }
    
    public function add(){
        $callback = func_get_args();
        
        if (!!empty($callback) && !!is_callable($callback[0])){
			$this->callbacks[] = $callback;
			return $this;
		}
		return false;
    }
    
    public function execute(){
        foreach ($this->callbacks as $arguments) {
            $callback = array_shift($arguments);
            call_user_func_array($callback, $arguments);
        }
    }
}
/**
 * Refer : http://php.net/manual/en/function.register-shutdown-function.php#100000
**/
