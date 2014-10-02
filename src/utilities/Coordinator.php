<?php

namespace Bella;

class Coordinator{
	
	private $AS = 0; // accessible scope, default is 0 that means no limit
	private $_name = '';
	
	protected $routes = [];

	public function setAccessibleScope($as){
		$this->AS = $as;
	}
	public function getAccessibleScope(){
		return $this->AS;
	}
	public function checkAccessibleScope(){
		$required = $this->AS;
		$userScope = 0;
		if($required===0){
			return true;
		}
		else{
			$user = Context::get('user');
			if(!!$user && is_object($user)){
				$userScope = $user->ascope;
			}
			if($userScope >= $required){
				return true;
			}
		}
		return false;
	}	
	
	public function setName($n){
		$this->_name = $n;
	}
	public function getName(){
		return $this->_name;
	}	
	
	public function loadHandler(){
		if(!$this->checkAccessibleScope()){
			return $this->deny();
		}
		$n = $this->getName();
		if($n){
			$md = Bella::loadHandler($n);
			if(!!$md && is_object($md)){
				$md->setName($n);
				return $md;
			}
		}
		return $this->deny();
	}

	public function route($regex, $callback){
		$this->routes[$regex] = $callback;
	}
	
	public function start(){
		$routes = Path::get();
		if($routes[0]===$this->_name){
			array_splice($routes, 0, 1);
			
			foreach($this->routes as $regex=>$callback){
				$params = explode('/', $regex);
				if(count($params)>0){
					
					if($params[0]===''){
						array_splice($params, 0, 1);
					}
					
					$data = [];
					$hasAction = false;
					
					for($i=0;$i<count($params);$i++){
						$sec = $params[$i];
						if(strpos($sec, ':')===false){
							if($sec===$routes[$i]){
								$hasAction = true;
							}
						}
						else{
							$m = str_replace(':', '', $sec);
							$data[$m] = $routes[$i];
						}
					}
					if(!!$data || !!$hasAction){
						call_user_func_array($callback, $data);
					}
				}
			}
		}		
	}
		
	public function parse(){
		//
	}
	
	public function deny(){
		return Bella::deny();
	}
}


