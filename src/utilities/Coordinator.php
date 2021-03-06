<?php

namespace Bella;

class Coordinator{
	
	private $AS = 0; // accessible scope, default is 0 that means no limit
	private $_name = '';

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
	
	protected function route($method, $regex, $callback){
		Router::add($method, $regex, $callback);
	}
	protected function get($regex, $callback){
		Router::get($regex, $callback);
	}
	protected function post($regex, $callback){
		Router::post($regex, $callback);
	}
	protected function all($regex, $callback){
		Router::all($regex, $callback);
	}
	
	public function start(){
		Router::parse();
	}
		
	public function parse(){
		//
	}
	
	public function deny(){
		return Bella::deny();
	}
}


