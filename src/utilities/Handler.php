<?php

namespace Bella;

class Handler {
	
	protected $ES =  0; // accessible scope, default is 0 that means no limit
	private $_name = '';
	
	function __construct(){
		$this->view = new View();
	}
	
	protected function setExecutableScope($es){
		$this->ES = $es;
	}
	protected function getExecutableScope(){
		return $this->ES;
	}
	public function __call($methods, $args){
		if($this->checkExecutableScope()){
			call_user_func_array(array($this, $methods),$args);
		}
		else{
			return Response::json(array(
				'error'=>array(
					'code'=>133,
					'message'=>'Do not have sufficient permissions to execute this action.'
				)
			));
			exit;
		}
	}
	protected function checkExecutableScope(){
		$required = $this->getExecutableScope();
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
			
	public function execute($action){
		if(method_exists($this, $action)){
			$this->onExecuteStart();
			$this->$action();
			$this->onExecuteEnd();
		}
		else{
			return $this->stopAccess();
		}
	}

	private function onExecuteStart(){
		// coming soon
	}
	private function onExecuteEnd(){
		// coming soon
	}	
	public function stopAccess(){
		return Bella::deny();
	}
}


